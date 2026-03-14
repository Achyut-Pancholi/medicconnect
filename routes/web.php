<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

// Basic Auth Routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

Route::middleware(['auth'])->group(function () {
    // Password Change routes (accessible even if ForcePasswordChange triggers, because of logic in middleware)
    Route::get('/password/change', function () {
        return view('auth.change-password');
    })->name('password.change.show');

    Route::post('/password/change', function (Request $request) {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);
        $user = $request->user();
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();
        return redirect()->route('dashboard')->with('success', 'Password updated successfully.');
    })->name('password.change.update');
});

Route::middleware(['auth', \App\Http\Middleware\ForcePasswordChange::class])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Referrals (Update Status, Create)
    Route::post('/referrals', function(Request $request) {
        // Create referral logic simply here or in controller
        abort_if($request->user()->cannot('create', \App\Models\Referral::class), 403);
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'receiver_id' => 'required|exists:users,id',
            'urgency_level' => 'required|in:Routine,Urgent,Emergency'
        ]);
        
        \App\Models\Referral::create([
            'patient_id' => $request->patient_id,
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
            'urgency_level' => $request->urgency_level,
            'status' => 'Pending',
        ]);
        return back()->with('success', 'Referral created.');
    })->name('referrals.store');
    
    Route::put('/referrals/{referral}/status', function(Request $request, \App\Models\Referral $referral) {
        abort_if($request->user()->cannot('update', $referral), 403);
        $request->validate(['status' => 'required|in:Pending,In Review,Completed']);
        $referral->update(['status' => $request->status]);
        return back()->with('success', 'Referral status updated.');
    })->name('referrals.updateStatus');

    // Patient Search APIs (Using exact requirement: AJAX Vanilla JS search)
    Route::get('/api/patients/search', [\App\Http\Controllers\PatientController::class, 'search'])->name('patients.search');

    // Get specific Medical Records for a patient (Basic view for specialist/GP)
    Route::get('/patients/{patient}/records', function(\App\Models\Patient $patient) {
        $user = request()->user();

        // Data Privacy Enhancement: Specialists can only access records for referred patients.
        if ($user->isSpecialist()) {
            $hasReferral = \App\Models\Referral::where('patient_id', $patient->id)
                ->where('receiver_id', $user->id)
                ->exists();
            abort_unless($hasReferral, 403, 'Unauthorized access to patient record.');
        }
        
        $records = $patient->medicalRecords()->get();
        // Filter those the user can view using policy
        $visibleRecords = $records->filter(function($record) use ($user) {
            return $user->can('view', $record);
        });
        
        // This would render a view of the records
        return view('patients.records', compact('patient', 'visibleRecords'));
    })->name('patients.records');

    // Export PDF
    Route::get('/records/{medicalRecord}/export', [\App\Http\Controllers\PrescriptionController::class, 'export'])->name('records.export');

    // Lab Reports
    Route::post('/records/{medicalRecord}/report', [\App\Http\Controllers\LabReportController::class, 'upload'])->name('reports.upload');
    Route::get('/records/{medicalRecord}/report/{filename}', [\App\Http\Controllers\LabReportController::class, 'view'])->name('reports.view');
});
