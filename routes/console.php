<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Pqr;
use App\Notifications\PqrEventNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pqrs:send-reminders', function () {
    $pqrs = Pqr::whereIn('estado', ['radicada','en_revision'])->whereBetween('fecha_limite_respuesta', [today(), today()->addDays(3)])->where(fn($q) => $q->whereNull('last_reminder_at')->orWhereDate('last_reminder_at','<',today()))->get();
    foreach ($pqrs as $pqr) {
        $notification = new PqrEventNotification($pqr, 'Solicitud próxima a vencer', 'La PQR-'.str_pad($pqr->id,4,'0',STR_PAD_LEFT).' vence el '.$pqr->fecha_limite_respuesta->format('d/m/Y').'.');
        $pqr->user?->notify($notification); $pqr->assignee?->notify($notification); $pqr->update(['last_reminder_at'=>now()]);
    }
    $this->info("Recordatorios enviados: {$pqrs->count()}");
})->purpose('Envía recordatorios de PQRS próximas a vencer');
Schedule::command('pqrs:send-reminders')->dailyAt('08:00');
