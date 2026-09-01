@php
    $cifras = [
        ['valor' => ajuste('cifra_empleo'), 'texto' => ajuste('cifra_empleo_detalle')],
        ['valor' => ajuste('cifra_ingreso'), 'texto' => ajuste('cifra_ingreso_detalle')],
        ['valor' => ajuste('cifra_informalidad'), 'texto' => ajuste('cifra_informalidad_detalle')],
        ['valor' => ajuste('cifra_jovenes'), 'texto' => ajuste('cifra_jovenes_detalle')],
    ];
@endphp

<x-layouts.publico :titulo="ajuste('sitio_nombre').' — '.ajuste('sitio_eslogan')"
                   :descripcion="ajuste('sitio_descripcion')">

    {{-- Hero --}}
    <x-publico.hero :titulo="ajuste('hero_titulo')" :subtitulo="ajuste('hero_subtitulo')">
        <x-slot:encima>
            {{-- Con el directorio vacío la píldora decía «0 establecimientos
                 afiliados en el Quindío» encima del lema, que es la primera
                 línea que lee quien entra. Y no es un caso raro: el directorio
                 nace vacío a propósito --las fichas de asociados no tienen
                 autorización de publicación (R-02) y las inventadas se
                 retiraron--, así que el estado inicial del sitio en producción
                 es justo ese. Sin fichas publicadas no se presume nada. --}}
            @if ($totalAsociados > 0)
                <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-marca-500/30 bg-marca-500/10 px-3 py-1 text-xs font-medium text-acento-fuerte">
                    <span class="h-1.5 w-1.5 rounded-full bg-marca-500"></span>
                    {{ $totalAsociados }} establecimientos afiliados en el Quindío
                </p>
            @endif
            <p class="mb-5 max-w-2xl font-display text-base font-medium leading-snug text-suave text-balance sm:text-lg">
                {{ ajuste('manifiesto_apertura') }}
            </p>
        </x-slot:encima>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <x-publico.boton :href="route('directorio.index')">
                {{ ajuste('hero_cta_directorio') }}
            </x-publico.boton>
            <x-publico.boton variante="contorno" :href="route('afiliate')">
                {{ ajuste('hero_cta_afiliate') }}
            </x-publico.boton>
        </div>
    </x-publico.hero>

    {{-- Cifras del Observatorio --}}
    <section class="border-b border-linea bg-superficie" aria-labelledby="cifras">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h2 id="cifras" class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">
                {{ ajuste('portada_cifras_titulo') }}
            </h2>
            <dl class="mt-6 grid grid-cols-2 gap-6 lg:grid-cols-4">
                @foreach ($cifras as $cifra)
                    <div>
                        <dt class="font-display text-2xl font-bold text-acento sm:text-3xl">{{ $cifra['valor'] }}</dt>
                        <dd class="mt-1.5 text-xs leading-relaxed text-tenue sm:text-sm">{{ $cifra['texto'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Accesos directos a lo que la directiva priorizó --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-5 md:grid-cols-2">
            <a href="{{ route('guia.index') }}"
               class="tarjeta tarjeta-hover tarjeta-pulsable group flex flex-col justify-between p-7">
                <div>
                    <span class="inline-flex rounded-xl bg-marca-500/10 p-3 text-acento">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                        </svg>
                    </span>
                    <h2 class="mt-5 font-display text-xl font-semibold">{{ ajuste('portada_guia_titulo') }}</h2>
                    <p class="mt-2.5 text-sm leading-relaxed text-tenue">
                        {{ ajuste('portada_guia_texto') }}
                    </p>
                </div>
                <span class="enlace-accion mt-6 text-sm font-medium text-acento group-hover:text-acento-fuerte">Ver la guía&nbsp;<x-publico.flecha /></span>
            </a>

            <a href="{{ route('empleo.index') }}"
               class="tarjeta tarjeta-hover tarjeta-pulsable group flex flex-col justify-between p-7">
                <div>
                    <span class="inline-flex rounded-xl bg-marca-500/10 p-3 text-acento">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a9.75 9.75 0 0 1-6.396 0l-1.32-.377a2.25 2.25 0 0 1-1.632-2.163V14.15M3.75 9.75v.375c0 .621.504 1.125 1.125 1.125h.375m0 0h14.25m-14.25 0v6.75m14.25-6.75h.375c.621 0 1.125-.504 1.125-1.125V9.75m-16.5 0V8.625c0-.621.504-1.125 1.125-1.125h14.25c.621 0 1.125.504 1.125 1.125V9.75m-16.5 0h16.5M9 7.5V6a2.25 2.25 0 0 1 2.25-2.25h1.5A2.25 2.25 0 0 1 15 6v1.5"/>
                        </svg>
                    </span>
                    <h2 class="mt-5 font-display text-xl font-semibold">{{ ajuste('portada_empleo_titulo') }}</h2>
                    <p class="mt-2.5 text-sm leading-relaxed text-tenue">
                        {{ ajuste('portada_empleo_texto') }}
                    </p>
                </div>
                <span class="enlace-accion mt-6 text-sm font-medium text-acento group-hover:text-acento-fuerte">Ver vacantes&nbsp;<x-publico.flecha /></span>
            </a>
        </div>
    </section>

    {{-- Asociados destacados --}}
    @if ($destacados->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8" aria-labelledby="destacados">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="destacados" class="font-display text-2xl font-bold sm:text-3xl">{{ ajuste('portada_destacados_titulo') }}</h2>
                    <p class="mt-2 text-sm text-tenue">{{ ajuste('portada_destacados_texto') }}</p>
                </div>
                <a href="{{ route('directorio.index') }}" class="enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                    Ver el directorio completo&nbsp;<x-publico.flecha />
                </a>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($destacados as $asociado)
                    <x-publico.tarjeta-asociado :asociado="$asociado" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Beneficios --}}
    <section class="border-y border-linea bg-superficie" aria-labelledby="beneficios">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 id="beneficios" class="font-display text-2xl font-bold sm:text-3xl">{{ ajuste('portada_beneficios_titulo') }}</h2>
            {{-- OBS3-01: el directivo pidió «ponerlo principal» (R22 02:48), así que
                 la entradilla deja de ser gris pequeña y pasa a leerse de verdad. --}}
            <p class="mt-3 max-w-2xl text-base text-suave">
                {{ ajuste('portada_beneficios_intro') }}
            </p>

            <div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($beneficios as $beneficio)
                    <div class="rounded-2xl border border-linea bg-fondo p-6">
                        <span class="inline-flex rounded-lg bg-marca-500/10 p-2.5 text-acento">
                            <x-dynamic-component :component="$beneficio->icono" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 font-display text-base font-semibold">{{ $beneficio->titulo }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-tenue">{{ $beneficio->descripcion }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Próximos eventos --}}
    @if ($proximosEventos->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="eventos">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="eventos" class="font-display text-2xl font-bold sm:text-3xl">{{ ajuste('portada_eventos_titulo') }}</h2>
                <a href="{{ route('eventos.index') }}" class="enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                    Ver todos&nbsp;<x-publico.flecha />
                </a>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($proximosEventos as $evento)
                    <article class="tarjeta tarjeta-hover tarjeta-pulsable overflow-hidden">
                        <a href="{{ route('eventos.show', $evento) }}">
                            @if ($evento->imagen)
                                <img src="{{ Storage::disk('public')->url($evento->imagen) }}" alt=""
                                     loading="lazy" decoding="async" width="400" height="225"
                                     class="aspect-video w-full object-cover">
                            @endif
                            <div class="p-5">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                        {{ $evento->tipo->getLabel() }}
                                    </span>
                                    <span class="text-apagado">{{ $evento->fecha_inicio->translatedFormat('d M Y') }}</span>
                                </div>
                                <h3 class="mt-3 font-display text-base font-semibold leading-snug">{{ $evento->titulo }}</h3>
                                <p class="mt-2 text-sm text-tenue">
                                    {{ $evento->esGratuito() ? 'Entrada libre' : pesos($evento->precio) }}
                                    @if ($evento->lugar)
                                        · {{ Str::limit($evento->lugar, 40) }}
                                    @endif
                                </p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Aliados, en dos niveles (OBS3-04) --}}
    @if ($aliadosInstitucionales->isNotEmpty() || $aliadosComerciales->isNotEmpty())
        <section class="border-t border-linea" aria-labelledby="aliados">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <h2 id="aliados" class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">
                    {{ ajuste('portada_aliados_titulo') }}
                </h2>

                {{--
                    Banda de arriba: las instituciones que respaldan al gremio.
                    El directivo las quiso aparte y por encima («R21 02:19»), y
                    el §27.5 lo fija como regla de contenido, no como estética.

                    El tratamiento distinto es de verdad distinto: rejilla en
                    vez de carrusel --se ven las cuatro a la vez, no hay que
                    arrastrar-- y logo contenido en vez de recortado, porque un
                    escudo institucional no se recorta.
                --}}
                @if ($aliadosInstitucionales->isNotEmpty())
                    <p class="antetitulo mt-5 text-acento">{{ ajuste('portada_aliados_institucionales') }}</p>
                    <ul class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach ($aliadosInstitucionales as $aliado)
                            <li class="tarjeta flex flex-col items-center gap-3 p-5 text-center">
                                @if ($aliado->logo)
                                    <img src="{{ Storage::disk('public')->url($aliado->logo) }}" alt="{{ $aliado->nombre }}"
                                         loading="lazy" decoding="async" width="192" height="108"
                                         class="h-14 w-full object-contain">
                                @endif
                                <p class="text-sm font-semibold text-balance">{{ $aliado->nombre }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- Banda de abajo: las marcas con convenio, como estaban. --}}
                @if ($aliadosComerciales->isNotEmpty())
                    <p class="antetitulo mt-9 text-tenue">{{ ajuste('portada_aliados_comerciales') }}</p>
                    <div class="mt-4 flex snap-x gap-4 overflow-x-auto pb-3">
                        @foreach ($aliadosComerciales as $aliado)
                            <div class="tarjeta flex w-56 shrink-0 snap-start flex-col p-4">
                                @if ($aliado->logo)
                                    <img src="{{ Storage::disk('public')->url($aliado->logo) }}" alt="{{ $aliado->nombre }}"
                                         loading="lazy" decoding="async" width="192" height="108"
                                         class="h-20 w-full rounded-lg object-cover">
                                @endif
                                <p class="mt-3 text-sm font-medium">{{ $aliado->nombre }}</p>
                                <p class="mt-1 line-clamp-2 text-xs text-apagado">{{ $aliado->descripcion }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
                <p class="mt-4 text-xs text-apagado">
                    El detalle de cada convenio es información privada de los afiliados.
                    {{-- `whitespace-nowrap` porque el espacio duro NO basta: Chromium parte la línea
                         delante de una caja atómica aunque la preceda un espacio de no separación
                         (medido; con el carácter de antes sí bastaba). Sin esto la flecha cae sola
                         al renglón siguiente entre 328 y 336 px y otra vez cerca de 600. --}}
                    <a href="{{ route('mi-cuenta.index') }}" class="enlace-accion whitespace-nowrap text-acento hover:text-acento-fuerte">Inicia sesión para verlo&nbsp;<x-publico.flecha /></a>
                </p>
            </div>
        </section>
    @endif

    {{-- Iniciativas en marcha --}}
    @if ($iniciativas->isNotEmpty())
        <section class="border-t border-linea bg-superficie" aria-labelledby="iniciativas">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="antetitulo text-acento">{{ ajuste('vision_nota') }}</p>
                        <h2 id="iniciativas" class="mt-3 font-display text-2xl font-bold sm:text-3xl">
                            {{ ajuste('iniciativas_titulo') }}
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm text-tenue">{{ ajuste('iniciativas_intro') }}</p>
                    </div>
                    <a href="{{ route('quienes-somos') }}#iniciativas"
                       class="enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                        Ver el detalle&nbsp;<x-publico.flecha />
                    </a>
                </div>

                <ol class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ($iniciativas as $indice => $iniciativa)
                        <li class="flex flex-col rounded-2xl border border-linea bg-fondo p-5">
                            {{-- El orden ya lo da el <ol>; este número solo lo repite en pantalla. --}}
                            <span aria-hidden="true" class="font-display text-2xl font-bold text-marca-500/40">
                                {{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="mt-2 font-display text-base font-bold leading-snug">{{ $iniciativa->nombre }}</h3>

                            <span @class([
                                'mt-3 inline-block w-fit rounded-md px-2 py-1 text-[.6rem] font-semibold uppercase tracking-wider',
                                'bg-emerald-500/15 text-exito' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::EnEjecucion,
                                'bg-amber-500/15 text-aviso' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Escalando,
                                'border border-linea-fuerte text-apagado' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Formulacion,
                            ])>{{ $iniciativa->estado_iniciativa->getLabel() }}</span>

                            <p class="mt-3 flex-1 text-xs leading-relaxed text-tenue">{{ $iniciativa->resumen }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- Cierre del manifiesto --}}
    <section class="trama-puntos border-t border-linea" aria-labelledby="manifiesto">
        <div class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6">
            <h2 id="manifiesto" class="font-display text-2xl font-bold leading-tight text-balance sm:text-4xl">
                {{ ajuste('manifiesto_cierre_titulo') }}
            </h2>
            <p class="antetitulo mt-6 text-acento">{{ ajuste('manifiesto_cierre_firma') }}</p>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="resplandor-marca border-t border-linea">
        <div class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6">
            <h2 class="font-display text-2xl font-bold text-balance sm:text-4xl">{{ ajuste('cta_final_titulo') }}</h2>
            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-suave sm:text-base text-pretty">
                {{ ajuste('cta_final_texto') }}
            </p>
            <x-publico.boton :href="route('afiliate')" class="mt-8">
                Quiero afiliarme
            </x-publico.boton>
        </div>
    </section>
</x-layouts.publico>
