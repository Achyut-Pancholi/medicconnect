<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicalRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MedicalRecord $medicalRecord): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGP()) {
            // GPs can view records of patients they have referred OUT
            $hasReferredOut = \App\Models\Referral::where('sender_id', $user->id)
                ->where('patient_id', $medicalRecord->patient_id)
                ->exists();
            // Or if they are the primary doctor (we'll assume GPs can see any record for a patient they referred at least once)
            return $hasReferredOut;
        }

        if ($user->isSpecialist()) {
            // Specialists can view records of patients referred TO them
            return \App\Models\Referral::where('receiver_id', $user->id)
                ->where('patient_id', $medicalRecord->patient_id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isGP() || $user->isSpecialist();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MedicalRecord $medicalRecord): bool
    {
        // Only the GP or Specialist involved in the patient's care could update it, or Admin
        return $this->view($user, $medicalRecord);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, MedicalRecord $medicalRecord): bool
    {
        return $user->isAdmin();
    }
}
