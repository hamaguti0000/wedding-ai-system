<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'actor_user_id',
        'action',
        'old_email',
        'new_email',
        'message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public static function record(
        User $user,
        ?User $actor,
        string $action,
        ?string $oldEmail,
        ?string $newEmail,
        string $message,
        array $meta = []
    ): self {
        return static::create([
            'user_id' => $user->id,
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'old_email' => $oldEmail,
            'new_email' => $newEmail,
            'message' => $message,
            'meta' => $meta ?: null,
        ]);
    }
}
