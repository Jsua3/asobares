@php
    $aprobada = $transaccion->estaAprobada();
    $pendiente = $transaccion->estado === \App\Enums\EstadoTransaccion::Pendiente;
@endphp

<x-layouts.publico titulo="Estado del pago — ASOBARES Quindío" descripcion="Resultado de la transacción.">

    <div class="flex min-h-[80vh] items-center">
        <div class="mx-auto w-full max-w-lg px-4 py-14 text-center sm:px-6">

            <span @class([
                'mx-auto flex h-16 w-16 items-center justify-center rounded-full',
                'bg-emerald-500/15' => $aprobada,
                'bg-amber-500/15' => $pendiente,
                'bg-marca-500/15' => ! $aprobada && ! $pendiente,
            ])>
                @if ($aprobada)
                    <svg class="h-8 w-8 text-exito" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                    </svg>
                @elseif ($pendiente)
                    <svg class="h-8 w-8 text-aviso" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                @else
                    <svg class="h-8 w-8 text-acento" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                @endif
            </span>

            <h1 class="mt-6 font-display text-2xl font-bold sm:text-3xl">
                @if ($aprobada)
                    Pago aprobado
                @elseif ($pendiente)
                    Pago en proceso
                @else
                    Pago rechazado
                @endif
            </h1>

            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-tenue text-pretty">
                @if ($aprobada)
                    @if ($transaccion->concepto === \App\Enums\ConceptoTransaccion::Evento)
                        Tu inscripción a «{{ $transaccion->inscripcion?->evento?->titulo }}» quedó confirmada.
                        Te enviamos los detalles al correo que registraste.
                    @elseif ($transaccion->concepto === \App\Enums\ConceptoTransaccion::Mensualidad)
                        Tu estado de cuenta quedó al día. Gracias por mantener el gremio funcionando.
                    @else
                        Registramos tu pago correctamente.
                    @endif
                @elseif ($pendiente)
                    Estamos esperando la confirmación de la entidad financiera. Te avisamos apenas se resuelva.
                @else
                    La transacción no se completó. No se hizo ningún cobro y puedes intentarlo de nuevo.
                @endif
            </p>

            <div class="tarjeta mt-8 p-6 text-left">
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-apagado">Referencia</dt>
                        <dd class="font-mono text-tinta">{{ $transaccion->referencia }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-apagado">Concepto</dt>
                        <dd class="text-tinta">{{ $transaccion->concepto->getLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-apagado">Monto</dt>
                        <dd class="font-semibold text-tinta">{{ pesos($transaccion->monto) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-apagado">Método</dt>
                        <dd class="text-tinta">{{ $transaccion->metodo->getLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-apagado">Estado</dt>
                        <dd @class([
                            'font-semibold',
                            'text-exito' => $aprobada,
                            'text-aviso' => $pendiente,
                            'text-acento' => ! $aprobada && ! $pendiente,
                        ])>{{ $transaccion->estado->getLabel() }}</dd>
                    </div>
                </dl>
            </div>

            <x-publico.boton :href="$volverA" class="mt-8">
                Volver
            </x-publico.boton>
        </div>
    </div>
</x-layouts.publico>
