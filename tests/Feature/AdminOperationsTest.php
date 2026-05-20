<?php

use App\Models\GuestProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

describe('運営ダッシュボード', function () {

    it('admin は運営ダッシュボードとライブAPIを見られる', function () {
        $this->actingAs(makeAdmin())
            ->get('/admin/ops')
            ->assertStatus(200)
            ->assertSee('運営ダッシュボード');

        $this->actingAs(makeAdmin())
            ->getJson('/admin/ops/live')
            ->assertOk()
            ->assertJsonStructure([
                'summary' => [
                    'total',
                    'attending',
                    'declining',
                    'pending',
                    'checked_in_guests',
                    'checked_in_people',
                    'seat_assigned',
                    'checkin_pending',
                    'response_rate',
                ],
                'recentResponses',
                'recentCheckins',
                'recentOperationLogs',
            ]);
    });
});

describe('ゲスト詳細画面', function () {

    it('admin はゲスト詳細を見られる', function () {
        $guest = makeGuest('attending');

        $this->actingAs(makeAdmin())
            ->get(route('admin.users.show', $guest->id))
            ->assertStatus(200)
            ->assertSee($guest->username)
            ->assertSee('チェックインQR');
    });

    it('admin は専用QR画面を見られる', function () {
        $guest = makeGuest('attending');

        $this->actingAs(makeAdmin())
            ->get(route('admin.users.qr', $guest->id))
            ->assertStatus(200)
            ->assertSee('受付QR');
    });

    it('admin は操作ログ画面を見られる', function () {
        $this->actingAs(makeAdmin())
            ->get(route('admin.audit.checkin'))
            ->assertStatus(200)
            ->assertSee('操作ログ');
    });
});

