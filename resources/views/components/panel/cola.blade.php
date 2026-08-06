@props([
    'etiqueta',
    'url',
    'icono' => 'heroicon-o-clock',
    'antiguedad' => null,
    'urgente' => false,
    'accion' => 'Revisar',
])

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
        <x-filament::icon :icon="$icono" class="h-5 w-5" />
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
        class="shrink-0 rounded-lg border border-linea px-3 py-1.5 text-sm font-medium text-tinta transition-colors hover:border-acento hover:text-acento"
    >
        {{ $accion }}
    </a>
</div>
