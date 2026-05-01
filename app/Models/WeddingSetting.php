<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingSetting extends Model
{
    protected $fillable = [
        'groom_name',
        'groom_name_en',
        'bride_name',
        'bride_name_en',
        'ceremony_date',
        'ceremony_time',
        'reception_time',
        'venue_name',
        'venue_address',
        'venue_url',
        'venue_nearest_station',
        'access_train',
        'access_car',
        'access_parking',
        'venue_map_embed',
        'message',
        'is_seating_public',
        'rsvp_deadline',
    ];

    protected function casts(): array
    {
        return [
            'ceremony_date'     => 'date',
            'is_seating_public' => 'boolean',
            'rsvp_deadline'     => 'date',
        ];
    }

    /** "2026年7月19日" 形式 */
    public function ceremonyDateJa(): string
    {
        return $this->ceremony_date->format('Y年n月j日');
    }

    /** "日"〜"土" */
    public function ceremonyDayOfWeek(): string
    {
        return ['日', '月', '火', '水', '木', '金', '土'][$this->ceremony_date->dayOfWeek];
    }

    /** "14:00"（秒を除く） */
    public function ceremonyTimeFormatted(): string
    {
        return substr($this->ceremony_time, 0, 5);
    }

    public function receptionTimeFormatted(): string
    {
        return substr($this->reception_time, 0, 5);
    }
}
