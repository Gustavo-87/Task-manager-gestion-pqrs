<?php
namespace App\Notifications;
use App\Models\Pqr;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class PqrEventNotification extends Notification {
    use Queueable;
    public function __construct(public Pqr $pqr, public string $title, public string $message) {}
    public function via(object $notifiable): array { return ['database', 'mail']; }
    public function toArray(object $notifiable): array { return ['pqr_id' => $this->pqr->id, 'title' => $this->title, 'message' => $this->message, 'url' => route('pqrs.show', $this->pqr)]; }
    public function toMail(object $notifiable): MailMessage { return (new MailMessage)->subject($this->title)->greeting("Hola {$notifiable->name}")->line($this->message)->action('Ver solicitud', route('pqrs.show', $this->pqr))->line('Este mensaje fue generado automáticamente por Resuelve.'); }
}
