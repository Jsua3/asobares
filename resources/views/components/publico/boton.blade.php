@props([
    'variante' => 'primaria',
    'href' => null,
    'tipo' => 'submit',
])

@php
    /*
     * Un solo portador para los 43 botones de acción del sitio. Antes la
     * cadena del submit primario estaba repetida idéntica ocho veces y la
     * `transition-colors` aparecía en 18 de 34: dos botones iguales se
     * comportaban distinto al pasar el ratón.
     *
     * `.pulsable` llega de fábrica: es el acuse de pulsación que el proyecto
     * no tenía en ningún sitio, y en táctil es el único que existe.
     */
    $base = 'inline-block rounded-xl px-6 py-3 text-center text-sm font-semibold pulsable transition-colors duration-(--duracion-boton) ease-color';

    $estilos = match ($variante) {
        'contorno' => 'border border-linea-fuerte text-tinta hover:border-marca-500/50 hover:bg-superficie-alta',
        default => 'bg-marca-500 text-white hover:bg-marca-600',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "{$base} {$estilos}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $tipo }}" {{ $attributes->merge(['class' => "{$base} {$estilos}"]) }}>
        {{ $slot }}
    </button>
@endif
