<?php

namespace App\Mail;

use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Le avisa al establecimiento que alguien se postuló. Los datos van en el
 * cuerpo porque el asociado no siempre tiene sesión abierta y la gracia es
 * que pueda llamar al candidato de inmediato.
 */
class NuevaPostulacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Postulacion $postulacion) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nueva postulación: {$this->postulacion->vacante->cargo}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.nueva-postulacion');
    }
}
