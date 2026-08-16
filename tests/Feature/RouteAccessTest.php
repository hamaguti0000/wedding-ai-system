<?php

use App\Models\GuestProfile;
use App\Models\EmailAuditLog;
use App\Models\User;

// ─── 未認証アクセス ───────────────────────────────────────
describe('未認証', function () {

    it('/home は /login にリダイレクト', function () {
        $this->get('/home')->assertRedirect('/login');
    });

    it('/admin は /login にリダイレクト', function () {
        $this->get('/admin')->assertRedirect('/login');
    });

    it('/admin/ops は /login にリダイレクト', function () {
        $this->get('/admin/ops')->assertRedirect('/login');
    });

    it('/profile は /login にリダイレクト', function () {
        $this->get('/profile')->assertRedirect('/login');
    });

    it('/invitation は /login にリダイレクト', function () {
        $this->get('/invitation')->assertRedirect('/login');
    });

    it('/seating は /login にリダイレクト', function () {
        $this->get('/seating')->assertRedirect('/login');
    });

    it('/login は 200', function () {
        $this->get('/login')->assertStatus(200);
    });

    it('/register は 200（登録ページが存在する）', function () {
        $this->get('/register')->assertStatus(200);
    });
});

// ─── admin ログイン ───────────────────────────────────────
describe('admin ログイン', function () {

    it('POST /login → /admin にリダイレクト', function () {
        $admin = makeAdmin();
        $this->post('/login', [
            'username' => $admin->username,
            'password' => 'password',
        ])->assertRedirect('/admin');
    });

    it('/admin は 200', function () {
        $this->actingAs(makeAdmin())->get('/admin')->assertStatus(200);
    });

    it('/home → /admin にリダイレクト', function () {
        $this->actingAs(makeAdmin())->get('/home')->assertRedirect('/admin');
    });

    it('/invitation → /admin にリダイレクト', function () {
        $this->actingAs(makeAdmin())->get('/invitation')->assertRedirect('/admin');
    });

    it('/profile は 200', function () {
        $this->actingAs(makeAdmin())->get('/profile')->assertStatus(200);
    });

    it('/admin/users/{id}/qr は 200', function () {
        $guest = makeGuest('attending');
        $this->actingAs(makeAdmin())
            ->get(route('admin.users.qr', $guest->id))
            ->assertStatus(200);
    });

    it('/admin/check-in/guests は 200', function () {
        makeGuest('attending');
        $this->actingAs(makeAdmin())
            ->get(route('admin.checkin.guests'))
            ->assertStatus(200);
    });

    it('/admin/audit/check-in は 200', function () {
        $this->actingAs(makeAdmin())
            ->get(route('admin.audit.checkin'))
            ->assertStatus(200);
    });

    it('/admin/audit/email は 200', function () {
        $admin = makeAdmin();
        $guest = makeGuest();

        EmailAuditLog::record(
            $guest,
            $admin,
            'admin_set',
            null,
            'guest@example.com',
            '管理者がメールアドレスを登録しました',
        );

        $this->actingAs($admin)
            ->get(route('admin.audit.email'))
            ->assertStatus(200)
            ->assertSee('メール操作ログ')
            ->assertSee('guest@example.com');
    });

    it('/seating → /admin/seating にリダイレクト', function () {
        $this->actingAs(makeAdmin())
            ->get('/seating')
            ->assertRedirect(route('admin.seating'));
    });
});

// ─── guest ログイン（未回答）─────────────────────────────
describe('guest ログイン（未回答）', function () {

    it('POST /login → /home にリダイレクト', function () {
        $guest = makeGuest();
        $this->post('/login', [
            'username' => $guest->username,
            'password' => 'password',
        ])->assertRedirect('/home');
    });

    it('/home は 200', function () {
        $this->actingAs(makeGuest())->get('/home')->assertStatus(200);
    });

    it('挙式終了に伴い/homeにRSVP未回答バナーは表示されない', function () {
        $this->actingAs(makeGuest())
            ->get('/home')
            ->assertDontSee('まだご出欠のご回答が届いておりません');
    });

    it('/admin はホームへリダイレクト', function () {
        $this->actingAs(makeGuest())->get('/admin')->assertRedirect(route('dashboard'));
    });

    it('/admin/ops はホームへリダイレクト', function () {
        $this->actingAs(makeGuest())->get('/admin/ops')->assertRedirect(route('dashboard'));
    });

    it('/invitation は 200', function () {
        $this->actingAs(makeGuest())->get('/invitation')->assertStatus(200);
    });

    it('/profile は 200', function () {
        $this->actingAs(makeGuest())->get('/profile')->assertStatus(200);
    });

    it('/seating → /home にリダイレクト（未出席）', function () {
        $this->actingAs(makeGuest())
            ->get('/seating')
            ->assertRedirect(route('dashboard'));
    });
});

