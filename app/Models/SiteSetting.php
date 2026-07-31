<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = ['nombre_conjunto', 'nit', 'representante_legal', 'direccion', 'ciudad', 'telefono', 'email', 'color_principal', 'dias_respuesta', 'logo_path'];

    protected function casts(): array
    {
        return ['dias_respuesta' => 'integer'];
    }

    public function organizacion(): BelongsTo
    {
        return $this->belongsTo(Organizacion::class);
    }

    public function copropiedad(): BelongsTo
    {
        return $this->belongsTo(Copropiedad::class);
    }

    public static function current(): self
    {
        if (! Schema::hasTable('site_settings')) {
            return new self(self::defaults());
        }

        return self::first() ?? new self(self::defaults());
    }

    public static function defaults(): array
    {
        return ['nombre_conjunto' => 'Mi conjunto residencial', 'ciudad' => 'Colombia', 'color_principal' => '#12382f', 'dias_respuesta' => 15];
    }
}
