<?php

namespace App\Mail;

use App\Models\Postulacion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirma a quien se postuló que su postulación llegó. Antes «postularse»
 * era un enlace de WhatsApp sin rastro; ahora queda registrada, pero hasta
 * el 30 ago 2026 solo se avisaba al establecimiento — el candidato mandaba
 * su formulario y no volvía a saber de él. El gremio lo pidió textualmente
 * en la revisión del 28 de agosto (OBS3-09).
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