// ─── guest ログイン（出席済み）───────────────────────────
describe('guest ログイン（出席済み）', function () {

    it('挙式終了に伴い/home に出席バナーは表示されない', function () {
        $this->actingAs(makeGuest('attending'))
            ->get('/home')
            ->assertDontSee('ご出席のご登録ありがとうございます');
    });

    it('/home に未回答バナーが表示されない', function () {
        $this->actingAs(makeGuest('attending'))
            ->get('/home')
            ->assertDontSee('まだご出欠のご回答が届いておりません');
    });

    it('/seating は 200', function () {
        $this->actingAs(makeGuest('attending'))
            ->get('/seating')
            ->assertStatus(200);
    });
});

// ─── guest ログイン（欠席済み）───────────────────────────
describe('guest ログイン（欠席済み）', function () {

    it('挙式終了に伴い/home に欠席バナーは表示されない', function () {
        $this->actingAs(makeGuest('declining'))
            ->get('/home')
            ->assertDontSee('欠席のご連絡ありがとうございます');
    });

    it('/seating → /home にリダイレクト（欠席）', function () {
        $this->actingAs(makeGuest('declining'))
            ->get('/seating')
            ->assertRedirect(route('dashboard'));
    });
});

// ─── ログアウト ──────────────────────────────────────────
describe('ログアウト', function () {

    it('POST /logout → /login にリダイレクト', function () {
        $this->actingAs(makeGuest())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    });
});

// ─── RSVP フォーム送信 ───────────────────────────────────
describe('RSVP フォーム送信', function () {

    it('有効な出席データ（アレルギーなし）を送信できる', function () {
        $guest = makeGuest();

        $this->actingAs($guest)->post('/invitation', attendingPayload())
            ->assertRedirect('/invitation');

        $p = GuestProfile::where('user_id', $guest->id)->first();
        expect($p->participation)->toBe('attending');
        expect($p->has_allergy)->toBeFalse();
    });

    it('有効な出席データ（アレルギーあり）を送信できる', function () {
        $guest = makeGuest();

        $this->actingAs($guest)->post('/invitation', attendingPayload([
            'has_allergy'   => '1',
            'allergy_notes' => '卵・乳製品アレルギーあり',
        ]))->assertRedirect('/invitation');

        $p = GuestProfile::where('user_id', $guest->id)->first();
        expect($p->has_allergy)->toBeTrue();
        expect($p->allergy_notes)->toBe('卵・乳製品アレルギーあり');
    });

    it('有効な欠席データを送信できる', function () {
        $guest = makeGuest();

        $this->actingAs($guest)->post('/invitation', [
            'participation' => 'declining',
        ])->assertRedirect('/invitation');

        expect(GuestProfile::where('user_id', $guest->id)->value('participation'))
            ->toBe('declining');
    });

    it('出席時にattending_countが未入力 → バリデーションエラー', function () {
        $this->actingAs(makeGuest())->post('/invitation',
            attendingPayload(['attending_count' => ''])
        )->assertSessionHasErrors(['attending_count']);
    });

    it('出席時にhas_allergyが未入力 → バリデーションエラー', function () {
        $this->actingAs(makeGuest())->post('/invitation',
            attendingPayload(['has_allergy' => ''])
        )->assertSessionHasErrors(['has_allergy']);
    });

    it('アレルギーありで詳細未入力 → バリデーションエラー', function () {
        $this->actingAs(makeGuest())->post('/invitation',
            attendingPayload(['has_allergy' => '1', 'allergy_notes' => ''])
        )->assertSessionHasErrors(['allergy_notes']);
    });

    it('欠席時は出席専用フィールドなしで送信できる', function () {
        $guest = makeGuest();

        $this->actingAs($guest)->post('/invitation', [
            'participation' => 'declining',
        ])->assertRedirect('/invitation');

        $p = GuestProfile::where('user_id', $guest->id)->first();
        expect($p->participation)->toBe('declining');
        expect($p->guest_side)->toBeNull();
        expect($p->relationship)->toBeNull();
    });

    it('必須項目なし → バリデーションエラー', function () {
        $this->actingAs(makeGuest())->post('/invitation', [])
            ->assertSessionHasErrors(['participation']);
    });
});
