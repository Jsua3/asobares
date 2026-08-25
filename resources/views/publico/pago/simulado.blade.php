<x-layouts.publico titulo="Pago — ASOBARES Quindío" descripcion="Pasarela de pago.">

    <div class="flex min-h-[80vh] items-center">
        <div class="mx-auto w-full max-w-lg px-4 py-14 sm:px-6">

            <div class="rounded-2xl border border-aviso-linea bg-aviso-fondo px-4 py-3 text-center text-xs text-aviso-suave">
                Pasarela simulada del prototipo. No se mueve dinero real.
            </div>

            <div class="tarjeta mt-5 overflow-hidden">
                <div class="border-b border-linea p-7 text-center">
                    <x-publico.logo alto="h-9" class="mx-auto" />
                    <h1 class="mt-4 font-display text-lg font-semibold">{{ ajuste('sitio_nombre') }}</h1>
                    <p class="mt-1 text-sm text-apagado">{{ $transaccion->concepto->getLabel() }}</p>
                </div>

                <div class="p-7">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-apagado">Referencia</dt>
                            <dd class="font-mono text-tinta">{{ $transaccion->referencia }}</dd>
                        </div>

                        @if ($transaccion->inscripcion?->evento)
                            <div class="flex justify-between gap-4">
                                <dt class="text-apagado">Evento</dt>
                                <dd class="text-right text-tinta">{{ $transaccion->inscripcion->evento->titulo }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-apagado">A nombre de</dt>
                                <dd class="text-right text-tinta">{{ $transaccion->inscripcion->nombre }}</dd>
                            </div>
                        @endif

                        @if ($transaccion->asociado)
                            <div class="flex justify-between gap-4">
                                <dt class="text-apagado">Establecimiento</dt>
                                <dd class="text-right text-tinta">{{ $transaccion->asociado->nombre }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-6 border-t border-linea pt-6">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm text-apagado">Total a pagar</span>
                            <span class="font-display text-3xl font-bold text-acento">{{ pesos($transaccion->monto) }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('pago.simulado.resolver', $transaccion) }}" class="mt-7"
                          x-data="{ metodo: 'pse', enviando: false }" x-on:submit="enviando = true">
                        @csrf

                        <fieldset>
                            <legend class="mb-3 text-sm font-medium text-tinta">Método de pago</legend>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach ([
                                    'pse' => ['PSE', 'Débito desde tu banco'],
                                    'tarjeta' => ['Tarjeta', 'Crédito o débito'],
                                ] as $valor => [$titulo, $detalle])
                                    <label class="pulsable cursor-pointer rounded-xl border p-4"
                                           :class="metodo === '{{ $valor }}' ? 'border-marca-500 bg-marca-500/10' : 'border-linea hover:border-linea-fuerte'">
                                        <input type="radio" name="metodo" value="{{ $valor }}" x-model="metodo" class="sr-only"
                                               @checked($valor === 'pse')>
                                        <span class="block text-sm font-semibold">{{ $titulo }}</span>
                                        <span class="mt-0.5 block text-xs text-apagado">{{ $detalle }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <p class="mt-3 text-xs text-apagado">
                            La cuenta del gremio está en Itaú. En producción, PSE debita directo desde tu banco.
                        </p>

                        <div class="mt-7 space-y-3">
                            <x-publico.boton name="decision" value="aprobar" class="w-full"
                                x-bind:disabled="enviando"
                                x-bind:class="enviando && 'opacity-55'"
                            >
                                Pagar {{ pesos($transaccion->monto) }}
                            </x-publico.boton>
                            <button type="submit" name="decision" value="rechazar"
                                    x-bind:disabled="enviando"
                                    x-bind:class="enviando && 'opacity-55'"
                                    class="pulsable w-full rounded-xl border border-linea px-6 py-3 text-sm text-tenue hover:border-marca-500/40 hover:text-fuerte">
                                Simular pago rechazado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.publico>
