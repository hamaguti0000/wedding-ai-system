<?php

use App\Models\GuestProfile;
use App\Models\GuestTaskAssignment;
use App\Models\ProgramItem;
use App\Models\TaskProgramItem;
use App\Models\WeddingSetting;
use App\Models\WeddingTask;

// ── /program ゲストアクセス ────────────────────────────────

describe('ProgramController /program', function () {

    it('ゲストがプログラムページに 200 でアクセスできる', function () {
        $this->actingAs(makeGuest('attending'))->get(route('program'))->assertOk();
    });

    it('役割なしゲストはデフォルトプログラムを表示する', function () {
        ProgramItem::create(['title' => 'デフォルト挙式', 'display_order' => 1]);

        $response = $this->actingAs(makeGuest('attending'))->get(route('program'));
        $response->assertOk()->assertSee('デフォルト挙式');
        $response->assertViewHas('isRoleProgram', false);
    });

    it('役割ありでプログラムなしのゲストはデフォルトプログラムを表示する', function () {
        ProgramItem::create(['title' => '共通プログラム', 'display_order' => 1]);
        $task  = WeddingTask::create(['name' => '受付', 'sort_order' => 1]);
        $guest = makeGuest('attending');
        GuestTaskAssignment::create(['user_id' => $guest->id, 'wedding_task_id' => $task->id]);

        $response = $this->actingAs($guest)->get(route('program'));
        $response->assertOk()->assertSee('共通プログラム');
        $response->assertViewHas('isRoleProgram', false);
    });

    it('役割にプログラムがある場合は役割別プログラムを表示する', function () {
        ProgramItem::create(['title' => 'デフォルト挙式', 'display_order' => 1]);
        $task = WeddingTask::create(['name' => '受付係', 'sort_order' => 1]);
        TaskProgramItem::create(['wedding_task_id' => $task->id, 'title' => '受付開始', 'sort_order' => 1]);

        $guest = makeGuest('attending');
        GuestTaskAssignment::create(['user_id' => $guest->id, 'wedding_task_id' => $task->id]);

        $response = $this->actingAs($guest)->get(route('program'));
        $response->assertOk()
            ->assertSee('受付開始')
            ->assertDontSee('デフォルト挙式');
        $response->assertViewHas('isRoleProgram', true);
    });

    it('WeddingSetting レコードがなくてもプログラムページが 500 にならない', function () {
        // 設定レコードを作らずにアクセス（$setting = null のケース）
        $this->actingAs(makeGuest('attending'))->get(route('program'))->assertOk();
    });

    it('未認証ユーザーは /login にリダイレクト', function () {
        $this->get(route('program'))->assertRedirect(route('login'));
    });
});

// ── 管理画面ページ ─────────────────────────────────────────

describe('管理画面ページ', function () {

    it('管理者が役割管理ページに 200 でアクセスできる', function () {
        $this->actingAs(makeAdmin())->get(route('admin.tasks'))->assertOk();
    });

    it('管理者がプログラム管理ページに 200 でアクセスできる', function () {
        $this->actingAs(makeAdmin())->get(route('admin.program'))->assertOk();
    });

    it('管理者が FAQ 管理ページに 200 でアクセスできる', function () {
        $this->actingAs(makeAdmin())->get(route('admin.faq'))->assertOk();
    });

    it('管理者がお知らせ管理ページに 200 でアクセスできる', function () {
        $this->actingAs(makeAdmin())->get(route('admin.news'))->assertOk();
    });

    it('ゲストは役割管理ページに 403', function () {
        $this->actingAs(makeGuest('attending'))->get(route('admin.tasks'))->assertForbidden();
    });

    it('ゲストはプログラム管理ページに 403', function () {
        $this->actingAs(makeGuest('attending'))->get(route('admin.program'))->assertForbidden();
    });
});

// ── WeddingSetting が存在しないとき各ページが落ちない ─────

describe('WeddingSetting レコードなしで各ページが 500 にならない', function () {

    it('ホームページが 200', function () {
        $this->actingAs(makeGuest('attending'))->get(route('dashboard'))->assertOk();
    });

    it('招待状ページが 200', function () {
        $this->actingAs(makeGuest('attending'))->get(route('invitation'))->assertOk();
    });

    it('プロフィールページが 200', function () {
        $this->actingAs(makeGuest('attending'))->get(route('profile.edit'))->assertOk();
    });

    it('プログラムページが 200', function () {
        $this->actingAs(makeGuest('attending'))->get(route('program'))->assertOk();
    });
});
