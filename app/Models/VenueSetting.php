<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueSetting extends Model
{
    protected $table = 'venue_settings';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'id');
    }
}
