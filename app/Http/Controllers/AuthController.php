<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            if (! Auth::user()->isAdmin() && (blank(Auth::user()->email) || ! Auth::user()->hasVerifiedEmail())) {
                return redirect()->route('email.register');
            }

            if (! Auth::user()->isAdmin() && Auth::user()->password_change_required) {
                return redirect()->route('password.change');
            }

            return redirect()->route(
                Auth::user()->isAdmin() ? 'admin.dashboard' : 'dashboard'
            );
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            LoginHistory::create([
                'user_id'    => $user->id,
                'username'   => $request->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
            ]);

            if (! $user->isAdmin() && blank($user->email)) {
                return redirect()->route('email.register')
                    ->with('message', 'メールアドレスを登録してください。パスワード再設定に必要です。');
            }

            if (! $user->isAdmin() && ! $user->hasVerifiedEmail()) {
                return redirect()->route('email.register')
                    ->with('message', 'メールアドレスの認証を完了してください。');
            }

            if (! $user->isAdmin() && $user->password_change_required) {
                return redirect()->route('password.change')
                    ->with('message', '安全のため、新しいパスワードを設定してください。');
            }

            return redirect()->intended(
                $user->isAdmin() ? route('admin.dashboard') : route('dashboard')
            );
        }

        LoginHistory::create([
            'user_id'    => null,
            'username'   => $request->username,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'failed',
        ]);

        return back()->withErrors([
            'username' => 'ユーザー名またはパスワードが正しくありません',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
