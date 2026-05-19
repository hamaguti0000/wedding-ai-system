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

        if (! $user) {
            return $next($request);
        }

        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        if (blank($user->email)) {
            return redirect()->route('profile.edit')
                ->with('message', 'メールアドレスを登録してください。パスワード再設定に必要です。');
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('profile.edit')
                ->with('message', 'メールアドレスの認証を完了してください。');
        }

        return $next($request);
    }

    private function isExemptRoute(Request $request): bool
    {
        return $request->routeIs(
            'profile.edit',
            'profile.update',
            'email.verify',
            'email.verify.resend',
            'logout',
            'csrf.refresh'
        );
    }
}
