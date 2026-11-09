<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingMemberRole extends Model
{
    protected $table = 'wedding_member_roles';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function members()
    {
        return $this->belongsToMany(WeddingMember::class, 'wedding_member_role_assignments', 'role_id', 'member_id');
    }
}
