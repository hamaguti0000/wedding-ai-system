<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAssignment extends Model
{
    protected $table = 'staff_assignments';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function staff()
    {
        return $this->belongsTo(VenueStaff::class, 'staff_id', 'id');
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class, 'wedding_id', 'id');
    }
}
