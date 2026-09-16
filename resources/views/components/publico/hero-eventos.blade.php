@props([
    'titulo',
    'subtitulo' => null,
])

{{--
    Hero de /eventos: el mismo criterio que /directorio (texto a la izquierda,
    fotografía panorámica fundida, no una tarjeta a la derecha).

    Fotografía propia, nunca la de Directorio, Guía ni Home:
    public/img/eventos/hero-eventos.webp

    Sin archivo: plano crema/noche. No geometría, no foto falsa.
--}}
@php
    $fotoHeroEventos = is_file(public_path('img/eventos/hero-eventos.webp'))
        ? 'img/eventos/hero-eventos.webp'
        : null;
@endphp

<section @class([
        'eventos-editorial-hero',
        'eventos-editorial-hero--con-foto' => (bool) $fotoHeroEventos,
    ])
    aria-labelledby="eventos-editorial-titulo"
    data-eventos-hero-slot="img/eventos/hero-eventos.webp">
    <div class="eventos-editorial-hero__plano" aria-hidden="true"></div>
    @if ($fotoHeroEventos)
        <div class="eventos-editorial-hero__foto" aria-hidden="true">
            <img src="{{ asset($fotoHeroEventos) }}"
                 alt=""
                 width="1672"
                 height="941"
                 fetchpriority="high"
                 decoding="async">
        </div>
    @endif
    <div class="eventos-editorial-hero__velo" aria-hidden="true"></div>
    <div class="eventos-editorial-hero__cuerpo">
        <h1 id="eventos-editorial-titulo">{{ $titulo }}</h1>
        @if (filled($subtitulo))
            <p class="eventos-editorial-hero__sub">{{ $subtitulo }}</p>
        @endif
    </div>
</section>
