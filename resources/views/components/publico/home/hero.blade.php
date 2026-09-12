@props(['destacados', 'totalAsociados'])

@php
    use Illuminate\Support\Facades\Storage;

    $fotosHero = $destacados
        ->filter(fn ($asociado) => filled($asociado->foto_portada))
        ->take(3)
        ->map(fn ($asociado) => Storage::disk('public')->url($asociado->foto_portada));

    $postersVideo = $fotosHero->values();

    $videoInstitucional = [
        'titulo' => ajuste('hero_video_titulo', 'ASOBARES Capítulo Quindío'),
        'detalle' => ajuste('hero_video_detalle', 'Una mirada breve al gremio que mueve la noche, la cultura y el territorio.'),
        'src' => file_exists(public_path('videos/asobares-institucional.mp4'))
            ? asset('videos/asobares-institucional.mp4')
            : null,
        'poster' => file_exists(public_path('videos/asobares-institucional.jpg'))
            ? asset('videos/asobares-institucional.jpg')
            : $postersVideo->get(0),
    ];
@endphp

<x-publico.hero :titulo="ajuste('hero_titulo')" atmosfera portada>
    <x-slot:medio>
        <div class="hero-video-fondo">
            @if ($videoInstitucional['poster'])
                <img src="{{ $videoInstitucional['poster'] }}"
                     alt=""
                     width="1600"
                     height="900"
                     class="imagen-viva absolute inset-0 h-full w-full object-cover">
            @else
                <div class="hero-video-respaldo absolute inset-0"></div>
            @endif

            @if ($videoInstitucional['src'])
                <video class="imagen-viva video-hero-capa absolute inset-0 h-full w-full object-cover"
                       x-data="videoHero"
                       x-bind:class="listo ? 'video-hero-capa--visible' : ''"
                       x-on:error="listo = false"
                       @if ($videoInstitucional['poster']) poster="{{ $videoInstitucional['poster'] }}" @endif
                       muted
                       loop
                       playsinline
                       preload="none">
                    <source src="{{ $videoInstitucional['src'] }}" type="video/mp4">
                </video>
            @endif
        </div>
    </x-slot:medio>

    <x-slot:encima>
        @if (! ajuste('hero_frase_corta'))
            <p class="home-editorial-eyebrow mb-4">{{ ajuste('sitio_nombre') }}</p>
        @endif
    </x-slot:encima>

    @if ($fraseCorta = ajuste('hero_frase_corta'))
        <p class="home-editorial-concepto mt-5 max-w-lg text-pretty">{{ $fraseCorta }}</p>
    @endif

    @if ($totalAsociados > 0)
        <p class="home-editorial-hero-secundario mt-4">
            {{ $totalAsociados }} afiliados en el Quindío
        </p>
    @endif

    <p class="home-editorial-hero-secundario mt-3 max-w-md text-pretty">
        {{ ajuste('hero_resumen_corto', 'Representamos la vida nocturna del Quindío con criterio, cultura y territorio.') }}
    </p>

    <div class="mt-7 flex flex-col gap-3 sm:flex-row">
        <x-publico.boton :href="route('directorio.index')">
            {{ ajuste('hero_cta_directorio') }}
        </x-publico.boton>
        <x-publico.boton variante="contorno-claro" :href="route('afiliate')">
            {{ ajuste('hero_cta_afiliate') }}
        </x-publico.boton>
    </div>

    <p class="home-editorial-video-nota pie-de-video mt-8 max-w-md border-l pl-4 leading-relaxed">
        <span class="antetitulo block">{{ ajuste('hero_video_rotulo', 'Video institucional') }}</span>
        <span class="mt-0.5 block">{{ $videoInstitucional['titulo'] }} · {{ $videoInstitucional['detalle'] }}</span>
    </p>
</x-publico.hero>
