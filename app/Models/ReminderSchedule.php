<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderSchedule extends Model
{
    protected $fillable = [
        'title',
        'target',
        'subject',
        'message',
        'scheduled_at',
        'sent_at',
        'status',
        'sent_count',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at'      => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function targetLabel(): string
    {
        return match ($this->target) {
            'all'           => '全ゲスト',
            'attending'     => '出席予定者のみ',
            'not_responded' => '未回答者のみ',
            default         => '—',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'   => $this->scheduled_at ? '予約済み' : '下書き',
            'sent'      => '送信済み',
            'cancelled' => 'キャンセル',
            default     => '—',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'sent'      => 'badge-success',
            'cancelled' => 'badge-muted',
            default     => $this->scheduled_at ? 'badge-warning' : 'badge-info',
        };
    }

    /** 送信対象ユーザーを取得する */
    public function resolveRecipients(): \Illuminate\Support\Collection
    {
        $query = User::where('role', 'guest')->whereNotNull('email');

        return match ($this->target) {
            'attending'     => $query->whereHas('guestProfile', fn ($q) => $q->where('participation', 'attending'))->get(),
            'not_responded' => $query->where(function ($q) {
                $q->doesntHave('guestProfile')
                  ->orWhereHas('guestProfile', fn ($q2) => $q2->whereNull('participation'));
            })->get(),
            default         => $query->get(), // 'all'
        };
    }
}
