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

        if ($request->routeIs(
            'profile.edit',
            'profile.update',
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
