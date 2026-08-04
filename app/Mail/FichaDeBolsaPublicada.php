<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso a quien se inscribió en la bolsa de artistas o de proveedores de que
 * su ficha ya está publicada. No recibe modelo porque sirve a dos modelos
 * distintos y solo necesita el nombre y la URL.
 */
class FichaDeBolsaPublicada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $nombreDeLaFicha,
        public readonly string $urlPublica,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ya apareces en el directorio: {$this->nombreDeLaFicha}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.ficha-de-bolsa-publicada');
    }
}
