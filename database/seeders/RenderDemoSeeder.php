<?php

namespace Database\Seeders;

use App\Models\Pqr;
use App\Models\TipoPqr;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RenderDemoSeeder extends Seeder
{
    public function run(): void
    {
        $password = getenv('DEMO_PASSWORD');

        if (! $password) {
            throw new RuntimeException(
                'La variable DEMO_PASSWORD no está configurada.'
            );
        }

        $admin = User::query()
            ->whereIn('email', [
                'gestionpqrs7@gmail.com',
                'admin.demo@gestionpqrs.test',
            ])
            ->first() ?? new User();

        $admin->forceFill([
            'name' => 'Administrador Demostración',
            'email' => 'gestionpqrs7@gmail.com',
            'password' => Hash::make($password),
            'rol' => 'admin',
            'activo' => true,
            'email_verified_at' => now(),
        ])->save();

        $residente = User::firstOrNew([
            'email' => 'residente.demo@gestionpqrs.test',
        ]);

        $residente->forceFill([
            'name' => 'Residente Demostración',
            'password' => Hash::make($password),
            'rol' => 'residente',
            'activo' => true,
            'email_verified_at' => now(),
        ])->save();

        $tipos = [];

        foreach ([
            'Petición' => 'Solicitud formal presentada por un residente.',
            'Queja' => 'Manifestación de inconformidad frente a una situación.',
            'Reclamo' => 'Solicitud relacionada con una afectación o incumplimiento.',
            'Sugerencia' => 'Propuesta de mejora para la administración.',
            'Solicitud' => 'Requerimiento general de información o trámite.',
        ] as $nombre => $descripcion) {
            $tipos[$nombre] = TipoPqr::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'descripcion' => $descripcion,
                    'activo' => true,
                ]
            );
        }

        $pqrs = [
            [
                'asunto' => 'Solicitud de certificado de residencia',
                'descripcion' => 'Se solicita certificado para realizar un trámite bancario.',
                'tipo' => 'Solicitud',
                'estado' => 'radicada',
                'dias' => 2,
                'respuesta' => null,
            ],
            [
                'asunto' => 'Ruido excesivo en zona común',
                'descripcion' => 'Se reporta ruido durante la noche en la zona social.',
                'tipo' => 'Queja',
                'estado' => 'en_revision',
                'dias' => 5,
                'respuesta' => null,
            ],
            [
                'asunto' => 'Revisión de cobro en administración',
                'descripcion' => 'El estado de cuenta presenta un cobro que requiere revisión.',
                'tipo' => 'Reclamo',
                'estado' => 'en_proceso',
                'dias' => 8,
                'respuesta' => null,
            ],
            [
                'asunto' => 'Mejora de iluminación en parqueadero',
                'descripcion' => 'Se sugiere instalar iluminación adicional en el parqueadero.',
                'tipo' => 'Sugerencia',
                'estado' => 'cerrada',
                'dias' => 12,
                'respuesta' => 'La propuesta fue aprobada para la próxima intervención.',
            ],
        ];

        foreach ($pqrs as $datos) {
            $fechaRadicacion = today()->subDays($datos['dias']);

            Pqr::updateOrCreate(
                [
                    'asunto' => $datos['asunto'],
                    'user_id' => $residente->id,
                ],
                [
                    'descripcion' => $datos['descripcion'],
                    'fecha_radicacion' => $fechaRadicacion,
                    'fecha_limite_respuesta' => $fechaRadicacion
                        ->copy()
                        ->addDays(15),
                    'tipo_pqr_id' => $tipos[$datos['tipo']]->id,
                    'estado' => $datos['estado'],
                    'respuesta' => $datos['respuesta'],
                    'respondida_por' => $datos['respuesta']
                        ? $admin->id
                        : null,
                    'respondida_en' => $datos['respuesta']
                        ? now()->subDay()
                        : null,
                ]
            );
        }
    }
}
