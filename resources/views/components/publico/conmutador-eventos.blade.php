@props([
    'activo' => 'proximos',
    'mes' => null,
    'totalProximos' => 0,
    'totalPasados' => 0,
])

@php
    /*
     * Tres destinos y no dos: el calendario es una vista HERMANA de Próximos y
     * Pasados, no un filtro dentro de ellas. Un mes concreto no tiene
     * «próximos» ni «pasados» — el mes ya es el filtro —, así que
     * `?cuando=` no significa nada dentro de él y no se arrastra.
     *
     * El componente existe porque estos segmentos los pintan DOS páginas. Con
     * el bloque copiado, el día que alguien retoque la pastilla activa en una,
     * la otra se queda con la anterior y nadie lo nota: es la misma deuda que
     * el conmutador Tarjetas/Mapa del directorio todavía tiene abierta.
     */
    $referencia = $mes ?? now();

    $segmentos = [
        'proximos' => [
            'texto' => "Próximos ({$totalProximos})",
            'url' => route('eventos.index', ['cuando' => 'proximos']),
        ],
        'pasados' => [
            'texto' => "Pasados ({$totalPasados})",
            'url' => route('eventos.index', ['cuando' => 'pasados']),
        ],
        'calendario' => [
            'texto' => 'Calendario',
            'url' => route('eventos.calendario', [$referencia->year, $referencia->format('m')]),
        ],
    ];
@endphp

{{--
    `flex-wrap` y no `inline-flex` a secas: con tres segmentos —«Próximos (3)»,
    «Pasados (3)» y «Calendario»— el grupo mide 334 px, y a 320 px de pantalla
    el contenido dispone de 288. Medido: la página se desplazaba en horizontal
    en `/eventos` y en las dos rutas del calendario. Un `inline-flex` no
    envuelve, así que el tercer segmento empujaba la caja fuera del viewport.
    Envolviendo, a 320 px cae a un segundo renglón dentro del mismo marco y a
    partir de ahí sigue en una sola línea.
--}}
<div class="inline-flex max-w-full flex-wrap rounded-xl border border-linea p-1" role="group" aria-label="Cambiar la vista de eventos">
    @foreach ($segmentos as $clave => $segmento)
        {{--
            `.pulsable` y ni una utilidad de movimiento al lado: en Tailwind 4
            la capa `utilities` gana siempre a `components`, así que una
            `transition-colors` suelta aquí pisaría la transición completa del
            portador —incluido su `transition-duration: 0ms` del `:active`— y
            el acuse al pulsar moriría sin que nada fallara.

            `min-h-11` es geometría medida: con `py-2` el segmento daba 37,7 px
            de alto, por debajo del objetivo táctil de 44.
        --}}
        <a href="{{ $segmento['url'] }}"
           @class([
               'pulsable inline-flex min-h-11 items-center rounded-lg px-5 text-sm',
               'bg-marca-500 font-medium text-white' => $activo === $clave,
               'text-tenue hover:text-fuerte' => $activo !== $clave,
           ])
           {{-- El nombre de transición viaja con la pastilla roja, así que al
                navegar entre las tres vistas se desliza de un segmento al
                siguiente en vez de parpadear. Es el mismo nombre que ya usan
                los chips del boletín, de proveedores y de la guía: sólo hay un
                elemento activo por documento, así que no se pisa con nadie. --}}
           @if ($activo === $clave) aria-current="true" style="view-transition-name: filtro-activo" @endif>
            {{ $segmento['texto'] }}
        </a>
    @endforeach
</div>
