<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organizacion extends Model
{
    protected $table = 'organizaciones';

    protected $fillable = [
        'nombre',
        'identificacion_tributaria',
        'email',
        'telefono',
        'estado',
        'desactivada_at',
    ];

    protected function casts(): array
    {
        return [
            'desactivada_at' => 'datetime',
        ];
    }

    public function copropiedades(): HasMany
    {
        return $this->hasMany(Copropiedad::class);
    }

    public function configuracion(): HasOne
    {
        return $this->hasOne(ConfiguracionOrganizacion::class);
    }
}
