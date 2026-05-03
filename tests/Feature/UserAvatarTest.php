<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('ユーザーアイコン', function () {

    it('プロフィールから絵文字アイコンを保存できる', function () {
        $user = makeGuest();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'avatar_type' => User::AVATAR_EMOJI,
                'avatar_emoji' => '🐻',
                'avatar_bg_color' => '#ffffff',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->avatar_type)->toBe(User::AVATAR_EMOJI);
        expect($user->avatar_emoji)->toBe('🐻');
        expect($user->avatar_image_path)->toBeNull();
        expect($user->avatar_bg_color)->toBe('#ffffff');
    });

    it('管理者のユーザー編集から写真アイコンを保存できる', function () {
        Storage::fake('public');

        $admin = makeAdmin();
        $guest = makeGuest();
        $photo = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $guest->id), [
                'username' => $guest->username,
                'role' => 'guest',
                'avatar_type' => User::AVATAR_PHOTO,
                'avatar_image' => $photo,
            ])
            ->assertRedirect(route('admin.users'));

        $guest->refresh();
        expect($guest->avatar_type)->toBe(User::AVATAR_PHOTO);
        expect($guest->avatar_image_path)->not->toBeNull();
        expect($guest->avatar_bg_color)->toBeNull();
        Storage::disk('public')->assertExists($guest->avatar_image_path);
    });
});
