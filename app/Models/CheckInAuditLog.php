<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckInAuditLog extends Model
{
    protected $fillable = [
        'guest_profile_id',
        'actor_user_id',
        'action',
        'source',
        'message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function guestProfile(): BelongsTo
    {
        return $this->belongsTo(GuestProfile::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function record(
        GuestProfile $profile,
        ?User $actor,
        string $action,
        ?string $source,
        string $message,
        array $meta = []
    ): self {
        return static::create([
            'guest_profile_id' => $profile->id,
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'source' => $source,
            'message' => $message,
            'meta' => $meta ?: null,
        ]);
    }
}
