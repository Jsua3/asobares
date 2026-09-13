@props(['proximosEventos', 'iniciativas', 'destacados'])

@php
    $eventosVisibles = $proximosEventos->values();
    $haySecuencia = $eventosVisibles->count() > 1;
@endphp

<section class="home-editorial-eventos revelar" data-revelar aria-labelledby="eventos-del-gremio">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="home-editorial-eyebrow">{{ ajuste('portada_eventos_titulo') }}</p>
                <h2 id="eventos-del-gremio" class="home-editorial-titulo mt-2 text-balance">
                    {{ ajuste('portada_actualidad_subtitulo', 'Eventos que mueven la noche del Quindío.') }}
                </h2>
            </div>
            <a href="{{ route('eventos.index') }}"
               class="home-editorial-enlace enlace-accion shrink-0 text-sm font-medium text-acento hover:text-acento-fuerte">
                Ver agenda&nbsp;<x-publico.flecha />
            </a>
        </div>

        @if ($eventosVisibles->isNotEmpty())
            <div class="home-editorial-eventos__carrusel mt-6"
                 @if ($haySecuencia) x-data="carruselEventos({{ $eventosVisibles->count() }})"
                      x-on:mouseenter="pausar()"
                      x-on:mouseleave="reanudar()"
                      x-on:focusin="pausar()"
                      x-on:focusout="reanudar()" @endif>
                @if ($haySecuencia)
                    <button type="button"
                            class="home-editorial-eventos__control home-editorial-eventos__control--prev"
                            x-on:click="anterior()"
                            aria-label="Ver el evento anterior">
                        <x-publico.flecha direccion="izquierda" />
                    </button>
                @endif

                <div class="home-editorial-eventos__pista" @if ($haySecuencia) x-ref="pista" @endif>
                    @foreach ($eventosVisibles as $indice => $evento)
                        @php
                            $fotoReal = filled($evento->imagen) && ! esImagenDeRelleno($evento->imagen);
                            $fotoEvento = urlDeFotoDeLaHome($evento->imagen);
                            $mesCorto = mb_strtoupper(rtrim($evento->fecha_inicio->translatedFormat('M'), '.'));
                        @endphp
                        <article class="home-editorial-evento {{ $indice === 0 ? 'is-activo' : '' }}"
                                 @if ($haySecuencia) :class="indice === {{ $indice }} && 'is-activo'" @endif>
                            <a href="{{ route('eventos.show', $evento) }}" class="home-editorial-evento__enlace tarjeta-pulsable">
                                <div class="home-editorial-evento__foto">
                                    @if ($fotoEvento)
                                        <img src="{{ $fotoEvento }}"
                                             alt="{{ $fotoReal ? $evento->titulo : '' }}"
                                             loading="lazy"
                                             decoding="async"
                                             width="960"
                                             height="620"
                                             class="home-editorial-evento__img">
                                    @else
                                        <div class="home-editorial-evento__fallback home-editorial-establecimiento__fallback" aria-hidden="true">
                                            <span class="home-editorial-establecimiento__monograma">A</span>
                                        </div>
                                    @endif
                                    <div class="home-editorial-evento__velo" aria-hidden="true"></div>
                                    <time class="home-editorial-evento__fecha" datetime="{{ $evento->fecha_inicio->toDateString() }}">
                                        <span class="home-editorial-evento__dia">{{ $evento->fecha_inicio->format('d') }}</span>
                                        <span class="home-editorial-evento__mes">{{ $mesCorto }}</span>
                                    </time>
                                    <div class="home-editorial-evento__cuerpo">
                                        <span class="home-editorial-evento__tipo">{{ $evento->tipo->getLabel() }}</span>
                                        <h3 class="home-editorial-evento__titulo">{{ $evento->titulo }}</h3>
                                        @if ($evento->lugar)
                                            <span class="home-editorial-evento__meta">{{ $evento->lugar }}</span>
                                        @endif
                                        <span class="home-editorial-evento__meta">
                                            {{ $evento->esGratuito() ? 'Entrada libre' : pesos($evento->precio) }}
                                        </span>
                                        <span class="home-editorial-evento__cta home-editorial-enlace">
                                            Ver evento&nbsp;<x-publico.flecha />
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if ($haySecuencia)
                    <button type="button"
                            class="home-editorial-eventos__control home-editorial-eventos__control--next"
                            x-on:click="siguiente()"
                            aria-label="Ver el evento siguiente">
                        <x-publico.flecha />
                    </button>

                    <ol class="home-editorial-eventos__progreso" aria-label="Eventos de la portada">
                        @foreach ($eventosVisibles as $indice => $evento)
                            <li>
                                <button type="button"
                                        class="home-editorial-eventos__punto"
                                        x-on:click="ir({{ $indice }})"
                                        aria-current="{{ $indice === 0 ? 'true' : 'false' }}"
                                        :aria-current="indice === {{ $indice }} ? 'true' : 'false'"
                                        aria-label="Ir al evento {{ $indice + 1 }} de {{ $eventosVisibles->count() }}: {{ $evento->titulo }}">
                                    <span aria-hidden="true">{{ str_pad((string) ($indice + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        @else
            <p class="mt-6 text-sm text-tenue">{{ ajuste('eventos_vacios_proximos', 'No hay eventos programados por ahora') }}</p>
        @endif
    </div>
</section>

<section class="home-editorial-actualidad home-editorial-movimiento revelar" data-revelar aria-labelledby="home-movimiento">
    <div class="home-editorial-movimiento__atmosfera" aria-hidden="true"></div>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="home-editorial-movimiento__cuerpo">
            <div class="home-editorial-movimiento__texto">
                <h2 id="home-movimiento" class="home-editorial-eyebrow">{{ ajuste('portada_videos_rotulo', 'ASOBARES en movimiento') }}</h2>
                <p class="home-editorial-movimiento__frase">{{ ajuste('portada_videos_titulo', 'Historias cortas para sentir el gremio.') }}</p>
            </div>
            <nav class="home-editorial-movimiento__accesos" aria-label="{{ ajuste('portada_videos_rotulo', 'ASOBARES en movimiento') }}">
                <a href="{{ route('guia.index') }}"
                   class="home-editorial-enlace enlace-accion"
                   aria-describedby="home-guia-texto">
                    {{ ajuste('portada_guia_titulo') }}&nbsp;<x-publico.flecha />
                </a>
                <p id="home-guia-texto" class="sr-only">{{ ajuste('portada_guia_texto') }}</p>
                <a href="{{ route('eventos.index') }}" class="home-editorial-enlace enlace-accion">
                    Ver agenda&nbsp;<x-publico.flecha />
                </a>
                <a href="{{ route('boletin.index') }}" class="home-editorial-enlace enlace-accion">
                    Boletín ASOBARES&nbsp;<x-publico.flecha />
                </a>
            </nav>
        </div>
    </div>
</section>
