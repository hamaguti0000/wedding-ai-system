<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerImportItem extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'photographer_import_batch_id',
        'gallery_photo_id',
        'original_name',
        'file_path',
        'display_file_path',
        'file_size',
        'mime_type',
        'status',
        'sort_order',
        'decided_at',
        'decided_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
            'file_size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PhotographerImportBatch::class, 'photographer_import_batch_id');
    }

    public function galleryPhoto(): BelongsTo
    {
        return $this->belongsTo(GalleryPhoto::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by_user_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . ($this->display_file_path ?: $this->file_path));
    }
}
