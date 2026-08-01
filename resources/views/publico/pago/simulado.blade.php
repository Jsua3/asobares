<x-layouts.publico titulo="Pago — ASOBARES Quindío" descripcion="Pasarela de pago.">

    <div class="flex min-h-[80vh] items-center">
        <div class="mx-auto w-full max-w-lg px-4 py-14 sm:px-6">

            <div class="rounded-2xl border border-amber-500/30 bg-amber-950/25 px-4 py-3 text-center text-xs text-amber-200">
                Pasarela simulada del prototipo. No se mueve dinero real.
            </div>

            <div class="tarjeta mt-5 overflow-hidden">
                <div class="border-b border-white/[.09] p-7 text-center">
                    <x-publico.logo alto="h-9" class="mx-auto" />
                    <h1 class="mt-4 font-display text-lg font-semibold">{{ ajuste('sitio_nombre') }}</h1>
                    <p class="mt-1 text-sm text-noche-400">{{ $transaccion->concepto->getLabel() }}</p>
                </div>

                <div class="p-7">
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-noche-400">Referencia</dt>
                            <dd class="font-mono text-noche-100">{{ $transaccion->referencia }}</dd>
                        </div>

                        @if ($transaccion->inscripcion?->evento)
                            <div class="flex justify-between gap-4">
                                <dt class="text-noche-400">Evento</dt>
                                <dd class="text-right text-noche-100">{{ $transaccion->inscripcion->evento->titulo }}</dd>
                            </div>
                            <div class="flex justify-between gap-4">
                                <dt class="text-noche-400">A nombre de</dt>
                                <dd class="text-right text-noche-100">{{ $transaccion->inscripcion->nombre }}</dd>
                            </div>
                        @endif

                        @if ($transaccion->asociado)
                            <div class="flex justify-between gap-4">
                                <dt class="text-noche-400">Establecimiento</dt>
                                <dd class="text-right text-noche-100">{{ $transaccion->asociado->nombre }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-6 border-t border-white/[.09] pt-6">
                        <div class="flex items-baseline justify-between">
                            <span class="text-sm text-noche-400">Total a pagar</span>
                            <span class="font-display text-3xl font-bold text-marca-400">{{ pesos($transaccion->monto) }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('pago.simulado.resolver', $transaccion) }}" class="mt-7"
                          x-data="{ metodo: 'pse' }">
                        @csrf

                        <fieldset>
                            <legend class="mb-3 text-sm font-medium text-noche-100">Método de pago</legend>
                            <div class="grid grid-cols-2 gap-3">
                                @foreach ([
                                    'pse' => ['PSE', 'Débito desde tu banco'],
                                    'tarjeta' => ['Tarjeta', 'Crédito o débito'],
                                ] as $valor => [$titulo, $detalle])
                                    <label class="cursor-pointer rounded-xl border p-4 transition-colors"
                                           :class="metodo === '{{ $valor }}' ? 'border-marca-500 bg-marca-500/10' : 'border-white/10 hover:border-white/25'">
                                        <input type="radio" name="metodo" value="{{ $valor }}" x-model="metodo" class="sr-only"
                                               @checked($valor === 'pse')>
                                        <span class="block text-sm font-semibold">{{ $titulo }}</span>
                                        <span class="mt-0.5 block text-xs text-noche-400">{{ $detalle }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <p class="mt-3 text-xs text-noche-500">
                            La cuenta del gremio está en Itaú. En producción, PSE debita directo desde tu banco.
                        </p>

                        <div class="mt-7 space-y-3">
                            <button type="submit" name="decision" value="aprobar"
                                    class="w-full rounded-xl bg-marca-500 px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                                Pagar {{ pesos($transaccion->monto) }}
                            </button>
                            <button type="submit" name="decision" value="rechazar"
                                    class="w-full rounded-xl border border-white/10 px-6 py-3 text-sm text-noche-300 transition-colors hover:border-marca-500/40 hover:text-white">
                                Simular pago rechazado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.publico>
