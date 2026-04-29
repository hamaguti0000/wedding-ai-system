<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatingTable extends Model
{
    protected $fillable = ['name', 'display_order', 'pos_x', 'pos_y'];

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function seatCount(): int
    {
        return $this->seats()->count();
    }

    public function assignedCount(): int
    {
        return $this->seats()
            ->whereHas('assignment')
            ->count();
    }
}
