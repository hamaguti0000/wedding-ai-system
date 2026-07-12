<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeddingSetting;
use App\Services\AvatarUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['guestProfile', 'taskAssignments.task.programItems']);
        $user->guestProfile?->ensureCheckInToken();
        $checkInUrl = $user->guestProfile?->checkInUrl();
        $needsEmailRegistration = blank($user->email);

        return view('profile', [
            'user'    => $user,
            'profile' => $user->guestProfile,
            'setting' => WeddingSetting::first(),
            'tasks'   => $user->taskAssignments,
            'checkInUrl' => $checkInUrl,
            'needsEmailRegistration' => $needsEmailRegistration,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
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
            'avatar_border_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'avatar_border_width' => [
                'nullable',
                'integer',
                'min:0',
                'max:10',
            ],
            'avatar_image' => [
                Rule::requiredIf(fn () => $request->input('avatar_type') === User::AVATAR_PHOTO && ! $user->avatar_image_path),
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,heic,heif',
                'max:5120',
            ],
        ], [
            'avatar_emoji.required' => '絵文字アイコンを選択してください',
            'avatar_emoji.in'       => '選択された絵文字アイコンは利用できません',
            'avatar_image.required' => '写真アイコンを使う場合は画像を選択してください',
            'avatar_image.mimes'    => '写真はJPEG・PNG・WEBP・GIF・HEIC形式のいずれかを選択してください',
            'avatar_image.max'      => '写真は5MB以下にしてください',
            'avatar_bg_color.regex' => '背景色の形式が正しくありません',
            'avatar_border_color.regex' => '枠線色の形式が正しくありません',
            'avatar_border_width.integer' => '枠線の太さは数値で入力してください',
        ]);

        $data = [];

        if ($request->filled('name')) {
            $data['name'] = $request->name;
        }

        if ($request->has('avatar_type')) {
            $avatarType = $request->input('avatar_type', User::AVATAR_INITIAL);
            $currentPath = $user->avatar_image_path;

            if ($avatarType === User::AVATAR_PHOTO) {
                if ($request->hasFile('avatar_image')) {
                    try {
                        $newPath = app(AvatarUploadService::class)->store($request->file('avatar_image'));
                    } catch (RuntimeException $e) {
                        return back()->withErrors(['avatar_image' => $e->getMessage()]);
                    }
                    if ($currentPath) {
                        Storage::disk('public')->delete($currentPath);
                    }
                    $data['avatar_image_path'] = $newPath;
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
            $data['avatar_border_color'] = $request->input('avatar_border_color', '#f0e4d0');
            $data['avatar_border_width'] = (int) $request->input('avatar_border_width', 3);
        }

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'プロフィールを更新しました');
    }
}
