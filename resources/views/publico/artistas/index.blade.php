<x-layouts.publico :titulo="ajuste('artistas_titulo').' — ASOBARES Quindío'"
                   descripcion="DJs, bandas y solistas del Quindío: género musical, contacto directo y video para escucharlos.">

    <x-publico.hero :titulo="ajuste('artistas_titulo')" :subtitulo="ajuste('artistas_intro')" compacto atmosfera />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Filtros --}}
        <form method="GET" action="{{ route('artistas.index') }}" class="revelar tarjeta grid gap-4 p-5 sm:grid-cols-3" data-revelar>
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
                       class="pulsable min-h-11 rounded-xl border border-linea px-4 py-2.5 text-sm text-tenue hover:text-fuerte">Limpiar</a>
                @endif
            </div>
        </form>

        @if ($artistas->isEmpty())
            <div class="revelar tarjeta mt-8 p-12 text-center" data-revelar>
                <p class="font-display text-lg font-semibold">No hay artistas con ese filtro</p>
                <p class="mt-2 text-sm text-tenue">Prueba con otro género o tipo.</p>
            </div>
        @else
            <div class="revelar mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" data-revelar>
                @foreach ($artistas as $artista)
                    <article @class([
                        'tarjeta tarjeta-hover tarjeta-pulsable group flex flex-col overflow-hidden',
                        'sm:col-span-2 sm:flex-row lg:col-span-2' => $loop->first,
                    ])>
                        <a href="{{ route('artistas.show', $artista) }}" @class([
                            'flex flex-1 flex-col',
                            'sm:flex-row' => $loop->first,
                        ])>
                            @if ($artista->foto)
                                <img src="{{ Storage::disk('public')->url($artista->foto) }}" alt=""
                                     loading="lazy" decoding="async" width="400" height="400"
                                     style="view-transition-name: portada-artista-{{ $artista->id }}"
                                     @class([
                                         'imagen-viva w-full object-cover',
                                         'aspect-square' => ! $loop->first,
                                         'aspect-[4/3] sm:aspect-auto sm:w-[42%] sm:shrink-0' => $loop->first,
                                     ])>
                            @endif

                            <div @class([
                                'flex flex-1 flex-col p-5',
                                'sm:p-7' => $loop->first,
                            ])>
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                        {{ $artista->tipo->getLabel() }}
                                    </span>
                                    @if ($artista->tieneVideo())
                                        <span class="inline-flex items-center gap-1 rounded-full border border-linea px-2.5 py-1 text-tenue"><svg class="size-[.85em]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 4.5 19.5 12 6 19.5Z"/></svg>Con video</span>
                                    @endif
                                </div>

                                <h2 class="mt-3 font-display text-lg font-semibold">{{ $artista->nombre }}</h2>
                                <p class="mt-1 text-sm text-acento">{{ $artista->genero_musical }}</p>

                                <p class="mt-3 line-clamp-2 flex-1 text-sm leading-relaxed text-tenue">
                                    {{ $artista->descripcion }}
                                </p>

                                <div class="mt-4 flex items-end justify-between gap-3">
                                    <div>
                                        {{-- OBS3-08: la tarifa NO sale. «De pronto no lo contacto
                                             porque se sesga de una vez con el precio» (R21 14:01);
                                             «yo no le pondría precio» (R21 14:37). El campo sigue en
                                             el modelo y en el panel, pero no se pinta nunca. --}}
                                        <p class="text-[.65rem] uppercase tracking-wide text-apagado">Tarifa</p>
                                        <p class="font-display text-base font-semibold">
                                            {{ ajuste('artistas_tarifa_leyenda') }}
                                        </p>
                                    </div>
                                    <span class="text-sm font-medium text-acento">Ver ficha&nbsp;<x-publico.flecha /></span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $artistas->links() }}</div>
        @endif

        <section class="revelar tarjeta-escena vidrio mt-16 rounded-[1.75rem] p-8 text-center" data-revelar>
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
