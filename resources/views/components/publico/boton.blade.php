@props([
    'variante' => 'primaria',
    'href' => null,
    'tipo' => 'submit',
])

@php
    /*
     * Portador único de los botones de acción del sitio: la variante decide
     * las clases, así que dos botones iguales no pueden comportarse distinto.
     *
     * `.pulsable` llega de fábrica: es el acuse de pulsación, y en táctil el
     * único que existe. No lleva la utilidad de fundido de color aparte
     * (`duration-(--duracion-
     * boton) ease-color` sobre la clase que Tailwind genera para animar
     * color, fondo y borde): esa utilidad compila en `@layer utilities`,
     * que en Tailwind 4 gana siempre a `@layer components` sin importar
     * especificidad, así que pisaría la transición que `.pulsable` declara
     * en `app.css` (incluida su `transition-duration: 0ms` del `:active`).
     * El color viaja dentro de `.pulsable`, no como utilidad aparte, y
     * `MovimientoTest` impide que esa clase vuelva a este archivo.
     */
    $base = 'inline-block rounded-xl px-6 py-3 text-center text-sm font-semibold pulsable';

    $estilos = match ($variante) {
        'contorno' => 'border border-linea-fuerte text-tinta hover:border-marca-500/50 hover:bg-superficie-alta',
        // Para fondos oscuros, como el video de la portada: un contorno de
        // tinta sería invisible sobre negro. El color va en el
        // portador `.contorno-claro` de app.css y no en utilidades: el blanco
        // fijo no sigue al tema y la guardia de tema lo prohíbe aquí.
        'contorno-claro' => 'border contorno-claro',
        // El relleno, el hover y la tinta viven en `.cta-vivo` (app.css) y no
        // en utilidades, por lo mismo que `contorno-claro`: al pulsar el botón
        // se vidria —el relleno se retira y deja ver lo de detrás— y eso pide un
        // fondo que dependa del estado. Una utilidad `bg-*` de
        // `@layer utilities` gana siempre a `@layer components` y lo pisaría.
        default => 'cta-vivo text-white',
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
