<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodigoVerificacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $codigo,
        public readonly ?string $clientName = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu código de verificación CESSA',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.codigo-verificacion',
            with: [
                'codigo' => $this->codigo,
                'clientName' => $this->clientName,
            ],
        );
    }
}
