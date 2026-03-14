<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $fillable = [
        'patient_id',
        'diagnosis',
        'symptoms',
        'treatment_plan',
        'visit_date',
    ];

    protected function casts(): array
    {
        return [
            'treatment_plan' => \App\Casts\EncryptedFieldCast::class,
            'visit_date' => 'date',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
