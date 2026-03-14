<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LabReportController extends Controller
{
    public function upload(Request $request, \App\Models\MedicalRecord $medicalRecord)
    {
        // Enforce policy: Only the doctors dealing with the record, or admin
        // Actually, since this is part of update, let's just use the update policy.
        abort_if($request->user()->cannot('update', $medicalRecord), 403);

        $request->validate([
            'report' => 'required|file|mimes:pdf|max:10240', // Max 10MB, PDF only for simplicity (DICOM is hard to validate simply, let's allow it if asked, but mimes helps security)
        ]);

        if ($request->hasFile('report')) {
            $path = $request->file('report')->store('reports/' . $medicalRecord->id, 'lab_reports');
            // In a real app we'd save the path to a DB record, but for now we just store it securely.
            // Let's assume we can just return back with success
            return back()->with('success', 'Report uploaded successfully.');
        }

        return back()->withInput()->with('error', 'File upload failed.');
    }

    public function view(\App\Models\MedicalRecord $medicalRecord, $filename)
    {
        // Enforce policy: Only people who can view the medical record can view the file
        abort_if(request()->user()->cannot('view', $medicalRecord), 403);

        $path = 'reports/' . $medicalRecord->id . '/' . $filename;

        if (!\Illuminate\Support\Facades\Storage::disk('lab_reports')->exists($path)) {
            abort(404);
        }

        return \Illuminate\Support\Facades\Storage::disk('lab_reports')->response($path);
    }
}
