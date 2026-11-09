<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingRole extends Model
{
    protected $table = 'wedding_roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
