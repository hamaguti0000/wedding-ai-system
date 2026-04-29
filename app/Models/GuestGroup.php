<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestGroup extends Model
{
    protected $table = 'guest_groups';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function wedding()
    {
        return $this->belongsTo(Wedding::class, 'wedding_id', 'id');
    }

    public function primaryGuest()
    {
        return $this->belongsTo(GuestProfile::class, 'primary_guest_id', 'id');
    }
}
