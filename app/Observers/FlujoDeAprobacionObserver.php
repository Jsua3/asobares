<?php

namespace App\Observers;

use App\Enums\EstadoPublicacion;
use App\Models\Evento;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Hace cumplir el flujo de aprobación en el modelo, no en el formulario.
 *
 * Da igual que alguien altere el HTML o mande la petición a mano: si el
 * usuario no tiene permiso de publicar ese recurso, el registro se guarda
 * como `pendiente_aprobacion` (RF-37).
 *
 * No avisa a nadie: el panel no activa `databaseNotifications()`. Quien puede
 * aprobar se entera por la banda «Te está esperando» del tablero
 * (`ColaDePendientes`), que pregunta a las mismas policies.
 * `Panel\AvisosQueSeVenTest` impide escribir avisos que nadie lee.
 */
class FlujoDeAprobacionObserver
{
    public function saving(Model $modelo): void
    {
        // Escape puntual para cambios de ciclo de vida que no son edición de
        // contenido (cerrar/reabrir una vacante): ver `Vacante::$saltaFlujoDeAprobacion`.
        // El `instanceof` es obligatorio: sin él, `$modelo->saltaFlujoDeAprobacion`
        // en cualquiera de los otros ocho modelos publicables pasaría por el
        // `__get` de Eloquent (atributos, relaciones, accessors) en vez de leer
        // una propiedad de PHP, dejando la puerta abierta a que una futura
        // columna o accessor con ese nombre apague RF-37 en silencio.
        if ($modelo instanceof Vacante && $modelo->saltaFlujoDeAprobacion) {
            return;
        }

        if ($modelo instanceof Evento && $modelo->esAltaComunitariaValidada()) {
            return;
        }

        $usuario = Auth::user();

        // Semillas, comandos de consola y jobs no pasan por el flujo.
        if (! $usuario instanceof User) {
            return;
        }

        // La frontera de publicación se cruza en los dos sentidos. Mirar sólo
        // el destino dejaba libre el camino de vuelta: quien no puede publicar
        // bajaba a borrador algo ya aprobado y lo sacaba del sitio en
        // silencio, sin aviso ni rastro en la cola de revisión. Un cambio de
        // estado sobre contenido publicado también es una decisión de
        // publicación, así que pasa por la misma puerta.
        if ($modelo->estado !== EstadoPublicacion::Publicado && ! $this->estabaPublicado($modelo)) {
            return;
        }

        if ($usuario->cannot('publicar', $modelo)) {
            $modelo->estado = EstadoPublicacion::PendienteAprobacion;
        }
    }

    /**
     * El estado con el que el registro venía de la base. `getOriginal` aplica
     * el cast, pero se compara también contra el valor crudo por si el modelo
     * llega sin castear desde una importación o un `insert` directo.
     */
    private function estabaPublicado(Model $modelo): bool
    {
        if (! $modelo->exists) {
            return false;
        }

        $original = $modelo->getOriginal('estado');

        return $original === EstadoPublicacion::Publicado
            || $original === EstadoPublicacion::Publicado->value;
    }
}
