<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TipoPqr;
use App\Models\Pqr;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin PQR',
            'email' => 'admin@pqrs.com',
        ]);

        $tipos = collect([
            ['nombre' => 'Petición', 'descripcion' => 'Solicitud formal presentada por un residente.'],
            ['nombre' => 'Queja', 'descripcion' => 'Manifestación de inconformidad frente a una situación.'],
            ['nombre' => 'Reclamo', 'descripcion' => 'Solicitud relacionada con una posible afectación o incumplimiento.'],
            ['nombre' => 'Sugerencia', 'descripcion' => 'Propuesta de mejora para la administración del conjunto.'],
            ['nombre' => 'Solicitud', 'descripcion' => 'Requerimiento general de información o trámite.'],
        ])->map(function ($tipo) {
            return TipoPqr::create($tipo);
        });

        Pqr::factory(30)->create([
            'user_id' => $user->id,
            'tipo_pqr_id' => $tipos->random()->id,
        ]);
    }
}