<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineItem extends Model
{
    protected $table = 'timeline_items';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function timeline()
    {
        return $this->belongsTo(Timeline::class, 'timeline_id', 'id');
    }
}
