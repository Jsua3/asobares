@php
    use Illuminate\Support\Facades\Storage;

    $desplaza = $coleccion->count() > 6;
@endphp

<div class="home-editorial-aliados__nivel">
    <p class="home-editorial-aliados__etiqueta">{{ $etiqueta }}</p>
    <ul @class([
        'home-editorial-aliados__logos',
        'home-editorial-aliados__logos--'.$variante,
        'home-editorial-aliados__logos--desplaza' => $desplaza,
    ])>
        @foreach ($coleccion as $aliado)
            @php
                $logo = filled($aliado->logo) && ! esImagenDeRelleno($aliado->logo)
                    ? Storage::disk('public')->url($aliado->logo)
                    : null;
                $enlace = enlaceSeguro($aliado->url);
                $etiquetaAccesible = $aliado->nombre.($enlace ? ' (sitio externo)' : '');
            @endphp
            <li class="home-editorial-aliados__item">
                @if ($enlace)
                    <a href="{{ $enlace }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="home-editorial-aliados__marca {{ $logo ? 'home-editorial-aliados__marca--logo' : 'home-editorial-aliados__marca--nombre' }}"
                       aria-label="{{ $etiquetaAccesible }}">
                        @if ($logo)
                            <img src="{{ $logo }}"
                                 alt="{{ $aliado->nombre }}"
                                 loading="lazy"
                                 decoding="async"
                                 width="160"
                                 height="72"
                                 class="home-editorial-aliados__logo">
                        @endif
                        <span @class([
                            'home-editorial-aliados__nombre',
                            'home-editorial-aliados__nombre--apoyo' => (bool) $logo,
                        ])>{{ $aliado->nombre }}</span>
                    </a>
                @else
                    <div class="home-editorial-aliados__marca {{ $logo ? 'home-editorial-aliados__marca--logo' : 'home-editorial-aliados__marca--nombre' }}">
                        @if ($logo)
                            <img src="{{ $logo }}"
                                 alt="{{ $aliado->nombre }}"
                                 loading="lazy"
                                 decoding="async"
                                 width="160"
                                 height="72"
                                 class="home-editorial-aliados__logo">
                        @endif
                        <span @class([
                            'home-editorial-aliados__nombre',
                            'home-editorial-aliados__nombre--apoyo' => (bool) $logo,
                        ])>{{ $aliado->nombre }}</span>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
