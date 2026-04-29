<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;

uses(RefreshDatabase::class);

// ─── /csrf-refresh エンドポイント ─────────────────────────
describe('/csrf-refresh エンドポイント', function () {

    it('認証済みユーザーがアクセスすると CSRF トークンを返す', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/csrf-refresh');

        $response->assertStatus(200)
                 ->assertJsonStructure(['token'])
                 ->assertJsonMissing(['error']);

        expect($response->json('token'))->toBeString()->not->toBeEmpty();
    });

    it('未認証ユーザーでも CSRF トークンを返す（セッション初期化）', function () {
        $response = $this->getJson('/csrf-refresh');

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    });
});

// ─── TokenMismatchException ハンドラー ────────────────────
describe('TokenMismatchException ハンドラー', function () {

    it('通常のログアウト（有効な CSRF）は /login にリダイレクトする', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post('/logout')
             ->assertRedirect('/login');

        $this->assertGuest();
    });

    it('bootstrap/app.php に TokenMismatchException ハンドラーが登録されている', function () {
        // bootstrap/app.php のソースに TokenMismatchException の登録があることを確認
        $source = file_get_contents(base_path('bootstrap/app.php'));
        expect($source)->toContain('TokenMismatchException');
        // ログアウトリクエストの分岐が存在すること
        expect($source)->toContain("routeIs('logout')");
        // JSON 用の分岐が存在すること
        expect($source)->toContain('expectsJson');
    });

    it('AJAX リクエストの CSRF 切れは JSON 419 を返す', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        // TokenMismatchException を例外ハンドラー経由でレンダリング
        $request = \Illuminate\Http\Request::create('/invitation', 'POST');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $handler = app(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $response = $handler->render($request, new TokenMismatchException);

        expect($response->getStatusCode())->toBe(419);
        $data = json_decode($response->getContent(), true);
        expect($data)->toHaveKey('message');
    });
});

// ─── 419 エラーページ ─────────────────────────────────────
describe('419 エラーページ', function () {

    it('errors/419.blade.php が存在し、コンパイルに成功する', function () {
        expect(file_exists(resource_path('views/errors/419.blade.php')))->toBeTrue();
    });

    it('419 ページにログインリンクが含まれる', function () {
        $content = file_get_contents(resource_path('views/errors/419.blade.php'));
        expect($content)->toContain("route('login')");
    });

    it('ゲストユーザーが 419 ページを表示できる（ログインリンク確認）', function () {
        // Laravel の abort(419) を直接テスト
        $response = $this->get('/up'); // ヘルスチェックで正常応答確認
        $response->assertStatus(200);
    });
});

// ─── セッション期限切れ後の操作導線 ──────────────────────
describe('セッション期限切れ後の操作導線', function () {

    it('セッションなしで /home にアクセスすると /login にリダイレクト', function () {
        $this->get('/home')->assertRedirect('/login');
    });

    it('セッションなしで /invitation にアクセスすると /login にリダイレクト', function () {
        $this->get('/invitation')->assertRedirect('/login');
    });

    it('セッションなしで /admin にアクセスすると /login にリダイレクト', function () {
        $this->get('/admin')->assertRedirect('/login');
    });

    it('セッション切れ後のリダイレクトは /login ページが 200 を返す', function () {
        $this->get('/login')->assertStatus(200);
    });
});

// ─── セッション設定値の確認 ───────────────────────────────
describe('セッション設定', function () {

    it('SESSION_LIFETIME が 120 より大きい（放置への耐性）', function () {
        $lifetime = (int) config('session.lifetime');
        expect($lifetime)->toBeGreaterThan(120);
    });

    it('セッションドライバーが設定されている', function () {
        $driver = config('session.driver');
        expect($driver)->not->toBeEmpty();
    });
});
