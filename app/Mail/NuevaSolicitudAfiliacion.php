<?php

namespace App\Mail;

use App\Models\SolicitudAfiliacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevaSolicitudAfiliacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly SolicitudAfiliacion $solicitud) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva solicitud de afiliación: {$this->solicitud->establecimiento_nombre}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.nueva-solicitud-afiliacion');
    }
}
