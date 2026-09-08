<?php

namespace App\Mail;

use App\Models\Mensaje;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevoMensajeContacto extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Mensaje $mensaje) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nuevo mensaje de contacto: {$this->mensaje->nombre}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.nuevo-mensaje-contacto');
    }
}
