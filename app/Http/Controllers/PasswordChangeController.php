<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    public function edit(Request $request)
    {
        return view('auth.change-password', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ], [
            'current_password.required' => '現在のパスワードを入力してください',
            'password.required' => '新しいパスワードを入力してください',
            'password.min' => 'パスワードは6文字以上にしてください',
            'password.confirmed' => '確認用パスワードが一致しません',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => '現在のパスワードが正しくありません'])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_change_required' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard')
            ->with('success', 'パスワードを変更しました');
    }
}
