<?php

namespace App\Services;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoInscripcion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Models\Asociado;
use App\Models\Inscripcion;
use App\Models\Transaccion;
use App\Pagos\PasarelaDePago;
use App\Pagos\ResultadoDePago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Punto único donde un pago cambia el estado del mundo.
 *
 * Regla dura: ninguna inscripción se confirma y ninguna cartera se salda
 * sin una transacción aprobada.
 */
class RegistroDePagos
{
    public function __construct(private readonly PasarelaDePago $pasarela) {}

    public function cobrarInscripcion(Inscripcion $inscripcion): Transaccion
    {
        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Evento,
            'inscripcion_id' => $inscripcion->id,
            'monto' => $inscripcion->evento->precio,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Pendiente,
            'metodo' => MetodoPago::Pse,
            'payload' => ['pasarela' => $this->pasarela->nombre()],
        ]);

        $inscripcion->update(['transaccion_id' => $transaccion->id]);

        return $transaccion;
    }

    public function cobrarMensualidad(Asociado $asociado, float $monto): Transaccion
    {
        return Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'asociado_id' => $asociado->id,
            'monto' => $monto,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Pendiente,
            'metodo' => MetodoPago::Pse,
            'payload' => ['pasarela' => $this->pasarela->nombre()],
        ]);
    }

    public function enlaceDePago(Transaccion $transaccion): string
    {
        return $this->pasarela->crearEnlaceDePago($transaccion);
    }

    /**
     * Aplica una confirmación de pago. Es idempotente: si la transacción ya
     * estaba resuelta, no vuelve a tocar nada (las pasarelas reintentan).
     */
    public function aplicarConfirmacion(ResultadoDePago $resultado): ?Transaccion
    {
        return DB::transaction(function () use ($resultado): ?Transaccion {
            $transaccion = str_starts_with($resultado->referencia, 'LNK_')
                ? Transaccion::where('bold_payment_link', $resultado->referencia)->lockForUpdate()->first()
                : Transaccion::where('referencia', $resultado->referencia)->lockForUpdate()->first();

            if ($transaccion === null) {
                return null;
            }

            if ($transaccion->estado !== EstadoTransaccion::Pendiente) {
                if ($this->contradiceLoResuelto($transaccion, $resultado)) {
                    $this->registrarIncidencia($transaccion, $resultado);
                }

                return $transaccion;
            }

            // Una aprobación que no cuadra con lo cobrado no se aplica: la
            // transacción sigue pendiente y queda constancia para revisarla a
            // mano. Es preferible un cobro sin resolver a una deuda saldada
            // con menos dinero del que se debía.
            if ($resultado->fueAprobado() && ! $this->montoConcuerda($transaccion, $resultado)) {
                Log::error('Confirmación de pago descartada: el monto notificado no cuadra.', [
                    'referencia' => $transaccion->referencia,
                    'esperado' => (float) $transaccion->monto,
                    'moneda_esperada' => $transaccion->moneda,
                    'notificado' => $resultado->monto,
                    'moneda_notificada' => $resultado->moneda,
                ]);

                return $transaccion;
            }

            $transaccion->update([
                'estado' => $resultado->estado,
                'metodo' => $resultado->metodo,
                'payload' => array_merge($transaccion->payload ?? [], $resultado->payload),
            ]);

            if ($resultado->fueAprobado()) {
                $this->aplicarEfectos($transaccion->fresh());
            }

            return $transaccion->fresh();
        });
    }

    /**
     * ¿La notificación trae un desenlace distinto del que ya quedó registrado?
     *
     * Repetir el mismo desenlace es el reintento normal de una pasarela, y un
     * evento que la pasarela no sabe traducir llega como Pendiente y no dice
     * nada del cobro: los dos se ignoran en silencio. Lo que no puede pasar
     * en silencio es lo contrario de lo resuelto, como una anulación
     * (VOID_APPROVED) sobre un cobro aprobado o una aprobación sobre uno
     * rechazado. Un intento SALE_REJECTED de API Link no cierra el enlace:
     * Bold permite volver a intentar el pago sobre el mismo LNK.
     */
    private function contradiceLoResuelto(Transaccion $transaccion, ResultadoDePago $resultado): bool
    {
        return $resultado->estado !== EstadoTransaccion::Pendiente
            && $resultado->estado !== $transaccion->estado;
    }

    /**
     * Deja rastro de una notificación que contradice un cobro ya resuelto.
     *
     * No revierte nada. Deshacer una inscripción confirmada o un abono a
     * cartera es una decisión de negocio que no está tomada, y la semántica
     * exacta de los eventos de Bold sigue sin confirmar en su sandbox. Lo que
     * sí garantiza es que alguien se entere por tres vías: una línea de error
     * en el log, la marca `incidencias` dentro de la propia transacción, que
     * es lo que abre quien concilia, y una entrada en la bitácora del panel.
     */
    private function registrarIncidencia(Transaccion $transaccion, ResultadoDePago $resultado): void
    {
        $payload = $transaccion->payload ?? [];
        $eventoId = data_get($resultado->payload, 'evento_id');

        if ($eventoId !== null && collect($payload['incidencias'] ?? [])
            ->contains(fn (array $incidencia): bool => ($incidencia['evento_id'] ?? null) === $eventoId)) {
            return;
        }

        $incidencia = [
            'tipo' => 'notificacion_contradictoria',
            'evento_id' => $eventoId,
            'estado_registrado' => $transaccion->estado->value,
            'estado_notificado' => $resultado->estado->value,
            'evento' => data_get($resultado->payload, 'evento.type'),
            'recibida_at' => now()->toIso8601String(),
        ];

        Log::error('Notificación de pago que contradice una transacción ya resuelta: no se aplica y queda para revisión manual.', [
            'referencia' => $transaccion->referencia,
            ...$incidencia,
        ]);

        $payload['incidencias'] = [...($payload['incidencias'] ?? []), $incidencia];

        $transaccion->update(['payload' => $payload]);

        activity('pagos')
            ->performedOn($transaccion)
            ->event('updated')
            ->withProperties(['referencia' => $transaccion->referencia, ...$incidencia])
            ->log(sprintf(
                'La pasarela notificó «%s» sobre el cobro %s, que ya estaba «%s». No se aplicó nada: hay que revisarlo a mano.',
                $resultado->estado->value,
                $transaccion->referencia,
                $transaccion->estado->value,
            ));
    }

    /**
     * ¿Lo que la pasarela dice haber cobrado es lo que se cobró?
     *
     * Una notificación sin monto no se da por buena. Si el nombre real del
     * campo no fuera ninguno de los que adivina `PasarelaBold`, aceptarla con
     * un aviso en el log haría que NINGÚN pago se comparara nunca contra lo
     * cobrado: el control quedaría inerte sin que nadie se entere, que es justo
     * el modo de fallar que no se puede permitir en la parte del dinero.
     *
     * Fallando cerrado, la transacción se queda pendiente y el desajuste se
     * ve en la primera prueba contra el sandbox, que es cuando toca
     * descubrirlo. Ningún pago se pierde: la referencia sigue viva y se
     * concilia a mano o con la notificación corregida.
     */
    private function montoConcuerda(Transaccion $transaccion, ResultadoDePago $resultado): bool
    {
        if ($resultado->monto === null) {
            Log::warning('La pasarela confirmó un pago sin informar el monto: no se concilia y queda pendiente.', [
                'referencia' => $transaccion->referencia,
            ]);

            return false;
        }

        if (round((float) $transaccion->monto, 2) !== round($resultado->monto, 2)) {
            return false;
        }

        if (data_get($resultado->payload, 'pasarela') === 'bold' && $resultado->monto !== floor($resultado->monto)) {
            return false;
        }

        return $resultado->moneda !== null
            && strtoupper($resultado->moneda) === strtoupper((string) $transaccion->moneda);
    }

    /** Lo que un pago aprobado desencadena, según su concepto. */
    private function aplicarEfectos(Transaccion $transaccion): void
    {
        match ($transaccion->concepto) {
            ConceptoTransaccion::Evento => $transaccion->inscripcion?->update([
                'estado' => EstadoInscripcion::Confirmada,
            ]),
            ConceptoTransaccion::Mensualidad => $transaccion->asociado?->cartera?->abonar((float) $transaccion->monto),
            ConceptoTransaccion::Afiliacion => null,
        };
    }
}
