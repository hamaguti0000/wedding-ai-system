<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return $next($request);
        }

        // 未ログインはログイン画面へ（403だとログインすれば見られることが伝わらない）。
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                abort(401, 'ログインが必要です');
            }

            return redirect()->guest(route('login'));
        }

        // ログイン済みのゲストが管理者用リンクを踏んだ場合は、403の画面を見せずに
        // ホームへ戻す。招待状のURLを共有した際などに管理者向けリンクを誤って
        // 踏むことがあり、エラー画面のままだと行き止まりになるため。
        if ($request->expectsJson()) {
            abort(403, '管理者権限が必要です');
        }

        return redirect()->route('dashboard')
            ->with('message', 'そのページは管理者専用です。');
    }
}
