<?php

namespace Database\Seeders;

use App\Models\WeddingSetting;
use Illuminate\Database\Seeder;

class WeddingSettingSeeder extends Seeder
{
    public function run(): void
    {
        // 既存レコードがあれば更新、なければ作成
        WeddingSetting::updateOrCreate(
            ['id' => 1],
            [
                'groom_name'     => '濵口 翔',
                'bride_name'     => '馬場 弥礼',
                'ceremony_date'  => '2026-07-19',
                'ceremony_time'  => '14:00:00',
                'reception_time' => '15:30:00',
                'venue_name'     => '◯◯チャペル',
                'venue_address'  => '◯◯県◯◯市◯◯町1-2-3',
                'venue_url'      => null,
                'message'        => "これまで支えてくださった皆さまへ\n感謝の気持ちを込めて\nささやかな祝宴を催します。\n\nご多用中誠に恐れ入りますが\nぜひご出席いただけますと幸いです。",
            ]
        );
    }
}
