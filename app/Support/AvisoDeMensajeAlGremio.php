<?php

namespace App\Support;

use App\Mail\MensajeRecibido;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Mail;

/**
 * Avisarle al gremio de que entró algo a la bandeja (Acta 08, A-04).
 *
 * Una PQR tiene plazo legal de quince días hábiles (Ley 1755 de 2015), y sin
 * este aviso ese reloj corre mientras nadie entra al panel. El destino es el
 * ajuste `contacto_correo_destino`, que la oficina edita como «Correo que
 * recibe los formularios».
 *
 * Avisa por **correo y nada más**, sin `sendToDatabase()`: el panel no activa
 * `databaseNotifications()`, así que una notificación de base quedaría en una
 * tabla que ninguna pantalla lee. Lo que sí se ve en el panel son el contador
 * del menú de `MensajeResource` y la tarjeta de bandeja del tablero.
 * `Panel\AvisosQueSeVenTest` impide escribir avisos que nadie lee.
 *
 * Vive en `Support/` y no en un observer para que el aviso salga **solo desde
 * los formularios públicos**: un observer también dispararía con las semillas,
 * la importación y cada `create()` de una prueba.
 */
final class AvisoDeMensajeAlGremio
{
    /** El ajuste que la oficina edita en «Ajustes del sitio». */
    public const string CLAVE_DESTINO = 'contacto_correo_destino';

    /**
     * El correo no puede tumbar la petición que lo dispara.
     *
     * La PQR ya quedó radicada y el ciudadano necesita su número aunque el
     * transporte esté caído. El fallo se reporta al registro y quien escribió
     * no se entera de nada.
     *
     * Vaciar el ajuste apaga el aviso, igual que vaciar el número apaga el botón
     * de WhatsApp: no es un error, es cómo se desactiva.
     */
    public static function enviar(Mensaje $mensaje): void
    {
        $destino = trim((string) ajuste(self::CLAVE_DESTINO));

        if ($destino === '') {
            return;
        }

        rescue(function () use ($destino, $mensaje): void {
            Mail::to($destino)->send(new MensajeRecibido($mensaje));
        });
    }
}
