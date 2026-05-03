<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeddingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['guestProfile', 'taskAssignments.task.programItems']);

        return view('profile', [
            'user'    => $user,
            'profile' => $user->guestProfile,
            'setting' => WeddingSetting::first(),
            'tasks'   => $user->taskAssignments,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'avatar_type' => ['nullable', Rule::in(array_keys(User::avatarTypeOptions()))],
            'avatar_emoji' => [
                Rule::requiredIf(fn () => $request->input('avatar_type') === User::AVATAR_EMOJI),
                'nullable',
                'string',
                Rule::in(array_keys(User::avatarEmojiOptions())),
            ],
            'avatar_bg_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'avatar_image' => [
                Rule::requiredIf(fn () => $request->input('avatar_type') === User::AVATAR_PHOTO && ! $user->avatar_image_path),
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:5120',
            ],
        ], [
            'avatar_emoji.required' => '絵文字アイコンを選択してください',
            'avatar_emoji.in'       => '選択された絵文字アイコンは利用できません',
            'avatar_image.required' => '写真アイコンを使う場合は画像を選択してください',
            'avatar_image.image'    => '写真は画像ファイルを選択してください',
            'avatar_image.max'      => '写真は5MB以下にしてください',
            'avatar_bg_color.regex' => '背景色の形式が正しくありません',
        ]);

        $data = [];

        if ($request->filled('name')) {
            $data['name'] = $request->name;
        }
        if ($request->filled('email')) {
            $data['email'] = $request->email;
        }

        if ($request->has('avatar_type')) {
            $avatarType = $request->input('avatar_type', User::AVATAR_INITIAL);
            $currentPath = $user->avatar_image_path;

            if ($avatarType === User::AVATAR_PHOTO) {
                if ($request->hasFile('avatar_image')) {
                    if ($currentPath) {
                        Storage::disk('public')->delete($currentPath);
                    }
                    $data['avatar_image_path'] = $request->file('avatar_image')->store('avatars', 'public');
                } elseif ($currentPath) {
                    $data['avatar_image_path'] = $currentPath;
                }
                $data['avatar_emoji'] = null;
                $data['avatar_bg_color'] = null;
            } else {
                if ($currentPath) {
                    Storage::disk('public')->delete($currentPath);
                }
                $data['avatar_image_path'] = null;
                $data['avatar_emoji'] = $avatarType === User::AVATAR_EMOJI ? $request->input('avatar_emoji') : null;
                $data['avatar_bg_color'] = $avatarType === User::AVATAR_EMOJI
                    ? $request->input('avatar_bg_color', '#ffffff')
                    : null;
            }

            $data['avatar_type'] = $avatarType;
        }

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'アイコンを更新しました');
    }
}
