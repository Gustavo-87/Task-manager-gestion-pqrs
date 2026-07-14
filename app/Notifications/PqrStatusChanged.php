<?php

namespace App\Notifications;

use App\Models\Pqr;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AppSetting;

class PqrStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Pqr $pqr,
        public string $previousStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Actualización de la PQR #{$this->pqr->id}")
            ->view('emails.pqr-status-changed', [
                'recipient' => $notifiable,
                'pqr' => $this->pqr,
                'previousStatus' => $this->statusLabel($this->previousStatus),
                'newStatus' => $this->statusLabel($this->newStatus),
                'settings' => AppSetting::current(),
            ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'radicada' => 'Radicada',
            'en_revision' => 'En revisión',
            'respondida' => 'Respondida',
            'cerrada' => 'Cerrada',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }
}
