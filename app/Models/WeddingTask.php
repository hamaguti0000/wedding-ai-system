<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeddingTask extends Model
{
    protected $fillable = ['name', 'description', 'display_on_home', 'sort_order'];

    protected function casts(): array
    {
        return ['display_on_home' => 'boolean'];
    }

    public function assignments()
    {
        return $this->hasMany(GuestTaskAssignment::class);
    }

    public function programItems()
    {
        return $this->hasMany(TaskProgramItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
