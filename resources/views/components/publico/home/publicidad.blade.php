@props(['publicidad'])

@php
    $nombre = $publicidad->nombre_comercial ?: $publicidad->anunciante;
    $imagen = urlDeFotoDeLaHome(
        $publicidad->imagen,
        config('home_banco.publicidad'),
        config('almacenamiento.publico')
    );
    $href = $publicidad->url_destino;
@endphp

<section class="home-editorial-publicidad revelar" data-revelar aria-label="Publicidad">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if ($href)
            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer sponsored" class="home-editorial-publicidad__banner group block overflow-hidden rounded-xl">
        @else
            <div class="home-editorial-publicidad__banner overflow-hidden rounded-xl">
        @endif
            <div class="relative min-h-[9rem] sm:min-h-[10.5rem]">
                @if ($imagen)
                    <img src="{{ $imagen }}"
                         alt="Publicidad de {{ $nombre }}"
                         loading="lazy"
                         decoding="async"
                         width="1280"
                         height="400"
                         class="home-editorial-publicidad__img absolute inset-0 h-full w-full object-cover">
                @else
                    <div class="home-editorial-publicidad__fallback" aria-hidden="true"></div>
                @endif
                <div class="home-editorial-publicidad__velo absolute inset-0" aria-hidden="true"></div>
                <div class="relative flex h-full min-h-[inherit] flex-col justify-end p-5 sm:flex-row sm:items-end sm:justify-between sm:p-6">
                    <div class="max-w-lg">
                        <p class="home-editorial-eyebrow text-white/75">{{ ajuste('publicidad_rotulo', 'Publicidad') }}</p>
                        <p class="mt-1 font-display text-xl font-semibold text-white sm:text-2xl">{{ $nombre }}</p>
                        <p class="mt-1 text-sm text-white/80">{{ ajuste('publicidad_pie', 'Campaña vigente de ASOBARES Capítulo Quindío.') }}</p>
                    </div>
                    @if ($href)
                        <span class="home-editorial-enlace mt-4 inline-flex items-center text-sm font-medium text-white sm:mt-0">
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
