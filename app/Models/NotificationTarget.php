<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTarget extends Model
{
    protected $table = 'notification_targets';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
