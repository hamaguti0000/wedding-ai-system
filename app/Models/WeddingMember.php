<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingMember extends Model
{
    protected $table = 'wedding_members';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function roles()
    {
        return $this->belongsToMany(WeddingMemberRole::class, 'wedding_member_role_assignments', 'member_id', 'role_id');
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class, 'wedding_id', 'id');
    }
}
