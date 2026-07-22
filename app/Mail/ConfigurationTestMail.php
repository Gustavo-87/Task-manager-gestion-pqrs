<?php

namespace App\Mail;

use App\Models\AppSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfigurationTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AppSetting $settings) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Prueba de configuración de correo');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.configuration-test');
    }
}
