<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLogin
{
    public function handle(Request $request, Closure $next)
    {
        // セッションに user が無ければログイン画面へ
        if (!session()->has('user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
