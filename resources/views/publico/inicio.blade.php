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
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-marca-500/30 bg-marca-500/10 px-3 py-1 text-xs font-medium text-marca-300">
                <span class="h-1.5 w-1.5 rounded-full bg-marca-500"></span>
                {{ $totalAsociados }} establecimientos afiliados en el Quindío
            </p>
            <p class="mb-5 max-w-2xl font-display text-base font-medium leading-snug text-noche-200 text-balance sm:text-lg">
                {{ ajuste('manifiesto_apertura') }}
            </p>
        </x-slot:encima>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('directorio.index') }}"
               class="rounded-xl bg-marca-500 px-6 py-3 text-center text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                {{ ajuste('hero_cta_directorio') }}
            </a>
            <a href="{{ route('afiliate') }}"
               class="rounded-xl border border-white/15 px-6 py-3 text-center text-sm font-semibold text-noche-50 transition-colors hover:border-marca-500/50 hover:bg-noche-900">
                {{ ajuste('hero_cta_afiliate') }}
            </a>
        </div>
    </x-publico.hero>

    {{-- Cifras del Observatorio --}}
    <section class="border-b border-white/[.09] bg-noche-900" aria-labelledby="cifras">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <h2 id="cifras" class="font-display text-xs font-semibold uppercase tracking-wider text-noche-400">
                La noche en cifras · Observatorio Económico
            </h2>
            <dl class="mt-6 grid grid-cols-2 gap-6 lg:grid-cols-4">
                @foreach ($cifras as $cifra)
                    <div>
                        <dt class="font-display text-2xl font-bold text-marca-400 sm:text-3xl">{{ $cifra['valor'] }}</dt>
                        <dd class="mt-1.5 text-xs leading-relaxed text-noche-300 sm:text-sm">{{ $cifra['texto'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Accesos directos a lo que la directiva priorizó --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-5 md:grid-cols-2">
            <a href="{{ route('guia.index') }}"
               class="tarjeta tarjeta-hover group flex flex-col justify-between p-7">
                <div>
                    <span class="inline-flex rounded-xl bg-marca-500/10 p-3 text-marca-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                        </svg>
                    </span>
                    <h2 class="mt-5 font-display text-xl font-semibold">Abre tu negocio</h2>
                    <p class="mt-2.5 text-sm leading-relaxed text-noche-300">
                        Los requisitos reales para abrir un establecimiento, municipio por municipio,
                        con checklist, costos y los formatos oficiales listos para descargar.
                    </p>
                </div>
                <span class="mt-6 text-sm font-medium text-marca-400 group-hover:text-marca-300">Ver la guía →</span>
            </a>

            <a href="{{ route('empleo.index') }}"
               class="tarjeta tarjeta-hover group flex flex-col justify-between p-7">
                <div>
                    <span class="inline-flex rounded-xl bg-marca-500/10 p-3 text-marca-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a9.75 9.75 0 0 1-6.396 0l-1.32-.377a2.25 2.25 0 0 1-1.632-2.163V14.15M3.75 9.75v.375c0 .621.504 1.125 1.125 1.125h.375m0 0h14.25m-14.25 0v6.75m14.25-6.75h.375c.621 0 1.125-.504 1.125-1.125V9.75m-16.5 0V8.625c0-.621.504-1.125 1.125-1.125h14.25c.621 0 1.125.504 1.125 1.125V9.75m-16.5 0h16.5M9 7.5V6a2.25 2.25 0 0 1 2.25-2.25h1.5A2.25 2.25 0 0 1 15 6v1.5"/>
                        </svg>
                    </span>
                    <h2 class="mt-5 font-display text-xl font-semibold">Bolsa de empleo</h2>
                    <p class="mt-2.5 text-sm leading-relaxed text-noche-300">
                        Bartenders, chefs, meseros y administradores para la vida nocturna del Quindío.
                        Publican solo los establecimientos asociados.
                    </p>
                </div>
                <span class="mt-6 text-sm font-medium text-marca-400 group-hover:text-marca-300">Ver vacantes →</span>
            </a>
        </div>
    </section>

    {{-- Asociados destacados --}}
    @if ($destacados->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8" aria-labelledby="destacados">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="destacados" class="font-display text-2xl font-bold sm:text-3xl">La noche del Quindío</h2>
                    <p class="mt-2 text-sm text-noche-300">Algunos de los establecimientos afiliados al gremio.</p>
                </div>
                <a href="{{ route('directorio.index') }}" class="text-sm font-medium text-marca-400 hover:text-marca-300">
                    Ver el directorio completo →
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
    <section class="border-y border-white/[.09] bg-noche-900" aria-labelledby="beneficios">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 id="beneficios" class="font-display text-2xl font-bold sm:text-3xl">Lo que gana tu establecimiento</h2>
            <p class="mt-2 max-w-2xl text-sm text-noche-300">
                Cinco beneficios concretos por estar afiliado al capítulo.
            </p>

            <div class="mt-9 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($beneficios as $beneficio)
                    <div class="rounded-2xl border border-white/[.09] bg-noche-950 p-6">
                        <span class="inline-flex rounded-lg bg-marca-500/10 p-2.5 text-marca-400">
                            <x-dynamic-component :component="$beneficio->icono" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 font-display text-base font-semibold">{{ $beneficio->titulo }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-noche-300">{{ $beneficio->descripcion }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Próximos eventos --}}
    @if ($proximosEventos->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" aria-labelledby="eventos">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="eventos" class="font-display text-2xl font-bold sm:text-3xl">Próximos eventos del gremio</h2>
                <a href="{{ route('eventos.index') }}" class="text-sm font-medium text-marca-400 hover:text-marca-300">
                    Ver todos →
                </a>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach ($proximosEventos as $evento)
                    <article class="tarjeta tarjeta-hover overflow-hidden">
                        <a href="{{ route('eventos.show', $evento) }}">
                            @if ($evento->imagen)
                                <img src="{{ Storage::disk('public')->url($evento->imagen) }}" alt=""
                                     loading="lazy" decoding="async" width="400" height="225"
                                     class="aspect-video w-full object-cover">
                            @endif
                            <div class="p-5">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-marca-300">
                                        {{ $evento->tipo->getLabel() }}
                                    </span>
                                    <span class="text-noche-400">{{ $evento->fecha_inicio->translatedFormat('d M Y') }}</span>
                                </div>
                                <h3 class="mt-3 font-display text-base font-semibold leading-snug">{{ $evento->titulo }}</h3>
                                <p class="mt-2 text-sm text-noche-300">
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

    {{-- Aliados --}}
    @if ($aliados->isNotEmpty())
        <section class="border-t border-white/[.09]" aria-labelledby="aliados">
            <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <h2 id="aliados" class="font-display text-xs font-semibold uppercase tracking-wider text-noche-400">
                    Aliados del capítulo
                </h2>
                <div class="mt-6 flex snap-x gap-4 overflow-x-auto pb-3">
                    @foreach ($aliados as $aliado)
                        <div class="tarjeta flex w-56 shrink-0 snap-start flex-col p-4">
                            @if ($aliado->logo)
                                <img src="{{ Storage::disk('public')->url($aliado->logo) }}" alt="{{ $aliado->nombre }}"
                                     loading="lazy" decoding="async" width="192" height="108"
                                     class="h-20 w-full rounded-lg object-cover">
                            @endif
                            <p class="mt-3 text-sm font-medium">{{ $aliado->nombre }}</p>
                            <p class="mt-1 line-clamp-2 text-xs text-noche-400">{{ $aliado->descripcion }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-noche-400">
                    El detalle de cada convenio es información privada de los afiliados.
                    <a href="{{ route('mi-cuenta.index') }}" class="text-marca-400 hover:text-marca-300">Inicia sesión para verlo →</a>
                </p>
            </div>
        </section>
    @endif

    {{-- Iniciativas en marcha --}}
    @if ($iniciativas->isNotEmpty())
        <section class="border-t border-white/[.09] bg-noche-900" aria-labelledby="iniciativas">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="antetitulo text-marca-400">{{ ajuste('vision_nota') }}</p>
                        <h2 id="iniciativas" class="mt-3 font-display text-2xl font-bold sm:text-3xl">
                            {{ ajuste('iniciativas_titulo') }}
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm text-noche-300">{{ ajuste('iniciativas_intro') }}</p>
                    </div>
                    <a href="{{ route('quienes-somos') }}#iniciativas"
                       class="text-sm font-medium text-marca-400 hover:text-marca-300">
                        Ver el detalle →
                    </a>
                </div>

                <ol class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ($iniciativas as $indice => $iniciativa)
                        <li class="flex flex-col rounded-2xl border border-white/[.09] bg-noche-950 p-5">
                            <span class="font-display text-2xl font-bold text-marca-500/40">
                                {{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="mt-2 font-display text-base font-bold leading-snug">{{ $iniciativa->nombre }}</h3>

                            <span @class([
                                'mt-3 inline-block w-fit rounded-md px-2 py-1 text-[.6rem] font-semibold uppercase tracking-wider',
                                'bg-emerald-500/15 text-emerald-300' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::EnEjecucion,
                                'bg-amber-500/15 text-amber-300' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Escalando,
                                'border border-white/15 text-noche-400' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Formulacion,
                            ])>{{ $iniciativa->estado_iniciativa->getLabel() }}</span>

                            <p class="mt-3 flex-1 text-xs leading-relaxed text-noche-300">{{ $iniciativa->resumen }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- Cierre del manifiesto --}}
    <section class="trama-puntos border-t border-white/[.09]" aria-labelledby="manifiesto">
        <div class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6">
            <h2 id="manifiesto" class="font-display text-2xl font-bold leading-tight text-balance sm:text-4xl">
                {{ ajuste('manifiesto_cierre_titulo') }}
            </h2>
            <p class="antetitulo mt-6 text-marca-400">{{ ajuste('manifiesto_cierre_firma') }}</p>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="resplandor-marca border-t border-white/[.09]">
        <div class="mx-auto max-w-4xl px-4 py-20 text-center sm:px-6">
            <h2 class="font-display text-2xl font-bold text-balance sm:text-4xl">{{ ajuste('cta_final_titulo') }}</h2>
            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-noche-200 sm:text-base text-pretty">
                {{ ajuste('cta_final_texto') }}
            </p>
            <a href="{{ route('afiliate') }}"
               class="mt-8 inline-block rounded-xl bg-marca-500 px-8 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                Quiero afiliarme
            </a>
        </div>
    </section>
</x-layouts.publico>
