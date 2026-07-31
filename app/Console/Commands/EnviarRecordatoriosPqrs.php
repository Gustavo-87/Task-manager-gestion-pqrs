<?php

namespace App\Console\Commands;

use App\Application\Contexto\ContextResolver;
use App\Application\Pqrs\ConsultaPqrsContextuales;
use App\Models\Copropiedad;
use App\Notifications\PqrEventNotification;
use Illuminate\Console\Command;

class EnviarRecordatoriosPqrs extends Command
{
    protected $signature = 'pqrs:send-reminders';

    protected $description = 'Envía recordatorios contextuales de PQRS próximas a vencer';

    public function __construct(
        private readonly ContextResolver $contextResolver,
        private readonly ConsultaPqrsContextuales $consultaPqrs
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $enviados = 0;

        Copropiedad::query()->orderBy('id')->each(function (Copropiedad $copropiedad) use (&$enviados): void {
            $contexto = $this->contextResolver->resolverExplicito(
                $copropiedad->organizacion_id,
                $copropiedad->id
            );
            $pqrs = $this->consultaPqrs->para($contexto)
                ->whereIn('estado', ['radicada', 'en_revision'])
                ->whereBetween('fecha_limite_respuesta', [today(), today()->addDays(3)])
                ->where(fn ($query) => $query
                    ->whereNull('last_reminder_at')
                    ->orWhereDate('last_reminder_at', '<', today()))
                ->get();

            foreach ($pqrs as $pqr) {
                $notification = new PqrEventNotification(
                    $pqr,
                    'Solicitud próxima a vencer',
                    'La PQR-'.str_pad($pqr->id, 4, '0', STR_PAD_LEFT)
                        .' vence el '.$pqr->fecha_limite_respuesta->format('d/m/Y').'.'
                );
                $pqr->user?->notify($notification);
                $pqr->assignee?->notify($notification);
                $pqr->update(['last_reminder_at' => now()]);
                $enviados++;
            }
        });

        $this->info("Recordatorios enviados: {$enviados}");

        return self::SUCCESS;
    }
}
