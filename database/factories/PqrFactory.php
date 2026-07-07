<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\TipoPqr;
use Illuminate\Database\Eloquent\Factories\Factory;

class PqrFactory extends Factory
{
    public function definition(): array
    {
        return [
            'asunto' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph(),
            'fecha_radicacion' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'fecha_limite_respuesta' => $this->faker->dateTimeBetween('now', '+15 days'),
            'estado' => $this->faker->randomElement(['radicada', 'en_revision', 'respondida', 'cerrada']),
            'user_id' => User::factory(),
            'tipo_pqr_id' => TipoPqr::factory(),
        ];
    }
}