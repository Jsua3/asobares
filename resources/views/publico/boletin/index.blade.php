<x-layouts.publico :titulo="ajuste('boletin_titulo').' — ASOBARES Quindío'"
                   :descripcion="ajuste('boletin_intro')">

    <x-publico.hero :titulo="ajuste('boletin_titulo')" :subtitulo="ajuste('boletin_intro')" compacto />

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('boletin.index') }}"
               @class([
                   'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                   'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => empty($filtros['categoria']),
                   'border-linea text-suave hover:border-marca-500/40' => ! empty($filtros['categoria']),
               ])
               @if (empty($filtros['categoria'])) style="view-transition-name: filtro-activo" @endif>Todas</a>

            @foreach ($categorias as $categoria)
                <a href="{{ route('boletin.index', ['categoria' => $categoria->value]) }}"
                   @class([
                       'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                       'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => ($filtros['categoria'] ?? null) === $categoria->value,
                       'border-linea text-suave hover:border-marca-500/40' => ($filtros['categoria'] ?? null) !== $categoria->value,
                   ])
                   @if (($filtros['categoria'] ?? null) === $categoria->value) style="view-transition-name: filtro-activo" @endif>{{ $categoria->getLabel() }}</a>
            @endforeach
        </div>

        @if ($noticias->isEmpty())
            <div class="tarjeta mt-8 p-12 text-center">
                <p class="font-display text-lg font-semibold">Todavía no hay publicaciones</p>
                <p class="mt-2 text-sm text-tenue">El boletín se publica alrededor de una vez al mes.</p>
            </div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($noticias as $noticia)
                    <article class="tarjeta tarjeta-hover tarjeta-pulsable flex flex-col overflow-hidden">
                        <a href="{{ route('boletin.show', $noticia) }}" class="flex flex-1 flex-col">
                            @if ($noticia->imagen)
                                <img src="{{ Storage::disk('public')->url($noticia->imagen) }}" alt=""
                                     loading="lazy" decoding="async" width="400" height="225"
                                     class="aspect-video w-full object-cover">
                            @endif
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                        {{ $noticia->categoria->getLabel() }}
                                    </span>
                                    <time datetime="{{ $noticia->publicado_at->toDateString() }}" class="text-apagado">
                                        {{ $noticia->publicado_at->translatedFormat('d M Y') }}
                                    </time>
                                </div>
                                <h2 class="mt-3 font-display text-base font-semibold leading-snug">{{ $noticia->titulo }}</h2>
                                <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-tenue">{{ $noticia->extracto }}</p>
                                <span class="mt-4 text-sm font-medium text-acento">Leer&nbsp;<x-publico.flecha /></span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $noticias->links() }}</div>
        @endif
    </div>
</x-layouts.publico>
