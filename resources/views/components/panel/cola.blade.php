@props([
    'etiqueta',
    'url',
    'icono' => 'heroicon-o-clock',
    'antiguedad' => null,
    'urgente' => false,
    'accion' => 'Revisar',
])

@php
    // Urgente cambia el icono de verdad, no solo su color: es el segundo
    // canal no cromático que el comentario de abajo promete.
    $iconoMostrado = $urgente ? 'heroicon-o-exclamation-triangle' : $icono;
@endphp

{{--
    Fila de la cola de pendientes. Lo urgente se distingue por icono y por
    rótulo, no solo por color: el estado activo del selector de tema del sitio
    ya incumplió WCAG 1.4.1 una vez por confiar únicamente en el color.
--}}
<div class="flex items-center gap-4 border-b border-linea py-3 last:border-0">
    <span @class([
        'flex h-9 w-9 shrink-0 items-center justify-center rounded-full',
        'bg-aviso-fondo text-aviso' => $urgente,
        'bg-marca-panel text-acento' => ! $urgente,
    ])>
        <x-filament::icon :icon="$iconoMostrado" class="h-5 w-5" />
    </span>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-medium text-tinta">
            {{ $etiqueta }}
            @if ($urgente)
                <span class="ml-1 text-xs font-semibold uppercase tracking-wide text-aviso">· Urgente</span>
            @endif
        </p>

        @if ($antiguedad)
            <p class="mt-0.5 text-xs text-tenue">{{ $antiguedad }}</p>
        @endif
    </div>

    <a
        href="{{ $url }}"
        {{-- El área crece, el dibujo no. Con `py-1.5` la pastilla mide 34 px de
             alto, y en el teléfono esta es la acción principal del tablero: se
             pulsa con el pulgar sobre una lista. El `::after` la lleva a 46 sin
             engordar el borde, que es el mismo recurso que usa «Afíliate» en la
             barra pública (34 -> 45 medidos). 6 px por lado y no 8 a propósito:
             las filas de la cola van una debajo de otra y un área más generosa
             empezaría a robarle el toque a la vecina. --}}
        class="relative shrink-0 rounded-lg border border-linea px-3 py-1.5 text-sm font-medium text-tinta transition-colors after:absolute after:inset-x-0 after:-inset-y-1.5 after:content-[''] hover:border-acento hover:text-acento"
    >
        {{ $accion }}
    </a>
</div>
