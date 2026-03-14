<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (!$query) {
            return response()->json([]);
        }

        $patients = \App\Models\Patient::where('medical_record_number', 'LIKE', "%{$query}%")
            ->orWhere('full_name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($patients);
    }
}
