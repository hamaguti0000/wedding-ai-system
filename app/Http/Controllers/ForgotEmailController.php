<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ForgotEmailController extends Controller
{
    public function show()
    {
        return view('auth.forgot-email');
    }

    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'ユーザー名を入力してください',
        ]);

        $user = User::where('username', $validated['username'])->first();

        if (! $user) {
            return back()
                ->withErrors(['username' => '該当するユーザーが見つかりません'])
                ->withInput();
        }

        if (blank($user->email)) {
            return back()
                ->with('message', 'このアカウントにはまだメールアドレスが登録されていません。ログイン後、プロフィール画面で登録してください。')
                ->withInput();
        }

        return back()
            ->with('found_email', $this->maskEmail($user->email))
            ->with('found_email_verified', $user->hasVerifiedEmail())
            ->withInput();
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        $maskedLocal = mb_substr($local, 0, 1) . str_repeat('*', max(3, mb_strlen($local) - 1));

        return $maskedLocal . '@' . $domain;
    }
}
