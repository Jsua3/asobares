@php
    $piezas = $noticias->getCollection();
    $protagonista = $piezas->first();
    $resto = $piezas->slice(1)->values();
    $secundaria = $resto->first();
    $indice = $resto->slice(1)->values();
@endphp

<x-layouts.publico :titulo="ajuste('seo_boletin_titulo', ajuste('boletin_titulo').' — ASOBARES Quindío')"
                   :descripcion="ajuste('seo_boletin_descripcion', ajuste('boletin_intro'))">

    @push('cabeza')
        @vite(['resources/css/gremio-editorial.css'])
    @endpush

    <div class="gremio-editorial gremio-editorial--revista">
        <div class="gremio-editorial-cuerpo">

            <header class="gremio-editorial-apertura revelar" data-revelar>
                <x-publico.folio-gremio numero="02" kicker="BOLETÍN" />
                <h1>{{ ajuste('boletin_titulo') }}</h1>
                <p class="gremio-editorial-entradilla">{{ ajuste('boletin_intro') }}</p>
            </header>

            <nav class="gremio-editorial-indice revelar" data-revelar aria-label="Secciones del boletín">
                <a href="{{ route('boletin.index') }}"
                   @class([
                       'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                       'border-transparent' => true,
                   ])
                   @if (empty($filtros['categoria'])) aria-current="page" @endif
                   @if (empty($filtros['categoria'])) style="view-transition-name: filtro-activo" @endif>Todas</a>

                @foreach ($categorias as $categoria)
                    <a href="{{ route('boletin.index', ['categoria' => $categoria->value]) }}"
                       @class([
                           'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                           'border-transparent' => true,
                       ])
                       @if (($filtros['categoria'] ?? null) === $categoria->value) aria-current="page" @endif
                       @if (($filtros['categoria'] ?? null) === $categoria->value) style="view-transition-name: filtro-activo" @endif>{{ $categoria->getLabel() }}</a>
                @endforeach
            </nav>

            @if ($noticias->isEmpty())
                <div class="gremio-editorial-vacio revelar" data-revelar>
                    <p class="font-display text-lg font-semibold">{{ ajuste('boletin_vacio_titulo', 'Todavía no hay publicaciones') }}</p>
                    <p>{{ ajuste('boletin_vacio_texto', 'El boletín se publica alrededor de una vez al mes.') }}</p>
                </div>
            @else
                <a href="{{ route('boletin.show', $protagonista) }}"
                   @class([
                       'gremio-editorial-lead revelar',
                       'gremio-editorial-lead--con-foto' => filled($protagonista->imagen),
                   ])
                   data-revelar>
                    @if ($protagonista->imagen)
                        <img src="{{ Storage::disk('public')->url($protagonista->imagen) }}" alt=""
                             loading="lazy" decoding="async" width="800" height="500">
                    @endif
                    <div>
                        <p class="gremio-editorial-kicker">
                            <span>{{ $protagonista->categoria->getLabel() }}</span>
                            <time datetime="{{ $protagonista->publicado_at->toDateString() }}">{{ $protagonista->publicado_at->translatedFormat('d M Y') }}</time>
                        </p>
                        <h2>{{ $protagonista->titulo }}</h2>
                        <p>{{ $protagonista->extracto }}</p>
                    </div>
                </a>

                @if ($secundaria)
                    <div class="gremio-editorial-tablero revelar" data-revelar>
                        <a href="{{ route('boletin.show', $secundaria) }}" class="gremio-editorial-secundaria">
                            @if ($secundaria->imagen)
                                <img src="{{ Storage::disk('public')->url($secundaria->imagen) }}" alt=""
                                     loading="lazy" decoding="async" width="640" height="400">
                            @endif
                            <p class="gremio-editorial-kicker">
                                <span>{{ $secundaria->categoria->getLabel() }}</span>
                                <time datetime="{{ $secundaria->publicado_at->toDateString() }}">{{ $secundaria->publicado_at->translatedFormat('d M Y') }}</time>
                            </p>
                            <h2>{{ $secundaria->titulo }}</h2>
                            <p>{{ $secundaria->extracto }}</p>
                        </a>

                        @if ($indice->isNotEmpty())
                            <ol class="gremio-editorial-indice-piezas">
                                @foreach ($indice as $noticia)
                                    <li>
                                        <a href="{{ route('boletin.show', $noticia) }}">
                                            <span class="gremio-editorial-kicker">
                                                {{ $noticia->categoria->getLabel() }}
                                                ·
                                                <time datetime="{{ $noticia->publicado_at->toDateString() }}">{{ $noticia->publicado_at->translatedFormat('d M Y') }}</time>
                                            </span>
                                            <span class="gremio-editorial-indice-piezas__titulo">{{ $noticia->titulo }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                @endif

                <div class="gremio-editorial-pagina">{{ $noticias->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.publico>
