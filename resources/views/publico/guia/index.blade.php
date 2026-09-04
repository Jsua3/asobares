<x-layouts.publico :titulo="ajuste('guia_titulo').' — ASOBARES Quindío'"
                   descripcion="Requisitos para abrir un bar, gastrobar o café en el Quindío: qué pide cada entidad, cuánto cuesta y qué formatos descargar, municipio por municipio.">

    @if (! ($seleccionado && $requisitos->isNotEmpty()))
        {{-- Un municipio cuya guía entera caducó (o que nunca la tuvo) deja
             esta URL respondiendo 200 con «Todavía no hay guía publicada»:
             sin esto seguiría siendo indexable aunque ya no salga del
             selector ni del sitemap. --}}
        @push('cabeza')
            <meta name="robots" content="noindex, follow">
        @endpush
    @endif

    <x-publico.hero :titulo="ajuste('guia_titulo')" :subtitulo="ajuste('guia_intro')" atmosfera />

    <div class="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">

        {{-- Selector de municipio --}}
        <section class="revelar" data-revelar aria-labelledby="selector">
            <h2 id="selector" class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">
                Escoge tu municipio
            </h2>
            <div class="mt-5 flex flex-wrap gap-2">
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
            <div class="revelar vidrio mt-10 grid gap-6 rounded-[1.5rem] p-6 sm:grid-cols-3" data-revelar>
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
            <div class="revelar mt-8 space-y-4" data-revelar>
                @foreach ($requisitos as $indice => $requisito)
                    <details @class([
                        'tarjeta tarjeta-hover group overflow-hidden',
                        'vidrio' => $indice === 0,
                    ]) @if ($indice === 0) open @endif>
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
                                        {{-- Decía «Sin costo directo», que es una AFIRMACIÓN: le
                                             dice al empresario que el trámite es gratis. Un
                                             `costo_aproximado` nulo no significa eso, significa que
                                             nadie ha averiguado cuánto vale --que es el estado de
                                             los ocho trámites, porque el documento oficial del
                                             gremio no trae ni una cifra--. Decirle «sin costo» a
                                             quien está haciendo cuentas para abrir un bar es
                                             exactamente el error que el §29.4 señala. La cabecera
                                             de la tarjeta ya lo dice bien: «Por confirmar». --}}
                                        <span class="rounded-full border border-linea px-2.5 py-0.5 text-apagado">Costo por confirmar</span>
                                    @endif

                                    @if ($requisito->checklist)
                                        <span class="text-apagado">{{ count($requisito->checklist) }} requisitos</span>
                                    @endif

                                    @if ($requisito->tieneAdjunto())
                                        <span class="text-acento">· Formato descargable</span>
                                    @endif

                                    @if ($requisito->estaVerificado())
                                        <span class="text-exito-suave">
                                            · Verificado el {{ $requisito->verificado_el->translatedFormat('d \d\e F \d\e Y') }}
                                        </span>
                                    @else
                                        <span class="text-aviso-suave">· Sin verificar contra la fuente oficial</span>
                                    @endif

                                    @if ($requisito->esTransitorio())
                                        <span class="rounded-full border border-aviso-linea bg-aviso-fondo px-2.5 py-0.5 text-aviso-suave">
                                            Vigente hasta el {{ $requisito->vigente_hasta->translatedFormat('d \d\e F \d\e Y') }}
                                        </span>
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

                            @if ($requisito->verificado_con)
                                <p class="mt-3 text-xs text-apagado">
                                    Fuente: {{ $requisito->verificado_con }}
                                </p>
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
                                    {{-- OBS3-10: la etiqueta no promete mas de lo que el enlace
                                         cumple. Con enlace puntual invita al tramite; con un
                                         dominio pelado dice lo que es, una puerta. Asi el dia que
                                         el gremio entregue las URL exactas la mejora se nota sola,
                                         sin tocar la vista. --}}
                                    <a href="{{ $requisito->enlace_externo }}" target="_blank" rel="noopener"
                                       class="pulsable inline-flex min-h-11 items-center rounded-xl border border-linea px-4 py-2.5 text-sm text-tinta hover:border-marca-500/50">
                                        {{ $requisito->enlaceEsPuntual()
                                            ? ajuste('guia_enlace_puntual')
                                            : ajuste('guia_enlace_portada') }}&nbsp;<x-publico.flecha direccion="externa" />
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
            <div class="revelar tarjeta-escena vidrio mt-8 rounded-[1.75rem] p-8 text-center" data-revelar>
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
            <div class="revelar tarjeta mt-10 p-12 text-center" data-revelar>
                <p class="font-display text-lg font-semibold">Todavía no hay guía publicada</p>
                <p class="mt-2 text-sm text-tenue">Estamos recopilando la información con las entidades.</p>
            </div>
        @endif
    </div>
</x-layouts.publico>
