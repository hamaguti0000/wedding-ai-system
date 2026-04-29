<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // HTTPリクエスト用
use Illuminate\Support\Facades\Auth; // Laravel認証用
use App\Models\User; // Userモデルを使う場合

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(
                Auth::user()->isAdmin() ? 'admin.dashboard' : 'dashboard'
            );
        }
        return view('login');
    }

    // ログイン処理
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $default = Auth::user()->isAdmin()
                ? route('admin.dashboard')
                : route('dashboard');

            return redirect()->intended($default);
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません',
        ]);
    }

    // ログアウト
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
