<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqrHistory extends Model
{
    protected $fillable = [
        'pqr_id',
        'campo',
        'valor_anterior',
        'valor_nuevo',
        'user_id',
    ];

    public function pqr()
    {
        return $this->belongsTo(Pqr::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
