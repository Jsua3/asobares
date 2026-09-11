@props(['proximosEventos', 'iniciativas', 'destacados'])

@php
    use Illuminate\Support\Facades\Storage;

    $postersVideo = $destacados
        ->filter(fn ($asociado) => filled($asociado->foto_portada))
        ->take(3)
        ->map(fn ($asociado) => Storage::disk('public')->url($asociado->foto_portada))
        ->values();

    $eventoPrincipal = $proximosEventos->first();
    $iniciativaPrincipal = $iniciativas->first();
@endphp

<section class="home-editorial-actualidad revelar" data-revelar aria-labelledby="actualidad">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="home-editorial-eyebrow">{{ ajuste('portada_eventos_titulo') }}</p>
            <h2 id="actualidad" class="home-editorial-titulo mt-2 text-balance">
                {{ ajuste('portada_actualidad_subtitulo', 'Eventos, iniciativas y oportunidades del gremio.') }}
            </h2>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-12 lg:gap-5">
            @if ($eventoPrincipal)
                <article class="home-editorial-actualidad__destacada lg:col-span-7">
                    <a href="{{ route('eventos.show', $eventoPrincipal) }}" class="group tarjeta-pulsable block h-full">
                        <div class="relative h-full min-h-[16rem] overflow-hidden rounded-xl sm:min-h-[18rem]">
                            @if ($eventoPrincipal->imagen)
                                <img src="{{ Storage::disk('public')->url($eventoPrincipal->imagen) }}"
                                     alt=""
                                     loading="lazy"
                                     decoding="async"
                                     width="800"
                                     height="520"
                                     class="imagen-viva home-editorial-actualidad__img h-full w-full object-cover">
                            @else
                                <div class="h-full w-full bg-[linear-gradient(135deg,#111111,#0d0d0d)]"></div>
                            @endif
                            <div class="home-editorial-actualidad__overlay absolute inset-x-0 bottom-0 p-5 sm:p-6">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/90 px-2.5 py-1 font-medium text-white">
                                        {{ $eventoPrincipal->tipo->getLabel() }}
                                    </span>
                                    <span class="text-white/75">{{ $eventoPrincipal->fecha_inicio->translatedFormat('d M Y') }}</span>
                                </div>
                                <h3 class="mt-3 font-display text-xl font-semibold text-white sm:text-2xl">{{ $eventoPrincipal->titulo }}</h3>
                                <p class="mt-2 line-clamp-2 text-sm text-white/75">
                                    {{ $eventoPrincipal->esGratuito() ? 'Entrada libre' : pesos($eventoPrincipal->precio) }}
                                    @if ($eventoPrincipal->lugar)
                                        · {{ Str::limit($eventoPrincipal->lugar, 48) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </a>
                </article>
            @endif

            <div @class([
                'flex flex-col gap-4',
                'lg:col-span-5' => $eventoPrincipal,
                'lg:col-span-12 lg:grid lg:grid-cols-3' => ! $eventoPrincipal,
            ])>
                @if ($iniciativaPrincipal)
                    <article class="home-editorial-actualidad__secundaria flex-1 rounded-xl p-5">
                        <p class="home-editorial-eyebrow text-acento">{{ ajuste('vision_nota') }}</p>
                        <h3 class="mt-2 font-display text-lg font-semibold">{{ $iniciativaPrincipal->nombre }}</h3>
                        <span @class([
                            'mt-3 inline-block w-fit rounded-md px-2 py-1 text-2xs font-semibold uppercase tracking-wider',
                            'bg-emerald-500/15 text-exito' => $iniciativaPrincipal->estado_iniciativa === \App\Enums\EstadoIniciativa::EnEjecucion,
                            'bg-amber-500/15 text-aviso' => $iniciativaPrincipal->estado_iniciativa === \App\Enums\EstadoIniciativa::Escalando,
                            'border border-linea-fuerte text-apagado' => $iniciativaPrincipal->estado_iniciativa === \App\Enums\EstadoIniciativa::Formulacion,
                        ])>{{ $iniciativaPrincipal->estado_iniciativa->getLabel() }}</span>
                        <p class="mt-3 line-clamp-2 text-sm text-tenue">{{ $iniciativaPrincipal->resumen }}</p>
                        <a href="{{ route('quienes-somos') }}#iniciativas"
                           class="home-editorial-enlace enlace-accion mt-4 inline-flex text-sm font-medium text-acento hover:text-acento-fuerte">
                            {{ ajuste('iniciativas_titulo') }}&nbsp;<x-publico.flecha />
                        </a>
                    </article>
                @elseif (! $eventoPrincipal)
                    <article class="home-editorial-actualidad__secundaria rounded-xl p-5">
                        <p class="home-editorial-eyebrow text-acento">{{ ajuste('vision_nota') }}</p>
                        <h3 class="mt-2 font-display text-lg font-semibold">{{ ajuste('iniciativas_titulo') }}</h3>
                        <p class="mt-2 text-sm text-tenue">{{ ajuste('iniciativas_intro') }}</p>
                        <a href="{{ route('quienes-somos') }}#iniciativas"
                           class="home-editorial-enlace enlace-accion mt-4 inline-flex text-sm font-medium text-acento hover:text-acento-fuerte">
                            Ver el detalle&nbsp;<x-publico.flecha />
                        </a>
                    </article>
                @endif

                <article class="home-editorial-actualidad__oportunidad flex-1 overflow-hidden rounded-xl">
                    <a href="{{ route('empleo.index') }}" class="group tarjeta-pulsable flex h-full flex-col justify-between p-5">
                        <div>
                            <p class="home-editorial-eyebrow">{{ ajuste('portada_empleo_titulo') }}</p>
                            <p class="mt-2 line-clamp-2 text-sm text-tenue">{{ ajuste('portada_empleo_texto') }}</p>
                        </div>
                        <span class="home-editorial-enlace enlace-accion mt-4 inline-flex text-sm font-medium text-acento group-hover:text-acento-fuerte">
                            Ver vacantes&nbsp;<x-publico.flecha />
                        </span>
                    </a>
                </article>

            </div>
        </div>

        <div class="home-editorial-actualidad__pie mt-6 flex flex-col gap-4 border-t border-linea/60 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl text-sm text-tenue">
                <p class="mb-3 text-xs leading-relaxed text-apagado">{{ ajuste('portada_guia_texto') }}</p>
                <span class="antetitulo text-acento">{{ ajuste('portada_videos_rotulo', 'ASOBARES en movimiento') }}</span>
                <span class="mt-1 block">{{ ajuste('portada_videos_titulo', 'Historias cortas para sentir el gremio.') }}</span>
                <span class="mt-1 block text-xs text-apagado">{{ ajuste('portada_videos_intro', 'Una banda audiovisual para mostrar recorridos, eventos, testimonios y momentos de la noche quindiana con un tono sobrio, local y cercano.') }}</span>
                <span class="mt-2 block text-xs text-apagado">
                    <span class="antetitulo">{{ ajuste('portada_videos_proxima_rotulo', 'Próxima pieza') }}</span>
                    {{ ajuste('portada_videos_proxima_texto', 'Clips de afiliados, activaciones y memoria del capítulo.') }}
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
                <a href="{{ route('guia.index') }}" class="home-editorial-enlace enlace-accion text-acento hover:text-acento-fuerte" title="{{ ajuste('portada_guia_texto') }}">
                    {{ ajuste('portada_guia_titulo') }}&nbsp;<x-publico.flecha />
                </a>
                <a href="{{ route('eventos.index') }}" class="home-editorial-enlace enlace-accion text-acento hover:text-acento-fuerte">
                    Ver agenda&nbsp;<x-publico.flecha />
                </a>
                <a href="{{ route('boletin.index') }}" class="home-editorial-enlace enlace-accion text-acento hover:text-acento-fuerte">
                    Boletín ASOBARES&nbsp;<x-publico.flecha />
                </a>
            </div>
        </div>
    </div>
</section>
