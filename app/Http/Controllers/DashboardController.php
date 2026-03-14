<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Fetch Referrals for Kanban
        $referralsQuery = \App\Models\Referral::with(['patient', 'sender', 'receiver']);
        
        if ($user->isGP()) {
            $referralsQuery->where('sender_id', $user->id);
        } elseif ($user->isSpecialist()) {
            $referralsQuery->where('receiver_id', $user->id);
        } // Admin sees all

        $referrals = $referralsQuery->get();
        
        $kanban = [
            'Pending' => $referrals->where('status', 'Pending'),
            'In Review' => $referrals->where('status', 'In Review'),
            'Completed' => $referrals->where('status', 'Completed'),
        ];

        // Monthly Health Analytics (Most frequent diagnosis types current month)
        $currentMonth = \Carbon\Carbon::now()->month;
        $currentYear = \Carbon\Carbon::now()->year;

        $analyticsQuery = \App\Models\MedicalRecord::selectRaw('diagnosis, count(*) as count')
            ->whereMonth('visit_date', $currentMonth)
            ->whereYear('visit_date', $currentYear);
        
        // Filter analytics by user visibility if strictly required, but usually admins/GPs see an aggregate
        // Let's assume clinic-wide analytics for simplicity, but strictly speaking Specialists might only see their own.
        // We'll show aggregate.
        
        $analytics = $analyticsQuery->groupBy('diagnosis')
            ->orderByDesc('count')
            ->take(5)
            ->get();

        return view('dashboard', compact('kanban', 'analytics', 'user'));
    }
}
