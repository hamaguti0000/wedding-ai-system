<?php

namespace App\Http\Controllers;

use App\Models\User;

class PeopleController extends Controller
{
    public function index()
    {
        // 一旦、席次表に登録済み（座席が割り当て済み）のゲストのみ一覧に出す
        $people = User::where('role', 'guest')
            ->whereHas('seatAssignment')
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
        abort_if($user->seatAssignment === null, 404);

        $photos = $user->taggedPhotos()
            ->where('is_active', true)
            ->where('status', 'approved')
            ->with('taggedUsers.guestProfile')
            ->orderByDesc('created_at')
            ->get();

        return view('people.show', compact('user', 'photos'));
    }
}
