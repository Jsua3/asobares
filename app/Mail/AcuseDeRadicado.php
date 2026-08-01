<?php

namespace App\Mail;

use App\Models\Mensaje;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Acuse de recibo de una PQR. En el demo el mailer es `log`: el correo
 * queda escrito en storage/logs/laravel.log y no sale nada real.
 */
class AcuseDeRadicado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Mensaje $mensaje) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Radicado {$this->mensaje->radicado} — ASOBARES Capítulo Quindío",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.acuse-de-radicado');
    }
}
