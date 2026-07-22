<?php

namespace Database\Seeders;

use App\Models\Audit;
use App\Models\Pqr;
use App\Models\PqrHistory;
use App\Models\TipoPqr;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public const AUDIT_PREFIX = '[DEMO]';
    public const ADMIN_EMAIL = 'gestionpqrs7@gmail.com';
    public const RESIDENT_EMAIL = 'residentepqrs@gmail.com';
    public const DEFAULT_PASSWORD = 'DemoPQRS2026!';

    public function run(): void
    {
        DB::transaction(function () {
            $this->clearExistingDemoData();

            $categories = collect([
                ['nombre' => 'Petición', 'descripcion' => 'Solicitudes formales de los residentes.'],
                ['nombre' => 'Queja', 'descripcion' => 'Inconformidades relacionadas con la convivencia o los servicios.'],
                ['nombre' => 'Reclamo', 'descripcion' => 'Situaciones que requieren revisión y una solución administrativa.'],
                ['nombre' => 'Sugerencia', 'descripcion' => 'Propuestas para mejorar el conjunto residencial.'],
                ['nombre' => 'Solicitud', 'descripcion' => 'Requerimientos generales de información o trámites.'],
            ])->mapWithKeys(function (array $category) {
                $model = TipoPqr::firstOrCreate(['nombre' => $category['nombre']], $category + ['activo' => true]);

                return [$category['nombre'] => $model];
            });

            $admin = User::create([
                'name' => 'Administrador del Conjunto',
                'email' => self::ADMIN_EMAIL,
                'rol' => 'admin',
                'activo' => true,
                'password' => self::DEFAULT_PASSWORD,
                'email_verified_at' => now(),
            ]);

            $resident = User::create([
                'name' => 'Residente de Prueba',
                'email' => self::RESIDENT_EMAIL,
                'rol' => 'residente',
                'activo' => true,
                'password' => self::DEFAULT_PASSWORD,
                'email_verified_at' => now(),
            ]);

            foreach ($this->cases() as $case) {
                $filedAt = Carbon::parse($case['filed_at']);
                $response = $case['response'];
                $responseDate = $response
                    ? Carbon::parse($case['responded_at'])
                    : null;

                $pqr = Pqr::create([
                    'asunto' => $case['subject'],
                    'descripcion' => $case['description'],
                    'fecha_radicacion' => $filedAt,
                    'fecha_limite_respuesta' => $filedAt->copy()->addDays(15),
                    'estado' => $case['state'],
                    'respuesta' => $response,
                    'user_id' => $resident->id,
                    'respondida_por' => $response ? $admin->id : null,
                    'respondida_en' => $responseDate,
                    'tipo_pqr_id' => $categories[$case['category']]->id,
                    'created_at' => $filedAt->copy()->setTime(9, 15),
                    'updated_at' => ($responseDate ?? $filedAt)->copy(),
                ]);

                if ($case['state'] !== 'radicada') {
                    PqrHistory::create([
                        'pqr_id' => $pqr->id,
                        'campo' => 'estado',
                        'valor_anterior' => 'radicada',
                        'valor_nuevo' => $case['state'],
                        'user_id' => $admin->id,
                        'created_at' => $pqr->updated_at,
                        'updated_at' => $pqr->updated_at,
                    ]);
                }

                if ($response) {
                    PqrHistory::create([
                        'pqr_id' => $pqr->id,
                        'campo' => 'respuesta',
                        'valor_anterior' => null,
                        'valor_nuevo' => $response,
                        'user_id' => $admin->id,
                        'created_at' => $responseDate,
                        'updated_at' => $responseDate,
                    ]);
                }

                Audit::create([
                    'user_id' => $resident->id,
                    'module' => 'PQR',
                    'action' => 'crear',
                    'auditable_type' => Pqr::class,
                    'auditable_id' => $pqr->id,
                    'description' => self::AUDIT_PREFIX." Creó la PQR #{$pqr->id}: {$case['subject']}",
                    'new_values' => ['estado' => 'radicada', 'categoria' => $case['category']],
                    'ip_address' => '192.0.2.10',
                    'user_agent' => 'Navegador de demostración académica',
                    'created_at' => $pqr->created_at,
                    'updated_at' => $pqr->created_at,
                ]);

                if ($case['state'] !== 'radicada') {
                    Audit::create([
                        'user_id' => $admin->id,
                        'module' => 'PQR',
                        'action' => 'cambiar_estado',
                        'auditable_type' => Pqr::class,
                        'auditable_id' => $pqr->id,
                        'description' => self::AUDIT_PREFIX." Cambió el estado de la PQR #{$pqr->id}",
                        'old_values' => ['estado' => 'radicada'],
                        'new_values' => ['estado' => $case['state']],
                        'ip_address' => '192.0.2.250',
                        'user_agent' => 'Navegador de demostración académica',
                        'created_at' => $pqr->updated_at,
                        'updated_at' => $pqr->updated_at,
                    ]);
                }

                if ($response) {
                    Audit::create([
                        'user_id' => $admin->id,
                        'module' => 'PQR',
                        'action' => 'resolver',
                        'auditable_type' => Pqr::class,
                        'auditable_id' => $pqr->id,
                        'description' => self::AUDIT_PREFIX." Resolvió la PQR #{$pqr->id}",
                        'old_values' => ['estado' => $case['state'] === 'cerrada' ? 'resuelta' : 'en_proceso'],
                        'new_values' => ['estado' => $case['state'], 'respuesta' => $response],
                        'ip_address' => '192.0.2.251',
                        'user_agent' => 'Navegador de demostración académica',
                        'created_at' => $responseDate,
                        'updated_at' => $responseDate,
                    ]);
                }
            }
        });
    }

    public static function clear(): void
    {
        DB::transaction(function () {
            Audit::where('description', 'like', self::AUDIT_PREFIX.'%')->delete();
            User::whereIn('email', [self::ADMIN_EMAIL, self::RESIDENT_EMAIL])->delete();
        });
    }

    private function clearExistingDemoData(): void
    {
        self::clear();
    }

    private function cases(): array
    {
        return [
            [
                'category' => 'Solicitud',
                'subject' => 'Certificado de paz y salvo',
                'description' => 'Solicito el certificado de paz y salvo del apartamento para presentarlo en un trámite bancario.',
                'state' => 'radicada',
                'filed_at' => '2026-07-20',
                'response' => null,
                'responded_at' => null,
            ],
            [
                'category' => 'Queja',
                'subject' => 'Ruido excesivo en zona social',
                'description' => 'Durante el fin de semana se presentó ruido después de las 11:00 p. m. en la zona social del conjunto.',
                'state' => 'en_revision',
                'filed_at' => '2026-07-18',
                'response' => null,
                'responded_at' => null,
            ],
            [
                'category' => 'Reclamo',
                'subject' => 'Revisión de cobro de administración',
                'description' => 'La factura del mes incluye un recargo que ya había sido cancelado en el pago anterior.',
                'state' => 'en_proceso',
                'filed_at' => '2026-07-15',
                'response' => null,
                'responded_at' => null,
            ],
            [
                'category' => 'Solicitud',
                'subject' => 'Permiso para mudanza',
                'description' => 'Solicito autorización para realizar la mudanza el sábado 25 de julio en horario de la mañana.',
                'state' => 'en_espera',
                'filed_at' => '2026-07-15',
                'response' => null,
                'responded_at' => null,
            ],
            [
                'category' => 'Solicitud',
                'subject' => 'Solicitud rechazada por información incompleta',
                'description' => 'La solicitud fue radicada sin anexar el documento soporte requerido por la administración.',
                'state' => 'rechazada',
                'filed_at' => '2026-07-12',
                'response' => null,
                'responded_at' => null,
            ],
            [
                'category' => 'Reclamo',
                'subject' => 'Falla en citófono del apartamento',
                'description' => 'El citófono no permite recibir llamadas desde portería ni abrir la puerta peatonal desde el apartamento.',
                'state' => 'resuelta',
                'filed_at' => '2026-07-08',
                'response' => 'El proveedor realizó el cambio del auricular interno y confirmó el funcionamiento correcto del citófono.',
                'responded_at' => '2026-07-11 09:45:00',
            ],
            [
                'category' => 'Sugerencia',
                'subject' => 'Mejora de iluminación en parqueadero',
                'description' => 'Sugiero instalar un punto adicional de iluminación en el parqueadero de visitantes para mejorar la visibilidad nocturna.',
                'state' => 'cerrada',
                'filed_at' => '2026-07-02',
                'response' => 'La sugerencia fue aprobada y el nuevo punto de iluminación quedó instalado durante la jornada de mantenimiento del 10 de julio.',
                'responded_at' => '2026-07-10 17:15:00',
            ],
        ];
    }
}
