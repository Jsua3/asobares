@php
    // El panel escribe texto plano y lo sembrado trae HTML: el modelo convierte
    // el texto en párrafos cuando hace falta y sanea el resultado.
    $contenidoSeguro = $noticia->contenidoSaneado();
@endphp

<x-layouts.publico :titulo="$noticia->titulo.' — Boletín ASOBARES Quindío'"
                   :descripcion="Str::limit($noticia->extracto, 155)"
                   ogTipo="article"
                   :ogImagen="$noticia->imagen ? Storage::disk('public')->url($noticia->imagen) : null">

    @php
        $jsonLd = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $noticia->titulo,
            'description' => $noticia->extracto,
            'datePublished' => $noticia->publicado_at?->toIso8601String(),
            'image' => $noticia->imagen ? Storage::disk('public')->url($noticia->imagen) : null,
            'publisher' => ['@type' => 'Organization', 'name' => ajuste('sitio_nombre')],
        ]);
    @endphp

    @push('jsonld')
        <x-publico.json-ld :datos="$jsonLd" />
    @endpush

    @push('cabeza')
        @vite(['resources/css/gremio-editorial.css'])
    @endpush

    <div class="gremio-editorial gremio-editorial--revista">
        <article class="gremio-editorial-lectura revelar" data-revelar>
            <a href="{{ route('boletin.index') }}" class="gremio-editorial-retorno enlace-accion"><x-publico.flecha direccion="izquierda" />&nbsp;Volver al boletín</a>

            <x-publico.folio-gremio numero="02" />

            <p class="gremio-editorial-kicker">
                <span>{{ $noticia->categoria->getLabel() }}</span>
                <time datetime="{{ $noticia->publicado_at->toDateString() }}">
                    {{ $noticia->publicado_at->translatedFormat('d \d\e F \d\e Y') }}
                </time>
            </p>

            <h1>{{ $noticia->titulo }}</h1>

            @if ($noticia->extracto)
                <p class="gremio-editorial-entradilla">{{ $noticia->extracto }}</p>
            @endif

            @if ($noticia->imagen)
                <figure>
                    <img src="{{ Storage::disk('public')->url($noticia->imagen) }}" alt=""
                         width="1200" height="675" decoding="async">
                </figure>
            @endif

            <div class="gremio-editorial-prosa prose-asobares
                        [&_a]:text-acento [&_a]:underline [&_a]:underline-offset-2
                        [&_p]:text-pretty [&_strong]:font-semibold [&_strong]:text-fuerte">
                {!! $contenidoSeguro !!}
            </div>

            @if ($relacionadas->isNotEmpty())
                <section class="gremio-editorial-seccion" aria-labelledby="relacionadas">
                    <h2 id="relacionadas">Más del boletín</h2>
                    <ol class="gremio-editorial-indice-piezas">
                        @foreach ($relacionadas as $relacionada)
                            <li>
                                <a href="{{ route('boletin.show', $relacionada) }}">
                                    <span class="gremio-editorial-kicker">
                                        {{ $relacionada->categoria->getLabel() }}
                                        ·
                                        {{ $relacionada->publicado_at->translatedFormat('d M Y') }}
                                    </span>
                                    <span class="gremio-editorial-indice-piezas__titulo">{{ $relacionada->titulo }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif
        </article>
    </div>
</x-layouts.publico>
