<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramItem extends Model
{
    protected $fillable = ['start_time', 'title', 'description', 'icon', 'display_order'];
}
