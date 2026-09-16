@props([
    'evento',
    'realizado' => false,
])

{{--
    Una ficha del riel de /eventos. Los datos salen del modelo publicado:
    no hay títulos, fechas, precios ni fotos cableados.
--}}
<article @class([
    'eventos-editorial-ficha',
    'eventos-editorial-ficha--realizado' => $realizado,
])>
    <a href="{{ route('eventos.show', $evento) }}" class="eventos-editorial-ficha__enlace">
        <time class="eventos-editorial-ficha__fecha" datetime="{{ $evento->fecha_inicio->toIso8601String() }}">
            <span class="eventos-editorial-ficha__dia">{{ $evento->fecha_inicio->format('d') }}</span>
            <span class="eventos-editorial-ficha__mes">{{ Str::upper($evento->fecha_inicio->translatedFormat('M')) }}</span>
            <span class="eventos-editorial-ficha__hora">{{ $evento->fecha_inicio->translatedFormat('g:i a') }}</span>
        </time>

        <div class="eventos-editorial-ficha__foto" style="view-transition-name: portada-evento-{{ $evento->id }}">
            @if ($evento->imagen)
                <img src="{{ Storage::disk('public')->url($evento->imagen) }}"
                     alt=""
                     loading="lazy"
                     decoding="async"
                     width="640"
                     height="400">
            @else
                <x-publico.hueco-foto />
            @endif
        </div>

        <div class="eventos-editorial-ficha__cuerpo">
            <div class="eventos-editorial-ficha__meta">
                <span class="eventos-editorial-ficha__tipo">{{ $evento->tipo->getLabel() }}</span>
                <span class="eventos-editorial-ficha__precio eventos-editorial-ficha__origen">{{ $evento->origenPublico()->getLabel() }}</span>
                @if ($evento->esGratuito())
                    <span class="eventos-editorial-ficha__precio">Gratuito</span>
                @else
                    <span class="eventos-editorial-ficha__precio">{{ pesos($evento->precio) }}</span>
                @endif
                @if ($realizado)
                    <span class="eventos-editorial-ficha__realizado">Realizado</span>
                @endif
            </div>

            <h2 class="eventos-editorial-ficha__titulo">{{ $evento->titulo }}</h2>

            @if ($evento->lugar)
                <p class="eventos-editorial-ficha__lugar">{{ $evento->lugar }}</p>
            @endif

            <p class="eventos-editorial-ficha__organiza">
                <span>Organiza</span>
                {{ $evento->organizadorVisible() }}
            </p>

            <span class="eventos-editorial-ficha__cta">Ver evento&nbsp;<x-publico.flecha /></span>
        </div>
    </a>
</article>
