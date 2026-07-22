<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationDelivery extends Model
{
    protected $fillable = ['pqr_id', 'type', 'recipient', 'notification_date'];

    protected function casts(): array
    {
        return ['notification_date' => 'date'];
    }
}
