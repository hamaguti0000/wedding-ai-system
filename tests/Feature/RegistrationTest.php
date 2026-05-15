<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────
// 正常系
// ─────────────────────────────────────────────

it('正しい入力で登録できてホームにリダイレクトされる', function () {
    Mail::fake();

    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('users', ['email' => 'taro@example.com']);
    $this->assertAuthenticated();
});

it('登録後メールアドレスは未認証状態になる', function () {
    Mail::fake();

    $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '花子',
        'email'                 => 'hanako@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $user = User::where('email', 'hanako@example.com')->first();
    expect($user->email_verified_at)->toBeNull();
});

it('ログイン済みなら登録ページはホームへリダイレクト', function () {
    $user = User::factory()->create(['role' => 'guest']);
    $this->actingAs($user)->get('/register')->assertRedirect(route('dashboard'));
});

// ─────────────────────────────────────────────
// 1. メールアドレスの形式チェック
// ─────────────────────────────────────────────

it('メールアドレスが不正な形式だと422が返る', function (string $email) {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => $email,
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertStatus(302);
    $res->assertSessionHasErrors('email');
})->with([
    'ドット始まり'        => '.invalid@example.com',
    '@なし'               => 'notanemail',
    'ドメインなし'        => 'user@',
    'スペース含む'        => 'user @example.com',
    '連続ドット'          => 'user..name@example.com',
    '@2つ'               => 'user@@example.com',
]);

it('メールアドレスのエラーメッセージが日本語', function () {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'not-an-email',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors(['email']);
    $errors = session('errors');
    expect($errors->first('email'))->toBe('正しいメールアドレスを入力してください');
});

// ─────────────────────────────────────────────
// 2. メールアドレスの重複チェック
// ─────────────────────────────────────────────

it('同じメールアドレスで2重登録できない', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $res = $this->post('/register', [
        'last_name'             => '田中',
        'first_name'            => '次郎',
        'email'                 => 'existing@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))
        ->toBe('このメールアドレスは既に登録されています');
    expect(User::where('email', 'existing@example.com')->count())->toBe(1);
});

it('メールアドレスは大文字小文字を区別しない重複チェック', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $res = $this->post('/register', [
        'last_name'             => '鈴木',
        'first_name'            => '三郎',
        'email'                 => 'TEST@EXAMPLE.COM',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors('email');
});

// ─────────────────────────────────────────────
// 3. パスワード強度チェック
// ─────────────────────────────────────────────

it('7文字パスワードは拒否される', function () {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'Short1',
        'password_confirmation' => 'Short1',
    ]);

    $res->assertSessionHasErrors('password');
    expect(session('errors')->first('password'))
        ->toBe('パスワードは8文字以上で入力してください');
});

it('8文字ちょうどのパスワードは許可される', function () {
    Mail::fake();

    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'Secure01',
        'password_confirmation' => 'Secure01',
    ]);

    $res->assertRedirect(route('dashboard'));
});

it('パスワードが255文字を超えると拒否される', function () {
    $longPass = str_repeat('a', 256);

    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => $longPass,
        'password_confirmation' => $longPass,
    ]);

    $res->assertSessionHasErrors('password');
});

it('パスワードと確認用が一致しないと拒否される', function () {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'DifferentPass1',
    ]);

    $res->assertSessionHasErrors('password');
    expect(session('errors')->first('password'))
        ->toBe('パスワードが一致しません');
});

// ─────────────────────────────────────────────
// 4. 必須項目が空の場合
// ─────────────────────────────────────────────

it('全項目空のときすべて必須エラーが返る', function () {
    $res = $this->post('/register', []);

    $res->assertSessionHasErrors(['last_name', 'first_name', 'email', 'password']);
});

it('姓が空のとき日本語エラー', function () {
    $res = $this->post('/register', [
        'last_name'             => '',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors('last_name');
    expect(session('errors')->first('last_name'))->toBe('姓は必須です');
});

it('名が空のとき日本語エラー', function () {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '',
        'email'                 => 'taro@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors('first_name');
    expect(session('errors')->first('first_name'))->toBe('名は必須です');
});

it('メールが空のとき日本語エラー', function () {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => '',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toBe('メールアドレスは必須です');
});

it('パスワードが空のとき日本語エラー', function () {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => '',
        'password_confirmation' => '',
    ]);

    $res->assertSessionHasErrors('password');
    expect(session('errors')->first('password'))->toBe('パスワードは必須です');
});

it('確認用パスワードが空のとき日本語エラー', function () {
    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => '',
    ]);

    $res->assertSessionHasErrors('password_confirmation');
    expect(session('errors')->first('password_confirmation'))
        ->toBe('確認用パスワードは必須です');
});

// ─────────────────────────────────────────────
// 5. 文字数上限
// ─────────────────────────────────────────────

it('姓が51文字以上のとき日本語エラー', function () {
    $res = $this->post('/register', [
        'last_name'             => str_repeat('あ', 51),
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors('last_name');
    expect(session('errors')->first('last_name'))
        ->toBe('姓は50文字以内で入力してください');
});

it('メールが256文字以上のとき日本語エラー', function () {
    $email = str_repeat('a', 248) . '@e.com'; // 255文字超

    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => $email,
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertSessionHasErrors('email');
});

// ─────────────────────────────────────────────
// ルートアクセス
// ─────────────────────────────────────────────

it('GET /register は200を返す', function () {
    $this->get('/register')->assertStatus(200);
});

it('POST /register は認証不要でアクセスできる', function () {
    Mail::fake();

    $res = $this->post('/register', [
        'last_name'             => '山田',
        'first_name'            => '太郎',
        'email'                 => 'taro@example.com',
        'password'              => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ]);

    $res->assertRedirect(route('dashboard'));
});
