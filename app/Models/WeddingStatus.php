<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingStatus extends Model
{
    protected $table = 'wedding_statuses';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function weddings()
    {
        return $this->hasMany(Wedding::class, 'status_id', 'id');
    }
}
