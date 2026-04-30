<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;

class AdminLoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim($request->get('q', ''));
        $from   = $request->get('from', '');
        $to     = $request->get('to', '');
        $status = $request->get('status', 'all');
        $side   = $request->get('side', 'all');

        $query = LoginHistory::with('user.guestProfile')->latest('created_at');

        // ユーザー名検索
        if ($q !== '') {
            $query->where('username', 'like', "%{$q}%");
        }

        // 期間フィルター
        if ($from !== '') {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to !== '') {
            $query->whereDate('created_at', '<=', $to);
        }

        // 状態フィルター
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // お立場フィルター（新郎側/新婦側）
        if ($side !== 'all') {
            $query->whereHas('user.guestProfile', fn($q) => $q->where('guest_side', $side));
        }

        $histories = $query->paginate(50)->withQueryString();

        // サマリー（フィルターなし全件）
        $totalSuccess = LoginHistory::where('status', 'success')->count();
        $totalFailed  = LoginHistory::where('status', 'failed')->count();

        // アクティブフィルターの有無
        $hasFilter = $q !== '' || $from !== '' || $to !== ''
                     || $status !== 'all' || $side !== 'all';

        return view('admin.login-history', compact(
            'histories', 'q', 'from', 'to', 'status', 'side',
            'totalSuccess', 'totalFailed', 'hasFilter'
        ));
    }
}
