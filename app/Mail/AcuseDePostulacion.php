<?php

namespace App\Mail;

use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirma a quien se postuló que su postulación llegó. Sin este acuse solo
 * se entera el establecimiento, y el candidato manda su formulario y no
 * vuelve a saber de él.
 *
 * Se manda siempre que la postulación se crea, exista o no correo del
 * establecimiento: que el gremio no pueda avisarle al bar no es motivo para
 * dejar al candidato sin acuse de su propio envío.
 */
class AcuseDePostulacion extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Postulacion $postulacion) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Recibimos tu postulación: {$this->postulacion->vacante->cargo}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.acuse-de-postulacion');
    }
}
