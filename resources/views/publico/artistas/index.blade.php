<x-layouts.publico :titulo="ajuste('artistas_titulo').' — ASOBARES Quindío'"
                   descripcion="DJs, bandas y solistas del Quindío: género musical, tarifa desde, contacto directo y video para escucharlos.">

    <x-publico.hero :titulo="ajuste('artistas_titulo')" :subtitulo="ajuste('artistas_intro')" compacto />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Filtros --}}
        <form method="GET" action="{{ route('artistas.index') }}" class="tarjeta grid gap-4 p-5 sm:grid-cols-3">
            <x-publico.campo nombre="tipo" etiqueta="Tipo" tipo="select" :valor="$filtros['tipo'] ?? null"
                             :opciones="['' => 'Todos'] + collect(\App\Enums\TipoArtista::cases())->mapWithKeys(fn ($t) => [$t->value => $t->getLabel()])->all()" />

            <x-publico.campo nombre="genero" etiqueta="Género musical" tipo="select" :valor="$filtros['genero'] ?? null"
                             :opciones="['' => 'Todos los géneros'] + $generos->mapWithKeys(fn ($g) => [$g => $g])->all()" />

            <div class="flex items-end gap-2">
                <x-publico.boton class="flex-1">
                    Filtrar
                </x-publico.boton>
                @if (array_filter($filtros ?? []))
                    <a href="{{ route('artistas.index') }}"
                       class="rounded-xl border border-linea px-4 py-2.5 text-sm text-tenue hover:text-fuerte">Limpiar</a>
                @endif
            </div>
        </form>

        @if ($artistas->isEmpty())
            <div class="tarjeta mt-8 p-12 text-center">
                <p class="font-display text-lg font-semibold">No hay artistas con ese filtro</p>
                <p class="mt-2 text-sm text-tenue">Prueba con otro género o tipo.</p>
            </div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($artistas as $artista)
                    <article class="tarjeta tarjeta-hover flex flex-col overflow-hidden">
                        <a href="{{ route('artistas.show', $artista) }}" class="flex flex-1 flex-col">
                            @if ($artista->foto)
                                <img src="{{ Storage::disk('public')->url($artista->foto) }}" alt=""
                                     loading="lazy" decoding="async" width="400" height="400"
                                     class="aspect-square w-full object-cover">
                            @endif

                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                        {{ $artista->tipo->getLabel() }}
                                    </span>
                                    @if ($artista->tieneVideo())
                                        <span class="rounded-full border border-linea px-2.5 py-1 text-tenue">▶ Con video</span>
                                    @endif
                                </div>

                                <h2 class="mt-3 font-display text-lg font-semibold">{{ $artista->nombre }}</h2>
                                <p class="mt-1 text-sm text-acento">{{ $artista->genero_musical }}</p>

                                <p class="mt-3 line-clamp-2 flex-1 text-sm leading-relaxed text-tenue">
                                    {{ $artista->descripcion }}
                                </p>

                                <div class="mt-4 flex items-end justify-between gap-3">
                                    <div>
                                        <p class="text-[.65rem] uppercase tracking-wide text-apagado">Tarifa desde</p>
                                        <p class="font-display text-base font-semibold">
                                            {{ $artista->tarifa_desde ? pesos($artista->tarifa_desde) : 'A convenir' }}
                                        </p>
                                    </div>
                                    <span class="text-sm font-medium text-acento">Ver ficha →</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $artistas->links() }}</div>
        @endif

        <section class="tarjeta mt-16 p-8 text-center">
            <h2 class="font-display text-xl font-bold">¿Eres DJ, banda o solista?</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm text-tenue">
                Inscríbete gratis en la bolsa de artistas del gremio y aparece cuando un establecimiento
                busque música para su noche.
            </p>
            <x-publico.boton :href="route('artistas.inscripcion')" class="mt-6">
                Inscribirme en la bolsa
            </x-publico.boton>
        </section>
    </div>
</x-layouts.publico>
