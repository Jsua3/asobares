<?php

namespace App\Observers;

use App\Enums\EstadoInscripcion;
use App\Enums\EstadoTransaccion;
use App\Models\Inscripcion;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

/**
 * «Ninguna inscripción se confirma sin una transacción aprobada», la regla
 * dura de `RegistroDePagos`, se hace cumplir en el modelo.
 *
 * El selector de estado del panel es editable: sin esto, la secretaría podría
 * marcar «Confirmada» a mano y regalar un cupo de un evento de pago. La
 * transacción no existiría, el cupo se consumiría y la conciliación quedaría
 * descuadrada. En el modelo no la salta ninguna petición hecha con sesión,
 * manipulada o no.
 */
class ConfirmacionDeInscripcionObserver
{
    public function saving(Inscripcion $inscripcion): void
    {
        if ($inscripcion->estado !== EstadoInscripcion::Confirmada) {
            return;
        }

        // Semillas, comandos y jobs no pasan por el cerrojo, igual que en
        // `FlujoDeAprobacionObserver`. El webhook de la pasarela tampoco tiene
        // sesión, y es justo el camino legítimo: ahí la garantía la da la
        // firma y la transacción aprobada que `RegistroDePagos` ya exige. Lo
        // que se vigila aquí es la escritura hecha por una persona con sesión
        // abierta en el panel.
        if (! Auth::user() instanceof User) {
            return;
        }

        // Sin cambio de estado no hay nada que vigilar: editar el teléfono de
        // alguien ya confirmado no puede quedar bloqueado.
        if ($inscripcion->exists && ! $inscripcion->isDirty('estado')) {
            return;
        }

        if ($this->tieneRespaldo($inscripcion)) {
            return;
        }

        throw new AuthorizationException(
            'Una inscripción de pago sólo se confirma con una transacción aprobada.'
        );
    }

    /**
     * Un evento gratuito se confirma solo; uno de pago necesita que su
     * transacción esté aprobada de verdad.
     */
    private function tieneRespaldo(Inscripcion $inscripcion): bool
    {
        if ($inscripcion->evento?->esGratuito() === true) {
            return true;
        }

        return $inscripcion->transaccion?->estado === EstadoTransaccion::Aprobada;
    }
}
