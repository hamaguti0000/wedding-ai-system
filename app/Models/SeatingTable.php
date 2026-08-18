<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatingTable extends Model
{
    protected $fillable = ['name', 'display_order', 'pos_x', 'pos_y'];

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function assignedGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            GuestGroup::class,
            'seating_table_group_assignments',
            'seating_table_id',
            'guest_group_id',
            'id',
            'id'
        );
    }

    public function seatCount(): int
    {
        return $this->seats()->count();
    }

    public function assignedCount(): int
    {
        return $this->seats()
            ->whereHas('assignment')
            ->count();
    }

    /**
     * display_order順での0始まりインデックスからテーブルの記号を作る。
     * A〜Z(26個)を使い切ったら、AA/AB…ではなくa〜z(小文字)に続ける方式
     * (エスコートカード印刷で卓を一目で区別するための表記、2026-05頃導入)。
     */
    public static function letterForIndex(int $index): string
    {
        if ($index < 26) {
            return chr(65 + $index);
        }

        $index -= 26;
        $letter = '';

        do {
            $letter = chr(97 + ($index % 26)) . $letter;
            $index = intdiv($index, 26) - 1;
        } while ($index >= 0);

        return $letter;
    }
}
