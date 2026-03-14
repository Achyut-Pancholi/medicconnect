@extends('layouts.app')

@section('title', 'Patient Records - ' . $patient->full_name)

@section('content')
<div class="patient-header">
    <h2>Patient: {{ $patient->full_name }}</h2>
    <p><strong>MRN:</strong> {{ $patient->medical_record_number }}</p>
    <p><strong>DOB:</strong> {{ $patient->date_of_birth->format('Y-m-d') }}</p>
    <p><strong>Blood Group:</strong> {{ $patient->blood_group }}</p>
    <p><strong>Emergency Contact:</strong> {{ $patient->emergency_contact_enc }}</p>
</div>

<div class="records-container">
    <h3>Medical Records</h3>
    @forelse($visibleRecords as $record)
    <div class="record-card">
        <div class="record-header">
            <h4>Visit on {{ $record->visit_date->format('Y-m-d') }}</h4>
            <a href="{{ route('records.export', $record->id) }}" class="btn btn-secondary btn-sm" target="_blank">Export PDF</a>
        </div>
        <p><strong>Diagnosis:</strong> {{ $record->diagnosis }}</p>
        <p><strong>Symptoms:</strong> {{ $record->symptoms }}</p>
        <p><strong>Treatment Plan:</strong> {{ $record->treatment_plan }}</p>
        
        <div class="lab-reports-section mt-3">
            <h5>Lab Reports</h5>
            <!-- In a complete app, we'd list uploaded reports. For simplicity, we just provide upload here -->
            <form action="{{ route('reports.upload', $record->id) }}" method="POST" enctype="multipart/form-data" class="upload-form">
                @csrf
                <input type="file" name="report" accept=".pdf" required>
                <button type="submit" class="btn btn-primary btn-sm">Upload Report (PDF)</button>
            </form>
        </div>
    </div>
    @empty
    <p class="empty-state">No accessible medical records found for this patient.</p>
    @endforelse
</div>
@endsection
