<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('メール未登録のユーザーはログイン後プロフィールへ誘導される', function () {
    $user = makeGuest('attending');
    $user->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ])->assertRedirect(route('profile.edit'));
});

it('メール未登録のユーザーはゲストページへ進めない', function () {
    $user = makeGuest('attending');
    $user->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('profile.edit'));
});

it('メール未認証のユーザーは認証完了までゲストページへ進めない', function () {
    $user = makeGuest('attending');
    $user->forceFill(['email_verified_at' => null])->save();

    $this->actingAs($user)
        ->get(route('invitation'))
        ->assertRedirect(route('profile.edit'));
});

it('メール登録時に確認メールが送信される', function () {
    Mail::fake();

    $user = makeGuest('attending');
    $user->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['email' => 'guest@example.com'])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('email_verification_sent');

    $user->refresh();
    expect($user->email)->toBe('guest@example.com');
    expect($user->email_verified_at)->toBeNull();
});

it('ログイン画面にパスワード再設定と登録メール確認の導線がある', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('パスワードを忘れた方はこちら')
        ->assertSee('登録メールアドレスを忘れた方はこちら');
});

it('ユーザー名から登録メールアドレスの一部を確認できる', function () {
    $user = User::factory()->create([
        'username' => 'guest-help',
        'email' => 'guest-help@example.com',
    ]);

    $this->post(route('email.forgot.lookup'), [
        'username' => $user->username,
    ])->assertRedirect()
        ->assertSessionHas('found_email', 'g*********@example.com');
});
