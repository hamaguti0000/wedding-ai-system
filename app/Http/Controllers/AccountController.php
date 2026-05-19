<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function showRegister()
    {
        if (Auth::check()) {
            if (blank(Auth::user()->email) || ! Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('profile.edit');
            }

            return redirect()->route('dashboard');
        }
        return view('create_user');
    }

    public function register(Request $request)
    {
        $request->validate([
            'last_name'             => 'required|string|max:50',
            'first_name'            => 'required|string|max:50',
            'email'                 => 'required|string|email:rfc,filter|max:255|unique:users,email',
            'password'              => 'required|string|min:8|max:255|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'last_name.required'             => '姓は必須です',
            'last_name.max'                  => '姓は50文字以内で入力してください',
            'first_name.required'            => '名は必須です',
            'first_name.max'                 => '名は50文字以内で入力してください',
            'email.required'                 => 'メールアドレスは必須です',
            'email.email'                    => '正しいメールアドレスを入力してください',
            'email.max'                      => 'メールアドレスは255文字以内で入力してください',
            'email.unique'                   => 'このメールアドレスは既に登録されています',
            'password.required'              => 'パスワードは必須です',
            'password.min'                   => 'パスワードは8文字以上で入力してください',
            'password.max'                   => 'パスワードは255文字以内で入力してください',
            'password.confirmed'             => 'パスワードが一致しません',
            'password_confirmation.required' => '確認用パスワードは必須です',
        ]);

        $user = User::create([
            'name'     => trim($request->last_name) . ' ' . trim($request->first_name),
            'email'    => trim($request->email),
            'password' => $request->password,
            'role'     => 'guest',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // メールアドレスが登録されていれば確認メールを送信
        try {
            $user->sendEmailVerification();
        } catch (\Throwable) {
            // メール送信失敗でも登録は完了させる
        }

        return redirect()->route('profile.edit')
            ->with('email_verification_sent', true);
    }
}
