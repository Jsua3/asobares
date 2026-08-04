<x-layouts.publico titulo="Mi cuenta — ASOBARES Quindío"
                   descripcion="Estado de cuenta y beneficios del establecimiento afiliado.">

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Saludo. Cerrar sesión vive en el desplegable de la navbar: tenerlo
             también aquí dejaba dos botones idénticos a la vista. --}}
        <header>
            <p class="text-sm text-apagado">Hola, {{ auth()->user()->name }}</p>
            <h1 class="mt-1 font-display text-3xl font-bold tracking-tight">{{ $asociado->nombre }}</h1>
            <p class="mt-1.5 text-sm text-tenue">
                {{ $asociado->categoria->nombre }} · {{ $asociado->municipio->nombre }}
                @if ($asociado->fecha_afiliacion)
                    · Afiliado desde {{ $asociado->fecha_afiliacion->translatedFormat('F \d\e Y') }}
                @endif
            </p>
        </header>

        <nav class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('mi-cuenta.vacantes.index') }}"
               class="rounded-xl border border-linea-fuerte px-5 py-2.5 text-sm font-semibold hover:border-marca-500/50">
                Mis vacantes
            </a>
        </nav>

        @if (session('exito'))
            <x-publico.alerta class="mt-8">{{ session('exito') }}</x-publico.alerta>
        @endif

        {{-- Estado de cartera --}}
        <section class="mt-10" aria-labelledby="cartera">
            <h2 id="cartera" class="font-display text-xl font-bold">Estado de cuenta</h2>

            @if ($cartera->estaAlDia())
                <div class="mt-5 rounded-2xl border border-exito-linea bg-exito-fondo p-8">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-500/20">
                            <svg class="h-6 w-6 text-exito" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                        </span>
                        <div>
                            <p class="font-display text-2xl font-bold text-exito">Estás al día</p>
                            <p class="mt-1.5 text-sm text-exito-suave">
                                No tienes saldos pendientes con el capítulo.
                                @if ($cartera->ultimo_pago_at)
                                    Tu último pago fue el {{ $cartera->ultimo_pago_at->translatedFormat('d \d\e F \d\e Y') }}.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-5 rounded-2xl border border-marca-500/40 bg-marca-panel p-8">
                    <div class="flex flex-wrap items-start justify-between gap-6">
                        <div class="flex items-start gap-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-marca-500/20">
                                <svg class="h-6 w-6 text-acento" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                                </svg>
                            </span>
                            <div>
                                <p class="font-display text-2xl font-bold text-acento-fuerte">
                                    Debes {{ $cartera->meses_mora }} {{ Str::plural('mes', $cartera->meses_mora) }}
                                </p>
                                <p class="mt-1.5 font-display text-3xl font-bold">{{ pesos($cartera->saldo_pendiente) }}</p>
                                @if ($cartera->ultimo_pago_at)
                                    <p class="mt-2 text-xs text-tenue">
                                        Último pago registrado: {{ $cartera->ultimo_pago_at->translatedFormat('d \d\e F \d\e Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('mi-cuenta.pagar') }}" class="shrink-0">
                            @csrf
                            <button type="submit"
                                    class="rounded-xl bg-marca-500 px-8 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                                Pagar ahora
                            </button>
                            <p class="mt-2 text-center text-[.65rem] text-apagado">PSE o tarjeta</p>
                        </form>
                    </div>
                </div>
            @endif

            @if ($cartera->actualizado_at)
                <p class="mt-3 text-xs text-apagado">
                    Información actualizada {{ $cartera->actualizado_at->diffForHumans() }}.
                    Si no coincide con tus registros, escríbenos a
                    <a href="mailto:{{ ajuste('contacto_correo') }}" class="text-acento">{{ ajuste('contacto_correo') }}</a>.
                </p>
            @endif
        </section>

        {{-- Historial de pagos --}}
        @if ($transacciones->isNotEmpty())
            <section class="mt-12" aria-labelledby="pagos">
                <h2 id="pagos" class="font-display text-xl font-bold">Tus últimos movimientos</h2>
                <div class="tarjeta mt-5 divide-y divide-linea">
                    @foreach ($transacciones as $transaccion)
                        <div class="flex flex-wrap items-center justify-between gap-3 p-5">
                            <div>
                                <p class="text-sm font-medium">{{ $transaccion->concepto->getLabel() }}</p>
                                <p class="mt-0.5 font-mono text-xs text-apagado">
                                    {{ $transaccion->referencia }} · {{ $transaccion->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-display text-sm font-semibold">{{ pesos($transaccion->monto) }}</p>
                                <p @class([
                                    'mt-0.5 text-xs',
                                    'text-exito' => $transaccion->estaAprobada(),
                                    'text-aviso' => $transaccion->estado === \App\Enums\EstadoTransaccion::Pendiente,
                                    'text-acento' => $transaccion->estado === \App\Enums\EstadoTransaccion::Rechazada,
                                ])>{{ $transaccion->estado->getLabel() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Convenios: contenido privado --}}
        <section class="mt-12" aria-labelledby="convenios">
            <div class="flex items-center gap-2.5">
                <svg class="h-4 w-4 text-acento" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
                <h2 id="convenios" class="font-display text-xl font-bold">Convenios vigentes</h2>
            </div>
            <p class="mt-2 text-sm text-tenue">
                El detalle de cada convenio es información privada de los afiliados. No aparece en el sitio público.
            </p>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                @foreach ($aliados as $aliado)
                    <div class="tarjeta p-6">
                        <div class="flex items-start gap-4">
                            @if ($aliado->logo)
                                <img src="{{ Storage::disk('public')->url($aliado->logo) }}" alt=""
                                     loading="lazy" width="64" height="48"
                                     class="h-12 w-16 shrink-0 rounded-lg object-cover">
                            @endif
                            <div class="min-w-0">
                                <h3 class="font-display text-base font-semibold">{{ $aliado->nombre }}</h3>
                                <p class="mt-1 text-xs text-apagado">{{ $aliado->descripcion }}</p>
                            </div>
                        </div>

                        @if ($aliado->tieneConvenioPrivado())
                            <div class="mt-4 rounded-xl border border-marca-500/25 bg-marca-panel p-4">
                                <p class="text-[.65rem] font-semibold uppercase tracking-wider text-acento">
                                    Condiciones del convenio
                                </p>
                                <p class="mt-2 text-sm leading-relaxed text-tinta">{{ $aliado->detalle_convenio }}</p>
                            </div>
                        @else
                            <p class="mt-4 text-xs text-apagado">
                                Este aliado todavía no tiene condiciones comerciales publicadas.
                            </p>
                        @endif

                        @if ($aliado->url)
                            <a href="{{ $aliado->url }}" target="_blank" rel="noopener"
                               class="mt-4 inline-block text-sm text-acento hover:text-acento-fuerte">
                                Sitio del aliado ↗
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-layouts.publico>
