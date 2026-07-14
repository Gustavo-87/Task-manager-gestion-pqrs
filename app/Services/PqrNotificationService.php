<?php

namespace App\Services;

use App\Models\NotificationDelivery;
use App\Models\Pqr;
use App\Models\User;
use App\Notifications\PqrDeadlineReminder;
use App\Notifications\PqrStatusChanged;
use Illuminate\Http\Request;
use Throwable;

class PqrNotificationService
{
    public function sendStatusChanged(Request $request, Pqr $pqr, string $previousStatus, string $newStatus): bool
    {
        try {
            $pqr->user->notify(new PqrStatusChanged($pqr, $previousStatus, $newStatus));
            AuditLogger::log($request, 'Notificaciones', 'enviar_correo', "Notificó a {$pqr->user->email} el cambio de estado de la PQR #{$pqr->id}", $pqr, null, [
                'recipient' => $pqr->user->email,
                'type' => 'cambio_estado',
            ]);

            return true;
        } catch (Throwable $exception) {
            report($exception);
            AuditLogger::log($request, 'Notificaciones', 'fallo_correo', "No fue posible notificar el cambio de estado de la PQR #{$pqr->id}", $pqr, null, [
                'recipient' => $pqr->user?->email,
                'type' => 'cambio_estado',
            ]);

            return false;
        }
    }

    public function sendDeadlineReminders(): array
    {
        $results = ['sent' => 0, 'skipped' => 0, 'failed' => 0];
        $today = today();
        $admins = User::where('rol', 'admin')->where('activo', true)->get();

        Pqr::with('user')
            ->whereNotIn('estado', ['respondida', 'cerrada'])
            ->whereDate('fecha_limite_respuesta', '>=', $today)
            ->whereDate('fecha_limite_respuesta', '<=', $today->copy()->addDay())
            ->each(function (Pqr $pqr) use ($admins, $today, &$results) {
                $daysRemaining = (int) $today->diffInDays($pqr->fecha_limite_respuesta, false);
                $type = $daysRemaining === 0 ? 'vence_hoy' : 'vence_manana';

                foreach ($admins as $admin) {
                    $alreadySent = NotificationDelivery::where([
                        'pqr_id' => $pqr->id,
                        'type' => $type,
                        'recipient' => $admin->email,
                    ])->whereDate('notification_date', $today)->exists();

                    if ($alreadySent) {
                        $results['skipped']++;
                        continue;
                    }

                    try {
                        $admin->notify(new PqrDeadlineReminder($pqr, $daysRemaining));
                        NotificationDelivery::create([
                            'pqr_id' => $pqr->id,
                            'type' => $type,
                            'recipient' => $admin->email,
                            'notification_date' => $today,
                        ]);
                        AuditLogger::logSystem('Notificaciones', 'enviar_correo', "Envió a {$admin->email} la alerta de vencimiento de la PQR #{$pqr->id}", $pqr, [
                            'recipient' => $admin->email,
                            'type' => $type,
                        ]);
                        $results['sent']++;
                    } catch (Throwable $exception) {
                        report($exception);
                        AuditLogger::logSystem('Notificaciones', 'fallo_correo', "No fue posible enviar a {$admin->email} la alerta de la PQR #{$pqr->id}", $pqr, [
                            'recipient' => $admin->email,
                            'type' => $type,
                        ]);
                        $results['failed']++;
                    }
                }
            });

        return $results;
    }
}
