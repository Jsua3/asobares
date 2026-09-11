@props(['proximosEventos', 'iniciativas', 'destacados'])

@php
    use Illuminate\Support\Facades\Storage;

    $postersVideo = $destacados
        ->filter(fn ($asociado) => filled($asociado->foto_portada))
        ->take(3)
        ->map(fn ($asociado) => Storage::disk('public')->url($asociado->foto_portada))
        ->values();

    $piezas = collect();

    foreach ($proximosEventos as $evento) {
        $piezas->push(['tipo' => 'evento', 'item' => $evento]);
    }

    foreach ($iniciativas as $iniciativa) {
        if ($piezas->count() >= 3) {
            break;
        }

        $piezas->push(['tipo' => 'iniciativa', 'item' => $iniciativa]);
    }

    $piezas = $piezas->take(3);
@endphp

<section class="home-editorial-actualidad revelar border-b border-linea" data-revelar aria-labelledby="actualidad">
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-4">
                <p class="home-editorial-kicker">{{ ajuste('portada_eventos_titulo') }}</p>
                <h2 id="actualidad" class="sr-only">{{ ajuste('portada_eventos_titulo') }}</h2>

                <div class="mt-6 grid gap-3">
                    <a href="{{ route('guia.index') }}"
                       class="home-editorial-acceso tarjeta-pulsable flex items-start gap-4 rounded-xl border border-linea p-4 transition-colors hover:border-linea-fuerte">
                        <span class="inline-flex shrink-0 rounded-lg bg-marca-500/10 p-2.5 text-acento" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block font-display text-base font-semibold">{{ ajuste('portada_guia_titulo') }}</span>
                            <span class="mt-1 block text-sm text-tenue">{{ ajuste('portada_guia_texto') }}</span>
                        </span>
                    </a>

                    <a href="{{ route('empleo.index') }}"
                       class="home-editorial-acceso tarjeta-pulsable flex items-start gap-4 rounded-xl border border-linea p-4 transition-colors hover:border-linea-fuerte">
                        <span class="inline-flex shrink-0 rounded-lg bg-marca-500/10 p-2.5 text-acento" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.073a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a9.75 9.75 0 0 1-6.396 0l-1.32-.377a2.25 2.25 0 0 1-1.632-2.163V14.15M3.75 9.75v.375c0 .621.504 1.125 1.125 1.125h.375m0 0h14.25m-14.25 0v6.75m14.25-6.75h.375c.621 0 1.125-.504 1.125-1.125V9.75m-16.5 0V8.625c0-.621.504-1.125 1.125-1.125h14.25c.621 0 1.125.504 1.125 1.125V9.75m-16.5 0h16.5M9 7.5V6a2.25 2.25 0 0 1 2.25-2.25h1.5A2.25 2.25 0 0 1 15 6v1.5"/>
                            </svg>
                        </span>
                        <span>
                            <span class="block font-display text-base font-semibold">{{ ajuste('portada_empleo_titulo') }}</span>
                            <span class="mt-1 block text-sm text-tenue">{{ ajuste('portada_empleo_texto') }}</span>
                        </span>
                    </a>
                </div>

                <div class="mt-8 rounded-xl border border-linea p-5">
                    <p class="antetitulo text-acento">{{ ajuste('portada_videos_rotulo', 'ASOBARES en movimiento') }}</p>
                    <p class="mt-2 font-display text-lg font-semibold">{{ ajuste('portada_videos_titulo', 'Historias cortas para sentir el gremio.') }}</p>
                    <p class="mt-2 text-sm text-tenue">{{ ajuste('portada_videos_intro', 'Una banda audiovisual para mostrar recorridos, eventos, testimonios y momentos de la noche quindiana con un tono sobrio, local y cercano.') }}</p>
                    @if ($poster = $postersVideo->first())
                        <img src="{{ $poster }}"
                             alt=""
                             loading="lazy"
                             decoding="async"
                             width="480"
                             height="320"
                             class="imagen-viva mt-4 aspect-[3/2] w-full rounded-lg object-cover">
                    @endif
                    <p class="mt-4 text-xs text-apagado">
                        <span class="antetitulo text-tenue">{{ ajuste('portada_videos_proxima_rotulo', 'Próxima pieza') }}</span>
                        {{ ajuste('portada_videos_proxima_texto', 'Clips de afiliados, activaciones y memoria del capítulo.') }}
                    </p>
                    <a href="{{ route('eventos.index') }}"
                       class="enlace-accion mt-4 inline-flex text-sm font-medium text-acento hover:text-acento-fuerte">
                        Ver agenda del gremio&nbsp;<x-publico.flecha />
                    </a>
                </div>

                <p class="mt-6 text-sm">
                    <a href="{{ route('boletin.index') }}" class="enlace-accion text-acento hover:text-acento-fuerte">
                        Boletín ASOBARES&nbsp;<x-publico.flecha />
                    </a>
                </p>
            </div>

            <div class="lg:col-span-8">
                @if ($piezas->isNotEmpty())
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($piezas as $pieza)
                            @if ($pieza['tipo'] === 'evento')
                                @php($evento = $pieza['item'])
                                <article class="home-editorial-pieza tarjeta tarjeta-hover tarjeta-pulsable overflow-hidden">
                                    <a href="{{ route('eventos.show', $evento) }}" class="block">
                                        @if ($evento->imagen)
                                            <img src="{{ Storage::disk('public')->url($evento->imagen) }}"
                                                 alt=""
                                                 loading="lazy"
                                                 decoding="async"
                                                 width="480"
                                                 height="320"
                                                 class="imagen-viva aspect-[16/10] w-full object-cover">
                                        @endif
                                        <div class="p-5">
                                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                                <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                                    {{ $evento->tipo->getLabel() }}
                                                </span>
                                                <span class="text-apagado">{{ $evento->fecha_inicio->translatedFormat('d M Y') }}</span>
                                            </div>
                                            <h3 class="mt-3 font-display text-base font-semibold">{{ $evento->titulo }}</h3>
                                            <p class="mt-2 line-clamp-2 text-sm text-tenue">
                                                {{ $evento->esGratuito() ? 'Entrada libre' : pesos($evento->precio) }}
                                                @if ($evento->lugar)
                                                    · {{ Str::limit($evento->lugar, 40) }}
                                                @endif
                                            </p>
                                        </div>
                                    </a>
                                </article>
                            @else
                                @php($iniciativa = $pieza['item'])
                                <article class="home-editorial-pieza tarjeta tarjeta-hover overflow-hidden">
                                    <div class="p-5">
                                        <p class="antetitulo text-acento">{{ ajuste('vision_nota') }}</p>
                                        <h3 class="mt-2 font-display text-base font-semibold">{{ $iniciativa->nombre }}</h3>
                                        <span @class([
                                            'mt-3 inline-block w-fit rounded-md px-2 py-1 text-2xs font-semibold uppercase tracking-wider',
                                            'bg-emerald-500/15 text-exito' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::EnEjecucion,
                                            'bg-amber-500/15 text-aviso' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Escalando,
                                            'border border-linea-fuerte text-apagado' => $iniciativa->estado_iniciativa === \App\Enums\EstadoIniciativa::Formulacion,
                                        ])>{{ $iniciativa->estado_iniciativa->getLabel() }}</span>
                                        <p class="mt-3 line-clamp-3 text-sm text-tenue">{{ $iniciativa->resumen }}</p>
                                        <a href="{{ route('quienes-somos') }}#iniciativas"
                                           class="enlace-accion mt-4 inline-flex text-sm font-medium text-acento hover:text-acento-fuerte">
                                            {{ ajuste('iniciativas_titulo') }}&nbsp;<x-publico.flecha />
                                        </a>
                                    </div>
                                </article>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-linea p-8 text-center">
                        <p class="antetitulo text-acento">{{ ajuste('vision_nota') }}</p>
                        <p class="mt-2 font-display text-lg font-semibold">{{ ajuste('iniciativas_titulo') }}</p>
                        <p class="mt-2 text-sm text-tenue">{{ ajuste('iniciativas_intro') }}</p>
                        <a href="{{ route('quienes-somos') }}#iniciativas"
                           class="enlace-accion mt-6 inline-flex text-sm font-medium text-acento hover:text-acento-fuerte">
                            Ver el detalle&nbsp;<x-publico.flecha />
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
