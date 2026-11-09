<?php

use App\Http\Controllers\ImpersonationController;
use App\Models\User;

// ─── 権限 ──────────────────────────────────────────────

describe('代理ログインの権限', function () {

    it('未認証では開始できない', function () {
        $target = makeGuest('attending');

        $this->post(route('admin.users.impersonate', $target->id))
            ->assertRedirect('/login');
    });

    /** adminミドルウェアがホームへ戻す（ゲストに403画面を見せない）。 */
    it('ゲストは他人に代理ログインできない', function () {
        $me     = makeGuest('attending');
        $target = makeGuest('attending');

        $this->actingAs($me)
            ->post(route('admin.users.impersonate', $target->id))
            ->assertRedirect(route('dashboard'));

        expect(auth()->id())->toBe($me->id);
    });

    it('管理者はゲストに代理ログインできる', function () {
        $admin  = makeAdmin();
        $target = makeGuest('attending');

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $target->id))
            ->assertRedirect(route('dashboard'));

        expect(auth()->id())->toBe($target->id);
        expect(session(ImpersonationController::SESSION_KEY))->toBe($admin->id);
    });

    /** 管理者を代理対象にできると、権限の横取りに使えてしまう。 */
    it('管理者アカウントには代理ログインできない', function () {
        $admin  = makeAdmin();
        $other  = makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.users.impersonate', $other->id));

        expect(auth()->id())->toBe($admin->id);
        expect(session()->has(ImpersonationController::SESSION_KEY))->toBeFalse();
    });

    /** 入れ子で乗り換えられると元の管理者へ戻れなくなる。 */
    it('代理ログイン中にさらに別のユーザーへは移れない', function () {
        $admin  = makeAdmin();
        $first  = makeGuest('attending');
        $second = makeGuest('attending');

        $this->actingAs($admin)->post(route('admin.users.impersonate', $first->id));
        expect(auth()->id())->toBe($first->id);

        $this->post(route('admin.users.impersonate', $second->id));

        expect(auth()->id())->toBe($first->id);
        expect(session(ImpersonationController::SESSION_KEY))->toBe($admin->id);
    });
});

// ─── 終了 ──────────────────────────────────────────────

describe('代理ログインの終了', function () {

    it('管理者に戻れる', function () {
        $admin  = makeAdmin();
        $target = makeGuest('attending');

        $this->actingAs($admin)->post(route('admin.users.impersonate', $target->id));
        expect(auth()->id())->toBe($target->id);

        $this->post(route('impersonate.stop'))
            ->assertRedirect(route('admin.dashboard'));

        expect(auth()->id())->toBe($admin->id);
        expect(session()->has(ImpersonationController::SESSION_KEY))->toBeFalse();
    });

    it('代理していない普通のゲストが叩いてもログイン状態は変わらない', function () {
        $guest = makeGuest('attending');

        $this->actingAs($guest)
            ->post(route('impersonate.stop'))
            ->assertRedirect(route('dashboard'));

        expect(auth()->id())->toBe($guest->id);
    });
});

// ─── 強制画面の免除 ────────────────────────────────────

describe('代理ログイン中の強制リダイレクト', function () {

    /**
     * パスワード未変更のゲストは通常パスワード変更画面へ飛ばされるが、
     * 代理ログイン中に飛ばされるとゲスト画面の確認ができない。
     */
    it('パスワード変更を求められているゲストでも画面を確認できる', function () {
        $admin  = makeAdmin();
        $target = makeGuest('attending');
        $target->password_change_required = true;
        $target->save();

        $this->actingAs($admin)->post(route('admin.users.impersonate', $target->id));

        $this->get('/home')->assertStatus(200);
    });
});
