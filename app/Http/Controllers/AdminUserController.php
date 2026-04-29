<?php

namespace App\Http\Controllers;

use App\Models\GuestProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'guest')
            ->with('guestProfile')
            ->latest()
            ->get();

        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'     => 'required|string|max:50|unique:users,username',
            'password'     => 'required|string|min:6',
            'last_name'    => 'nullable|string|max:50',
            'first_name'   => 'nullable|string|max:50',
            'furigana_sei' => 'nullable|string|max:50',
            'furigana_mei' => 'nullable|string|max:50',
            'guest_side'   => 'nullable|in:groom,bride',
            'relationship' => 'nullable|in:friend,family,colleague,other',
        ], [
            'username.required' => 'ユーザー名は必須です',
            'username.unique'   => 'このユーザー名はすでに使われています',
            'password.required' => 'パスワードは必須です',
            'password.min'      => 'パスワードは6文字以上にしてください',
        ]);

        $fullName = trim(($request->last_name ?? '') . ' ' . ($request->first_name ?? ''));

        $user = User::create([
            'name'     => $fullName ?: $request->username,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => 'guest',
        ]);

        GuestProfile::create([
            'user_id'       => $user->id,
            'last_name'     => $request->last_name,
            'first_name'    => $request->first_name,
            'furigana_sei'  => $request->furigana_sei,
            'furigana_mei'  => $request->furigana_mei,
            'guest_side'    => $request->guest_side ?: null,
            'relationship'  => $request->relationship ?: null,
            'participation' => 'pending',
        ]);

        return redirect()->route('admin.users')
            ->with('success', "「{$request->username}」を登録しました");
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            abort(403, '管理者アカウントは削除できません');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'ユーザーを削除しました');
    }
}
