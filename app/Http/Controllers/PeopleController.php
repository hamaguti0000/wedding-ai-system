<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

    public function showByReference(string $token, Request $request)
    {
        $user = User::fromPublicReferenceToken($token);
        abort_if(! $user, 404);

        return $this->show($user, $request);
    }

    public function show(User $user, Request $request)
    {
        abort_if($user->isAdmin(), 404);
        abort_if($user->seatAssignment === null, 404);

        $photos = $user->taggedPhotos()
            ->where('is_active', true)
            ->where('status', 'approved')
            ->with(Schema::hasTable('guest_groups') ? ['taggedUsers.guestProfile', 'taggedGroups.primaryGuest'] : ['taggedUsers.guestProfile'])
            ->orderByDesc('created_at')
            ->get();

        $backSource = $request->query('from', 'people');
        if (! in_array($backSource, ['gallery', 'seating', 'people'], true)) {
            $backSource = 'people';
        }

        [$backUrl, $backLabel] = match ($backSource) {
            'gallery' => [route('gallery'), 'ギャラリーに戻る'],
            'seating' => [route('seating.guest'), '席次表に戻る'],
            default => [route('people.index'), '参加者一覧に戻る'],
        };

        return view('people.show', compact('user', 'photos', 'backUrl', 'backLabel', 'backSource'));
    }
}
