<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $guests = User::where('role', 'guest')
            ->with('guestProfile')
            ->orderBy('created_at')
            ->get();

        $attending = $guests->filter(fn($u) => $u->guestProfile?->participation === 'attending');
        $declining = $guests->filter(fn($u) => $u->guestProfile?->participation === 'declining');
        $pending   = $guests->filter(fn($u) => !$u->guestProfile || $u->guestProfile->participation === 'pending');
        $responded = $guests->count() - $pending->count();

        $summary = [
            'total'          => $guests->count(),
            'attending'      => $attending->count(),
            'declining'      => $declining->count(),
            'pending'        => $pending->count(),
            'people_count'   => $attending->sum(fn($u) => $u->guestProfile?->attending_count ?? 0),
            'allergy_count'  => $attending->filter(fn($u) => $u->guestProfile?->has_allergy)->count(),
            'response_rate'  => $guests->count() > 0
                ? round(($responded / $guests->count()) * 100)
                : 0,
        ];

        return view('admin.dashboard', compact('guests', 'summary'));
    }
}
