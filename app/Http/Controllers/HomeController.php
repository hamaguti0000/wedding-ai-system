<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
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

        $user->load(['guestProfile', 'taskAssignments.task']);

        $setting = WeddingSetting::first();

        return view('home', [
            'user'           => $user,
            'profile'        => $user->guestProfile,
            'setting'        => $setting,
            'homeTasks'      => $user->taskAssignments->filter(
                fn($a) => $a->task?->display_on_home
            ),
            'deadlinePassed' => $setting?->rsvp_deadline !== null
                && today()->isAfter($setting->rsvp_deadline),
            'news' => NewsItem::where('is_active', true)
                ->orderBy('sort_order')->orderByDesc('published_date')
                ->limit(5)->get(),
        ]);
    }
}
