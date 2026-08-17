@php
    $puntos = $vista === 'mapa'
        ? $asociados->filter->tieneUbicacion()->map(fn ($a) => [
            'lat' => $a->lat,
            'lng' => $a->lng,
            'nombre' => $a->nombre,
            'html' => '<strong>'.e($a->nombre).'</strong><br>'
                .e($a->categoria->nombre).' · '.e($a->municipio->nombre).'<br>'
                .'<a href="'.route('directorio.show', $a).'">Ver ficha</a>',
        ])->values()->all()
        : [];
@endphp

<x-layouts.publico titulo="Directorio de establecimientos — ASOBARES Quindío"
                   descripcion="Bares, gastrobares, cafés y discotecas afiliados al gremio en Armenia, Salento, Filandia y todo el Quindío.">

    <x-publico.hero titulo="Directorio de establecimientos" compacto
                    subtitulo="Los bares, gastrobares, cafés y discotecas que hacen parte del gremio en el Quindío." />

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Filtros: GET para que la URL se pueda compartir --}}
        <form method="GET" action="{{ route('directorio.index') }}"
              class="tarjeta grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
            <input type="hidden" name="vista" value="{{ $vista }}">

            <x-publico.campo nombre="q" etiqueta="Buscar por nombre" placeholder="Ej.: La Cava"
                             :valor="$filtros['q'] ?? null" />

            <x-publico.campo nombre="municipio" etiqueta="Municipio" tipo="select"
                             :valor="$filtros['municipio'] ?? null"
                             :opciones="['' => 'Todos los municipios'] + $municipios->pluck('nombre', 'slug')->all()" />

            <x-publico.campo nombre="categoria" etiqueta="Categoría" tipo="select"
                             :valor="$filtros['categoria'] ?? null"
                             :opciones="['' => 'Todas las categorías'] + $categorias->pluck('nombre', 'slug')->all()" />

            <div class="flex items-end gap-2">
                <x-publico.boton class="flex-1">
                    Filtrar
                </x-publico.boton>
                @if (array_filter($filtros ?? []))
                    <a href="{{ route('directorio.index') }}"
                       class="rounded-xl border border-linea px-4 py-2.5 text-sm text-tenue hover:text-fuerte">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>

        {{-- Cambio de vista --}}
        <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-tenue">
                @if ($vista === 'mapa')
                    {{ $asociados->count() }} {{ Str::plural('establecimiento', $asociados->count()) }} en el mapa
                @else
                    {{ $asociados->total() }} {{ Str::plural('establecimiento', $asociados->total()) }}
                @endif
            </p>

            <div class="inline-flex rounded-xl border border-linea p-1" role="group" aria-label="Cambiar vista">
                @foreach (['grid' => 'Tarjetas', 'mapa' => 'Mapa'] as $clave => $texto)
                    <a href="{{ request()->fullUrlWithQuery(['vista' => $clave, 'page' => null]) }}"
                       @class([
                           'rounded-lg px-4 py-1.5 text-sm transition-colors',
                           'bg-marca-500 font-medium text-white' => $vista === $clave,
                           'text-tenue hover:text-fuerte' => $vista !== $clave,
                       ])
                       @if ($vista === $clave) aria-current="true" @endif>
                        {{ $texto }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Resultados --}}
        @if ($vista === 'mapa')
            <x-publico.mapa :puntos="$puntos" class="mt-6" />
            @if ($puntos === [])
                <p class="mt-4 text-center text-sm text-apagado">
                    Ningún establecimiento de este filtro tiene ubicación registrada.
                </p>
            @endif
        @elseif ($asociados->isEmpty())
            <div class="tarjeta mt-6 p-12 text-center">
                <p class="font-display text-lg font-semibold">No encontramos establecimientos con ese filtro</p>
                <p class="mt-2 text-sm text-tenue">Prueba con otro municipio o limpia la búsqueda.</p>
                <a href="{{ route('directorio.index') }}"
                   class="mt-5 inline-block rounded-xl border border-linea-fuerte px-5 py-2.5 text-sm hover:border-marca-500/50">
                    Ver todos
                </a>
            </div>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($asociados as $asociado)
                    <x-publico.tarjeta-asociado :asociado="$asociado" />
                @endforeach
            </div>

            <div class="mt-10">{{ $asociados->links() }}</div>
        @endif
    </div>
</x-layouts.publico>
