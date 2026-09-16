@props(['publicidad'])

@php
    $nombre = $publicidad->nombre_comercial ?: $publicidad->anunciante;
    $imagen = urlDeFotoDeLaHome(
        $publicidad->imagen,
        config('home_banco.publicidad'),
        config('almacenamiento.publico')
    );
    $href = $publicidad->url_destino;
    $rotulo = ajuste('publicidad_rotulo', 'Contenido patrocinado');
    $nombreAccesible = $rotulo.': '.$nombre;
@endphp

<section class="home-editorial-publicidad revelar" data-revelar aria-label="{{ $rotulo }}">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if ($href)
            <a href="{{ $href }}"
               target="_blank"
               rel="noopener noreferrer sponsored"
               class="home-editorial-publicidad__pieza group"
               aria-label="{{ $nombreAccesible }}">
        @else
            <div class="home-editorial-publicidad__pieza" role="group" aria-label="{{ $nombreAccesible }}">
        @endif
            <div class="home-editorial-publicidad__escena">
                @if ($imagen)
                    <img src="{{ $imagen }}"
                         alt="{{ $nombreAccesible }}"
                         loading="lazy"
                         decoding="async"
                         width="1280"
                         height="480"
                         class="home-editorial-publicidad__img">
                @else
                    <div class="home-editorial-publicidad__fallback" aria-hidden="true"></div>
                @endif
                <div class="home-editorial-publicidad__velo" aria-hidden="true"></div>
                <div class="home-editorial-publicidad__cuerpo">
                    <div class="home-editorial-publicidad__texto">
                        <p class="home-editorial-publicidad__rotulo">{{ $rotulo }}</p>
                        <p class="home-editorial-publicidad__titulo">{{ $nombre }}</p>
                        <p class="home-editorial-publicidad__pie">{{ ajuste('publicidad_pie', 'Campaña vigente de ASOBARES Capítulo Quindío.') }}</p>
                    </div>
                    @if ($href)
                        <span class="home-editorial-publicidad__cta home-editorial-enlace" aria-hidden="true">
                            Conocer más&nbsp;<x-publico.flecha />
                        </span>
                    @endif
                </div>
            </div>
        @if ($href)
            </a>
        @else
            </div>
        @endif
    </div>
</section>
