<?php

namespace App\Mail;

use App\Models\Mensaje;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaPqr extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Mensaje $mensaje) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva PQR {$this->mensaje->radicado}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.nueva-pqr');
    }
}
