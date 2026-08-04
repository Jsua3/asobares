<?php

namespace App\Mail;

use App\Models\Vacante;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VacanteAprobada extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Vacante $vacante) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu vacante ya está publicada: {$this->vacante->cargo}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.vacante-aprobada');
    }
}
