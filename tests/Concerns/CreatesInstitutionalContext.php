<?php

namespace Tests\Concerns;

use App\Models\Copropiedad;
use App\Models\Organizacion;
use App\Models\SiteSetting;

trait CreatesInstitutionalContext
{
    /** @return array{Organizacion, Copropiedad} */
    protected function createInstitutionalContext(): array
    {
        $organizacion = Organizacion::create([
            'nombre' => 'Organización inicial',
            'estado' => 'activa',
        ]);
        $copropiedad = Copropiedad::create([
            'organizacion_id' => $organizacion->id,
            'nombre' => 'Copropiedad inicial',
            'estado' => 'activa',
        ]);
        $settings = SiteSetting::create([
            'nombre_conjunto' => 'Copropiedad inicial',
            'color_principal' => '#12382f',
            'dias_respuesta' => 15,
        ]);
        $settings->organizacion()->associate($organizacion);
        $settings->copropiedad()->associate($copropiedad);
        $settings->save();

        return [$organizacion, $copropiedad];
    }
}
