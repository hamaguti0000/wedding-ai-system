<?php

namespace App\Http\Controllers;

use App\Models\EmailAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailRegistrationController extends Controller
{
    public function edit(Request $request)
    {
        return view('auth.email-register', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $originalEmail = $user->email;

        $request->validate([
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,filter',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ], [
            'email.required' => 'メールアドレスは必須です',
            'email.email' => '正しいメールアドレスを入力してください',
            'email.max' => 'メールアドレスは255文字以内で入力してください',
            'email.unique' => 'このメールアドレスは既に登録されています',
        ]);

        $email = strtolower(trim($request->email));

        if ($email === $originalEmail && $user->hasVerifiedEmail()) {
            return redirect()->route($user->password_change_required ? 'password.change' : 'dashboard');
        }

        if ($email !== $originalEmail) {
            $user->forceFill([
                'email' => $email,
                'email_verified_at' => null,
                'email_verification_token' => null,
                'email_verification_sent_at' => null,
            ])->save();

            EmailAuditLog::record(
                $user,
                $user,
                $originalEmail ? 'self_update' : 'self_set',
                $originalEmail,
                $email,
                $originalEmail ? '本人がメールアドレスを変更しました' : '本人がメールアドレスを登録しました',
                ['source' => 'email_registration']
            );
        }

        try {
            $user->refresh()->sendEmailVerification(force: true);
        } catch (\Throwable) {
            return back()
                ->withInput()
                ->with('error', 'メールアドレスは保存しましたが、確認メールの送信に失敗しました。時間をおいて再送してください。');
        }

        return back()->with('email_verification_sent', true);
    }
}
