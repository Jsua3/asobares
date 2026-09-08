<x-layouts.publico titulo="Artistas del Quindío — ASOBARES Quindío"
                   descripcion="Directorio de artistas con sus contactos, exclusivo para establecimientos afiliados.">

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        <header>
            <a href="{{ route('mi-cuenta.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
                <x-publico.flecha direccion="izquierda" />&nbsp;Mi cuenta
            </a>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight">Artistas del Quindío</h1>
            <p class="mt-1.5 max-w-2xl text-sm text-tenue">
                Los datos de contacto son un beneficio de tu afiliación: la ficha pública del artista
                muestra su trabajo, pero no su teléfono.
            </p>
        </header>

        {{-- Filtro por tipo de artista --}}
        <div class="mt-8 flex flex-wrap gap-2">
            <a href="{{ route('mi-cuenta.artistas.index') }}"
               @class([
                   'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                   'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => empty($filtros['tipo']),
                   'border-linea text-suave hover:border-marca-500/40' => ! empty($filtros['tipo']),
               ])>Todos</a>

            @foreach ($tipos as $tipo)
                <a href="{{ route('mi-cuenta.artistas.index', ['tipo' => $tipo->value]) }}"
                   @class([
                       'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                       'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => ($filtros['tipo'] ?? null) === $tipo->value,
                       'border-linea text-suave hover:border-marca-500/40' => ($filtros['tipo'] ?? null) !== $tipo->value,
                   ])>{{ $tipo->getLabel() }}</a>
            @endforeach
        </div>

        @if ($generos->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($generos as $genero)
                    <a href="{{ route('mi-cuenta.artistas.index', array_filter(['tipo' => $filtros['tipo'] ?? null, 'genero' => $genero])) }}"
                       @class([
                           'pulsable inline-flex min-h-11 items-center rounded-xl border px-3.5 text-2xs uppercase tracking-wide',
                           'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => ($filtros['genero'] ?? null) === $genero,
                           'border-linea text-apagado hover:border-marca-500/40' => ($filtros['genero'] ?? null) !== $genero,
                       ])>{{ $genero }}</a>
                @endforeach
            </div>
        @endif

        @if ($artistas->isEmpty())
            <div class="tarjeta mt-8 p-12 text-center">
                <p class="font-display text-lg font-semibold">No hay artistas en esta selección</p>
                <p class="mt-2 text-sm text-tenue">Estamos ampliando la base.</p>
            </div>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($artistas as $artista)
                    <article class="tarjeta tarjeta-hover flex flex-col overflow-hidden">
                        @if ($artista->foto)
                            <img src="{{ Storage::disk('public')->url($artista->foto) }}" alt="{{ $artista->nombre }}"
                                 width="400" height="300" loading="lazy" decoding="async"
                                 class="aspect-[4/3] w-full object-cover">
                        @endif

                        <div class="flex flex-1 flex-col p-6">
                            <h2 class="font-display text-base font-semibold">
                                <a href="{{ route('artistas.show', $artista) }}" class="enlace-accion hover:text-acento">
                                    {{ $artista->nombre }}
                                </a>
                            </h2>

                            <p class="mt-1 text-xs text-acento">
                                {{ $artista->tipo->getLabel() }}@if ($artista->genero_musical) · {{ $artista->genero_musical }}@endif
                            </p>

                            @if ($artista->municipio)
                                <p class="mt-1 text-xs text-apagado">{{ $artista->municipio->nombre }}</p>
                            @endif

                            <p class="mt-3 flex-1 text-sm leading-relaxed text-tenue">{{ $artista->descripcion }}</p>

                            {{-- La tarifa NO se muestra tampoco aquí: «la tarifa del artista no se
                                 publica» es decisión del 28 de agosto y el campo sigue en el modelo
                                 solo para la secretaría. Se enseña la misma leyenda editable que la
                                 ficha pública. --}}
                            <p class="mt-3 text-2xs text-apagado">{{ ajuste('artistas_tarifa_leyenda') }}</p>

                            <div class="mt-5 space-y-2">
                                @if ($enlace = enlaceWhatsapp($artista->whatsapp, "Hola {$artista->nombre}, los vi en el directorio de artistas de ASOBARES Quindío."))
                                    <x-publico.boton :href="$enlace" target="_blank" rel="noopener nofollow" class="w-full">
                                        WhatsApp
                                    </x-publico.boton>
                                @endif
                                @if ($artista->instagram_url)
                                    <a href="{{ $artista->instagram_url }}" target="_blank" rel="noopener nofollow"
                                       class="pulsable block min-h-11 wrap-anywhere rounded-xl border border-linea px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
                                        Instagram&nbsp;<x-publico.flecha direccion="externa" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $artistas->links() }}</div>
        @endif
    </div>
</x-layouts.publico>
