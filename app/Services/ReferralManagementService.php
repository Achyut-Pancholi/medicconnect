<?php

namespace App\Services;

use App\Models\Referral;
use Carbon\Carbon;

class ReferralManagementService
{
    /**
     * Escalate urgency level for referrals not accepted within 48 hours.
     */
    public function escalateUrgency()
    {
        $referrals = Referral::where('status', 'Pending')
            ->where('created_at', '<', Carbon::now()->subHours(48))
            ->get();

        foreach ($referrals as $referral) {
            if ($referral->urgency_level === 'Routine') {
                $referral->urgency_level = 'Urgent';
            } elseif ($referral->urgency_level === 'Urgent') {
                $referral->urgency_level = 'Emergency';
            }
            $referral->save();
        }
    }
}
