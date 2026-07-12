<?php

use App\Models\WeddingSetting;
use Illuminate\Support\Facades\Storage;

it('管理画面のプロフィール写真URLに更新時刻ベースのクエリパラメータが付き、ブラウザキャッシュを回避する', function () {
    Storage::fake('public');
    $admin = makeAdmin();

    $setting = WeddingSetting::updateOrCreate([], validSettingsPayload([
        'groom_photo' => 'profiles/groom.jpg',
        'bride_photo' => 'profiles/bride.jpg',
    ]));

    $response = $this->actingAs($admin)->get(route('admin.profiles'));

    $response->assertOk();
    $response->assertSee('storage/profiles/groom.jpg?v=' . $setting->updated_at->timestamp, false);
    $response->assertSee('storage/profiles/bride.jpg?v=' . $setting->updated_at->timestamp, false);
});

it('公開プロフィール一覧のURLにもキャッシュ回避用パラメータが付く', function () {
    $setting = WeddingSetting::updateOrCreate([], validSettingsPayload([
        'groom_photo' => 'profiles/groom.jpg',
        'bride_photo' => 'profiles/bride.jpg',
    ]));

    $guest = makeGuest('attending');
    $response = $this->actingAs($guest)->get(route('profiles.index'));

    $response->assertOk();
    $response->assertSee('storage/profiles/groom.jpg?v=' . $setting->updated_at->timestamp, false);
    $response->assertSee('storage/profiles/bride.jpg?v=' . $setting->updated_at->timestamp, false);
});
