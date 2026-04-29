<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestAllergy extends Model
{
    protected $fillable = ['guest_profile_id', 'allergy_name'];

    public function guestProfile()
    {
        return $this->belongsTo(GuestProfile::class);
    }
}
