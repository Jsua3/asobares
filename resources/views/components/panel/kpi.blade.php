@props([
    'etiqueta',
    'valor',
    'url' => null,
    'delta' => null,
    'detalle' => null,
    'icono' => null,
    'n' => null,
])

@php
    // El umbral se comparte con el observatorio: si divergieran, la misma
    // cifra sería «muestra pequeña» en una tarjeta y suficiente en la
    // gráfica de al lado. Se rotula para no presentar ruido como tendencia
    // — con 60 asociados, casi todas las series del gremio caen aquí en
    // 2026, y la interfaz tiene que decirlo.
    $muestraChica = $n !== null && $n < \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA;
    $sube = $delta !== null && $delta >= 0;
    $deltaTexto = $delta === null
        ? null
        : ($sube ? '+' : '−').number_format(abs((float) $delta), 1, ',', '.').' %';
@endphp

<x-panel.vidrio :hover="filled($url)" class="p-5">
    <div class="flex items-start justify-between gap-3">
        <p class="antetitulo text-tenue">{{ $etiqueta }}</p>

        @if ($icono)
            <x-filament::icon :icon="$icono" class="h-5 w-5 text-acento" />
        @endif
    </div>

    {{--
        La cifra se pinta de una. El conteo animado que vivía aquí retrasaba
        600 ms la lectura de lo único que la página existe para enseñar, y
        además solo contaba en las tarjetas cuyo ajuste era parseable: en la
        misma fila, unas contaban y otras no.
    --}}
    <p class="mt-3 font-display text-3xl font-bold tracking-tight text-fuerte">{{ $valor }}</p>

    @if ($deltaTexto)
        <p @class([
            'mt-1 flex items-center gap-1 text-sm font-medium',
            'text-exito' => $sube,
            'text-acento' => ! $sube,
        ])>
            <x-filament::icon
                :icon="$sube ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down'"
                class="h-4 w-4"
            />
            {{ $deltaTexto }}
            <span class="text-tenue font-normal">vs. mes anterior</span>
        </p>
    @endif

    @if ($detalle)
        <p class="mt-2 text-sm text-tenue">{{ $detalle }}</p>
    @endif

    @if ($n !== null)
        <p @class(['mt-2 text-xs', $muestraChica ? 'text-aviso' : 'text-apagado'])>
            n = {{ $n }}@if ($muestraChica) · muestra pequeña @endif
        </p>
    @endif

    @if ($url)
        <a href="{{ $url }}" class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-acento hover:text-acento-fuerte">
            Ver detalle
            <x-filament::icon icon="heroicon-o-arrow-right" class="h-4 w-4" />
        </a>
    @endif
</x-panel.vidrio>
