@props(['publicidad'])

@php
    use Illuminate\Support\Facades\Storage;

    $nombre = $publicidad->nombre_comercial ?: $publicidad->anunciante;
    $imagen = Storage::disk(config('almacenamiento.publico'))->url($publicidad->imagen);
    $href = $publicidad->url_destino;
@endphp

<aside {{ $attributes->class('tarjeta overflow-hidden p-0') }} aria-label="Publicidad">
    @if ($href)
        <a href="{{ $href }}" target="_blank" rel="noopener noreferrer sponsored" class="block">
    @else
        <div>
    @endif
        <div class="grid gap-0 sm:grid-cols-[minmax(0,1fr)_16rem]">
            <div class="flex flex-col justify-center p-5 sm:p-6">
                <p class="antetitulo text-acento">Publicidad</p>
                <h2 class="mt-2 font-display text-xl font-bold text-fuerte">{{ $nombre }}</h2>
                <p class="mt-2 text-sm text-tenue">Campaña vigente de ASOBARES Capitulo Quindio.</p>
            </div>

            <img
                src="{{ $imagen }}"
                alt="Publicidad de {{ $nombre }}"
                loading="lazy"
                decoding="async"
                width="640"
                height="360"
                class="imagen-viva h-full min-h-44 w-full object-cover"
            >
        </div>
    @if ($href)
        </a>
    @else
        </div>
    @endif
</aside>
