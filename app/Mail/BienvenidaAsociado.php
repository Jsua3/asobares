<?php

namespace App\Mail;

use App\Models\Asociado;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaAsociado extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $usuario,
        public readonly Asociado $asociado,
        public readonly string $token,
        public readonly bool $reenvio = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reenvio
                ? 'Nuevo enlace de acceso a Mi Cuenta ASOBARES'
                : 'Tu afiliación a ASOBARES fue aprobada',
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.bienvenida-asociado', with: [
            'reenvio' => $this->reenvio,
            'url' => route('mi-cuenta.password.reset', [
                'token' => $this->token,
                'email' => $this->usuario->email,
            ]),
        ]);
    }
}
