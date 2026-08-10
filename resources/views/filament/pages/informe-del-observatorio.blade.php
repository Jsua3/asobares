<x-filament-panels::page>
    @php
        $indicadores = $this->indicadores();
        $series = $this->series();
        $sinMuestra = $this->indicadoresSinMuestra();
    @endphp

    {{--
        Ver `theme.css` (@media print): la barra lateral, la barra superior y
        este botón desaparecen al imprimir. `window.print()` abre el mismo
        diálogo que Ctrl/Cmd+P — no hay descarga de archivo real, el PDF lo
        arma el navegador.
    --}}
    <div class="print:hidden">
        <x-filament::button color="gray" icon="heroicon-o-printer" tag="button" type="button" x-on:click="window.print()">
            Imprimir o guardar como PDF
        </x-filament::button>
    </div>

    <div class="informe-bloque mt-6 flex flex-col gap-4 border-b border-linea pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <x-filament-panels::logo />

            <div>
                <p class="font-display text-lg font-bold text-fuerte">
                    {{ \Filament\Facades\Filament::getBrandName() }}
                </p>
                <p class="text-sm text-tenue">Observatorio del gremio — informe institucional</p>
            </div>
        </div>

        <p class="text-sm text-tenue">Generado el {{ $this->generadoEl() }}</p>
    </div>

    <div class="informe-bloque mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @foreach ($indicadores as $indicador)
            <div class="rounded-xl border border-linea p-4">
                <p class="text-xs text-tenue">{{ $indicador['etiqueta'] }}</p>
                <p class="mt-1 font-display text-xl font-bold text-fuerte">{{ $indicador['valor'] }}</p>
                <p @class([
                    'mt-1 text-xs',
                    'text-apagado' => $indicador['serie']->hayMuestraSuficiente(),
                    'text-aviso' => ! $indicador['serie']->hayMuestraSuficiente(),
                ])>
                    {{ $indicador['serie']->rotuloDeMuestra() }}
                </p>
            </div>
        @endforeach
    </div>

    {{--
        Cada serie se dibuja como tabla, no como el `ChartWidget` que ya
        existe para ella en `Observatorio` (mismo dato, mismo `MetricasDelObservatorio`).
        Un `ChartWidget` renderiza sobre `<canvas>` vía Chart.js, y Chart.js
        dibuja ese canvas después de que el navegador ya calculó el layout —
        justo el paso que un motor de impresión puede saltarse u ordenar de
        otra forma. El riesgo real es una gráfica en blanco en el papel que
        la dirección lleva a una alcaldía, y una tabla de HTML no depende de
        ningún ciclo de render en JavaScript: lo que compone la página es lo
        que sale impreso.

        `data-serie` en cada `<section>` es el identificador estable de esa
        sección (ver el docblock de `InformeDelObservatorio::series()`): no
        cambia si se retoca el título visible, así que sigue sirviendo para
        ubicarla en las pruebas aunque el rótulo se edite.
    --}}
    <div class="mt-8 space-y-8">
        @foreach ($series as $item)
            <section class="informe-bloque" data-serie="{{ $item['clave'] }}">
                <h2 class="text-base text-fuerte">
                    {{ $item['titulo'] }}
                    <span @class([
                        'ml-2 text-sm font-normal',
                        'text-apagado' => $item['serie']->hayMuestraSuficiente(),
                        'text-aviso' => ! $item['serie']->hayMuestraSuficiente(),
                    ])>
                        ({{ $item['serie']->rotuloDeMuestra() }})
                    </span>
                </h2>

                @if ($item['serie']->estaVacia())
                    <p class="mt-2 text-sm text-tenue">Todavía no hay datos que mostrar.</p>
                @elseif (! $item['serie']->hayMuestraSuficiente())
                    {{-- El rótulo que decide, no el `n` combinado: en una serie que
                         cruza medidas independientes el total puede estar muy por
                         encima del umbral y aun así no alcanzar, y ponerlo al lado
                         del mínimo se leía como una contradicción. --}}
                    <p class="mt-2 text-sm text-aviso">
                        Esta cifra todavía no alcanza muestra suficiente
                        ({{ $item['serie']->rotuloDeLaMuestraQueDecide() }}, mínimo
                        {{ \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA }}) para sostener
                        {{ $item['que'] }}. La tabla queda como referencia, no como afirmación.
                    </p>
                @endif

                @unless ($item['serie']->estaVacia())
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="border-b border-linea text-left text-tenue">
                                    <th class="py-1 pr-4 font-medium"></th>
                                    @foreach (array_keys($item['serie']->series) as $clave)
                                        <th class="py-1 pr-4 font-medium">{{ $clave }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item['serie']->etiquetas as $indice => $etiqueta)
                                    <tr class="border-b border-linea">
                                        <td class="py-1 pr-4 text-tinta">{{ $etiqueta }}</td>
                                        @foreach ($item['serie']->series as $clave => $valores)
                                            <td class="py-1 pr-4 text-tinta">
                                                {{ $this->formatearCelda($clave, $valores[$indice] ?? 0) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endunless
            </section>
        @endforeach
    </div>

    <div class="informe-bloque mt-8 rounded-xl border border-aviso-linea bg-aviso-fondo p-4">
        <h2 class="text-sm text-aviso-suave">Descargo sobre el tamaño de muestra</h2>

        <p class="mt-2 text-sm text-tenue">
            Toda cifra de este informe trae su n: el número real de registros que la sostiene.
            El observatorio marca una muestra como suficiente a partir de
            {{ \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA }} observaciones; por debajo de ese
            umbral, una cifra no aguanta una afirmación todavía.
        </p>

        @if ($sinMuestra !== [])
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-aviso-suave">
                @foreach ($sinMuestra as $item)
                    <li>{{ $item['titulo'] }}: {{ $item['serie']->rotuloDeMuestra() }}, todavía sin muestra suficiente.</li>
                @endforeach
            </ul>
        @else
            <p class="mt-3 text-sm text-exito">Hoy todos los indicadores de este informe alcanzan muestra suficiente.</p>
        @endif
    </div>
</x-filament-panels::page>
