<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wedding extends Model
{
    protected $fillable = ['user_id', 'title', 'date', 'location'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function guestProfiles()
    {
        return $this->hasMany(GuestProfile::class);
    }
    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
