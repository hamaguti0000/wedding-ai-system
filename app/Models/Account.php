<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';
    protected $primaryKey = 'id';
    public $incrementing = false; // UUIDなので自動増分なし
    protected $keyType = 'string';
    protected $fillable = ['email', 'password_hash', 'display_name', 'is_active', 'auth_type', 'last_login_at'];
}
