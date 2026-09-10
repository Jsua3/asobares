@props([
    'filtros',
    'municipios',
    'categorias',
    'vista',
    'hayFiltros',
    'listado',
])

{{-- GET para que la URL se pueda compartir. El action y el #resultados viven
     en la vista; aquí solo van los campos que se reutilizan en escritorio y
     en la hoja móvil. --}}
<form method="GET" action="{{ route('directorio.index') }}#resultados" {{ $attributes }}>
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
        @if ($hayFiltros)
            <a href="{{ $listado }}"
               class="pulsable min-h-11 rounded-xl border border-linea px-4 py-2.5 text-sm text-tenue hover:text-fuerte">
                Limpiar
            </a>
        @endif
    </div>
</form>
