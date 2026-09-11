@props(['proximosEventos', 'iniciativas', 'destacados'])

@php
    use Illuminate\Support\Facades\Storage;

    $eventoPrincipal = $proximosEventos->first();
    $iniciativaPrincipal = $iniciativas->first();
    $fotoEventoEditorial = asset(config('home_banco.evento', 'img/home/evento-destacado.png'));
@endphp

<section class="home-editorial-actualidad revelar" data-revelar aria-labelledby="actualidad">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
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
                        <div class="relative h-full min-h-[17rem] overflow-hidden rounded-xl sm:min-h-[20rem] lg:min-h-[22rem]">
                            @if ($eventoPrincipal->imagen)
                                <img src="{{ Storage::disk('public')->url($eventoPrincipal->imagen) }}"
                                     alt=""
                                     loading="lazy"
                                     decoding="async"
                                     width="800"
                                     height="520"
                                     class="home-editorial-actualidad__img h-full w-full object-cover">
                            @else
                                <img src="{{ $fotoEventoEditorial }}"
                                     alt=""
                                     loading="lazy"
                                     decoding="async"
                                     width="800"
                                     height="520"
                                     class="home-editorial-actualidad__img h-full w-full object-cover">
                            @endif
                            <div class="home-editorial-actualidad__velo" aria-hidden="true"></div>
                            <div class="home-editorial-actualidad__overlay absolute inset-x-0 bottom-0 p-5 sm:p-6">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/90 px-2.5 py-1 font-medium text-white">
                                        {{ $eventoPrincipal->tipo->getLabel() }}
                                    </span>
                                    <span class="text-white/80">{{ $eventoPrincipal->fecha_inicio->translatedFormat('d M Y') }}</span>
                                </div>
                                <h3 class="mt-3 font-display text-xl font-semibold text-white sm:text-2xl">{{ $eventoPrincipal->titulo }}</h3>
                                <p class="mt-2 text-sm text-white/85">
                                    {{ $eventoPrincipal->esGratuito() ? 'Entrada libre' : pesos($eventoPrincipal->precio) }}
                                </p>
                                @if ($eventoPrincipal->lugar)
                                    <p class="mt-1 text-sm text-white/75">{{ $eventoPrincipal->lugar }}</p>
                                @endif
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
                        <p class="home-editorial-copy mt-3 text-sm leading-relaxed text-tenue">{{ $iniciativaPrincipal->resumen }}</p>
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
                            <p class="home-editorial-copy mt-2 text-sm leading-relaxed text-tenue">{{ ajuste('portada_empleo_texto') }}</p>
                        </div>
                        <span class="home-editorial-enlace enlace-accion mt-4 inline-flex text-sm font-medium text-acento group-hover:text-acento-fuerte">
                            Ver vacantes&nbsp;<x-publico.flecha />
                        </span>
                    </a>
                </article>
            </div>
        </div>

        <div class="home-editorial-movimiento mt-6 border-t border-linea/60 pt-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-xl">
                    <p class="home-editorial-eyebrow text-acento">{{ ajuste('portada_videos_rotulo', 'ASOBARES en movimiento') }}</p>
                    <p class="mt-1 font-display text-base font-semibold sm:text-lg">{{ ajuste('portada_videos_titulo', 'Historias cortas para sentir el gremio.') }}</p>
                    <p class="mt-2 text-sm leading-relaxed text-tenue">{{ ajuste('portada_videos_intro', 'Una banda audiovisual para mostrar recorridos, eventos, testimonios y momentos de la noche quindiana con un tono sobrio, local y cercano.') }}</p>
                    <p class="sr-only">{{ ajuste('portada_videos_proxima_rotulo', 'Próxima pieza') }} {{ ajuste('portada_videos_proxima_texto', 'Clips de afiliados, activaciones y memoria del capítulo.') }}</p>
                    <p class="mb-3 text-xs leading-relaxed text-apagado">{{ ajuste('portada_guia_texto') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
                    <a href="{{ route('guia.index') }}" class="home-editorial-enlace enlace-accion text-acento hover:text-acento-fuerte">
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
    </div>
</section>
