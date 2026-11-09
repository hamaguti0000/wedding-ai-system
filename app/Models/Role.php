<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function accounts()
    {
        return $this->belongsToMany(Account::class, 'account_roles', 'role_id', 'account_id');
    }
}
