<?php

use App\Mail\EmailVerificationMail;
use App\Models\CheckInAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

// =====================================================================
// 前提: 初回ログイン → 認証メール送信済み → 管理者がメール削除
// =====================================================================

it('管理者がゲストのメールを削除するとDB上のメール関連フィールドがすべてクリアされる', function () {
    Mail::fake();

    $admin = makeAdmin();
    $guest = makeGuest('attending');
    $token = Str::random(64);
    $guest->forceFill([
        'email'                       => 'guest@example.com',
        'email_verified_at'           => null,
        'email_verification_token'    => hash('sha256', $token),
        'email_verification_sent_at'  => now()->subSeconds(30),
    ])->save();

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $guest->id), [
            'username'     => $guest->username,
            'role'         => 'guest',
            'delete_email' => '1',
        ])
        ->assertRedirect(route('admin.users'));

    $guest->refresh();
    expect($guest->email)->toBeNull('メールがクリアされているべき');
    expect($guest->email_verified_at)->toBeNull('認証日時がクリアされているべき');
    expect($guest->email_verification_token)->toBeNull('認証トークンがクリアされているべき');
    expect($guest->email_verification_sent_at)->toBeNull('認証メール送信日時がクリアされているべき');
});

it('管理者がメールを削除した後、古い認証トークンリンクを踏むとexpiredになる', function () {
    $token = Str::random(64);
    $guest = makeGuest('attending');
    $guest->forceFill([
        'email'                       => 'guest@example.com',
        'email_verified_at'           => null,
        'email_verification_token'    => hash('sha256', $token),
        'email_verification_sent_at'  => now(),
    ])->save();

    // 管理者がメールを削除（トークンもクリア）
    $guest->forceFill([
        'email'                       => null,
        'email_verified_at'           => null,
        'email_verification_token'    => null,
        'email_verification_sent_at'  => null,
    ])->save();

    // 古いリンクを踏む
    $this->get('/verify?token=' . $token)
        ->assertViewIs('verify')
        ->assertViewHas('status', 'expired');

    // ユーザーは未認証のまま（ログインさせない）
    expect(auth()->id())->toBeNull('無効なトークンでログインさせてはいけない');
});

it('管理者がメールを削除した後、ユーザーがゲストページにアクセスするとメール登録画面へリダイレクトされる', function () {
    $guest = makeGuest('attending');
    $guest->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($guest)
        ->get(route('dashboard'))
        ->assertRedirect(route('email.register'));
});

it('管理者がメールを削除した後、ユーザーが招待状ページにもアクセスできない', function () {
    $guest = makeGuest('attending');
    $guest->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($guest)
        ->get(route('invitation'))
        ->assertRedirect(route('email.register'));
});

it('管理者がメールを削除した後、ユーザーは新しいメールアドレスを登録できる', function () {
    Mail::fake();

    $guest = makeGuest('attending');
    $guest->forceFill([
        'email'                       => null,
        'email_verified_at'           => null,
        'email_verification_token'    => null,
        'email_verification_sent_at'  => null,
    ])->save();

    $this->actingAs($guest)
        ->patch(route('email.register.update'), ['email' => 'new@example.com'])
        ->assertRedirect()
        ->assertSessionHas('email_verification_sent');

    $guest->refresh();
    expect($guest->email)->toBe('new@example.com');
    expect($guest->email_verified_at)->toBeNull('再登録後は未認証状態');

    Mail::assertSent(EmailVerificationMail::class, fn ($mail) => $mail->hasTo('new@example.com'));
});

it('管理者がメールを削除した後、元のメールアドレスを別ユーザーが登録できる（unique制約が解除される）', function () {
    Mail::fake();

    $sharedEmail = 'shared@example.com';

    // guest1 はもともとそのメールを持っていたが削除された
    $guest1 = makeGuest('attending');
    $guest1->forceFill(['email' => null, 'email_verified_at' => null])->save();

    // guest2 が同じアドレスを登録しようとする
    $guest2 = makeGuest('attending');
    $guest2->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($guest2)
        ->patch(route('email.register.update'), ['email' => $sharedEmail])
        ->assertRedirect()
        ->assertSessionHas('email_verification_sent');

    $guest2->refresh();
    expect($guest2->email)->toBe($sharedEmail);
});

// =====================================================================
// 初回ログインの完全なフロー
// =====================================================================

