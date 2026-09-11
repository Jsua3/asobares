@props(['cifrasDelGremio', 'cifrasDelGremioActualizadas'])

@php
    $cifras = [
        [
            'valor' => ajuste('cifra_empleo'),
            'texto' => ajuste('cifra_empleo_detalle'),
            'icono' => 'empleo',
        ],
        [
            'valor' => ajuste('cifra_ingreso'),
            'texto' => ajuste('cifra_ingreso_detalle'),
            'icono' => 'ingreso',
        ],
        [
            'valor' => ajuste('cifra_informalidad'),
            'texto' => ajuste('cifra_informalidad_detalle'),
            'icono' => 'informalidad',
        ],
        [
            'valor' => ajuste('cifra_jovenes'),
            'texto' => ajuste('cifra_jovenes_detalle'),
            'icono' => 'jovenes',
        ],
    ];
@endphp

<section class="home-editorial-cifras revelar" data-revelar aria-labelledby="cifras">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="home-editorial-cifras__cabecera">
            <p class="home-editorial-eyebrow">{{ ajuste('portada_cifras_titulo') }}</p>
            <h2 id="cifras" class="home-editorial-titulo mt-2 text-balance">
                {{ ajuste('portada_cifras_subtitulo', 'La noche también mueve la economía.') }}
            </h2>
        </div>

        <dl class="home-editorial-cifras__banda mt-6">
            @foreach ($cifras as $indice => $cifra)
                @if ($indice > 0)
                    <div class="home-editorial-cifras__divisor hidden lg:block" aria-hidden="true"></div>
                @endif
                <div class="home-editorial-cifra">
                    <dt class="flex items-start gap-3">
                        <span class="home-editorial-cifra__icono" aria-hidden="true">
                            @switch($cifra['icono'])
                                @case('empleo')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                    @break
                                @case('ingreso')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    @break
                                @case('informalidad')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                                    @break
                                @default
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                            @endswitch
                        </span>
                        <span>
                            <span class="home-editorial-cifra__valor font-display font-bold text-acento">{{ $cifra['valor'] }}</span>
                            <span class="home-editorial-cifra__detalle mt-1 block text-sm text-tenue">{{ $cifra['texto'] }}</span>
                        </span>
                    </dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>

@if ($cifrasDelGremio->isNotEmpty())
    <section class="home-editorial-cifras-gremio" aria-labelledby="cifras-gremio">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-1">
                <h2 id="cifras-gremio" class="home-editorial-eyebrow">{{ ajuste('portada_gremio_titulo') }}</h2>
                @if ($cifrasDelGremioActualizadas !== null)
                    <p class="text-xs text-apagado">
                        Actualizado el {{ $cifrasDelGremioActualizadas->translatedFormat('d \d\e F \d\e Y') }}
                    </p>
                @endif
            </div>
            <dl class="home-editorial-cifras__banda home-editorial-cifras__banda--gremio mt-4">
                @foreach ($cifrasDelGremio as $indice => $cifra)
                    @if ($indice > 0)
                        <div class="home-editorial-cifras__divisor hidden lg:block" aria-hidden="true"></div>
                    @endif
                    <div class="home-editorial-cifra">
                        <dt>
                            <span class="home-editorial-cifra__valor font-display text-xl font-bold text-acento sm:text-2xl">{{ $cifra['valor'] }}</span>
                            <span class="home-editorial-cifra__detalle mt-1 block text-xs text-tenue sm:text-sm">{{ $cifra['texto'] }}</span>
                        </dt>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
@endif
