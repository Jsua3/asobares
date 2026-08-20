<x-layouts.publico :titulo="ajuste('guia_titulo').' — ASOBARES Quindío'"
                   descripcion="Requisitos para abrir un bar, gastrobar o café en el Quindío: qué pide cada entidad, cuánto cuesta y qué formatos descargar, municipio por municipio.">

    <x-publico.hero :titulo="ajuste('guia_titulo')" :subtitulo="ajuste('guia_intro')" />

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Selector de municipio --}}
        <section aria-labelledby="selector">
            <h2 id="selector" class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">
                Escoge tu municipio
            </h2>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($municipios as $municipio)
                    <a href="{{ route('guia.index', ['municipio' => $municipio->slug]) }}"
                       @class([
                           'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                           'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => $seleccionado?->is($municipio),
                           'border-linea text-suave hover:border-marca-500/40 hover:text-fuerte' => ! $seleccionado?->is($municipio),
                       ])
                       @if ($seleccionado?->is($municipio)) aria-current="true" style="view-transition-name: filtro-activo" @endif>
                        {{ $municipio->nombre }}
                    </a>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-apagado">
                Estamos levantando la guía municipio por municipio con la información que cada entidad
                entrega al gremio. Si falta el tuyo,
                <a href="{{ route('contacto') }}" class="enlace-accion text-acento hover:text-acento-fuerte">escríbenos</a>.
            </p>
        </section>

        @if ($seleccionado && $requisitos->isNotEmpty())
            {{-- Resumen --}}
            <div class="tarjeta mt-10 grid gap-6 p-6 sm:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-apagado">Municipio</p>
                    <p class="mt-1 font-display text-lg font-semibold">{{ $seleccionado->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-apagado">Entidades a visitar</p>
                    <p class="mt-1 font-display text-lg font-semibold">{{ $requisitos->count() }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-apagado">Costo aproximado</p>
                    <p class="mt-1 font-display text-lg font-semibold text-acento">
                        {{ $costoTotal > 0 ? pesos($costoTotal) : 'Por confirmar' }}
                    </p>
                </div>
            </div>

            {{-- Requisitos por entidad --}}
            <div class="mt-8 space-y-4">
                @foreach ($requisitos as $indice => $requisito)
                    <details class="tarjeta group overflow-hidden" @if ($indice === 0) open @endif>
                        {{-- `fila-pulsable` y no `pulsable`: encoger el <summary> movería la
                             flecha de `group-open:rotate-180` y se leerían dos movimientos
                             peleados sobre el mismo gesto. --}}
                        <summary class="fila-pulsable flex cursor-pointer list-none items-start gap-4 p-6">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-marca-500/15 font-display text-sm font-bold text-acento">
                                {{ $indice + 1 }}
                            </span>

                            <span class="min-w-0 flex-1">
                                <span class="block font-display text-base font-semibold leading-snug">{{ $requisito->entidad }}</span>
                                <span class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                                    @if ($requisito->tieneCosto())
                                        <span class="rounded-full bg-marca-500/15 px-2.5 py-0.5 font-medium text-acento-fuerte">
                                            {{ pesos($requisito->costo_aproximado) }}
                                        </span>
                                    @else
                                        <span class="rounded-full border border-linea px-2.5 py-0.5 text-apagado">Sin costo directo</span>
                                    @endif

                                    @if ($requisito->checklist)
                                        <span class="text-apagado">{{ count($requisito->checklist) }} requisitos</span>
                                    @endif

                                    @if ($requisito->tieneAdjunto())
                                        <span class="text-acento">· Formato descargable</span>
                                    @endif
                                </span>
                            </span>

                            <svg class="mt-1 h-5 w-5 shrink-0 text-apagado transition-transform group-open:rotate-180"
                                 fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </summary>

                        <div class="border-t border-linea px-6 py-6 sm:pl-19">
                            @if ($requisito->descripcion)
                                <p class="text-sm leading-relaxed text-suave">{{ $requisito->descripcion }}</p>
                            @endif

                            @if ($requisito->checklist)
                                <h3 class="mt-6 text-xs font-semibold uppercase tracking-wider text-apagado">
                                    Lo que te van a pedir
                                </h3>
                                <ul class="mt-3 space-y-2.5">
                                    @foreach ($requisito->checklist as $item)
                                        <li class="flex items-start gap-3 text-sm text-tinta">
                                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-marca-500"></span>
                                            <span class="leading-relaxed">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="mt-6 flex flex-wrap gap-2.5">
                                @if ($requisito->tieneAdjunto())
                                    <x-publico.boton :href="route('guia.formato', $requisito)" class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                        Descargar formato
                                    </x-publico.boton>
                                @endif

                                @if ($requisito->enlace_externo)
                                    <a href="{{ $requisito->enlace_externo }}" target="_blank" rel="noopener"
                                       class="pulsable inline-flex min-h-11 items-center rounded-xl border border-linea px-4 py-2.5 text-sm text-tinta hover:border-marca-500/50">
                                        Sitio de la entidad&nbsp;<x-publico.flecha direccion="externa" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>

            {{-- Descargo: texto fijo de la página, no un acuse de nada. --}}
            <x-publico.alerta tipo="aviso" :animado="false" class="mt-10">
                {{ ajuste('guia_descargo') }}
            </x-publico.alerta>

            {{-- CTA --}}
            <div class="tarjeta mt-8 p-8 text-center">
                <h2 class="font-display text-xl font-semibold">¿Dudas con algún trámite?</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                    La orientación jurídica es gratuita para los afiliados, pero si estás empezando y todavía no
                    haces parte del gremio, escríbenos igual: para eso existe esta guía.
                </p>
                <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
                    <x-publico.boton :href="route('contacto')">
                        Escríbenos
                    </x-publico.boton>
                    <x-publico.boton variante="contorno" :href="route('afiliate')">
                        Conoce la afiliación
                    </x-publico.boton>
                </div>
            </div>
        @else
            <div class="tarjeta mt-10 p-12 text-center">
                <p class="font-display text-lg font-semibold">Todavía no hay guía publicada</p>
                <p class="mt-2 text-sm text-tenue">Estamos recopilando la información con las entidades.</p>
            </div>
        @endif
    </div>
</x-layouts.publico>
