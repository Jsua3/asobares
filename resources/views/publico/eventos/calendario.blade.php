@php
    $tituloMes = Str::ucfirst($mes->translatedFormat('F \d\e Y'));

    /*
     * La agenda de móvil se pinta del MISMO `$porDia` que la rejilla, sin una
     * consulta más, pero sólo con los días DEL MES: los colgantes se entienden
     * como relleno dentro de una cuadrícula y en una lista vertical serían dos
     * fechas de agosto coladas entre las de septiembre.
     *
     * Y sólo los días que tienen algo: treinta filas vacías no son un
     * calendario, son ruido. La forma del mes la da la rejilla; lo que da la
     * lista es la información.
     */
    $agenda = collect($porDia)
        ->filter(fn (array $eventos, string $fecha): bool => Str::startsWith($fecha, $mes->format('Y-m')))
        ->sortKeys();

    $diasDeLaSemana = $semanas->first();
@endphp

<x-layouts.publico :titulo="'Calendario de eventos · '.$tituloMes.' — ASOBARES Quindío'"
                   :descripcion="'Agenda del gremio de la vida nocturna del Quindío en '.$tituloMes.': ferias, foros y capacitaciones.'">

    @if ($fueraDeRango)
        {{-- Los meses anterior y siguiente son enlaces sin tope: sin esto un
             rastreador pasea por años enteros de calendarios vacíos y los
             indexa todos. Se pueden navegar; no se indexan. --}}
        @push('cabeza')
            <meta name="robots" content="noindex, follow">
        @endpush
    @endif

    <x-publico.hero titulo="Eventos y capacitaciones" compacto atmosfera
                    subtitulo="Solo eventos del gremio: ferias, foros y formación para los establecimientos del Quindío." />

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        <div class="revelar" data-revelar>
            <x-publico.conmutador-eventos activo="calendario" :mes="$mes"
                                          :total-proximos="$totalProximos"
                                          :total-pasados="$totalPasados" />
        </div>

        {{--
            El mando del mes: dos `<a href>` y nada más. Sin JavaScript, no por
            austeridad sino porque es la única mecánica coherente con un sitio
            de recarga completa —aquí filtrar, paginar y abrir un detalle son
            SIEMPRE navegación—, y porque así cada mes tiene una URL que se
            comparte, se marca y se indexa.

            `x-publico.boton` y no un enlace pelado: trae `.pulsable` de fábrica,
            que en táctil es el único acuse que existe, y su `px-6 py-3` da el
            objetivo de 44 px. En móvil los nombres de mes se esconden y quedan
            las dos flechas, así que el título cabe entre ellas a 375 px.
        --}}
        <nav aria-label="Cambiar de mes" class="revelar mt-8 flex items-center justify-between gap-3" data-revelar>
            <x-publico.boton variante="contorno" rel="prev"
                             :href="route('eventos.calendario', [$anterior->year, $anterior->format('m')])">
                <x-publico.flecha direccion="izquierda" />
                <span class="sr-only">Mes anterior: {{ Str::ucfirst($anterior->translatedFormat('F \d\e Y')) }}</span>
                <span class="ml-1 hidden sm:inline" aria-hidden="true">{{ Str::ucfirst($anterior->translatedFormat('F')) }}</span>
            </x-publico.boton>

            <h2 class="font-display text-lg font-semibold sm:text-xl"
                style="view-transition-name: calendario-titulo">{{ $tituloMes }}</h2>

            <x-publico.boton variante="contorno" rel="next"
                             :href="route('eventos.calendario', [$siguiente->year, $siguiente->format('m')])">
                <span class="mr-1 hidden sm:inline" aria-hidden="true">{{ Str::ucfirst($siguiente->translatedFormat('F')) }}</span>
                <span class="sr-only">Mes siguiente: {{ Str::ucfirst($siguiente->translatedFormat('F \d\e Y')) }}</span>
                <x-publico.flecha />
            </x-publico.boton>
        </nav>

        {{--
            ESCRITORIO: la rejilla de siete columnas.

            Es una `<table>` y no una cuadrícula de `<div>`: así el lector de
            pantalla anuncia «miércoles» al entrar en la celda, y esa es la
            mitad de la información de un calendario. Con `<div>` habría que
            reconstruirla a mano con roles, que es la misma tabla peor escrita.

            Vive detrás de `sm:` a propósito. Siete columnas en 375 px dan
            celdas de 53 px: descontado el relleno, ni el título del evento se
            lee ni el objetivo llega a 44. Móvil recibe la agenda de más abajo,
            que es la misma información en la forma que cabe.
        --}}
        <div class="revelar vidrio mt-5 hidden overflow-hidden rounded-[1.5rem] sm:block"
             data-revelar
             style="view-transition-name: calendario-rejilla">
            <table class="w-full table-fixed border-collapse">
                <caption class="sr-only">Eventos del gremio en {{ $tituloMes }}</caption>

                <thead>
                    <tr>
                        @foreach ($diasDeLaSemana as $dia)
                            <th scope="col" class="border-b border-linea bg-superficie-alta px-2 py-2 text-2xs font-medium text-tenue">
                                <abbr title="{{ $dia->translatedFormat('l') }}" class="no-underline">{{ Str::ucfirst($dia->translatedFormat('D')) }}</abbr>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($semanas as $semana)
                        <tr>
                            @foreach ($semana as $dia)
                                <td @class([
                                        'h-28 border-b border-r border-linea p-1.5 align-top last:border-r-0',
                                        'bg-superficie' => $dia->month === $mes->month,
                                        'bg-superficie-alta' => $dia->month !== $mes->month,
                                    ])
                                    @if ($dia->isToday()) aria-current="date" @endif>
                                    <span @class([
                                        'inline-flex h-6 w-6 items-center justify-center rounded-full text-xs',
                                        'bg-marca-500 font-semibold text-white' => $dia->isToday(),
                                        'text-tinta' => ! $dia->isToday() && $dia->month === $mes->month,
                                        'text-apagado' => ! $dia->isToday() && $dia->month !== $mes->month,
                                    ])>{{ $dia->day }}</span>

                                    @foreach ($porDia[$dia->toDateString()] ?? [] as $evento)
                                        {{--
                                            El acuse es de COLOR y no de fondo a propósito: `.enlace-accion`
                                            sólo transiciona `color`, así que un `hover:bg-*` aquí saltaría
                                            de golpe al lado de un texto que sí funde, y nada fallaría.

                                            La pastilla mide unos 27 px de alto, por debajo de los 44: es
                                            deliberado y está acotado. Esta rejilla sólo existe de `sm:`
                                            para arriba, donde el puntero es un ratón; el objetivo táctil
                                            de este mismo evento vive en la agenda de abajo, con `min-h-11`.
                                        --}}
                                        <a href="{{ route('eventos.show', $evento) }}"
                                           class="enlace-accion mt-1 block truncate rounded-md bg-marca-500/15 px-1.5 py-1 text-xs font-medium text-acento-fuerte hover:text-fuerte"
                                           title="{{ $evento->titulo }}">{{ $evento->titulo }}</a>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{--
            MÓVIL: la misma información como agenda vertical. Cero JavaScript,
            cero scroll horizontal y cada enlace con su objetivo de 44 px.
        --}}
        <ol class="revelar mt-5 space-y-3 sm:hidden" data-revelar style="view-transition-name: calendario-agenda">
            @forelse ($agenda as $fecha => $eventosDelDia)
                @php ($dia = \Illuminate\Support\Carbon::parse($fecha))
                <li class="vidrio rounded-[1.25rem] p-4" @if ($dia->isToday()) aria-current="date" @endif>
                    <p class="text-2xs uppercase tracking-wide text-apagado">
                        {{ Str::ucfirst($dia->translatedFormat('l d \d\e F')) }}
                        @if ($dia->isToday())
                            <span class="ml-1 rounded-full bg-marca-500/15 px-2 py-0.5 font-medium text-acento-fuerte">Hoy</span>
                        @endif
                    </p>

                    <ul class="mt-2 divide-y divide-linea">
                        @foreach ($eventosDelDia as $evento)
                            <li class="py-1">
                                <a href="{{ route('eventos.show', $evento) }}"
                                   class="enlace-accion flex min-h-11 flex-col justify-center text-sm font-medium text-tinta hover:text-acento">
                                    {{ $evento->titulo }}
                                    <span class="text-xs font-normal text-tenue">
                                        {{ $evento->fecha_inicio->translatedFormat('g:i a') }} · {{ $evento->tipo->getLabel() }}@if ($evento->lugar) · {{ $evento->lugar }}@endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @empty
                <li class="tarjeta p-8 text-center">
                    <p class="font-display text-base font-semibold">No hay eventos del gremio en {{ $tituloMes }}</p>
                    <a href="{{ route('eventos.index', ['cuando' => 'proximos']) }}"
                       class="enlace-accion relative mt-3 inline-flex min-h-11 items-center text-sm font-medium text-acento hover:text-fuerte">
                        Ver los próximos eventos&nbsp;<x-publico.flecha />
                    </a>
                </li>
            @endforelse
        </ol>

        {{-- El mismo aviso para escritorio. La rejilla se pinta igual aunque el
             mes esté vacío: un calendario sin eventos sigue siendo información,
             y esconderlo dejaría la página sin nada donde antes había un mes. --}}
        @if ($agenda->isEmpty())
            <p class="mt-4 hidden text-sm text-tenue sm:block">
                No hay eventos del gremio en {{ $tituloMes }}.
                <a href="{{ route('eventos.index', ['cuando' => 'proximos']) }}"
                   class="enlace-accion font-medium text-acento hover:text-fuerte">Ver los próximos eventos&nbsp;<x-publico.flecha /></a>
            </p>
        @endif
    </div>
</x-layouts.publico>
