@props(['cifrasDelGremio', 'cifrasDelGremioActualizadas'])

@php
    $cifras = [
        ['valor' => ajuste('cifra_empleo'), 'texto' => ajuste('cifra_empleo_detalle')],
        ['valor' => ajuste('cifra_ingreso'), 'texto' => ajuste('cifra_ingreso_detalle')],
        ['valor' => ajuste('cifra_informalidad'), 'texto' => ajuste('cifra_informalidad_detalle')],
        ['valor' => ajuste('cifra_jovenes'), 'texto' => ajuste('cifra_jovenes_detalle')],
    ];
@endphp

<section class="home-editorial-cifras revelar border-b border-linea" data-revelar aria-labelledby="cifras">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-14 lg:px-8">
        <p class="home-editorial-kicker">{{ ajuste('portada_cifras_titulo') }}</p>
        <h2 id="cifras" class="sr-only">{{ ajuste('portada_cifras_titulo') }}</h2>
        <dl class="mt-8 grid grid-cols-2 gap-x-6 gap-y-8 sm:gap-x-10 lg:grid-cols-4">
            @foreach ($cifras as $cifra)
                <div class="home-editorial-cifra">
                    <dt class="font-display text-3xl font-bold tracking-tight text-acento sm:text-4xl lg:text-5xl">{{ $cifra['valor'] }}</dt>
                    <dd class="mt-2 max-w-[14rem] text-sm leading-relaxed text-tenue">{{ $cifra['texto'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

@if ($cifrasDelGremio->isNotEmpty())
    <section class="home-editorial-cifras-gremio border-b border-linea" aria-labelledby="cifras-gremio">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
                <h2 id="cifras-gremio" class="home-editorial-kicker">{{ ajuste('portada_gremio_titulo') }}</h2>
                @if ($cifrasDelGremioActualizadas !== null)
                    <p class="text-xs text-apagado">
                        Actualizado el {{ $cifrasDelGremioActualizadas->translatedFormat('d \d\e F \d\e Y') }}
                    </p>
                @endif
            </div>
            <dl class="mt-6 grid grid-cols-2 gap-6 lg:grid-cols-4">
                @foreach ($cifrasDelGremio as $cifra)
                    <div class="home-editorial-cifra">
                        <dt class="font-display text-2xl font-bold text-acento sm:text-3xl">{{ $cifra['valor'] }}</dt>
                        <dd class="mt-1.5 text-xs leading-relaxed text-tenue sm:text-sm">{{ $cifra['texto'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
@endif
