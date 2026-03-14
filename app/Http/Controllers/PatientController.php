<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function search(Request $request)
    {
        $user = $request->user();
        $query = $request->get('q');
        
        if (!$query) {
            return response()->json([]);
        }

        $patientsQuery = \App\Models\Patient::where(function($q) use ($query) {
            $q->where('medical_record_number', 'LIKE', "%{$query}%")
              ->orWhere('full_name', 'LIKE', "%{$query}%");
        });

        // Data Privacy Enhancement: Specialists only see referred patients.
        // GPs and Admins need global search to identify existing patients for referrals.
        if ($user->isSpecialist()) {
            $patientsQuery->whereHas('referrals', function($q) use ($user) {
                $q->where('receiver_id', $user->id);
            });
        }

        $patients = $patientsQuery->limit(10)->get();

        return response()->json($patients);
    }
}