describe('ユーザー管理', function () {

    it('admin はCSVを確認してからゲストを一括登録できる', function () {
        $csv = implode("\n", [
            'No.,関係,姓,名,ユーザー名,敬称,肩書き1,肩書き2,お言葉',
            '1,親族,濵口,達彦,hamaguchi_tatsuhiko,様,新郎父,,',
            '2,親族,小山,りみ,koyama_rimi,様,新郎従姉妹,母側兄弟の長女,よろしくお願いします',
        ]);

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs(makeAdmin())
            ->post(route('admin.users.import.preview'), [
                'guest_csv' => $file,
            ])
            ->assertOk()
            ->assertSee('CSV登録確認')
            ->assertSee('hamaguchi_tatsuhiko')
            ->assertSee('登録可');

        $this->actingAs(makeAdmin())
            ->post(route('admin.users.import'), [
                'initial_password' => 'password123',
                'rows' => [
                    [
                        'last_name' => '濵口',
                        'first_name' => '達彦',
                        'username' => 'hamaguchi_tatsuhiko',
                        'relationship_text' => '親族',
                        'title1' => '新郎父',
                        'title2' => '',
                        'notes' => '',
                    ],
                    [
                        'last_name' => '小山',
                        'first_name' => 'りみ',
                        'username' => 'koyama_rimi_fixed',
                        'relationship_text' => '親族',
                        'title1' => '新郎従姉妹',
                        'title2' => '母側兄弟の長女',
                        'notes' => 'よろしくお願いします',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', [
            'username' => 'hamaguchi_tatsuhiko',
            'role' => 'guest',
            'password_change_required' => true,
        ]);
        $this->assertDatabaseHas('guest_profiles', [
            'last_name' => '濵口',
            'first_name' => '達彦',
            'guest_side' => 'groom',
            'relationship' => 'family',
            'relationship_detail' => '新郎父',
        ]);
        $this->assertDatabaseHas('guest_profiles', [
            'last_name' => '小山',
            'first_name' => 'りみ',
            'notes' => 'よろしくお願いします',
        ]);
        $this->assertDatabaseHas('users', [
            'username' => 'koyama_rimi_fixed',
        ]);
    });

    it('CSV内の登録済みユーザー名と重複を一覧で見られる', function () {
        User::factory()->create(['username' => 'existing_guest']);

        $file = UploadedFile::fake()->createWithContent(
            'guests.csv',
            "姓,名,ユーザー名\n山田,太郎,existing_guest\n田中,花子,duplicate_guest\n佐藤,一郎,duplicate_guest"
        );

        $this->actingAs(makeAdmin())
            ->post(route('admin.users.import.preview'), [
                'guest_csv' => $file,
            ])
            ->assertOk()
            ->assertSee('既に登録済みのユーザー名です')
            ->assertSee('CSV内でユーザー名が重複しています')
            ->assertSee('要修正');

        expect(User::where('username', 'existing_guest')->count())->toBe(1);
    });

    it('CSVのデータエラーを登録前に一覧で見られる', function () {
        $file = UploadedFile::fake()->createWithContent(
            'guests.csv',
            "姓,名,ユーザー名\n小山,？,bad user\n山田,太郎,valid_user,余分な列"
        );

        $this->actingAs(makeAdmin())
            ->post(route('admin.users.import.preview'), [
                'guest_csv' => $file,
            ])
            ->assertOk()
            ->assertSee('ユーザー名は半角英数字')
            ->assertSee('名に未確認文字')
            ->assertSee('CSVの列数がヘッダーより多いです')
            ->assertSee('要修正');
    });

    it('CSVにユーザー名ヘッダーがない場合はエラーにする', function () {
        $file = UploadedFile::fake()->createWithContent(
            'guests.csv',
            "姓,名\n山田,太郎"
        );

        $this->actingAs(makeAdmin())
            ->from(route('admin.users'))
            ->post(route('admin.users.import.preview'), [
                'guest_csv' => $file,
            ])
            ->assertRedirect(route('admin.users'))
            ->assertSessionHasErrors('guest_csv');
    });

    it('admin はユーザーを一括削除できる', function () {
        $admin = makeAdmin();
        $guestA = makeGuest('pending');
        $guestB = makeGuest('pending');

        $this->actingAs($admin)
            ->delete(route('admin.users.bulk-destroy'), [
                'user_ids' => [$guestA->id, $guestB->id],
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseMissing('users', ['id' => $guestA->id]);
        $this->assertDatabaseMissing('users', ['id' => $guestB->id]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    });

    it('一括削除で自分自身は削除されない', function () {
        $admin = makeAdmin();
        $guest = makeGuest('pending');

        $this->actingAs($admin)
            ->delete(route('admin.users.bulk-destroy'), [
                'user_ids' => [$admin->id, $guest->id],
            ])
            ->assertRedirect(route('admin.users'));

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('users', ['id' => $guest->id]);
    });
});

describe('受付チェックイン', function () {

    it('admin はチェックイン画面を見られる', function () {
        $guest = makeGuest('attending');

        $this->actingAs(makeAdmin())
            ->get(route('admin.checkin.show', $guest->guestProfile->checkin_token))
            ->assertStatus(200)
            ->assertSee($guest->username);
    });

    it('QRトークンで受付済みにできる', function () {
        $guest = makeGuest('attending');
        $profile = $guest->guestProfile;

        $this->actingAs(makeAdmin())
            ->postJson(route('admin.checkin.store', $profile->checkin_token), [
                'token' => $profile->checkin_token,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $profile->refresh();
        expect($profile->checked_in_at)->not->toBeNull();
        expect($profile->checked_in_by_user_id)->toBe(auth()->id());
    });

    it('画像アップロードからQRを読み取れる', function () {
        Http::fake([
            'api.qrserver.com/*' => Http::response([
                [
                    'symbol' => [
                        ['data' => 'https://example.test/admin/check-in/sample-token'],
                    ],
                ],
            ], 200),
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'qr.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO3Z4X0AAAAASUVORK5CYII=')
        );

        $this->actingAs(makeAdmin())
            ->postJson(route('admin.checkin.scan'), [
                'file' => $file,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('token', 'sample-token');
    });
});

describe('受付一覧', function () {

    it('admin は受付一覧を見られる', function () {
        makeGuest('attending');

        $this->actingAs(makeAdmin())
            ->get(route('admin.checkin.guests'))
            ->assertStatus(200)
            ->assertSee('受付一覧');
    });

    it('admin は一覧から手動で受付できる', function () {
        $guest = makeGuest('attending');
        $profile = $guest->guestProfile;

        $this->actingAs(makeAdmin())
            ->post(route('admin.checkin.guests.check-in', $profile))
            ->assertRedirect(route('admin.checkin.show', $profile->checkin_token));

        $profile->refresh();
        expect($profile->checked_in_at)->not->toBeNull();
        expect($profile->checked_in_by_user_id)->toBe(auth()->id());
    });

    it('受付操作は操作ログに残る', function () {
        $guest = makeGuest('attending');
        $profile = $guest->guestProfile;

        $this->actingAs(makeAdmin())
            ->post(route('admin.checkin.guests.check-in', $profile))
            ->assertRedirect();

        $this->actingAs(makeAdmin())
            ->delete(route('admin.checkin.guests.cancel', $profile))
            ->assertRedirect(route('admin.checkin.guests'));

        $this->assertDatabaseCount('check_in_audit_logs', 2);
        $this->assertDatabaseHas('check_in_audit_logs', [
            'guest_profile_id' => $profile->id,
            'action' => 'check_in',
            'source' => 'manual',
        ]);
        $this->assertDatabaseHas('check_in_audit_logs', [
            'guest_profile_id' => $profile->id,
            'action' => 'cancel',
            'source' => 'manual',
        ]);
    });

    it('出欠回答は操作ログに残る', function () {
        $guest = makeGuest();

        $this->actingAs($guest)
            ->post(route('invitation.update'), attendingPayload())
            ->assertRedirect(route('invitation'));

        $profile = $guest->guestProfile()->firstOrFail();

        $this->assertDatabaseHas('check_in_audit_logs', [
            'guest_profile_id' => $profile->id,
            'action' => 'rsvp_update',
            'source' => 'rsvp',
        ]);
    });

    it('admin は一覧から受付取り消しできる', function () {
        $guest = makeGuest('attending');
        $profile = $guest->guestProfile;

        $this->actingAs(makeAdmin())
            ->post(route('admin.checkin.guests.check-in', $profile))
            ->assertRedirect();

        $profile->refresh();
        expect($profile->isCheckedIn())->toBeTrue();

        $this->actingAs(makeAdmin())
            ->delete(route('admin.checkin.guests.cancel', $profile))
            ->assertRedirect(route('admin.checkin.guests'));

        $profile->refresh();
        expect($profile->checked_in_at)->toBeNull();
        expect($profile->checked_in_by_user_id)->toBeNull();
    });
});
