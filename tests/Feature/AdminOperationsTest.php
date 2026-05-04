<?php

use App\Models\GuestProfile;
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
