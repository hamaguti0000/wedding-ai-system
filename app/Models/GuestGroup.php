<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GuestGroup extends Model
{
    protected $table = 'guest_groups';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'guest_side',
        'relationship',
        'primary_guest_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function assignedSeatingTables(): BelongsToMany
    {
        return $this->belongsToMany(
            SeatingTable::class,
            'seating_table_group_assignments',
            'guest_group_id',
            'seating_table_id',
            'id',
            'id'
        );
    }

    public function wedding(): BelongsTo
    {
        return $this->belongsTo(Wedding::class, 'wedding_id', 'id');
    }

    public function primaryGuest(): BelongsTo
    {
        return $this->belongsTo(GuestProfile::class, 'primary_guest_id', 'id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'guest_group_members',
            'guest_group_id',
            'user_id',
            'id',
            'id'
        )->withTimestamps();
    }

    public static function galleryGroupLabels(): array
    {
        return [
            'groom|family' => '濵口 親族',
            'groom|friend' => '濵口 友人',
            'groom|colleague' => '濵口 職場',
            'bride|family' => '馬場 親族',
            'bride|friend' => '馬場 友人',
            'bride|colleague' => '馬場 職場',
        ];
    }

    public static function galleryGroupOrder(): array
    {
        return array_flip(array_keys(self::galleryGroupLabels()));
    }

    public static function gallerySortRankFor(?string $guestSide, ?string $relationship): int
    {
        $key = ($guestSide ?: 'unknown') . '|' . ($relationship ?: 'other');

        return self::galleryGroupOrder()[$key] ?? 99;
    }

    public static function galleryLabelFor(?string $guestSide, ?string $relationship): ?string
    {
        $key = ($guestSide ?: 'unknown') . '|' . ($relationship ?: 'other');

        return self::galleryGroupLabels()[$key] ?? null;
    }

    public static function gallerySortRankForProfile(?GuestProfile $profile): int
    {
        return self::gallerySortRankFor($profile?->guest_side, $profile?->relationship);
    }

    public function displayName(): string
    {
        if ($this->name) {
            return $this->name;
        }

        if ($this->primaryGuest) {
            return $this->primaryGuest->fullName() . ' グループ';
        }

        return 'グループ ' . $this->id;
    }

    public function galleryDisplayName(): string
    {
        return self::galleryLabelFor($this->guest_side, $this->relationship) ?: $this->displayName();
    }

    public function gallerySortRank(): int
    {
        return self::gallerySortRankFor($this->guest_side, $this->relationship);
    }
}
