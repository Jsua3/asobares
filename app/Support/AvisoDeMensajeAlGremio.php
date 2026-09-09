<?php

namespace App\Support;

use App\Mail\MensajeRecibido;
use App\Models\Mensaje;
use Illuminate\Support\Facades\Mail;

/**
 * Avisarle al gremio de que entró algo a la bandeja (Acta 08, A-04).
 *
 * Hasta el 9 de septiembre de 2026 nadie avisaba. El formulario de contacto
 * guardaba el mensaje y, si era PQR, le mandaba el acuse **al ciudadano**; al
 * gremio le quedaba una tarjeta en el tablero, que solo se ve entrando al panel
 * a propósito. Una PQR tiene plazo legal de quince días hábiles (Ley 1755 de
 * 2015) y ese reloj corría sin que nadie lo mirara.
 *
 * Y el ajuste `contacto_correo_destino` --que el panel ofrece editar con la
 * etiqueta «Correo que recibe los formularios»-- no lo leía **ni una línea del
 * proyecto**: se podía cambiar, guardar y ver el aviso verde sin que cambiara
 * nada. Esta clase es lo que faltaba para que ese campo signifique algo.
 *
 * Avisa por **correo y nada más**, y eso tiene su historia. La primera versión de
 * esta clase mandaba además una notificación de base con `sendToDatabase()`, que
 * es lo que uno escribiría mirando `FlujoDeAprobacionObserver`. Al compilar salió
 * que **la campana del panel está retirada desde el 7 de septiembre** (D-L22,
 * `AdminPanelProvider`): `databaseNotifications()` está comentado. O sea que esa
 * notificación se habría escrito en una tabla que ninguna pantalla lee — el mismo
 * defecto que esta sesión vino a arreglar, recién estrenado.
 *
 * Lo que sí se ve, y por eso es lo que se usa: el **contador del menú** en
 * `MensajeResource` y la tarjeta de bandeja del tablero. Es exactamente el
 * mecanismo que D-L22 dejó dicho que sustituía a la campana.
 *
 * Vive en `Support/` y no en un observer para que el aviso salga **solo desde
 * los formularios públicos**: un observer también dispararía con las semillas,
 * la importación y cada `create()` de una prueba.
 */
final class AvisoDeMensajeAlGremio
{
    /** El ajuste que la oficina edita en «Ajustes del sitio». */
    public const string CLAVE_DESTINO = 'contacto_correo_destino';

    public static function enviar(Mensaje $mensaje): void
    {
        self::escribirAlBuzon($mensaje);
    }

    /**
     * El correo no puede tumbar la petición que lo dispara (§9, D-23).
     *
     * La PQR ya quedó radicada y el ciudadano necesita su número aunque el
     * transporte esté caído --que es como ha estado producción desde el primer
     * despliegue--. El fallo se reporta al registro y quien escribió no se
     * entera de nada.
     *
     * Vaciar el ajuste apaga el aviso, igual que vaciar el número apaga el botón
     * de WhatsApp: no es un error, es cómo se desactiva.
     */
    private static function escribirAlBuzon(Mensaje $mensaje): void
    {
        $destino = trim((string) ajuste(self::CLAVE_DESTINO));

        if ($destino === '') {
            return;
        }

        rescue(fn () => Mail::to($destino)->send(new MensajeRecibido($mensaje)));
    }
}
