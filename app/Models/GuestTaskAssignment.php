<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestTaskAssignment extends Model
{
    protected $fillable = ['user_id', 'wedding_task_id', 'custom_time', 'custom_note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(WeddingTask::class, 'wedding_task_id');
    }
}
