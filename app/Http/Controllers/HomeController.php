<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $user->load('guestProfile');

        $setting = WeddingSetting::first();

        return view('home', [
            'user'           => $user,
            'profile'        => $user->guestProfile,
            'setting'        => $setting,
            'deadlinePassed' => $setting?->rsvp_deadline !== null
                && today()->isAfter($setting->rsvp_deadline),
        ]);
    }
}
