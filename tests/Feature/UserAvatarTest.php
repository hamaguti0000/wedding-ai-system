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

    it('プロフィールから背景色を指定して絵文字アイコンを保存できる', function () {
        $user = makeGuest();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'avatar_type' => User::AVATAR_EMOJI,
                'avatar_emoji' => '🌸',
                'avatar_bg_color' => '#dbeafe',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->avatar_type)->toBe(User::AVATAR_EMOJI);
        expect($user->avatar_emoji)->toBe('🌸');
        expect($user->avatar_bg_color)->toBe('#dbeafe');
    });

    it('管理者のユーザー編集から写真アイコンを保存できる', function () {
        Storage::fake('public');

        $admin = makeAdmin();
        $guest = makeGuest();
        $photo = UploadedFile::fake()->createWithContent(
            'avatar.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO3Z4X0AAAAASUVORK5CYII=')
        );

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

    it('管理者のユーザー編集で絵文字背景色を保存できる', function () {
        $admin = makeAdmin();
        $guest = makeGuest();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $guest->id), [
                'username' => $guest->username,
                'role' => 'guest',
                'avatar_type' => User::AVATAR_EMOJI,
                'avatar_emoji' => '🐱',
                'avatar_bg_color' => '#fecaca',
            ])
            ->assertRedirect(route('admin.users'));

        $guest->refresh();
        expect($guest->avatar_type)->toBe(User::AVATAR_EMOJI);
        expect($guest->avatar_emoji)->toBe('🐱');
        expect($guest->avatar_bg_color)->toBe('#fecaca');
    });

    it('絵文字アバターの背景色は白が既定値になる', function () {
        $user = makeGuest();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'avatar_type' => User::AVATAR_EMOJI,
                'avatar_emoji' => '✨',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->avatar_bg_color)->toBe('#ffffff');
        expect($user->avatarBackgroundColor())->toBe('#ffffff');
    });
});
