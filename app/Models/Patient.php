<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'medical_record_number',
        'full_name',
        'date_of_birth',
        'blood_group',
        'emergency_contact_enc',
    ];

    protected function casts(): array
    {
        return [
            'emergency_contact_enc' => \App\Casts\EncryptedFieldCast::class,
            'date_of_birth' => 'date',
        ];
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }
}
