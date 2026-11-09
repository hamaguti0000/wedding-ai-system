<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\SeatingTable;
use App\Models\WeddingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuestSeatingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        // admin は管理者用の席次表編集画面へ
        if ($user->isAdmin()) {
            return redirect()->route('admin.seating');
        }

        // 認可チェック（サーバー側）
        // 出席回答済みのゲストのみ閲覧可能
        // 未ログイン → auth ミドルウェアが /login へリダイレクト済み
        // 未回答・欠席 → /home へリダイレクト（RSVP バナーが現状を説明する）
        $profile = $user->load('guestProfile')->guestProfile;

        if (!$profile || $profile->participation !== 'attending') {
            return redirect()->route('dashboard');
        }

        $setting = WeddingSetting::first();

        // 席次表が非公開の場合はホームへリダイレクト
        if ($setting !== null && !$setting->is_seating_public) {
            return redirect()->route('dashboard');
        }

        // 管理者が確定したレイアウトを取得
        $tables = SeatingTable::with([
            'seats.assignment.user.guestProfile',
        ])->orderBy('display_order')->get();

        // 自分の席を特定（未配置なら null）
        $myAssignment = $user->load('seatAssignment.seat')->seatAssignment;
        $mySeat       = $myAssignment?->seat;
        $myTableId    = $mySeat?->seating_table_id;

        $typeConfig = Seat::typeConfig();

        // テーブルも席も存在しない場合は未公開扱い
        $isPublished = $tables->isNotEmpty()
            && $tables->some(fn($t) => $t->seats->isNotEmpty());

        return view('seating.guest', compact(
            'tables',
            'mySeat',
            'myTableId',
            'user',
            'profile',
            'setting',
            'typeConfig',
            'isPublished'
        ));
    }
}
