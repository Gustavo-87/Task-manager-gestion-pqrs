<?php

namespace Database\Factories;

use App\Models\TipoPqr;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PqrFactory extends Factory
{
    public function definition(): array
    {
        $estado = $this->faker->randomElement(['radicada', 'en_revision', 'en_proceso', 'en_espera', 'rechazada', 'resuelta', 'cerrada']);
        $respuesta = in_array($estado, ['resuelta', 'cerrada'], true)
            ? $this->faker->sentence(12)
            : null;

        return [
            'asunto' => $this->faker->sentence(4),
            'descripcion' => $this->faker->paragraph(),
            'fecha_radicacion' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'fecha_limite_respuesta' => $this->faker->dateTimeBetween('now', '+15 days'),
            'estado' => $estado,
            'respuesta' => $respuesta,
            'respondida_en' => $respuesta ? $this->faker->dateTimeBetween('-15 days', 'now') : null,
            'user_id' => User::factory(),
            'respondida_por' => $respuesta ? User::factory() : null,
            'tipo_pqr_id' => TipoPqr::factory(),
        ];
    }
}
