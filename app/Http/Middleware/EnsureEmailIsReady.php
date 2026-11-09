<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsReady
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        // 管理者による代理ログイン中は、本人にメール登録・認証をさせるための画面へ
        // 飛ばさない（ゲスト画面の確認ができなくなるため）。
        if ($request->session()->has(\App\Http\Controllers\ImpersonationController::SESSION_KEY)) {
            return $next($request);
        }

        if ($this->isExemptRoute($request) || $user->isEmailRegistrationExempt()) {
            return $next($request);
        }

        if (blank($user->email)) {
            return redirect()->route('email.register')
                ->with('message', 'メールアドレスを登録してください。パスワード再設定に必要です。');
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('email.register')
                ->with('message', 'メールアドレスの認証を完了してください。');
        }

        return $next($request);
    }

    private function isExemptRoute(Request $request): bool
    {
        return $request->routeIs(
            'email.register',
            'email.register.update',
            'email.verify',
            'email.verify.resend',
            'logout',
            'csrf.refresh'
        );
    }
}
