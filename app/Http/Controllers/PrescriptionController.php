<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrescriptionController extends Controller
{
    public function export(\App\Models\MedicalRecord $medicalRecord)
    {
        // Enforce policy: Only the doctor or admin can export this specific record
        abort_if(request()->user()->cannot('view', $medicalRecord), 403);

        $medicalRecord->load('patient');
        
        $data = [
            'patient' => $medicalRecord->patient,
            'record' => $medicalRecord,
            'doctor' => request()->user(), // The doctor exporting it
            'date' => \Carbon\Carbon::now()->format('Y-m-d')
        ];

        // We load a view 'pdf.prescription' with the data
        $pdf = Pdf::loadView('pdf.prescription', $data);

        return $pdf->download('prescription_' . $medicalRecord->patient->medical_record_number . '.pdf');
    }
}
