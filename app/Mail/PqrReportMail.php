<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PqrReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $dateFrom,
        public string $dateTo,
        private string $pdfContent,
        private string $filename,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Reporte de PQRS: {$this->dateFrom} a {$this->dateTo}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.pqr-report');
    }

    public function attachments(): array
    {
        return [Attachment::fromData(fn () => $this->pdfContent, $this->filename)->withMime('application/pdf')];
    }
}
