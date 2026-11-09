<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $table = 'venues';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function settings()
    {
        return $this->hasMany(VenueSetting::class, 'venue_id', 'id');
    }

    public function staff()
    {
        return $this->hasMany(VenueStaff::class, 'venue_id', 'id');
    }
}
