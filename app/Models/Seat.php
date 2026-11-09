<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Seat extends Model
{
    protected $fillable = ['seating_table_id', 'type', 'label', 'pos_x', 'pos_y'];

    public function seatingTable(): BelongsTo
    {
        return $this->belongsTo(SeatingTable::class);
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(SeatAssignment::class);
    }

    public function isAssigned(): bool
    {
        return $this->assignment()->exists();
    }

    /**
     * 席タイプ定義 — PHP・JS 共通の単一ソース。
     * 新タイプを追加する場合はここに1行追記するだけでよい。
     */
    public static function typeConfig(): array
    {
        return [
            'normal'    => ['label' => '一般席',   'color' => '#9b8573', 'bg' => '#f8f4ef'],
            'groom'     => ['label' => '新郎席',   'color' => '#4a6fa5', 'bg' => '#eff3fb'],
            'bride'     => ['label' => '新婦席',   'color' => '#a5526a', 'bg' => '#fdf0f4'],
            'vip'       => ['label' => '主賓席',   'color' => '#b38b59', 'bg' => '#fef9f0'],
            'family'    => ['label' => '親族席',   'color' => '#6a4fa5', 'bg' => '#f4f0fd'],
            'colleague' => ['label' => '職場席',   'color' => '#a5752d', 'bg' => '#fef5e8'],
            'other'     => ['label' => 'その他',   'color' => '#6b5b4e', 'bg' => '#f0ebe3'],
        ];
    }

    public function typeLabel(): string
    {
        return static::typeConfig()[$this->type]['label'] ?? $this->type;
    }
}
