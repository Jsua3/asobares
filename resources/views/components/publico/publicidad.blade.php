@props(['publicidad'])

@php
    use Illuminate\Support\Facades\Storage;

    $nombre = $publicidad->nombre_comercial ?: $publicidad->anunciante;
    $imagen = Storage::disk(config('almacenamiento.publico'))->url($publicidad->imagen);
    $href = $publicidad->url_destino;
    $rotulo = ajuste('publicidad_rotulo', 'Contenido patrocinado');
    $pie = ajuste('publicidad_pie', 'Campaña vigente de ASOBARES Capítulo Quindío.');
    $nombreAccesible = $rotulo.': '.$nombre;
@endphp

<aside {{ $attributes->class('directorio-pauta') }} aria-label="{{ $rotulo }}">
    @if ($href)
        <a href="{{ $href }}"
           target="_blank"
           rel="noopener noreferrer sponsored"
           class="directorio-pauta__pieza"
           aria-label="{{ $nombreAccesible }}">
    @else
        <div class="directorio-pauta__pieza" role="group" aria-label="{{ $nombreAccesible }}">
    @endif
        <div class="directorio-pauta__cuerpo">
            <p class="directorio-pauta__rotulo">{{ $rotulo }}</p>
            <h2 class="directorio-pauta__titulo">{{ $nombre }}</h2>
            <p class="directorio-pauta__pie">{{ $pie }}</p>
            @if ($href)
                <span class="directorio-pauta__cta" aria-hidden="true">
                    Conocer más&nbsp;<x-publico.flecha />
                </span>
            @endif
        </div>

        <img
            src="{{ $imagen }}"
            alt=""
            loading="lazy"
            decoding="async"
            width="640"
            height="360"
            class="imagen-viva directorio-pauta__img h-full min-h-44 w-full object-cover"
        >
    @if ($href)
        </a>
    @else
        </div>
    @endif
</aside>