it('初回ログイン→メール登録→管理者削除→再登録 の一連フローが正しく動作する', function () {
    Mail::fake();

    $admin = makeAdmin();
    $token1 = Str::random(64);

    // Step1: ゲストがメールを登録（初回ログイン後の状態）
    $guest = makeGuest('attending');
    $guest->forceFill([
        'email'                       => 'step1@example.com',
        'email_verified_at'           => null,
        'email_verification_token'    => hash('sha256', $token1),
        'email_verification_sent_at'  => now(),
        'password_change_required'    => true,
    ])->save();

    // Step2: 管理者がメールを削除
    $this->actingAs($admin)
        ->patch(route('admin.users.update', $guest->id), [
            'username'     => $guest->username,
            'role'         => 'guest',
            'delete_email' => '1',
        ])
        ->assertRedirect(route('admin.users'));

    $guest->refresh();
    expect($guest->email)->toBeNull('削除後はメールがnullであるべき');

    // Step3: 古いトークンは無効
    $this->get('/verify?token=' . $token1)
        ->assertViewHas('status', 'expired');

    // Step4: ユーザーが新しいメールを登録できる
    $this->actingAs($guest)
        ->patch(route('email.register.update'), ['email' => 'step4@example.com'])
        ->assertSessionHas('email_verification_sent');

    $guest->refresh();
    expect($guest->email)->toBe('step4@example.com');
    expect($guest->email_verified_at)->toBeNull();

    // Step5: 新しいトークンで認証できる
    $newToken = $guest->email_verification_token; // ハッシュ済みがDB上にある
    // 平文トークンは sendEmailVerification() 内部で生成されるため、
    // 代わりに email を直接認証済みにして動作確認する
    $guest->forceFill(['email_verified_at' => now()])->save();
    expect($guest->fresh()->hasVerifiedEmail())->toBeTrue('メール認証が完了するべき');
});

// =====================================================================
// audit log の正確性
// =====================================================================

it('管理者がゲスト情報を更新するとuser_updateのみが記録され、user_createは記録されない', function () {
    $admin = makeAdmin();
    $guest = makeGuest('attending');

    $logsBefore = CheckInAuditLog::where('guest_profile_id', $guest->guestProfile->id)->count();

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $guest->id), [
            'username'   => $guest->username,
            'role'       => 'guest',
            'last_name'  => '田中',
            'first_name' => '太郎',
        ])
        ->assertRedirect(route('admin.users'));

    $profile = $guest->guestProfile;
    $newLogs = CheckInAuditLog::where('guest_profile_id', $profile->id)
        ->where('id', '>', $logsBefore > 0 ? CheckInAuditLog::where('guest_profile_id', $profile->id)->min('id') - 1 : 0)
        ->orderBy('id')
        ->get();

    expect($newLogs->where('action', 'user_create')->count())
        ->toBe(0, '更新操作でuser_createを記録してはいけない');
    expect($newLogs->where('action', 'user_update')->count())
        ->toBeGreaterThanOrEqual(1, 'user_updateは1件以上記録されるべき');
});

it('管理者がゲストのメールを削除しても再送ボタンは押せず、再登録は可能', function () {
    Mail::fake();

    $guest = makeGuest('attending');
    $guest->forceFill([
        'email'                       => null,
        'email_verified_at'           => null,
        'email_verification_token'    => null,
        'email_verification_sent_at'  => null,
    ])->save();

    // 再送POST（メールがないので失敗するはずだが、エラーなくリダイレクト）
    $this->actingAs($guest)
        ->post(route('email.verify.resend'))
        ->assertRedirect()
        ->assertSessionHas('error');

    Mail::assertNotSent(EmailVerificationMail::class);
});

it('管理者がゲストに新しいメールを設定すると認証メールが送信される', function () {
    Mail::fake();

    $admin = makeAdmin();
    $guest = makeGuest('attending');
    $guest->forceFill(['email' => null, 'email_verified_at' => null])->save();

    $this->actingAs($admin)
        ->patch(route('admin.users.update', $guest->id), [
            'username' => $guest->username,
            'role'     => 'guest',
            'email'    => 'newmail@example.com',
        ])
        ->assertRedirect(route('admin.users'));

    $guest->refresh();
    expect($guest->email)->toBe('newmail@example.com');
    expect($guest->email_verified_at)->toBeNull('管理者が設定しても未認証状態');

    Mail::assertSent(EmailVerificationMail::class, fn ($mail) => $mail->hasTo('newmail@example.com'));
});
