<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminRsvpController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

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

        $filtered = match($filter) {
            'attending' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'attending'),
            'declining' => $guests->filter(fn($u) => $u->guestProfile?->participation === 'declining'),
            'pending'   => $guests->filter(fn($u) => !$u->guestProfile || $u->guestProfile->participation === 'pending'),
            default     => $guests,
        };

        // 出席者の合計人数（大人＋子供）
        $totalAttending = $guests
            ->filter(fn($u) => $u->guestProfile?->participation === 'attending')
            ->sum(fn($u) => $u->guestProfile->attending_count ?? 0);

        $totalChildren = $guests
            ->filter(fn($u) => $u->guestProfile?->participation === 'attending')
            ->sum(fn($u) => $u->guestProfile->children_count ?? 0);

        return view('admin.rsvp', compact(
            'filtered', 'summary', 'filter', 'totalAttending', 'totalChildren'
        ));
    }
}
