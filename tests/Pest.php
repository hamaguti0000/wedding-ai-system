<?php

use App\Models\GuestProfile;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| Feature テスト全体に RefreshDatabase と TestCase を適用する
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| 共通ファクトリーヘルパー
|--------------------------------------------------------------------------
| Feature テスト全体で使えるユーザー生成関数。
| 個別ファイルで再定義すると PHP の関数重複宣言エラーになるため、
| ここで一元管理する。
*/

function makeAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

/**
 * ゲストユーザーを作成する。
 * $participation: null = プロフィールなし / 'attending' / 'declining' / 'pending'
 */
function makeGuest(?string $participation = null): User
{
    $user = User::factory()->create(['role' => 'guest']);

    if ($participation !== null) {
        GuestProfile::factory()->create([
            'user_id'       => $user->id,
            'participation' => $participation,
            'responded_at'  => now(),
        ]);
    }

    return $user;
}

/** 出席時の有効な最小送信データセット */
function attendingPayload(array $overrides = []): array
{
    return array_merge([
        'participation'   => 'attending',
        'attending_count' => 2,
        'children_count'  => 0,
        'has_allergy'     => '0',
    ], $overrides);
}

/** 設定画面の有効な送信データセット */
function validSettingsPayload(array $overrides = []): array
{
    return array_merge([
        'groom_name'     => '濵口 翔',
        'bride_name'     => '馬場 弥礼',
        'ceremony_date'  => '2026-07-19',
        'ceremony_time'  => '12:00',
        'reception_time' => '13:00',
        'venue_name'     => 'テスト会場',
        'venue_address'  => '東京都渋谷区1-1-1',
    ], $overrides);
}
