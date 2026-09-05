@php
    $cifras = [
        ['valor' => ajuste('cifra_empleo'), 'texto' => ajuste('cifra_empleo_detalle')],
        ['valor' => ajuste('cifra_ingreso'), 'texto' => ajuste('cifra_ingreso_detalle')],
        ['valor' => ajuste('cifra_informalidad'), 'texto' => ajuste('cifra_informalidad_detalle')],
        ['valor' => ajuste('cifra_jovenes'), 'texto' => ajuste('cifra_jovenes_detalle')],
    ];

    $fotosHero = $destacados
        ->filter(fn ($asociado) => filled($asociado->foto_portada))
        ->take(3)
        ->map(fn ($asociado) => Storage::disk('public')->url($asociado->foto_portada));

    $destacadoPrincipal = $destacados->first();
    $destacadosSecundarios = $destacados->skip(1);
    $eventoPrincipal = $proximosEventos->first();
    $eventosSecundarios = $proximosEventos->skip(1);
    $postersVideo = $fotosHero->values();
    $videosPortada = collect([
        [
            'titulo' => ajuste('portada_video_1_titulo', 'La noche se mueve'),
            'detalle' => ajuste('portada_video_1_detalle', 'Recorridos, eventos y voces del sector'),
            'src' => null,
            'poster' => $postersVideo->get(0),
        ],
        [
            'titulo' => ajuste('portada_video_2_titulo', 'Rutas del gremio'),
            'detalle' => ajuste('portada_video_2_detalle', 'Establecimientos, cocina, barra y cultura local'),
            'src' => null,
            'poster' => $postersVideo->get(1),
        ],
        [
            'titulo' => ajuste('portada_video_3_titulo', 'Agenda viva'),
            'detalle' => ajuste('portada_video_3_detalle', 'Encuentros, formación y noches memorables'),
            'src' => null,
            'poster' => $postersVideo->get(2),
        ],
    ]);
    $videoPrincipal = $videosPortada->first();

    /*
     * El video del hero viaja en el repositorio (`public/videos/`), no en el
     * bucket: Cloud despliega desde git y un archivo ignorado es un archivo
     * que en producción no existe. Su póster es su propio primer fotograma y
     * no una foto de asociado, porque en producción no hay fichas publicadas
     * --`$postersVideo` viene vacía-- y porque saltar de una foto ajena al
     * video se ve como un corte. Las fotos quedan de respaldo para cuando el
     * video no esté.
     */
    $videoInstitucional = [
        'titulo' => ajuste('hero_video_titulo', 'ASOBARES Capítulo Quindío'),
        'detalle' => ajuste('hero_video_detalle', 'Una mirada breve al gremio que mueve la noche, la cultura y el territorio.'),
        'src' => file_exists(public_path('videos/asobares-institucional.mp4'))
            ? asset('videos/asobares-institucional.mp4')
            : null,
        'poster' => file_exists(public_path('videos/asobares-institucional.jpg'))
            ? asset('videos/asobares-institucional.jpg')
            : $postersVideo->get(0),
    ];
@endphp

