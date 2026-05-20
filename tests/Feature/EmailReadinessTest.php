<?php

use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Models\WeddingSetting;
use Illuminate\Support\Facades\Mail;

it('メール未登録のユーザーはログイン後メール登録画面へ誘導される', function () {
    $user = makeGuest('attending');
    $user->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->post('/login', [
        'username' => $user->username,
        'password' => 'password',
    ])->assertRedirect(route('email.register'));
});

it('メール未登録のユーザーはゲストページへ進めない', function () {
    $user = makeGuest('attending');
    $user->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('email.register'));
});

it('メール未認証のユーザーは認証完了までゲストページへ進めない', function () {
    $user = makeGuest('attending');
    $user->forceFill(['email_verified_at' => null])->save();

    $this->actingAs($user)
        ->get(route('invitation'))
        ->assertRedirect(route('email.register'));
});

it('メール認証済みでも初期パスワード変更が必要なら変更画面へ進む', function () {
    $user = makeGuest('attending');
    $user->forceFill(['password_change_required' => true, 'password_changed_at' => null])->save();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('password.change'));
});

it('管理者はメール未登録でも管理画面へ進める', function () {
    $admin = makeAdmin();
    $admin->forceFill(['email' => null, 'email_verified_at' => null, 'password_change_required' => true])->save();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('メール登録時に確認メールが送信される', function () {
    Mail::fake();

    $user = makeGuest('attending');
    $user->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($user)
        ->patch(route('email.register.update'), ['email' => 'guest@example.com'])
        ->assertRedirect()
        ->assertSessionHas('email_verification_sent');

    $user->refresh();
    expect($user->email)->toBe('guest@example.com');
    expect($user->email_verified_at)->toBeNull();
});

it('確認メールのヘッダーはトップページと同じ英字名を表示する', function () {
    WeddingSetting::create(validSettingsPayload([
        'groom_name_en' => 'Kakeru',
        'bride_name_en' => 'Mirai',
    ]));

    $html = (new EmailVerificationMail(makeGuest('pending'), 'sample-token'))->render();

    expect($html)->toContain('Kakeru &amp; Mirai')
        ->not->toContain('翔 &amp; 礼');
});

it('ログイン画面にパスワード再設定と登録メール確認の導線がある', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('パスワードを忘れた方はこちら')
        ->assertSee('登録メールアドレスを忘れた方はこちら');
});

it('パスワード再設定画面でパスワード表示切り替えができる', function () {
    $this->get(route('password.reset', ['token' => 'dummy-token', 'email' => 'guest@example.com']))
        ->assertOk()
        ->assertSee('data-password-toggle="password"', false)
        ->assertSee('data-password-toggle="password_confirmation"', false)
        ->assertSee('パスワードを表示');
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
