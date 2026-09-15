<x-layouts.publico :titulo="ajuste('seo_guia_titulo', ajuste('guia_titulo').' — ASOBARES Quindío')"
                   :descripcion="ajuste('seo_guia_descripcion', 'Requisitos para abrir un bar, gastrobar o café en el Quindío: qué pide cada entidad y ante quién se tramita, municipio por municipio.')">

    @push('cabeza')
        @vite(['resources/css/guia-editorial.css'])
    @endpush

    @if (! ($seleccionado && $requisitos->isNotEmpty()))
        {{-- Un municipio cuya guía entera caducó (o que nunca la tuvo) deja
             esta URL respondiendo 200 con «Todavía no hay guía publicada»:
             sin esto seguiría siendo indexable aunque ya no salga del
             selector ni del sitemap. --}}
        @push('cabeza')
            <meta name="robots" content="noindex, follow">
        @endpush
    @endif

    {{-- Fotografía definitiva: `public/media/abre-tu-negocio/hero-abre-tu-negocio.png`. --}}
    @php
        $fotoGuiaEscena = collect([
            'media/abre-tu-negocio/hero-abre-tu-negocio.png',
            'img/guia/hero-abre-tu-negocio.png',
            'img/guia/hero-abre-tu-negocio.webp',
            'img/guia/hero-abre-tu-negocio.jpg',
        ])->first(fn (string $ruta): bool => is_file(public_path($ruta)));
    @endphp
    <div class="guia-editorial">
    <x-publico.hero :titulo="ajuste('guia_titulo')" :subtitulo="ajuste('guia_intro')" atmosfera>
        <x-slot:medio>
            <x-publico.hueco-foto :foto="ajuste('guia_foto', null)" />
        </x-slot:medio>
        <x-slot:escena>
            <div class="guia-editorial-escena" aria-hidden="true">
                <div class="guia-editorial-escena__campo">
                    @if ($fotoGuiaEscena)
                        <img src="{{ asset($fotoGuiaEscena) }}"
                             alt=""
                             width="1672"
                             height="941"
                             class="guia-editorial-escena__foto">
                    @else
                        <div class="guia-editorial-escena__placeholder"
                             data-guia-placeholder="geometria-04d"></div>
                    @endif
                </div>
                <div class="guia-editorial-escena__velo"></div>
                @if (file_exists(public_path('media/abre-tu-negocio/animacion-logo.mp4')))
                    <div class="guia-editorial-logo-vivo" x-data="guiaIdentidad">
                        <video x-ref="identidad"
                               muted
                               playsinline
                               loop
                               preload="auto"
                               width="1080"
                               height="1080">
                            <source src="{{ asset('media/abre-tu-negocio/animacion-logo.mp4') }}" type="video/mp4">
                        </video>
                    </div>
                @endif
            </div>
        </x-slot:escena>
    </x-publico.hero>

    <div class="guia-editorial__cuerpo mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">

        {{-- Selector de municipio --}}
        <section class="guia-editorial-selector" aria-labelledby="selector">
            <h2 id="selector" class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">
                Escoge tu municipio
            </h2>
            <div class="guia-editorial-municipios mt-5">
                @foreach ($municipios as $municipio)
                    <a href="{{ route('guia.index', ['municipio' => $municipio->slug]) }}"
                       class="guia-editorial-municipio pulsable"
                       @if ($seleccionado?->is($municipio)) aria-current="true" @endif>
                        {{ $municipio->nombre }}
                    </a>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-apagado">
                {{ ajuste('guia_selector_ayuda', 'Estamos levantando la guía municipio por municipio con la información que cada entidad entrega al gremio. Si falta el tuyo, escríbenos.') }}
            </p>
        </section>

        @if ($seleccionado && $requisitos->isNotEmpty())
            {{-- Resumen --}}
            <div class="guia-editorial-resumen">
                <div>
                    <p class="guia-editorial-resumen__etiqueta">Municipio</p>
                    <p class="guia-editorial-resumen__valor">{{ $seleccionado->nombre }}</p>
                </div>
                <div>
                    <p class="guia-editorial-resumen__etiqueta">Entidades a visitar</p>
                    <p class="guia-editorial-resumen__valor">{{ $requisitos->count() }}</p>
                </div>
                <div>
                    <p class="guia-editorial-resumen__etiqueta">Costo aproximado</p>
                    <p class="guia-editorial-resumen__valor guia-editorial-resumen__valor--acento">
                        {{ $costoTotal > 0 ? pesos($costoTotal) : 'Por confirmar' }}
                    </p>
                </div>
            </div>

            {{-- Requisitos por entidad --}}
            <div class="mt-8 space-y-4">
                @foreach ($requisitos as $indice => $requisito)
                    <details class="guia-editorial-requisito group" @if ($indice === 0) open @endif>
                        {{-- `fila-pulsable` y no `pulsable`: encoger el <summary> movería la
                             flecha de `group-open:rotate-180` y se leerían dos movimientos
                             peleados sobre el mismo gesto. --}}
                        <summary class="guia-editorial-requisito__cabecera fila-pulsable">
                            <span class="guia-editorial-requisito__indice">
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
                                        {{-- «Por confirmar» y no una afirmación de gratuidad: un
                                             `costo_aproximado` nulo no significa que el trámite
                                             sea gratis, significa que nadie ha averiguado cuánto
                                             vale, y el documento oficial del gremio no trae
                                             cifras. Decirle «sin costo» a quien está haciendo
                                             cuentas para abrir un bar sería darle un dato falso.
                                             La cabecera de la tarjeta dice lo mismo. --}}
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

                        <div class="guia-editorial-requisito__cuerpo">
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
                                    {{-- La etiqueta no promete más de lo que el enlace cumple.
                                         Con enlace puntual invita al trámite; con un dominio
                                         pelado dice lo que es, una puerta. Cuando un requisito
                                         recibe su URL exacta, la etiqueta cambia sola, sin tocar
                                         la vista. --}}
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
            <div class="guia-editorial-cta revelar tarjeta-escena vidrio mt-8 rounded-[1.75rem] p-8 text-center" data-revelar>
                <h2 class="font-display text-xl font-semibold">{{ ajuste('guia_cta_titulo', '¿Dudas con algún trámite?') }}</h2>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                    {{ ajuste('guia_cta_texto', 'La orientación jurídica es gratuita para los afiliados, pero si estás empezando y todavía no haces parte del gremio, escríbenos igual: para eso existe esta guía.') }}
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
            <div class="guia-editorial-vacio">
                <p class="font-display text-lg font-semibold">Todavía no hay guía publicada</p>
                <p class="mt-2 text-sm text-tenue">Estamos recopilando la información con las entidades.</p>
            </div>
        @endif
    </div>
    </div>
</x-layouts.publico>
