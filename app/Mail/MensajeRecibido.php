<?php

namespace App\Mail;

use App\Models\Mensaje;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al gremio de que entró algo a la bandeja (Acta 08, A-04): una PQR con
 * plazo legal de quince días hábiles no puede esperar a que alguien se acuerde
 * de abrir el panel.
 *
 * ⚠️ **No lleva el texto del mensaje ni el teléfono de quien escribe.** El correo
 * del gremio sale de la aplicación y aterriza en un buzón de Google que este
 * proyecto no controla, se reenvía y se queda archivado años; copiar ahí los
 * datos personales del ciudadano los saca del sistema que sí sabe borrarlos
 * cuando vence su plazo (`mensajes:depurar`, §9 del encargo). El aviso dice
 * qué llegó, de qué tipo y cuándo vence; el contenido se lee en el panel, que
 * es donde vive.
 */
class MensajeRecibido extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Mensaje $mensaje) {}

    public function envelope(): Envelope
    {
        $asunto = $this->mensaje->esPqr()
            ? "PQR {$this->mensaje->radicado} — responder antes del {$this->mensaje->venceEl()?->translatedFormat('j \d\e F')}"
            : 'Nuevo mensaje en la bandeja del sitio';

        return new Envelope(subject: $asunto);
    }

    public function content(): Content
    {
        return new Content(markdown: 'correo.mensaje-recibido');
    }
}
