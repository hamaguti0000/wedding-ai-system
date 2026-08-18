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
}
