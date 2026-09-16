@props([
    'titulo',
    'subtitulo' => null,
    'ctaPerfil' => null,
    'ctaVacantes' => null,
])

{{--
    Hero de /empleo: el mismo criterio que /directorio (texto a la izquierda,
    fotografía panorámica fundida, no una tarjeta a la derecha).

    Fotografía propia, nunca la de Directorio, Eventos, Guía ni Home:
    public/img/empleo/hero-empleo.png

    Sin archivo: plano crema/noche. No geometría, no foto falsa.
--}}
@php
    $fotoHeroEmpleo = is_file(public_path('img/empleo/hero-empleo.png'))
        ? 'img/empleo/hero-empleo.png'
        : null;
@endphp

<section @class([
        'empleo-editorial-hero',
        'empleo-editorial-hero--con-foto' => (bool) $fotoHeroEmpleo,
    ])
    aria-labelledby="empleo-editorial-titulo"
    data-empleo-hero-slot="img/empleo/hero-empleo.png">
    <div class="empleo-editorial-hero__plano" aria-hidden="true"></div>
    @if ($fotoHeroEmpleo)
        <div class="empleo-editorial-hero__foto" aria-hidden="true">
            <img src="{{ asset($fotoHeroEmpleo) }}"
                 alt=""
                 width="1920"
                 height="1080"
                 fetchpriority="high"
                 decoding="async">
        </div>
    @endif
    <div class="empleo-editorial-hero__velo" aria-hidden="true"></div>
    <div class="empleo-editorial-hero__cuerpo">
        <h1 id="empleo-editorial-titulo">{{ $titulo }}</h1>
        @if (filled($subtitulo))
            <p class="empleo-editorial-hero__sub">{{ $subtitulo }}</p>
        @endif
        @if (filled($ctaPerfil) || filled($ctaVacantes))
            <div class="empleo-editorial-hero__acciones">
                @if (filled($ctaPerfil))
                    <a href="#perfil" class="empleo-editorial-hero__cta pulsable">{{ $ctaPerfil }}</a>
                @endif
                @if (filled($ctaVacantes))
                    <a href="#vacantes" class="empleo-editorial-hero__cta empleo-editorial-hero__cta--contorno pulsable">{{ $ctaVacantes }}</a>
                @endif
            </div>
        @endif
    </div>
</section>
