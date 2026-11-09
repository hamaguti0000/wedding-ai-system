<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaPermission extends Model
{
    protected $table = 'media_permissions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function mediaFile()
    {
        return $this->belongsTo(MediaFile::class, 'media_file_id', 'id');
    }

    public function weddingRole()
    {
        return $this->belongsTo(WeddingRole::class, 'wedding_role_id', 'id');
    }
}
