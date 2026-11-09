<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RSVP extends Model
{
    protected $table = 'rsvps';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function guestProfile()
    {
        return $this->belongsTo(GuestProfile::class, 'guest_profile_id', 'id');
    }
}
