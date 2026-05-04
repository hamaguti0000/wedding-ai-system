<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
        'checkin_token',
        'checked_in_at',
        'checked_in_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'responded_at'    => 'datetime',
            'checked_in_at'   => 'datetime',
            'attending_count' => 'integer',
            'children_count'  => 'integer',
            'has_allergy'     => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by_user_id');
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

    public function isCheckedIn(): bool
    {
        return $this->checked_in_at !== null;
    }

    public function ensureCheckInToken(): string
    {
        if (! $this->checkin_token) {
            $this->forceFill([
                'checkin_token' => (string) Str::uuid(),
            ])->saveQuietly();

            $this->refresh();
        }

        return (string) $this->checkin_token;
    }

    public function checkInUrl(): string
    {
        return route('admin.checkin.show', ['token' => $this->ensureCheckInToken()]);
    }

    public function checkInStatusLabel(): string
    {
        return $this->isCheckedIn()
            ? '受付済み'
            : '未受付';
    }
}
