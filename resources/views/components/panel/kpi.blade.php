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
        El conteo animado sube desde cero al entrar en pantalla. `x-intersect`
        evita animar tarjetas que nadie está mirando, y quien pidió menos
        movimiento ve el valor final de una.
    --}}
    <p
        class="mt-3 font-display text-3xl font-bold tracking-tight text-fuerte"
        x-data="{
            mostrado: @js((string) $valor),
            animar() {
                const crudo = @js((string) $valor);
                const destino = Number(crudo.replace(/[^0-9,-]/g, '').replace(',', '.'));

                if (! Number.isFinite(destino) || destino === 0) return;
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                const inicio = performance.now();
                const paso = (ahora) => {
                    const avance = Math.min((ahora - inicio) / 600, 1);
                    const valor = Math.round(destino * (1 - Math.pow(1 - avance, 3)));
                    this.mostrado = crudo.replace(/[0-9.,]+/, valor.toLocaleString('es-CO'));
                    if (avance < 1) requestAnimationFrame(paso);
                    else this.mostrado = crudo;
                };
                requestAnimationFrame(paso);
            },
        }"
        x-intersect.once="animar()"
        x-text="mostrado"
    >{{ $valor }}</p>

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
