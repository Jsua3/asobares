@props([
    'variante' => 'primaria',
    'href' => null,
    'tipo' => 'submit',
])

@php
    /*
     * Un solo portador para los 43 botones de acción del sitio. Antes la
     * cadena del submit primario estaba repetida idéntica ocho veces y la
     * utilidad de fundido de color aparecía en 18 de 34: dos botones
     * iguales se comportaban distinto al pasar el ratón.
     *
     * `.pulsable` llega de fábrica: es el acuse de pulsación que el proyecto
     * no tenía en ningún sitio, y en táctil es el único que existe. Ya no
     * lleva la utilidad de fundido de color aparte (`duration-(--duracion-
     * boton) ease-color` sobre la clase que Tailwind genera para animar
     * color, fondo y borde): esa utilidad compila en `@layer utilities`,
     * que en Tailwind 4 gana siempre a `@layer components` sin importar
     * especificidad, así que pisaba la transición que `.pulsable` declara
     * en `app.css` (incluida su `transition-duration: 0ms` del `:active`).
     * El color viaja ahora dentro de `.pulsable`, no como utilidad aparte —
     * la guardia de abajo impide que esa clase vuelva a este archivo.
     */
    $base = 'inline-block rounded-xl px-6 py-3 text-center text-sm font-semibold pulsable';

    $estilos = match ($variante) {
        'contorno' => 'border border-linea-fuerte text-tinta hover:border-marca-500/50 hover:bg-superficie-alta',
        // Para fondos oscuros, como el video de la portada: el contorno de
        // tinta era un botón invisible sobre negro. El color va en el
        // portador `.contorno-claro` de app.css y no en utilidades: el blanco
        // fijo no sigue al tema y la guardia de tema lo prohíbe aquí.
        'contorno-claro' => 'border contorno-claro',
        default => 'cta-vivo bg-marca-500 text-white hover:bg-marca-600',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "{$base} {$estilos}"]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $tipo, 'class' => "{$base} {$estilos}"]) }}>
        {{ $slot }}
    </button>
@endif
