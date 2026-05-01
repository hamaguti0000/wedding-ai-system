<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['guestProfile', 'taskAssignments.task']);

        return view('profile', [
            'user'    => $user,
            'profile' => $user->guestProfile,
            'setting' => WeddingSetting::first(),
            'tasks'   => $user->taskAssignments,
        ]);
    }
}
