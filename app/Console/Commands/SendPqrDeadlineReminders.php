<?php

namespace App\Console\Commands;

use App\Services\PqrNotificationService;
use Illuminate\Console\Command;

class SendPqrDeadlineReminders extends Command
{
    protected $signature = 'pqrs:notify-deadlines';

    protected $description = 'Envía alertas de PQR que vencen mañana o el día de hoy';

    public function handle(PqrNotificationService $notifications): int
    {
        $results = $notifications->sendDeadlineReminders();
        $this->info("Enviadas: {$results['sent']}; omitidas: {$results['skipped']}; fallidas: {$results['failed']}.");

        return $results['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
