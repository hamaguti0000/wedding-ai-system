<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_side',
        'relationship',
        'relationship_detail',
        'last_name',
        'first_name',
        'furigana_sei',
        'furigana_mei',
        'phone',
        'postal_code',
        'address',
        'participation',
        'attending_count',
        'children_count',
        'has_allergy',
        'allergy_notes',
        'dietary_notes',
        'notes',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at'    => 'datetime',
            'attending_count' => 'integer',
            'children_count'  => 'integer',
            'has_allergy'     => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullName(): string
    {
        return $this->last_name . ' ' . $this->first_name;
    }

    public function furigana(): string
    {
        return trim(($this->furigana_sei ?? '') . ' ' . ($this->furigana_mei ?? ''));
    }

    public function participationLabel(): string
    {
        return match ($this->participation) {
            'attending' => '出席',
            'declining' => '欠席',
            default     => '未回答',
        };
    }

    public function guestSideLabel(): string
    {
        return match ($this->guest_side) {
            'groom' => '新郎側',
            'bride' => '新婦側',
            default => '—',
        };
    }

    public function relationshipLabel(): string
    {
        return match ($this->relationship) {
            'friend'    => '友人・知人',
            'family'    => '親族',
            'colleague' => '職場関係',
            'other'     => $this->relationship_detail ?: 'その他',
            default     => '—',
        };
    }

    public function isAttending(): bool
    {
        return $this->participation === 'attending';
    }

    public function isDeclining(): bool
    {
        return $this->participation === 'declining';
    }
}
