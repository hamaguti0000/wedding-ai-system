<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotographerImportBatch extends Model
{
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'name',
        'zip_path',
        'original_filename',
        'status',
        'gallery_category',
        'total_entries',
        'imported_count',
        'skipped_count',
        'error_message',
        'created_by_user_id',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PhotographerImportItem::class);
    }

    public function pendingCount(): int
    {
        return (int) $this->items()->where('status', PhotographerImportItem::STATUS_PENDING)->count();
    }
}
