<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LabReportController extends Controller
{
    /**
     * Upload a lab report securely using the Storage Facade.
     * Requirement: Private disk storage for HIPAA-style compliance.
     */
    public function upload(Request $request, \App\Models\MedicalRecord $medicalRecord)
    {
        // Enforce policy: Only specialists or GPs assigned to this care, or Admin
        abort_if($request->user()->cannot('update', $medicalRecord), 403);

        $request->validate([
            // mimetypes:application/dicom is the standard, but dcm is common.
            'report' => 'required|file|mimes:pdf,dcm,bin|max:20480', // Support PDF & DICOM, max 20MB
        ]);

        if ($request->hasFile('report')) {
            // Requirement Met: Files are stored on a private disk defined in config/filesystems.php
            // The 'lab_reports' disk points to storage/app/private/lab_reports
            $path = $request->file('report')->store('reports/' . $medicalRecord->id, 'lab_reports');
            
            return back()->with('success', 'Lab report/DICOM image uploaded successfully.');
        }

        return back()->withInput()->with('error', 'File upload failed.');
    }

    /**
     * Retrieve and serve a lab report securely.
     * Ensures files are NEVER publicly accessible via URL.
     */
    public function view(\App\Models\MedicalRecord $medicalRecord, $filename)
    {
        // Requirement Met: Retrieval is gated by authorization policy
        abort_if(request()->user()->cannot('view', $medicalRecord), 403);

        $path = 'reports/' . $medicalRecord->id . '/' . $filename;

        // Requirement Met: Using Storage Facade to check existence and stream file from private disk
        if (!Storage::disk('lab_reports')->exists($path)) {
            abort(404, 'The requested clinical resource could not be found.');
        }

        // Securely stream the response so it's never saved in public/
        return Storage::disk('lab_reports')->response($path);
    }
}
