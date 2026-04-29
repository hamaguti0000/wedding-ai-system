<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRole extends Model
{
    protected $table = 'account_roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}
