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

        $summary = [
            'total'     => $guests->count(),
            'attending' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'attending')->count(),
            'declining' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'declining')->count(),
            'pending'   => $guests->filter(fn($u) => !$u->guestProfile || $u->guestProfile->participation === 'pending')->count(),
        ];

        return view('admin.dashboard', compact('guests', 'summary'));
    }
}
