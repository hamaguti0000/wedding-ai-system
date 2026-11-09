<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
