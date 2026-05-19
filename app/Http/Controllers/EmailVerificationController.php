<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    /** GET /verify?token=xxx */
    public function verify(Request $request)
    {
        $token = $request->query('token');

        if (blank($token)) {
            return view('verify', ['status' => 'invalid']);
        }

        $user = User::verifyEmailToken($token);

        if (!$user) {
            return view('verify', ['status' => 'expired']);
        }

        // 別ユーザーのセッションで認証URLを開いた場合も、トークンの持ち主として扱う。
        Auth::login($user);
        $request->session()->regenerate();

        return view('verify', ['status' => 'success', 'user' => $user]);
    }

    /** POST /verify/resend（ログイン必須） */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'メールアドレスは既に確認済みです。');
        }

        if (blank($user->email)) {
            return back()->with('error', '先にメールアドレスを登録してください。');
        }

        $sent = $user->sendEmailVerification();

        if (!$sent) {
            return back()->with('info', '確認メールは60分以内に送信済みです。しばらくしてから再試行してください。');
        }

        return back()->with('success', '確認メールを再送信しました。受信ボックスをご確認ください。');
    }
}
