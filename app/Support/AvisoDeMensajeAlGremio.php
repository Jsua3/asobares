<?php

namespace App\Support;

use App\Mail\MensajeRecibido;
use App\Models\Mensaje;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
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
 * Avisa por dos caminos a propósito, porque uno de los dos está roto: el correo
 * saliente no existe todavía (D-07, sin SMTP) y la notificación del panel sí
 * funciona hoy. El día que haya SMTP no hay que tocar nada.
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
        self::notificarEnElPanel($mensaje);
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

    /**
     * Notificación en el panel para quien atiende la bandeja.
     *
     * Se pregunta por la policy y no por un rol fijo, igual que hace
     * `FlujoDeAprobacionObserver`: si mañana se crea un rol nuevo que atienda
     * PQR, se entera solo.
     *
     * Va en `rescue()` por lo mismo que el correo: una notificación que falla
     * --la tabla llena, un usuario corrupto-- no puede dejar sin radicado a
     * quien acaba de poner una queja.
     */
    private static function notificarEnElPanel(Mensaje $mensaje): void
    {
        rescue(function () use ($mensaje): void {
            $cuerpo = $mensaje->esPqr()
                ? "Radicado {$mensaje->radicado}. La ley da quince días hábiles: vence el "
                    .$mensaje->venceEl()?->translatedFormat('j \d\e F').'.'
                : "Llegó por el formulario de {$mensaje->tipo->getLabel()}.";

            $destinatarios = User::query()
                ->whereHas('roles')
                ->get()
                ->filter(fn (User $usuario): bool => $usuario->can('viewAny', Mensaje::class));

            foreach ($destinatarios as $destinatario) {
                Notification::make()
                    ->title($mensaje->esPqr() ? 'Entró una PQR' : 'Entró un mensaje')
                    ->body($cuerpo)
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->iconColor($mensaje->esPqr() ? 'danger' : 'info')
                    ->actions([
                        Action::make('abrir')
                            ->label('Abrir la bandeja')
                            ->url(route('filament.admin.resources.mensajes.index'))
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($destinatario);
            }
        });
    }
}
