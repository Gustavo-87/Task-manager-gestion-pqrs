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
    public const EMAIL_SUFFIX = '@demo-pqrs.example.com';
    public const AUDIT_PREFIX = '[DEMO]';

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

            $admins = collect([
                ['name' => 'Laura Martínez', 'email' => 'laura.admin'.self::EMAIL_SUFFIX],
                ['name' => 'Carlos Ramírez', 'email' => 'carlos.admin'.self::EMAIL_SUFFIX],
            ])->map(fn (array $data) => User::create($data + [
                'rol' => 'admin', 'activo' => true, 'password' => 'DemoPQRS2026!', 'email_verified_at' => now(),
            ]));

            $residentNames = [
                'Ana Torres', 'Miguel Rodríguez', 'Sofía Hernández', 'Daniel Gómez',
                'Valentina Castro', 'Andrés López', 'Camila Vargas', 'Juan Pérez',
                'Mariana Rojas', 'Felipe Sánchez', 'Natalia Moreno', 'Sebastián Ortiz',
            ];

            $residents = collect($residentNames)->map(function (string $name, int $index) {
                return User::create([
                    'name' => $name,
                    'email' => 'residente'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).self::EMAIL_SUFFIX,
                    'rol' => 'residente',
                    'activo' => $index !== 11,
                    'password' => 'DemoPQRS2026!',
                    'email_verified_at' => now(),
                ]);
            });

            $cases = $this->cases();
            $states = ['radicada', 'en_revision', 'respondida', 'cerrada'];
            $today = Carbon::today();

            foreach ($cases as $index => [$category, $subject, $description]) {
                $resident = $residents[$index % $residents->count()];
                $daysAgo = match (true) {
                    $index < 4 => $index,
                    $index < 8 => 13 + ($index % 3),
                    default => 18 + (($index * 7) % 72),
                };
                $filedAt = $today->copy()->subDays($daysAgo);
                $state = $states[$index % count($states)];

                $pqr = Pqr::create([
                    'asunto' => $subject,
                    'descripcion' => $description,
                    'fecha_radicacion' => $filedAt,
                    'fecha_limite_respuesta' => $filedAt->copy()->addDays(15),
                    'estado' => $state,
                    'user_id' => $resident->id,
                    'tipo_pqr_id' => $categories[$category]->id,
                    'created_at' => $filedAt->copy()->setTime(9 + ($index % 8), 15),
                    'updated_at' => $filedAt->copy()->addDays(min(5, $daysAgo))->setTime(14, 30),
                ]);

                if ($state !== 'radicada') {
                    PqrHistory::create([
                        'pqr_id' => $pqr->id,
                        'campo' => 'estado',
                        'valor_anterior' => 'radicada',
                        'valor_nuevo' => $state,
                        'user_id' => $admins[$index % $admins->count()]->id,
                        'created_at' => $pqr->updated_at,
                        'updated_at' => $pqr->updated_at,
                    ]);
                }

                Audit::create([
                    'user_id' => $resident->id,
                    'module' => 'PQR',
                    'action' => 'crear',
                    'auditable_type' => Pqr::class,
                    'auditable_id' => $pqr->id,
                    'description' => self::AUDIT_PREFIX." Creó la PQR #{$pqr->id}: {$subject}",
                    'new_values' => ['estado' => 'radicada', 'categoria' => $category],
                    'ip_address' => '192.0.2.'.(($index % 200) + 1),
                    'user_agent' => 'Navegador de demostración académica',
                    'created_at' => $pqr->created_at,
                    'updated_at' => $pqr->created_at,
                ]);

                if ($state !== 'radicada') {
                    Audit::create([
                        'user_id' => $admins[$index % $admins->count()]->id,
                        'module' => 'PQR',
                        'action' => 'cambiar_estado',
                        'auditable_type' => Pqr::class,
                        'auditable_id' => $pqr->id,
                        'description' => self::AUDIT_PREFIX." Cambió el estado de la PQR #{$pqr->id}",
                        'old_values' => ['estado' => 'radicada'],
                        'new_values' => ['estado' => $state],
                        'ip_address' => '192.0.2.250',
                        'user_agent' => 'Navegador de demostración académica',
                        'created_at' => $pqr->updated_at,
                        'updated_at' => $pqr->updated_at,
                    ]);
                }
            }
        });
    }

    public static function clear(): void
    {
        DB::transaction(function () {
            Audit::where('description', 'like', self::AUDIT_PREFIX.'%')->delete();
            User::where('email', 'like', '%'.self::EMAIL_SUFFIX)->delete();
        });
    }

    private function clearExistingDemoData(): void
    {
        self::clear();
    }

    private function cases(): array
    {
        return [
            ['Petición', 'Certificado de paz y salvo', 'Solicito el certificado de paz y salvo del apartamento para realizar un trámite bancario.'],
            ['Queja', 'Ruido excesivo en horas de la noche', 'Durante varios fines de semana se ha presentado ruido elevado después de las 11:00 p. m.'],
            ['Reclamo', 'Cobro incorrecto en la cuota de administración', 'La factura presenta un recargo que ya fue pagado el mes anterior.'],
            ['Sugerencia', 'Instalación de bicicleteros', 'Propongo instalar bicicleteros cubiertos cerca de la portería principal.'],
            ['Solicitud', 'Autorización para mudanza', 'Solicito autorización para realizar la mudanza el próximo sábado en la mañana.'],
            ['Petición', 'Copia del reglamento de propiedad horizontal', 'Agradezco enviar una copia digital del reglamento vigente del conjunto.'],
            ['Queja', 'Mascota sin correa en zona común', 'Se han presentado recorridos frecuentes de una mascota sin correa en el parque infantil.'],
            ['Reclamo', 'Daño en vehículo dentro del parqueadero', 'Reporto un rayón encontrado en el vehículo estacionado durante la noche.'],
            ['Sugerencia', 'Mejorar iluminación del sendero', 'El sendero posterior tiene poca iluminación y representa riesgo para los residentes.'],
            ['Solicitud', 'Reserva del salón comunal', 'Deseo reservar el salón comunal para una reunión familiar.'],
            ['Petición', 'Revisión de cámaras de seguridad', 'Solicito revisar las cámaras por la pérdida de un paquete en recepción.'],
            ['Queja', 'Uso inadecuado de parqueadero de visitantes', 'Un vehículo permanece varios días ocupando un espacio destinado a visitantes.'],
            ['Reclamo', 'Filtración de agua en apartamento', 'Existe una filtración proveniente de una tubería de zona común.'],
            ['Sugerencia', 'Jornada de reciclaje mensual', 'Propongo organizar una jornada mensual para residuos electrónicos y reciclables.'],
            ['Solicitud', 'Ingreso de técnico de internet', 'Solicito autorizar el ingreso del técnico para instalación del servicio.'],
            ['Petición', 'Estado de cuenta detallado', 'Requiero un estado de cuenta con el detalle de pagos y conceptos del año.'],
            ['Queja', 'Basuras fuera del horario establecido', 'Algunos residentes dejan residuos después del horario de recolección.'],
            ['Reclamo', 'Intermitencia del citófono', 'El citófono del apartamento falla y no permite recibir llamadas de portería.'],
            ['Sugerencia', 'Clases grupales en zona social', 'Sugiero organizar actividades deportivas para niños y adultos los fines de semana.'],
            ['Solicitud', 'Actualización de datos de contacto', 'Solicito actualizar el teléfono y correo registrados para mi apartamento.'],
            ['Petición', 'Información sobre asamblea anual', 'Deseo conocer la fecha y agenda previstas para la próxima asamblea.'],
            ['Queja', 'Obstrucción de pasillo común', 'Hay objetos almacenados de forma permanente en el pasillo del tercer piso.'],
            ['Reclamo', 'Falla recurrente del ascensor', 'El ascensor de la torre dos presenta paradas inesperadas y ruidos fuertes.'],
            ['Sugerencia', 'Zona de lectura en el salón social', 'Propongo adecuar un pequeño espacio de lectura para residentes.'],
            ['Solicitud', 'Duplicado de tarjeta de acceso', 'Solicito expedir una tarjeta adicional de acceso para un integrante del hogar.'],
            ['Petición', 'Acta de la última asamblea', 'Solicito copia digital del acta aprobada en la última asamblea general.'],
            ['Queja', 'Humo de cigarrillo en áreas comunes', 'Se presenta consumo frecuente de cigarrillo cerca de las ventanas de la torre.'],
            ['Reclamo', 'Demora en reparación de puerta', 'La puerta peatonal continúa dañada pese al reporte realizado anteriormente.'],
            ['Sugerencia', 'Señalización de rutas de evacuación', 'Sugiero reforzar la señalización visible de las rutas de evacuación.'],
            ['Solicitud', 'Permiso para ingreso de mobiliario', 'Solicito permitir el ingreso de muebles durante el horario autorizado.'],
            ['Petición', 'Consulta de sanción registrada', 'Solicito información y soportes sobre una sanción reflejada en mi cuenta.'],
            ['Queja', 'Pelotas en zona de parqueaderos', 'Niños juegan con balones cerca de los vehículos durante la tarde.'],
            ['Reclamo', 'Humedad en depósito privado', 'Se evidencia humedad causada posiblemente por una canaleta de zona común.'],
            ['Sugerencia', 'Canal informativo para residentes', 'Propongo consolidar avisos importantes en un canal digital oficial.'],
            ['Solicitud', 'Registro de nuevo vehículo', 'Solicito registrar un vehículo nuevo asociado al parqueadero del apartamento.'],
            ['Petición', 'Cronograma de mantenimiento', 'Agradezco compartir el cronograma de mantenimiento de ascensores y bombas.'],
            ['Queja', 'Puerta de acceso queda abierta', 'La puerta de la torre no cierra correctamente durante algunas horas.'],
            ['Reclamo', 'Cobro por parqueadero no asignado', 'La factura incluye un parqueadero adicional que no pertenece al apartamento.'],
            ['Sugerencia', 'Instalar dispensadores para mascotas', 'Sugiero instalar dispensadores de bolsas en las zonas verdes.'],
            ['Solicitud', 'Autorización de visitante frecuente', 'Solicito registrar temporalmente a un familiar que apoyará el cuidado de un residente.'],
        ];
    }
}
