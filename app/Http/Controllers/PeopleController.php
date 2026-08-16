<?php

namespace App\Http\Controllers;

use App\Models\User;

class PeopleController extends Controller
{
    public function index()
    {
        $people = User::where('role', 'guest')
            ->with('guestProfile')
            ->get()
            ->sortBy(function (User $u) {
                $p = $u->guestProfile;
                return $p ? $p->furigana() ?: $p->last_name . $p->first_name : $u->name;
            })
            ->values();

        return view('people.index', compact('people'));
    }

    public function show(User $user)
    {
        abort_if($user->isAdmin(), 404);

        $photos = $user->taggedPhotos()
            ->where('is_active', true)
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->get();

        return view('people.show', compact('user', 'photos'));
    }
}
