<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;

class AdminLoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        // Laravel の ConvertEmptyStringsToNull ミドルウェアが空文字を null に変換するため
        // ?: '' で確実に文字列に統一する
        $q      = trim((string)($request->get('q') ?: ''));
        $from   = (string)($request->get('from') ?: '');
        $to     = (string)($request->get('to')   ?: '');
        $status = (string)($request->get('status') ?: 'all');
        $side   = (string)($request->get('side')   ?: 'all');

        // status/side が空になった場合のフォールバック
        if ($status === '') $status = 'all';
        if ($side   === '') $side   = 'all';

        $query = LoginHistory::with('user.guestProfile')->latest('created_at');

        // ── ユーザー名（部分一致）──
        if ($q !== '') {
            $query->where('username', 'like', '%' . $q . '%');
        }

        // ── 期間（両端とも有効な日付文字列のときのみ適用）──
        if ($from !== '' && strtotime($from) !== false) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to !== '' && strtotime($to) !== false) {
            $query->whereDate('created_at', '<=', $to);
        }

        // ── 状態（success / failed）──
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // ── お立場（新郎側 / 新婦側）──
        // ネストした whereHas は明示的なクロージャで記述する（arrow fn の変数衝突を回避）
        if ($side !== 'all') {
            $sideValue = $side;
            $query->whereHas('user', function ($uq) use ($sideValue) {
                $uq->whereHas('guestProfile', function ($pq) use ($sideValue) {
                    $pq->where('guest_side', $sideValue);
                });
            });
        }

        $histories = $query->paginate(50)->withQueryString();

        // サマリー（フィルターなし全件）
        $totalSuccess = LoginHistory::where('status', 'success')->count();
        $totalFailed  = LoginHistory::where('status', 'failed')->count();

        $hasFilter = $q !== '' || $from !== '' || $to !== ''
                     || $status !== 'all' || $side !== 'all';

        return view('admin.login-history', compact(
            'histories', 'q', 'from', 'to', 'status', 'side',
            'totalSuccess', 'totalFailed', 'hasFilter'
        ));
    }
}
