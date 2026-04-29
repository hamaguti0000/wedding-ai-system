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
        $users = User::with('guestProfile')->latest()->get();
        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'     => 'required|string|max:50|unique:users,username',
            'password'     => 'required|string|min:6',
            'role'         => 'required|in:admin,guest',
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
            'role.required'     => 'ロールを選択してください',
        ]);

        $fullName = trim(($request->last_name ?? '') . ' ' . ($request->first_name ?? ''));

        $user = User::create([
            'name'     => $fullName ?: $request->username,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        if ($request->role === 'guest') {
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
        }

        return redirect()->route('admin.users')
            ->with('success', "「{$request->username}」を登録しました");
    }

    public function edit(int $id)
    {
        $user = User::with('guestProfile')->findOrFail($id);
        return view('admin.users-edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::with('guestProfile')->findOrFail($id);

        $request->validate([
            'username'            => 'required|string|max:50|unique:users,username,' . $id,
            'role'                => 'required|in:admin,guest',
            'last_name'           => 'nullable|string|max:50',
            'first_name'          => 'nullable|string|max:50',
            'furigana_sei'        => 'nullable|string|max:50',
            'furigana_mei'        => 'nullable|string|max:50',
            'guest_side'          => 'nullable|in:groom,bride',
            'relationship'        => 'nullable|in:friend,family,colleague,other',
            'relationship_detail' => 'nullable|string|max:100',
            'participation'       => 'nullable|in:attending,declining,pending',
            'attending_count'     => 'nullable|integer|min:0|max:20',
            'children_count'      => 'nullable|integer|min:0|max:10',
            'has_allergy'         => 'nullable|in:0,1',
            'allergy_notes'       => 'nullable|string|max:500',
            'phone'               => ['nullable', 'string', 'max:20'],
            'postal_code'         => 'nullable|string|max:8',
            'address'             => 'nullable|string|max:200',
            'notes'               => 'nullable|string|max:1000',
            'password'            => 'nullable|string|min:6|confirmed',
        ], [
            'username.required'  => 'ユーザー名は必須です',
            'username.unique'    => 'このユーザー名はすでに使われています',
            'password.min'       => 'パスワードは6文字以上にしてください',
            'password.confirmed' => '確認用パスワードが一致しません',
        ]);

        $fullName = trim(($request->last_name ?? '') . ' ' . ($request->first_name ?? ''));

        $userData = [
            'username' => $request->username,
            'name'     => $fullName ?: $user->username,
            'role'     => $request->role,
        ];
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }
        $user->update($userData);

        if ($request->role === 'guest') {
            GuestProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'last_name'           => $request->last_name,
                    'first_name'          => $request->first_name,
                    'furigana_sei'        => $request->furigana_sei,
                    'furigana_mei'        => $request->furigana_mei,
                    'guest_side'          => $request->guest_side ?: null,
                    'relationship'        => $request->relationship ?: null,
                    'relationship_detail' => $request->relationship_detail,
                    'participation'       => $request->participation ?? 'pending',
                    'attending_count'     => $request->attending_count ?? 0,
                    'children_count'      => $request->children_count ?? 0,
                    'has_allergy'         => $request->has_allergy === '1',
                    'allergy_notes'       => $request->allergy_notes,
                    'phone'               => $request->phone,
                    'postal_code'         => $request->postal_code,
                    'address'             => $request->address,
                    'notes'               => $request->notes,
                    'responded_at'        => $request->participation !== 'pending'
                                                ? ($user->guestProfile?->responded_at ?? now())
                                                : null,
                ]
            );
        }

        return redirect()->route('admin.users')
            ->with('success', "「{$user->username}」の情報を更新しました");
    }

    public function updatePassword(Request $request, int $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required'  => '新しいパスワードを入力してください',
            'password.min'       => 'パスワードは6文字以上にしてください',
            'password.confirmed' => '確認用パスワードが一致しません',
        ]);

        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('admin.users')
            ->with('success', "「{$user->username}」のパスワードを変更しました");
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', '自分自身は削除できません');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'ユーザーを削除しました');
    }
}
