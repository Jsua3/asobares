<?php

namespace App\Mail;

use App\Models\Vacante;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Devolver sin decir por qué obliga al asociado a adivinar y a llamar a la
 * oficina: el motivo viaja en el correo y queda guardado en la vacante.
 */
class VacanteDevuelta extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Vacante $vacante) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu vacante necesita un ajuste: {$this->vacante->cargo}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.vacante-devuelta');
    }
}
