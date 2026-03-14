@extends('layouts.app')

@section('title', 'Dashboard - MediConnect')

@section('content')
<div class="dashboard-header">
    <h1>Welcome, {{ $user->name }}</h1>
    <div class="patient-search-container">
        <input type="text" id="patient-search" placeholder="Search Patients by MRN or Name..." autocomplete="off">
        <div id="search-results" class="search-results-dropdown"></div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="kanban-board">
        <h2>Referral Pipeline</h2>
        <div class="kanban-columns">
            @foreach(['Pending', 'In Review', 'Completed'] as $status)
            <div class="kanban-column" id="col-{{ Str::slug($status) }}">
                <h3>{{ $status }}</h3>
                <div class="kanban-cards">
                    @forelse($kanban[$status] as $referral)
                    <div class="kanban-card urgency-{{ strtolower($referral->urgency_level) }}">
                        <p><strong>Patient:</strong> <a href="{{ route('patients.records', $referral->patient_id) }}">{{ $referral->patient->full_name }}</a> ({{ $referral->patient->medical_record_number }})</p>
                        <p><strong>GP:</strong> {{ $referral->sender->name }}</p>
                        <p><strong>Specialist:</strong> {{ $referral->receiver->name }}</p>
                        <p><strong>Urgency:</strong> <span class="badge badge-{{ strtolower($referral->urgency_level) }}">{{ $referral->urgency_level }}</span></p>
                        
                        @if($user->can('update', $referral))
                        <form method="POST" action="{{ route('referrals.updateStatus', $referral->id) }}">
                            @csrf
                            @method('PUT')
                            <select name="status" onchange="this.form.submit()">
                                <option value="Pending" {{ $referral->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="In Review" {{ $referral->status == 'In Review' ? 'selected' : '' }}>In Review</option>
                                <option value="Completed" {{ $referral->status == 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </form>
                        @endif
                    </div>
                    @empty
                    <p class="empty-state">No referrals</p>
                    @endforelse
                </div>
            </div>
            @endforeach
        </div>
        
        @if($user->isGP())
        <div style="margin-top: 20px;">
            <h3>Create New Referral</h3>
            <form method="POST" action="{{ route('referrals.store') }}" class="referral-form">
                @csrf
                <!-- In a real app these would be searchable dropdowns. For now, simple inputs -->
                <input type="number" name="patient_id" placeholder="Patient ID" required>
                <input type="number" name="receiver_id" placeholder="Specialist ID" required>
                <select name="urgency_level" required>
                    <option value="Routine">Routine</option>
                    <option value="Urgent">Urgent</option>
                    <option value="Emergency">Emergency</option>
                </select>
                <button type="submit" class="btn btn-primary">Create Referral</button>
            </form>
        </div>
        @endif
    </div>

    <div class="analytics-panel">
        <h2>Monthly Health Analytics</h2>
        <p>Top conditions this month:</p>
        <ul class="analytics-list">
            @forelse($analytics as $stat)
            <li>
                <span class="diagnosis-name">{{ $stat->diagnosis }}</span>
                <span class="diagnosis-count">{{ $stat->count }} cases</span>
            </li>
            @empty
            <li>No data for this month.</li>
            @endforelse
        </ul>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('patient-search');
        const resultsContainer = document.getElementById('search-results');

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length < 2) {
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';
                return;
            }

            fetch(`{{ route('patients.search') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(patient => {
                            const div = document.createElement('div');
                            div.className = 'search-result-item';
                            div.innerHTML = `<strong>${patient.medical_record_number}</strong> - ${patient.full_name}`;
                            // The URL assumes route is patients/{patient}/records
                            div.onclick = function() {
                                window.location.href = `/patients/${patient.id}/records`;
                            };
                            resultsContainer.appendChild(div);
                        });
                        resultsContainer.style.display = 'block';
                    } else {
                        resultsContainer.innerHTML = '<div class="search-result-empty">No patients found.</div>';
                        resultsContainer.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error fetching patients:', error));
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!searchInput.contains(event.target) && !resultsContainer.contains(event.target)) {
                resultsContainer.style.display = 'none';
            }
        });
    });
</script>
@endsection
