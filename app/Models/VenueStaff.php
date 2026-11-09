<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueStaff extends Model
{
    protected $table = 'venue_staff';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany(VenueStaffRole::class, 'venue_staff_role_assignments', 'staff_id', 'role_id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'id');
    }
}
