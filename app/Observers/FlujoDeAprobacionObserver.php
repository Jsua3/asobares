<?php

namespace App\Observers;

use App\Enums\EstadoPublicacion;
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

    /*
     * Aquí vivía `saved()`, que por cada registro enviado a revisión consultaba
     * todos los usuarios con rol, le preguntaba a la policy por cada uno y le
     * guardaba una notificación de base de datos.
     *
     * Se retiró el 9 de septiembre de 2026, y el motivo no es de estilo: **la
     * campana del panel se había apagado dos días antes** (D-L22, 7 sep). Sus
     * dos líneas quedaron comentadas en `AdminPanelProvider` con el argumento de
     * que la banda «Te está esperando» del tablero cuenta mejor lo mismo, pero
     * nadie retiró a quien escribía en ella. Resultado: consultas y filas nuevas
     * en cada guardado de contenido, sin una sola pantalla que las leyera.
     *
     * Y cuatro aserciones de `FlujoDeAprobacionTest` en verde sobre ese aviso
     * invisible, que es lo que lo mantuvo escondido: la suite decía que
     * funcionaba y el usuario no podía verlo. Falso verde número trece de este
     * proyecto, encontrado al construir el aviso de PQR del Acta 08 —que iba a
     * repetir el mismo error—.
     *
     * Quien puede aprobar sigue enterándose, por donde D-L22 dijo que se
     * enteraría: la **cola de pendientes** del tablero (`ColaDePendientes`), que
     * pregunta a las mismas policies, se pinta de verdad y ya tenía sus propias
     * pruebas. Las de `FlujoDeAprobacionTest` afirman ahora sobre ella.
     *
     * `Panel\AvisosQueSeVenTest` vigila que las dos mitades no se vuelvan a
     * separar: o hay campana y hay quien escriba, o no hay ninguna de las dos.
     * Si el gremio pide avisos de verdad —correo, campana o lo que sea—, es un
     * frente propio con su decisión, tal como dejó dicho D-L22.
     */
}