<x-layouts.publico :titulo="ajuste('sitio_nombre').' — '.ajuste('sitio_eslogan')"
                   :descripcion="ajuste('sitio_descripcion')">

    {{-- Hero --}}
    <x-publico.hero :titulo="ajuste('hero_titulo')" atmosfera portada>
        <x-slot:medio>
            <div class="hero-video-fondo">
                @if ($videoInstitucional['poster'])
                    <img src="{{ $videoInstitucional['poster'] }}"
                         alt=""
                         width="1600"
                         height="900"
                         class="imagen-viva absolute inset-0 h-full w-full object-cover">
                @else
                    <div class="hero-video-respaldo absolute inset-0"></div>
                @endif

                @if ($videoInstitucional['src'])
                    {{-- Sin `autoplay` y con `preload="none"` a propósito: quien pidió
                         menos movimiento se queda con el póster y ni siquiera descarga
                         el megabyte y medio. `videoHero` --en `app.js`, junto al resto
                         de `reduceMovimiento()`-- es quien lo arranca cuando el
                         movimiento está permitido, y solo entonces se funde encima.

                         El fondo a pantalla completa es de esta rama; el
                         comportamiento del video viene de f83c9ea, que arregló el hero
                         mudo en producción. Las dos cosas caben. --}}
                    <video class="imagen-viva video-hero-capa absolute inset-0 h-full w-full object-cover"
                           x-data="videoHero"
                           x-bind:class="listo ? 'video-hero-capa--visible' : ''"
                           x-on:error="listo = false"
                           @if ($videoInstitucional['poster']) poster="{{ $videoInstitucional['poster'] }}" @endif
                           muted
                           loop
                           playsinline
                           preload="none">
                        <source src="{{ $videoInstitucional['src'] }}" type="video/mp4">
                    </video>
                @endif
            </div>
        </x-slot:medio>

        <x-slot:encima>
            {{-- Con el directorio vacío la píldora decía «0 establecimientos
                 afiliados en el Quindío» encima del lema, que es la primera
                 línea que lee quien entra. Y no es un caso raro: el directorio
                 nace vacío a propósito --las fichas de asociados no tienen
                 autorización de publicación (R-02) y las inventadas se
                 retiraron--, así que el estado inicial del sitio en producción
                 es justo ese. Sin fichas publicadas no se presume nada. --}}
            @if ($totalAsociados > 0)
                {{-- Sobre el video oscuro: borde y fondo de blanco, no de marca
                     sobre tinta, que sobre negro no se leía. El blanco vive en
                     el portador `.etiqueta-clara` de app.css, no en utilidades
                     que no siguen al tema. El punto sigue rojo. --}}
                <p class="mb-4 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs font-medium etiqueta-clara">
                    <span class="h-1.5 w-1.5 rounded-full bg-marca-500"></span>
                    {{ $totalAsociados }} establecimientos afiliados en el Quindío
                </p>
            @endif
            <p class="mb-5 max-w-2xl font-display text-base font-medium leading-snug text-white/72 text-balance sm:text-lg">
                {{ ajuste('manifiesto_apertura') }}
            </p>

            {{-- `hero_frase_corta` estaba sembrada y exigida por
                 `PortadaEditableTest` sin que ninguna vista la pintara. Va de
                 antetítulo, justo encima del titular: es el sitio donde una
                 frase corta no compite con el `<h1>` ni con el manifiesto.
                 La intención original no está escrita en ningún sitio, así que
                 esta colocación queda a confirmar con la Persona 2.

                 Vacía no se pinta, y no lleva texto de respaldo: hasta que el
                 sembrador de contenido oficial corra en producción la clave no
                 existe, y un párrafo vacío con margen deja un hueco encima del
                 titular. Inventarle un valor por defecto sería peor: en
                 producción solo entra texto de documento oficial del gremio. --}}
            @if ($fraseCorta = ajuste('hero_frase_corta'))
                <p class="antetitulo mb-3 text-acento">{{ $fraseCorta }}</p>
            @endif
        </x-slot:encima>

        <p class="mt-5 max-w-xl text-base leading-relaxed text-white/74 sm:text-lg text-pretty">
            {{ ajuste('hero_resumen_corto', 'Representamos la vida nocturna del Quindío con criterio, cultura y territorio.') }}
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <x-publico.boton :href="route('directorio.index')">
                {{ ajuste('hero_cta_directorio') }}
            </x-publico.boton>
            <x-publico.boton variante="contorno-claro" :href="route('afiliate')">
                {{ ajuste('hero_cta_afiliate') }}
            </x-publico.boton>
        </div>

        {{-- El rótulo del video: `hero_video_rotulo`, `_titulo` y `_detalle` están
             sembrados con contenido oficial y `PortadaEditableTest` exige que se
             pinten. La tarjeta que los pintaba se fue con la portada a pantalla
             completa (Persona 2, 4 sep) y quedó anotado «hay que decidir dónde
             van»: van al pie del texto del hero, como pie de foto del video que
             corre detrás. Colocación a confirmar con la Persona 2. --}}
        <p class="mt-10 max-w-md border-l pl-4 text-sm leading-relaxed text-white/72 pie-de-video">
            <span class="antetitulo block text-white/60">{{ ajuste('hero_video_rotulo', 'Video institucional') }}</span>
            <span class="mt-1 block font-display font-semibold text-white">{{ $videoInstitucional['titulo'] }}</span>
            <span class="mt-0.5 block">{{ $videoInstitucional['detalle'] }}</span>
        </p>
    </x-publico.hero>

    {{-- Cifras del Observatorio --}}
    <section class="revelar border-b border-linea bg-superficie" data-revelar aria-labelledby="cifras">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 id="cifras" class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">
                {{ ajuste('portada_cifras_titulo') }}
            </h2>
            <dl class="mt-8 grid grid-cols-2 gap-x-8 gap-y-10 lg:grid-cols-4">
                @foreach ($cifras as $cifra)
                    <div>
                        <dt class="font-display text-3xl font-bold text-acento sm:text-4xl">{{ $cifra['valor'] }}</dt>
                        <dd class="mt-2 max-w-[16rem] text-sm text-tenue">{{ $cifra['texto'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- El gremio en cifras (D-25, Acta 05): la oficina las teclea en el
         panel cada quince días. Sin cifras no se pinta nada, igual que los
         destacados y los eventos: el sitio no presume lo que no tiene. --}}
    @if ($cifrasDelGremio->isNotEmpty())
        <section class="border-b border-linea" aria-labelledby="cifras-gremio">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
                    <h2 id="cifras-gremio" class="font-display text-xs font-semibold uppercase tracking-wider text-apagado">
                        {{ ajuste('portada_gremio_titulo') }}
                    </h2>
                    @if ($cifrasDelGremioActualizadas !== null)
                        <p class="text-xs text-apagado">
                            Actualizado el {{ $cifrasDelGremioActualizadas->translatedFormat('d \d\e F \d\e Y') }}
                        </p>
                    @endif
                </div>
                <dl class="mt-6 grid grid-cols-2 gap-6 lg:grid-cols-4">
                    @foreach ($cifrasDelGremio as $cifra)
                        <div>
                            <dt class="font-display text-2xl font-bold text-acento sm:text-3xl">{{ $cifra['valor'] }}</dt>
                            <dd class="mt-1.5 text-xs leading-relaxed text-tenue sm:text-sm">{{ $cifra['texto'] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
    @endif

    {{-- Accesos directos a lo que la directiva priorizó --}}
    <section class="revelar mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-revelar>
        <div class="grid gap-6 lg:grid-cols-12">
            <a href="{{ route('guia.index') }}"
               class="tarjeta-escena vidrio tarjeta-hover tarjeta-pulsable group flex flex-col justify-between p-8 sm:p-10 lg:col-span-7">
                <div>
                    <span class="inline-flex rounded-xl bg-marca-500/10 p-3 text-acento">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                        </svg>
                    </span>
                    <h2 class="mt-6 font-display text-2xl font-semibold sm:text-3xl">{{ ajuste('portada_guia_titulo') }}</h2>
                    <p class="mt-3 max-w-md text-sm text-tenue sm:text-base">
                        {{ ajuste('portada_guia_texto') }}
                    </p>
                </div>
                <span class="enlace-accion mt-8 text-sm font-medium text-acento group-hover:text-acento-fuerte">Ver la guía&nbsp;<x-publico.flecha /></span>
            </a>

            <a href="{{ route('empleo.index') }}"
               class="tarjeta tarjeta-hover tarjeta-pulsable group flex flex-col justify-between p-8 lg:col-span-5">
                <div>
                    <span class="inline-flex rounded-xl bg-marca-500/10 p-3 text-acento">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a9.75 9.75 0 0 1-6.396 0l-1.32-.377a2.25 2.25 0 0 1-1.632-2.163V14.15M3.75 9.75v.375c0 .621.504 1.125 1.125 1.125h.375m0 0h14.25m-14.25 0v6.75m14.25-6.75h.375c.621 0 1.125-.504 1.125-1.125V9.75m-16.5 0V8.625c0-.621.504-1.125 1.125-1.125h14.25c.621 0 1.125.504 1.125 1.125V9.75m-16.5 0h16.5M9 7.5V6a2.25 2.25 0 0 1 2.25-2.25h1.5A2.25 2.25 0 0 1 15 6v1.5"/>
                        </svg>
                    </span>
                    <h2 class="mt-6 font-display text-xl font-semibold sm:text-2xl">{{ ajuste('portada_empleo_titulo') }}</h2>
                    <p class="mt-3 text-sm text-tenue">
                        {{ ajuste('portada_empleo_texto') }}
                    </p>
                </div>
                <span class="enlace-accion mt-8 text-sm font-medium text-acento group-hover:text-acento-fuerte">Ver vacantes&nbsp;<x-publico.flecha /></span>
            </a>
        </div>
    </section>

    {{-- Franja audiovisual: lista para enlazar los videos reales sin tocar backend. --}}
    <section class="revelar luz-ambiente border-y border-linea bg-superficie" data-revelar aria-labelledby="videos-portada">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-20 sm:px-6 lg:grid-cols-12 lg:px-8">
            <div class="flex flex-col justify-center lg:col-span-4">
                <p class="antetitulo text-acento">{{ ajuste('portada_videos_rotulo', 'ASOBARES en movimiento') }}</p>
                <h2 id="videos-portada" class="mt-4 font-display text-3xl font-bold text-balance sm:text-4xl">
                    {{ ajuste('portada_videos_titulo', 'Historias cortas para sentir el gremio.') }}
                </h2>
                <p class="mt-4 text-sm text-tenue sm:text-base text-pretty">
                    {{ ajuste('portada_videos_intro', 'Una banda audiovisual para mostrar recorridos, eventos, testimonios y momentos de la noche quindiana con un tono sobrio, local y cercano.') }}
                </p>
                <a href="{{ route('eventos.index') }}"
                   class="enlace-accion mt-8 w-fit text-sm font-medium text-acento hover:text-acento-fuerte">
                    {{ ajuste('portada_videos_cta', 'Ver agenda del gremio') }}&nbsp;<x-publico.flecha />
                </a>
            </div>

            <div class="lg:col-span-8">
                <div class="video-marquesina tarjeta-escena vidrio group overflow-hidden rounded-[1.75rem]"
                     x-data="escena"
                     x-on:mousemove="seguir($event)"
                     x-on:mouseleave="salir()"
                     x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`">
                    <div class="grid min-h-[26rem] lg:grid-cols-[minmax(0,1fr)_17rem]">
                        <div class="relative overflow-hidden">
                            @if ($videoPrincipal['src'])
                                <video src="{{ $videoPrincipal['src'] }}"
                                       poster="{{ $videoPrincipal['poster'] }}"
                                       class="imagen-viva h-full min-h-[20rem] w-full object-cover"
                                       controls
                                       playsinline
                                       preload="metadata"></video>
                            @elseif ($videoPrincipal['poster'])
                                <img src="{{ $videoPrincipal['poster'] }}"
                                     alt=""
                                     loading="lazy"
                                     decoding="async"
                                     width="960"
                                     height="640"
                                     class="imagen-viva h-full min-h-[20rem] w-full object-cover">
                            @else
                                <div class="h-full min-h-[20rem] bg-[radial-gradient(circle_at_72%_18%,rgb(238_65_55_/_0.24),transparent_34%),linear-gradient(135deg,var(--asb-superficie-alta),var(--asb-fondo))]"></div>
                            @endif

                            <div class="video-velo absolute inset-0"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6 text-white sm:p-8">
                                <span class="video-play inline-flex h-12 w-12 items-center justify-center rounded-full">
                                    <svg class="ml-0.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l11.22-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z"/>
                                    </svg>
                                </span>
                                <h3 class="mt-5 font-display text-2xl font-bold sm:text-3xl">{{ $videoPrincipal['titulo'] }}</h3>
                                <p class="mt-2 max-w-md text-sm text-white/80">{{ $videoPrincipal['detalle'] }}</p>
                            </div>
                        </div>

                        <div class="video-panel p-4 text-white">
                            <div class="flex h-full flex-col gap-3">
                                @foreach ($videosPortada->skip(1) as $video)
                                    <article class="video-mini group/item grid grid-cols-[5.75rem_1fr] gap-3 rounded-xl p-2">
                                        <div class="relative overflow-hidden rounded-lg">
                                            @if ($video['poster'])
                                                <img src="{{ $video['poster'] }}"
                                                     alt=""
                                                     loading="lazy"
                                                     decoding="async"
                                                     width="184"
                                                     height="128"
                                                     class="imagen-viva aspect-[4/3] w-full object-cover">
                                            @else
                                                <div class="video-poster-vacio aspect-[4/3]"></div>
                                            @endif
                                            <span class="absolute inset-0 flex items-center justify-center text-white/90">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                    <path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l11.22-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 self-center">
                                            <h3 class="font-display text-sm font-semibold">{{ $video['titulo'] }}</h3>
                                            <p class="mt-1 line-clamp-2 text-xs text-white/60">{{ $video['detalle'] }}</p>
                                        </div>
                                    </article>
                                @endforeach

                                <div class="video-mini mt-auto rounded-xl p-4">
                                    <p class="antetitulo text-white/50">{{ ajuste('portada_videos_proxima_rotulo', 'Próxima pieza') }}</p>
                                    <p class="mt-2 text-sm font-medium text-white">{{ ajuste('portada_videos_proxima_texto', 'Clips de afiliados, activaciones y memoria del capítulo.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Asociados destacados --}}
    @if ($destacados->isNotEmpty())
        <section class="revelar mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8" data-revelar aria-labelledby="destacados">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="destacados" class="font-display text-2xl font-bold sm:text-3xl">{{ ajuste('portada_destacados_titulo') }}</h2>
                    <p class="mt-2 max-w-xl text-sm text-tenue">{{ ajuste('portada_destacados_texto') }}</p>
                </div>
                <a href="{{ route('directorio.index') }}" class="enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                    Ver el directorio completo&nbsp;<x-publico.flecha />
                </a>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <x-publico.tarjeta-asociado :asociado="$destacadoPrincipal" variante="editorial" />
                </div>
                @if ($destacadosSecundarios->isNotEmpty())
                    <div class="flex flex-col gap-5 lg:col-span-5">
                        @foreach ($destacadosSecundarios->take(2) as $asociado)
                            <x-publico.tarjeta-asociado :asociado="$asociado" variante="horizontal" />
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($destacadosSecundarios->count() > 2)
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($destacadosSecundarios->skip(2) as $asociado)
                        <x-publico.tarjeta-asociado :asociado="$asociado" />
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Beneficios --}}
    <section class="revelar border-y border-linea bg-superficie" data-revelar aria-labelledby="beneficios">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <h2 id="beneficios" class="font-display text-2xl font-bold sm:text-3xl">{{ ajuste('portada_beneficios_titulo') }}</h2>
            {{-- OBS3-01: el directivo pidió «ponerlo principal» (R22 02:48), así que
                 la entradilla deja de ser gris pequeña y pasa a leerse de verdad. --}}
            <p class="mt-4 max-w-2xl text-base text-suave">
                {{ ajuste('portada_beneficios_intro') }}
            </p>

            <ol class="mt-12 divide-y divide-linea border-y border-linea">
                @foreach ($beneficios as $indice => $beneficio)
                    <li @class([
                        'grid gap-5 py-8 sm:grid-cols-[auto_1fr] sm:items-start sm:gap-8',
                        'lg:grid-cols-[auto_1fr_2fr] lg:py-10' => $indice === 0,
                    ])>
                        <span aria-hidden="true" class="font-display text-3xl font-bold text-marca-500/35">
                            {{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div>
                            <span class="inline-flex rounded-lg bg-marca-500/10 p-2.5 text-acento">
                                <x-dynamic-component :component="$beneficio->icono" class="h-5 w-5" />
                            </span>
                            <h3 class="mt-4 font-display text-lg font-semibold">{{ $beneficio->titulo }}</h3>
                        </div>
                        <p class="text-sm text-tenue sm:col-span-2 lg:col-span-1 lg:pt-1">{{ $beneficio->descripcion }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Próximos eventos --}}
    @if ($proximosEventos->isNotEmpty())
        <section class="revelar mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" data-revelar aria-labelledby="eventos">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="eventos" class="font-display text-2xl font-bold sm:text-3xl">{{ ajuste('portada_eventos_titulo') }}</h2>
                <a href="{{ route('eventos.index') }}" class="enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                    Ver todos&nbsp;<x-publico.flecha />
                </a>
            </div>

            <div class="mt-10 grid gap-6 lg:grid-cols-12">
                <article class="tarjeta tarjeta-hover tarjeta-pulsable overflow-hidden lg:col-span-7">
                    <a href="{{ route('eventos.show', $eventoPrincipal) }}" class="block sm:flex sm:min-h-full">
                        @if ($eventoPrincipal->imagen)
                            <div class="relative sm:w-[46%] sm:shrink-0">
                                <img src="{{ Storage::disk('public')->url($eventoPrincipal->imagen) }}" alt=""
                                     loading="lazy" decoding="async" width="720" height="480"
                                     class="imagen-viva aspect-[16/10] h-full w-full object-cover sm:aspect-auto sm:min-h-56">
                            </div>
                        @endif
                        <div class="flex flex-1 flex-col justify-center p-6 sm:p-8">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                    {{ $eventoPrincipal->tipo->getLabel() }}
                                </span>
                                <span class="text-apagado">{{ $eventoPrincipal->fecha_inicio->translatedFormat('d M Y') }}</span>
                            </div>
                            <h3 class="mt-4 font-display text-xl font-semibold sm:text-2xl">{{ $eventoPrincipal->titulo }}</h3>
                            <p class="mt-3 text-sm text-tenue">
                                {{ $eventoPrincipal->esGratuito() ? 'Entrada libre' : pesos($eventoPrincipal->precio) }}
                                @if ($eventoPrincipal->lugar)
                                    · {{ Str::limit($eventoPrincipal->lugar, 48) }}
                                @endif
                            </p>
                            <span class="enlace-accion mt-6 text-sm font-medium text-acento">Ver detalle&nbsp;<x-publico.flecha /></span>
                        </div>
                    </a>
                </article>

                @if ($eventosSecundarios->isNotEmpty())
                    <div class="flex flex-col gap-5 lg:col-span-5">
                        @foreach ($eventosSecundarios as $evento)
                            <article class="tarjeta tarjeta-hover tarjeta-pulsable overflow-hidden">
                                <a href="{{ route('eventos.show', $evento) }}" class="flex gap-4 p-4 sm:p-5">
                                    @if ($evento->imagen)
                                        <img src="{{ Storage::disk('public')->url($evento->imagen) }}" alt=""
                                             loading="lazy" decoding="async" width="160" height="120"
                                             class="imagen-viva h-24 w-28 shrink-0 rounded-xl object-cover">
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                                {{ $evento->tipo->getLabel() }}
                                            </span>
                                            <span class="text-apagado">{{ $evento->fecha_inicio->translatedFormat('d M Y') }}</span>
                                        </div>
                                        <h3 class="mt-2 font-display text-base font-semibold">{{ $evento->titulo }}</h3>
                                        <p class="mt-1 text-sm text-tenue">
                                            {{ $evento->esGratuito() ? 'Entrada libre' : pesos($evento->precio) }}
                                            @if ($evento->lugar)
                                                · {{ Str::limit($evento->lugar, 32) }}
                                            @endif
                                        </p>
                                    </div>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Aliados, en dos niveles (OBS3-04) --}}
    @if ($aliadosInstitucionales->isNotEmpty() || $aliadosComerciales->isNotEmpty())
        <section class="revelar border-t border-linea" data-revelar aria-labelledby="aliados">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
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
                    <p class="antetitulo mt-6 text-acento">{{ ajuste('portada_aliados_institucionales') }}</p>
                    <ul class="mt-5 grid grid-cols-2 gap-6 sm:grid-cols-4">
                        @foreach ($aliadosInstitucionales as $aliado)
                            <li class="flex flex-col items-center gap-3 p-4 text-center">
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
                    <p class="antetitulo mt-10 text-tenue">{{ ajuste('portada_aliados_comerciales') }}</p>
                    <div class="mt-5 flex snap-x gap-4 overflow-x-auto pb-3">
                        @foreach ($aliadosComerciales as $aliado)
                            <div class="vidrio flex w-56 shrink-0 snap-start flex-col overflow-hidden p-4">
                                @if ($aliado->logo)
                                    <img src="{{ Storage::disk('public')->url($aliado->logo) }}" alt="{{ $aliado->nombre }}"
                                         loading="lazy" decoding="async" width="192" height="108"
                                         class="imagen-viva h-20 w-full rounded-lg object-cover">
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
        <section class="revelar border-t border-linea bg-superficie" data-revelar aria-labelledby="iniciativas">
            <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
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

                <ol class="mt-12 divide-y divide-linea border-y border-linea lg:grid lg:grid-cols-5 lg:divide-x lg:divide-y-0">
                    @foreach ($iniciativas as $indice => $iniciativa)
                        <li class="flex flex-col py-6 lg:px-5 lg:py-8 lg:first:pl-0 lg:last:pr-0">
                            {{-- El orden ya lo da el <ol>; este número solo lo repite en pantalla. --}}
                            <span aria-hidden="true" class="font-display text-2xl font-bold text-marca-500/40">
                                {{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="mt-3 font-display text-base font-bold">{{ $iniciativa->nombre }}</h3>

                            <span @class([
                                'mt-3 inline-block w-fit rounded-md px-2 py-1 text-2xs font-semibold uppercase tracking-wider',
                                'bg-emerald-500/15 text-exito' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::EnEjecucion,
                                'bg-amber-500/15 text-aviso' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Escalando,
                                'border border-linea-fuerte text-apagado' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Formulacion,
                            ])>{{ $iniciativa->estado_iniciativa->getLabel() }}</span>

                            <p class="mt-3 flex-1 text-xs text-tenue">{{ $iniciativa->resumen }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- Cierre del manifiesto --}}
    <section class="revelar trama-puntos border-t border-linea" data-revelar aria-labelledby="manifiesto">
        <div class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6">
            <h2 id="manifiesto" class="font-display text-3xl font-bold text-balance sm:text-5xl">
                {{ ajuste('manifiesto_cierre_titulo') }}
            </h2>
            <p class="antetitulo mt-8 text-acento">{{ ajuste('manifiesto_cierre_firma') }}</p>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="luz-ambiente resplandor-marca border-t border-linea">
        <div class="mx-auto max-w-3xl px-4 py-24 text-center sm:px-6">
            <div class="vidrio rounded-[1.75rem] px-6 py-12 sm:px-12 sm:py-16">
                <h2 class="font-display text-2xl font-bold text-balance sm:text-4xl">{{ ajuste('cta_final_titulo') }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-sm text-suave sm:text-base text-pretty">
                    {{ ajuste('cta_final_texto') }}
                </p>
                <x-publico.boton :href="route('afiliate')" class="mt-8">
                    Quiero afiliarme
                </x-publico.boton>
            </div>
        </div>
    </section>
</x-layouts.publico>
