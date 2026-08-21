<?php

namespace App\Mail;

use App\Models\Paciente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PacienteQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public Paciente $paciente;
    public string $qrPath;

    /**
     * @param Paciente $paciente
     * @param string $qrPath Ruta absoluta al archivo PNG del QR generado
     */
    public function __construct(Paciente $paciente, string $qrPath)
    {
        $this->paciente = $paciente;
        $this->qrPath = $qrPath;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu código QR de paciente',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.paciente-qr',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->qrPath)
                ->as('mi-codigo-qr.png')
                ->withMime('image/png'),
        ];
    }
}