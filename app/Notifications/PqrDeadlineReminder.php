<?php

namespace App\Notifications;

use App\Models\Pqr;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AppSetting;

class PqrDeadlineReminder extends Notification
{
    use Queueable;

    public function __construct(public Pqr $pqr, public int $daysRemaining) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $timing = $this->daysRemaining === 0 ? 'vence hoy' : 'vence mañana';

        return (new MailMessage)
            ->subject("Alerta de vencimiento: PQRS #{$this->pqr->id} {$timing}")
            ->view('emails.pqr-deadline-reminder', [
                'recipient' => $notifiable,
                'pqr' => $this->pqr,
                'timing' => $timing,
                'settings' => AppSetting::current(),
            ]);
    }
}
