<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Copropiedad extends Model
{
    protected $table = 'copropiedades';

    protected $fillable = [
        'organizacion_id',
        'nombre',
        'nit',
        'representante_legal',
        'direccion',
        'ciudad',
        'telefono',
        'email',
        'estado',
        'desactivada_at',
    ];

    protected function casts(): array
    {
        return [
            'desactivada_at' => 'datetime',
        ];
    }

    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }

    public function configuracion(): HasOne
    {
        return $this->hasOne(ConfiguracionCopropiedad::class);
    }

    public function membresiasCopropiedad(): HasMany
    {
        return $this->hasMany(MembresiaCopropiedad::class);
    }

    public function pqrs(): HasMany
    {
        return $this->hasMany(Pqr::class);
    }
}
