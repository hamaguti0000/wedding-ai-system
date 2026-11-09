<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueStaffRole extends Model
{
    protected $table = 'venue_staff_roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function staff()
    {
        return $this->belongsToMany(VenueStaff::class, 'venue_staff_role_assignments', 'role_id', 'staff_id');
    }
}
