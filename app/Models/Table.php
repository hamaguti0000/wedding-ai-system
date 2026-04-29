<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $table = 'tables';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function seats()
    {
        return $this->hasMany(Seat::class, 'table_id', 'id');
    }

    public function wedding()
    {
        return $this->belongsTo(Wedding::class, 'wedding_id', 'id');
    }
}
