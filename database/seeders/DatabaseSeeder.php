<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Referral;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Users ───────────────────────────────────────────────────
        $defaultPassword = Hash::make('ChangeMe@123');

        $admin = User::create([
            'name'          => 'Alice Admin',
            'email'         => 'admin@mediconnect.test',
            'password'      => $defaultPassword,
            'employee_id'   => 'EMP-001',
            'role'          => 'Admin',
            'specialization'=> null,
        ]);

        // 3 General Practitioners
        $gps = collect([
            ['name' => 'Dr. John Williams',   'email' => 'gp1@mediconnect.test', 'employee_id' => 'EMP-002', 'specialization' => 'Family Medicine'],
            ['name' => 'Dr. Sarah Chen',      'email' => 'gp2@mediconnect.test', 'employee_id' => 'EMP-003', 'specialization' => 'General Practice'],
            ['name' => 'Dr. Marcus Johnson',  'email' => 'gp3@mediconnect.test', 'employee_id' => 'EMP-004', 'specialization' => 'Internal Medicine'],
        ])->map(fn($data) => User::create([...$data, 'password' => $defaultPassword, 'role' => 'GP']));

        // 3 Specialists
        $specialists = collect([
            ['name' => 'Dr. Emily Harper',  'email' => 'spec1@mediconnect.test', 'employee_id' => 'EMP-005', 'specialization' => 'Cardiology'],
            ['name' => 'Dr. Ravi Patel',    'email' => 'spec2@mediconnect.test', 'employee_id' => 'EMP-006', 'specialization' => 'Neurology'],
            ['name' => 'Dr. Lena Schmidt',  'email' => 'spec3@mediconnect.test', 'employee_id' => 'EMP-007', 'specialization' => 'Oncology'],
        ])->map(fn($data) => User::create([...$data, 'password' => $defaultPassword, 'role' => 'Specialist']));

        // ─── Patients ─────────────────────────────────────────────────
        $patientsData = [
            ['full_name' => 'James Carter',      'dob' => '1978-04-12', 'blood_group' => 'A+',  'emergency_contact' => '+1-555-0101'],
            ['full_name' => 'Maria Lopez',       'dob' => '1990-11-03', 'blood_group' => 'O-',  'emergency_contact' => '+1-555-0102'],
            ['full_name' => 'Kevin Nguyen',      'dob' => '1965-07-28', 'blood_group' => 'B+',  'emergency_contact' => '+1-555-0103'],
            ['full_name' => 'Priya Sharma',      'dob' => '1985-02-17', 'blood_group' => 'AB+', 'emergency_contact' => '+1-555-0104'],
            ['full_name' => 'Thomas Müller',     'dob' => '1952-09-30', 'blood_group' => 'A-',  'emergency_contact' => '+49-555-0105'],
            ['full_name' => 'Aisha Okonkwo',     'dob' => '2000-01-14', 'blood_group' => 'O+',  'emergency_contact' => '+1-555-0106'],
            ['full_name' => 'Robert Park',       'dob' => '1973-06-22', 'blood_group' => 'B-',  'emergency_contact' => '+1-555-0107'],
            ['full_name' => 'Nina Kowalski',     'dob' => '1988-12-09', 'blood_group' => 'A+',  'emergency_contact' => '+48-555-0108'],
            ['full_name' => 'David Osei',        'dob' => '1961-03-05', 'blood_group' => 'O+',  'emergency_contact' => '+1-555-0109'],
            ['full_name' => 'Fatima Al-Hassan',  'dob' => '1995-08-19', 'blood_group' => 'AB-', 'emergency_contact' => '+1-555-0110'],
        ];

        $patients = [];
        foreach ($patientsData as $i => $pd) {
            $patients[] = Patient::create([
                'medical_record_number' => 'MRN-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'full_name'             => $pd['full_name'],
                'date_of_birth'         => $pd['dob'],
                'blood_group'           => $pd['blood_group'],
                'emergency_contact_enc' => $pd['emergency_contact'],
            ]);
        }

        // ─── Medical Records ─────────────────────────────────────────
        $diagnoses = [
            ['diagnosis' => 'Hypertension Stage II',    'symptoms' => 'Persistent headaches, shortness of breath, dizziness',  'treatment' => "Amlodipine 5mg daily\nLifestyle modifications: low-sodium diet, 30min daily exercise\nFollow-up in 4 weeks"],
            ['diagnosis' => 'Type 2 Diabetes Mellitus', 'symptoms' => 'Polyuria, polydipsia, fatigue, blurred vision',          'treatment' => "Metformin 500mg twice daily with meals\nDietary counselling\nHbA1c recheck in 3 months"],
            ['diagnosis' => 'Acute Myocardial Infarction', 'symptoms' => 'Severe chest pain, diaphoresis, nausea',              'treatment' => "Aspirin 325mg stat\nNitroglycerine sublingual\nPCI referral to Cardiology — URGENT\nClopidogrel 75mg daily"],
            ['diagnosis' => 'Migraine with Aura',        'symptoms' => 'Unilateral headache, photophobia, visual scotoma',      'treatment' => "Sumatriptan 50mg as needed\nTopiramate 25mg nightly prophylaxis\nTrigger diary"],
            ['diagnosis' => 'Lumbar Disc Herniation',    'symptoms' => 'Lower back pain radiating to left leg, paraesthesia',   'treatment' => "Naproxen 500mg twice daily\nPhysiotherapy referral\nMRI lumbar spine ordered"],
            ['diagnosis' => 'Community-Acquired Pneumonia', 'symptoms' => 'Productive cough, fever 38.9°C, pleuritic chest pain', 'treatment' => "Amoxicillin-Clavulanate 875mg twice daily × 7 days\nChest X-ray in 3 weeks"],
            ['diagnosis' => 'Hypothyroidism',             'symptoms' => 'Fatigue, weight gain, cold intolerance, depression',   'treatment' => "Levothyroxine 50mcg daily (titrate per TSH)\nTSH recheck in 6 weeks"],
            ['diagnosis' => 'Anxiety Disorder',           'symptoms' => 'Palpitations, excessive worry, insomnia, tremor',      'treatment' => "Sertraline 50mg daily\nCognitive Behavioural Therapy referral\nReview in 1 month"],
            ['diagnosis' => 'Chronic Kidney Disease (Stage 3)', 'symptoms' => 'Peripheral oedema, foamy urine, fatigue',        'treatment' => "ACE inhibitor adjusted dose\nDiet: low-protein, low-potassium\nNephrology referral"],
            ['diagnosis' => 'Breast Cancer (Stage II)',   'symptoms' => 'Painless breast lump, skin dimpling, axillary lymphadenopathy', 'treatment' => "Oncology referral — URGENT\nBiopsy ordered\nMammography + ultrasound complete"],
        ];

        $visitBase = Carbon::now()->subMonths(2);
        $records = [];
        foreach ($patients as $i => $patient) {
            $d = $diagnoses[$i];
            $records[] = MedicalRecord::create([
                'patient_id'     => $patient->id,
                'diagnosis'      => $d['diagnosis'],
                'symptoms'       => $d['symptoms'],
                'treatment_plan' => $d['treatment'],
                'visit_date'     => $visitBase->copy()->addDays($i * 5),
            ]);
        }

        // Second visit for some patients (current month for analytics)
        $currentMonth = Carbon::now()->startOfMonth();
        foreach (array_slice($patients, 0, 5) as $i => $patient) {
            $d = $diagnoses[$i];
            MedicalRecord::create([
                'patient_id'     => $patient->id,
                'diagnosis'      => $d['diagnosis'],
                'symptoms'       => $d['symptoms'] . ' (follow-up)',
                'treatment_plan' => $d['treatment'] . "\n\nFollow-up notes: Patient responding to treatment.",
                'visit_date'     => $currentMonth->copy()->addDays($i * 3),
            ]);
        }

        // ─── Referrals ────────────────────────────────────────────────
        $referralsData = [
            // GP1 → Cardiologist for Patient 3 (MI)
            ['patient' => $patients[2], 'sender' => $gps[0], 'receiver' => $specialists[0], 'urgency' => 'Emergency', 'status' => 'In Review'],
            // GP2 → Neurologist for Patient 4 (Migraine)
            ['patient' => $patients[3], 'sender' => $gps[1], 'receiver' => $specialists[1], 'urgency' => 'Urgent',    'status' => 'Pending'],
            // GP3 → Oncologist for Patient 10 (Breast Cancer)
            ['patient' => $patients[9], 'sender' => $gps[2], 'receiver' => $specialists[2], 'urgency' => 'Emergency', 'status' => 'Pending'],
            // GP1 → Cardiologist for Patient 1 (Hypertension)
            ['patient' => $patients[0], 'sender' => $gps[0], 'receiver' => $specialists[0], 'urgency' => 'Routine',   'status' => 'Completed'],
            // GP2 → Neurologist for Patient 9 (CKD)
            ['patient' => $patients[8], 'sender' => $gps[1], 'receiver' => $specialists[1], 'urgency' => 'Urgent',    'status' => 'In Review'],
        ];

        foreach ($referralsData as $r) {
            Referral::create([
                'patient_id'    => $r['patient']->id,
                'sender_id'     => $r['sender']->id,
                'receiver_id'   => $r['receiver']->id,
                'urgency_level' => $r['urgency'],
                'status'        => $r['status'],
                'created_at'    => Carbon::now()->subDays(rand(1, 10)),
            ]);
        }

        $this->command->info('✅  MediConnect seeded successfully.');
        $this->command->line('  Default password for all users: ChangeMe@123');
        $this->command->line('  Admin: admin@mediconnect.test');
        $this->command->line('  GP1:   gp1@mediconnect.test');
        $this->command->line('  Spec1: spec1@mediconnect.test');
    }
}
