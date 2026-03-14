<?php

namespace App\Policies;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReferralPolicy
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
    public function view(User $user, Referral $referral): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $referral->sender_id || $user->id === $referral->receiver_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isGP();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Referral $referral): bool
    {
        if ($user->isAdmin()) return true;

        // GP can update if pending (e.g. change receiver or urgency)
        if ($user->id === $referral->sender_id && $referral->status === 'Pending') {
            return true;
        }

        // Specialist can update status (Pending -> In Review -> Completed)
        if ($user->id === $referral->receiver_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Referral $referral): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Referral $referral): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Referral $referral): bool
    {
        return $user->isAdmin();
    }
}
