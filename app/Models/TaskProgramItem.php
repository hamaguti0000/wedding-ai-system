<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskProgramItem extends Model
{
    protected $fillable = ['wedding_task_id', 'start_time', 'title', 'description', 'sort_order'];

    public function task()
    {
        return $this->belongsTo(WeddingTask::class, 'wedding_task_id');
    }
}
