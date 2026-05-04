<?php

namespace App\Http\Controllers;

use App\Models\GuestProfile;
use App\Models\CheckInAuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AdminOperationsController extends Controller
{
    public function index(): View
    {
        return view('admin.ops', $this->payload());
    }

    public function metrics(): JsonResponse
    {
        return response()->json($this->payload());
    }

    private function payload(): array
    {
        $guests = User::where('role', 'guest')
            ->with(['guestProfile', 'seatAssignment.seat.seatingTable'])
            ->orderBy('created_at')
            ->get();

        $attending = $guests->filter(fn ($u) => $u->guestProfile?->participation === 'attending');
        $declining = $guests->filter(fn ($u) => $u->guestProfile?->participation === 'declining');
        $pending   = $guests->filter(fn ($u) => ! $u->guestProfile || $u->guestProfile->participation === 'pending');
        $assigned  = $guests->filter(fn ($u) => $u->seatAssignment !== null);
        $checkedInCount = GuestProfile::whereNotNull('checked_in_at')->count();
        $checkedInPeople = GuestProfile::whereNotNull('checked_in_at')->sum('attending_count');
        $checkedInProfiles = GuestProfile::with(['user.seatAssignment.seat.seatingTable', 'checkedInBy'])
            ->whereNotNull('checked_in_at')
            ->orderByDesc('checked_in_at')
            ->limit(8)
            ->get();

        $summary = [
            'total'               => $guests->count(),
            'attending'           => $attending->count(),
            'declining'           => $declining->count(),
            'pending'             => $pending->count(),
            'checked_in_guests'   => $checkedInCount,
            'checked_in_people'   => $checkedInPeople,
            'seat_assigned'       => $assigned->count(),
            'checkin_pending'     => max(0, $attending->count() - $checkedInCount),
            'response_rate'       => $guests->count() > 0
                ? round((($guests->count() - $pending->count()) / $guests->count()) * 100)
                : 0,
        ];

        $recentResponses = $guests
            ->filter(fn ($u) => $u->guestProfile?->responded_at)
            ->sortByDesc(fn ($u) => $u->guestProfile?->responded_at)
            ->take(6)
            ->values()
            ->map(fn ($u) => $this->guestSnapshot($u));

        $recentCheckins = $checkedInProfiles->map(fn ($profile) => [
            'id'           => $profile->id,
            'name'         => $profile->fullName(),
            'username'     => $profile->user?->username ?? '',
            'checked_at'   => optional($profile->checked_in_at)->format('Y/m/d H:i'),
            'table'        => $profile->user?->seatAssignment?->seat?->seatingTable?->name,
            'seat'         => $profile->user?->seatAssignment?->seat?->label,
            'by'           => $profile->checkedInBy?->name,
        ]);

        $recentOperationLogs = CheckInAuditLog::with(['guestProfile.user', 'actor'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'message' => $log->message,
                'source' => $log->source,
                'at' => $log->created_at?->format('Y/m/d H:i'),
                'guest' => $log->guestProfile?->fullName() ?: $log->guestProfile?->user?->name,
                'actor' => $log->actor?->name,
            ]);

        return compact('summary', 'recentResponses', 'recentCheckins', 'recentOperationLogs');
    }

    private function guestSnapshot(User $user): array
    {
        $profile = $user->guestProfile;

        return [
            'id'         => $user->id,
            'name'       => $profile?->fullName() ?: $user->name,
            'username'   => $user->username,
            'status'     => $profile?->participation ?? 'pending',
            'responded'  => optional($profile?->responded_at)->format('Y/m/d H:i'),
            'table'      => $user->seatAssignment?->seat?->seatingTable?->name,
            'seat'       => $user->seatAssignment?->seat?->label,
            'checkin'    => $profile?->isCheckedIn() ? '受付済み' : '未受付',
        ];
    }
}
