<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pqr extends Model
{
    use HasFactory;

    protected $fillable = [
        'asunto',
        'descripcion',
        'fecha_radicacion',
        'fecha_limite_respuesta',
        'estado',
        'user_id',
        'tipo_pqr_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_radicacion' => 'date',
            'fecha_limite_respuesta' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tipoPqr()
    {
        return $this->belongsTo(TipoPqr::class);
    }

    public function histories()
    {
        return $this->hasMany(PqrHistory::class);
    }

    public function scopeRespondidas($query)
    {
        return $query->where('estado', 'respondida');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', '!=', 'respondida');
    }

    public function scopeBuscar($query, $texto)
    {
        return $query->where('asunto', 'LIKE', "%{$texto}%");
    }
}
