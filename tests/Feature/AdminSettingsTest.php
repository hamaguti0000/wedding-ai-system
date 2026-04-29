<?php

use App\Models\WeddingSetting;

// ─── GET /admin/settings ──────────────────────────────────

describe('AdminSettingController::edit() 設定画面表示', function () {

    it('管理者が設定画面を表示できる', function () {
        $this->actingAs(makeAdmin())
            ->get('/admin/settings')
            ->assertStatus(200);
    });

    it('ゲストは 403', function () {
        $this->actingAs(makeGuest())
            ->get('/admin/settings')
            ->assertStatus(403);
    });

    it('設定レコードがなくても表示できる（firstOrNew）', function () {
        $this->actingAs(makeAdmin())
            ->get('/admin/settings')
            ->assertStatus(200);

        expect(WeddingSetting::count())->toBe(0);
    });

    it('既存設定が View の $setting に渡される', function () {
        WeddingSetting::create([
            'groom_name'     => '濵口 翔',
            'bride_name'     => '馬場 弥礼',
            'ceremony_date'  => '2026-07-19',
            'ceremony_time'  => '12:00:00',
            'reception_time' => '13:00:00',
            'venue_name'     => 'テスト会場',
            'venue_address'  => '東京都渋谷区1-1-1',
        ]);

        $data = $this->actingAs(makeAdmin())
            ->get('/admin/settings')
            ->getOriginalContent()
            ->getData();

        expect($data['setting'])->toBeInstanceOf(WeddingSetting::class);
        expect($data['setting']->groom_name)->toBe('濵口 翔');
    });
});

// ─── POST /admin/settings ─────────────────────────────────

describe('AdminSettingController::update() 設定保存', function () {

    it('有効なデータで設定を保存・リダイレクトする', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload())
            ->assertRedirect(route('admin.settings'));

        $setting = WeddingSetting::first();
        expect($setting)->not->toBeNull();
        expect($setting->groom_name)->toBe('濵口 翔');
        expect($setting->bride_name)->toBe('馬場 弥礼');
    });

    it('ceremony_time と reception_time は秒付きで保存される', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload([
                'ceremony_time'  => '11:30',
                'reception_time' => '12:30',
            ]));

        $setting = WeddingSetting::first();
        expect($setting->ceremony_time)->toBe('11:30:00');
        expect($setting->reception_time)->toBe('12:30:00');
    });

    it('既存設定を上書き保存できる（レコードが増えない）', function () {
        WeddingSetting::create([
            'groom_name'     => '旧名',
            'bride_name'     => '旧名',
            'ceremony_date'  => '2025-01-01',
            'ceremony_time'  => '10:00:00',
            'reception_time' => '11:00:00',
            'venue_name'     => '旧会場',
            'venue_address'  => '旧住所',
        ]);

        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['groom_name' => '新名']));

        expect(WeddingSetting::count())->toBe(1);
        expect(WeddingSetting::first()->groom_name)->toBe('新名');
    });

    it('venue_url に有効な URL を指定できる', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['venue_url' => 'https://example.com']))
            ->assertSessionHasNoErrors();

        expect(WeddingSetting::first()->venue_url)->toBe('https://example.com');
    });

    it('venue_url は省略可能（null 保存）', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload())
            ->assertSessionHasNoErrors();

        expect(WeddingSetting::first()->venue_url)->toBeNull();
    });

    it('groom_name 未入力はバリデーションエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['groom_name' => '']))
            ->assertSessionHasErrors(['groom_name']);
    });

    it('bride_name 未入力はバリデーションエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['bride_name' => '']))
            ->assertSessionHasErrors(['bride_name']);
    });

    it('ceremony_date に無効な日付はエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['ceremony_date' => 'not-a-date']))
            ->assertSessionHasErrors(['ceremony_date']);
    });

    it('ceremony_time に無効な形式（25:99）はエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['ceremony_time' => '25:99']))
            ->assertSessionHasErrors(['ceremony_time']);
    });

    it('reception_time に無効な形式はエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['reception_time' => 'abc']))
            ->assertSessionHasErrors(['reception_time']);
    });

    it('venue_name 未入力はバリデーションエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['venue_name' => '']))
            ->assertSessionHasErrors(['venue_name']);
    });

    it('venue_address 未入力はバリデーションエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['venue_address' => '']))
            ->assertSessionHasErrors(['venue_address']);
    });

    it('venue_url に無効な URL はエラー', function () {
        $this->actingAs(makeAdmin())
            ->post('/admin/settings', validSettingsPayload(['venue_url' => 'not-a-url']))
            ->assertSessionHasErrors(['venue_url']);
    });

    it('ゲストは 403', function () {
        $this->actingAs(makeGuest())
            ->post('/admin/settings', validSettingsPayload())
            ->assertStatus(403);
    });
});
