<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin() || ! $user->password_change_required) {
            return $next($request);
        }

        // 管理者による代理ログイン中は、本人にパスワードを変更させるための画面へ
        // 飛ばさない。飛ばすとゲスト画面の確認ができないうえ、管理者が本人の
        // パスワードを変更してしまえる状態になる。
        if ($request->session()->has(\App\Http\Controllers\ImpersonationController::SESSION_KEY)) {
            return $next($request);
        }

        if ($request->routeIs(
            'email.register',
            'email.register.update',
            'email.verify',
            'email.verify.resend',
            'password.change',
            'password.change.update',
            'logout',
            'csrf.refresh'
        )) {
            return $next($request);
        }

        return redirect()->route('password.change')
            ->with('message', '安全のため、新しいパスワードを設定してください。');
    }
}
