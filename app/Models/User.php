<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function guestProfile(): HasOne
    {
        return $this->hasOne(GuestProfile::class);
    }

    public function seatAssignment(): HasOne
    {
        return $this->hasOne(SeatAssignment::class);
    }

    public function taskAssignments()
    {
        return $this->hasMany(GuestTaskAssignment::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
