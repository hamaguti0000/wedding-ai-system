<?php

use App\Models\GuestProfile;

describe('PATCH /admin/users/{id}/guest-info アクセス制御', function () {

    it('未認証 → /login にリダイレクト', function () {
        $guest = makeGuest('attending');

        $this->patch(route('admin.users.guest-info', $guest), ['guest_side' => 'groom'])
            ->assertRedirect('/login');
    });

    it('ゲストは更新できず /home へリダイレクトされる', function () {
        $guest  = makeGuest('attending');
        $target = makeGuest('attending');

        $this->actingAs($guest)
            ->patch(route('admin.users.guest-info', $target), ['guest_side' => 'groom'])
            ->assertRedirect(route('dashboard'));

        expect($target->fresh()->guestProfile->guest_side)->toBeNull();
    });

    it('管理者アカウントに対しては422を返す', function () {
        $admin  = makeAdmin();
        $target = makeAdmin();

        $this->actingAs($admin)
            ->patch(route('admin.users.guest-info', $target), ['guest_side' => 'groom'])
            ->assertStatus(422);
    });
});

describe('PATCH /admin/users/{id}/guest-info 更新内容', function () {

    it('お立場とご関係だけを更新できる', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');
        GuestProfile::where('user_id', $guest->id)->update(['guest_side' => 'bride']);

        $response = $this->actingAs($admin)
            ->patch(route('admin.users.guest-info', $guest), [
                'guest_side'   => 'groom',
                'relationship' => 'family',
            ]);

        $response->assertOk()->assertJson(['success' => true, 'guest_side' => 'groom']);

        $profile = $guest->fresh()->guestProfile;
        expect($profile->guest_side)->toBe('groom');
        expect($profile->relationship)->toBe('family');
    });

    it('他のRSVP項目（出欠・人数など）は変更されない', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');
        GuestProfile::where('user_id', $guest->id)->update([
            'attending_count' => 3,
            'allergy_notes'   => '甲殻類アレルギー',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.guest-info', $guest), ['guest_side' => 'bride']);

        $profile = $guest->fresh()->guestProfile;
        expect($profile->participation)->toBe('attending');
        expect($profile->attending_count)->toBe(3);
        expect($profile->allergy_notes)->toBe('甲殻類アレルギー');
    });

    it('プロフィール未作成のゲストにも新規作成して保存できる', function () {
        $admin = makeAdmin();
        $guest = makeGuest(); // プロフィールなし

        $this->actingAs($admin)
            ->patch(route('admin.users.guest-info', $guest), ['guest_side' => 'groom'])
            ->assertOk();

        expect($guest->fresh()->guestProfile?->guest_side)->toBe('groom');
    });

    it('不正な値は422で拒否される', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');

        $this->actingAs($admin)
            ->patchJson(route('admin.users.guest-info', $guest), ['guest_side' => 'invalid'])
            ->assertStatus(422);
    });

    it('空文字を送ると未設定に戻せる', function () {
        $admin = makeAdmin();
        $guest = makeGuest('attending');
        GuestProfile::where('user_id', $guest->id)->update(['guest_side' => 'groom']);

        $this->actingAs($admin)
            ->patch(route('admin.users.guest-info', $guest), ['guest_side' => ''])
            ->assertOk();

        expect($guest->fresh()->guestProfile->guest_side)->toBeNull();
    });
});
