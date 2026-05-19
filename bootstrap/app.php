<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'email.ready' => \App\Http\Middleware\EnsureEmailIsReady::class,
            'password.ready' => \App\Http\Middleware\EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * セッション切れ（419 TokenMismatch）を 419 エラー画面ではなく
         * ログインページへのリダイレクトに変換する。
         *
         * ユーザーが長時間放置後に操作した場合などに発生する。
         * ログアウトボタン・フォーム送信どちらのケースも安全に処理する。
         */
        $exceptions->render(function (
            \Illuminate\Session\TokenMismatchException $e,
            \Illuminate\Http\Request $request
        ) {
            // AJAX / API リクエストには JSON で返す
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'セッションが切れました。ページを再読み込みして再試行してください。',
                ], 419);
            }

            // セッションを安全に破棄（既に無効でも例外にしない）
            try {
                \Illuminate\Support\Facades\Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Throwable) {}

            // ログアウトリクエストの場合はメッセージなしでログインへ
            if ($request->routeIs('logout') || $request->is('logout')) {
                return redirect()->route('login');
            }

            // その他のフォーム送信（RSVP・設定変更など）
            return redirect()->route('login')
                ->with('message', 'セッションが切れました。再度ログインして操作してください。');
        });
    })->create();
