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
                'avatar_border_color' => '#b38b59',
                'avatar_border_width' => 5,
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->avatar_type)->toBe(User::AVATAR_EMOJI);
        expect($user->avatar_emoji)->toBe('🐻');
        expect($user->avatar_image_path)->toBeNull();
        expect($user->avatar_bg_color)->toBe('#ffffff');
        expect($user->avatar_border_color)->toBe('#b38b59');
        expect($user->avatar_border_width)->toBe(5);
    });

    it('プロフィールから背景色を指定して絵文字アイコンを保存できる', function () {
        $user = makeGuest();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'avatar_type' => User::AVATAR_EMOJI,
                'avatar_emoji' => '🌸',
                'avatar_bg_color' => '#dbeafe',
                'avatar_border_color' => '#3d2f25',
                'avatar_border_width' => 2,
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->avatar_type)->toBe(User::AVATAR_EMOJI);
        expect($user->avatar_emoji)->toBe('🌸');
        expect($user->avatar_bg_color)->toBe('#dbeafe');
        expect($user->avatar_border_color)->toBe('#3d2f25');
        expect($user->avatar_border_width)->toBe(2);
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
                'avatar_border_color' => '#a8d8b9',
                'avatar_border_width' => 4,
            ])
            ->assertRedirect(route('admin.users'));

        $guest->refresh();
        expect($guest->avatar_type)->toBe(User::AVATAR_PHOTO);
        expect($guest->avatar_image_path)->not->toBeNull();
        expect($guest->avatar_bg_color)->toBeNull();
        expect($guest->avatar_border_color)->toBe('#a8d8b9');
        expect($guest->avatar_border_width)->toBe(4);
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
                'avatar_border_color' => '#9b8573',
                'avatar_border_width' => 6,
            ])
            ->assertRedirect(route('admin.users'));

        $guest->refresh();
        expect($guest->avatar_type)->toBe(User::AVATAR_EMOJI);
        expect($guest->avatar_emoji)->toBe('🐱');
        expect($guest->avatar_bg_color)->toBe('#fecaca');
        expect($guest->avatar_border_color)->toBe('#9b8573');
        expect($guest->avatar_border_width)->toBe(6);
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
        expect($user->avatarBorderColor())->toBe('#f0e4d0');
        expect($user->avatarBorderWidth())->toBe(3);
    });
});

describe('受付QR表示', function () {

    it('プロフィールに自分の受付QRが表示される', function () {
        $guest = makeGuest('attending');

        $this->actingAs($guest)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('受付QR')
            ->assertSee($guest->guestProfile->checkin_token);
    });
});

describe('プロフィールメール', function () {

    it('プロフィールからメールアドレスを登録できる', function () {
        $user = makeGuest('attending');
        $user->forceFill(['email' => null])->save();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'email' => 'guest@example.com',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        expect($user->email)->toBe('guest@example.com');
        $this->assertDatabaseHas('check_in_audit_logs', [
            'guest_profile_id' => $user->guestProfile->id,
            'action' => 'profile_update',
            'source' => 'profile',
        ]);
    });

    it('メール未登録のときプロフィール画面で案内が出る', function () {
        $user = makeGuest('attending');
        $user->forceFill(['email' => null])->save();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('メールアドレス')
            ->assertSee('初回ログイン時に登録しておくと後から困りません');
    });
});
