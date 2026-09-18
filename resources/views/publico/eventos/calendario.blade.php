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

    @push('cabeza')
        @vite(['resources/css/eventos-editorial.css'])
    @endpush

    @if ($fueraDeRango)
        {{-- Los meses anterior y siguiente son enlaces sin tope: sin esto un
             rastreador pasea por años enteros de calendarios vacíos y los
             indexa todos. Se pueden navegar; no se indexan. --}}
        @push('cabeza')
            <meta name="robots" content="noindex, follow">
        @endpush
    @endif

    <div class="eventos-editorial"
         x-data="calendarioComunitario(@js(old('fecha', $mes->toDateString())), @js($errors->any()))">
    <x-publico.hero-eventos
        :titulo="ajuste('eventos_titulo', 'Eventos y capacitaciones')"
        :subtitulo="ajuste('eventos_intro', 'Eventos, capacitaciones y experiencias del gremio y sus aliados para el sector gastronómico y de entretenimiento del Quindío.')" />

    <div class="eventos-editorial-cuerpo mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        @if (session('exito'))
            <x-publico.alerta class="mt-6">{{ session('exito') }}</x-publico.alerta>
        @endif

        <div class="revelar" data-revelar>
            <x-publico.conmutador-eventos activo="calendario" :mes="$mes"
                                          :total-proximos="$totalProximos"
                                          :total-pasados="$totalPasados" />
        </div>

        {{--
            El mando del mes usa dos enlaces, porque es la mecánica coherente con un sitio
            de recarga completa —aquí filtrar, paginar y abrir un detalle son
            SIEMPRE navegación—, y porque así cada mes tiene una URL que se
            comparte, se marca y se indexa.

            `x-publico.boton` y no un enlace pelado: trae `.pulsable` de fábrica,
            que en táctil es el único acuse que existe, y su `px-6 py-3` da el
            objetivo de 44 px. En móvil los nombres de mes se esconden y quedan
            las dos flechas, así que el título cabe entre ellas a 375 px.
        --}}
        <nav aria-label="Cambiar de mes" class="eventos-editorial-mes revelar mt-8 flex items-center justify-between gap-3" data-revelar>
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
            Aviso general, no un banner: el mismo botón de la casa
            (`x-publico.boton`), que ya trae `.pulsable` --su acuse y su foco
            los vigila `MovimientoTest`, no esta hoja--. Sin fecha en el
            dataset: `abrir()` la deja vacía y la persona la elige en el
            campo Fecha del formulario, igual que si hubiera entrado sin
            pulsar ningún día.
        --}}
        <div class="eventos-editorial-cta revelar mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-linea bg-superficie-alta px-4 py-3"
             data-revelar>
            <p class="m-0 text-sm font-semibold text-suave">¿Tienes un evento para compartir?</p>
            <x-publico.boton variante="contorno" tipo="button" x-on:click="abrir()">
                <span aria-hidden="true">+</span> Agregar evento
            </x-publico.boton>
        </div>

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
        <div class="eventos-editorial-calendario revelar mt-5 hidden overflow-hidden rounded-[1.5rem] sm:block"
             data-revelar
             style="view-transition-name: calendario-rejilla">
            <table class="w-full table-fixed border-collapse">
                <caption class="sr-only">Eventos en {{ $tituloMes }}</caption>

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
                                    @if ($dia->month !== $mes->month) data-fuera="true" @endif
                                    @if ($dia->isToday()) aria-current="date" @endif>
                                    <div class="eventos-editorial-celda-cabecera">
                                        <span @class([
                                            'inline-flex h-6 w-6 items-center justify-center rounded-full text-xs',
                                            'bg-accion font-semibold text-white' => $dia->isToday(),
                                            'text-tinta' => ! $dia->isToday() && $dia->month === $mes->month,
                                            'text-apagado' => ! $dia->isToday() && $dia->month !== $mes->month,
                                        ])>{{ $dia->day }}</span>

                                        <button type="button" data-fecha="{{ $dia->toDateString() }}"
                                                x-on:click="abrir($el.dataset.fecha)"
                                                class="eventos-editorial-agregar"
                                                aria-label="Agregar evento el {{ $dia->translatedFormat('d \d\e F \d\e Y') }}">
                                            <span aria-hidden="true">+</span>
                                        </button>
                                    </div>

                                    @foreach ($porDia[$dia->toDateString()] ?? [] as $evento)
                                        {{--
                                            Sin `hover:` en la lista: el radio, el color y los tres estados de
                                            la pastilla los dicta `eventos-editorial.css`, que va sin capa y
                                            gana a toda utilidad de aquí. Un `hover:text-*` en esta etiqueta
                                            no se ve nunca, porque el `color` de la hoja lo pisa.

                                            La pastilla mide unos 27 px de alto, por debajo de los 44: es
                                            deliberado y está acotado. Esta rejilla sólo existe de `sm:`
                                            para arriba, donde el puntero es un ratón; el objetivo táctil
                                            de este mismo evento vive en la agenda de abajo, con `min-h-11`.
                                        --}}
                                        <a href="{{ route('eventos.show', $evento) }}"
                                           class="enlace-accion mt-1 block truncate rounded-md bg-marca-500/15 px-1.5 py-1 text-xs font-medium text-acento-fuerte"
                                           title="{{ $evento->titulo }}">{{ $evento->titulo }}</a>
                                    @endforeach
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="eventos-editorial-dias-movil mt-5 sm:hidden" aria-label="Seleccionar fecha para agregar evento">
            <p class="mb-2 text-sm font-medium text-tinta">Selecciona un día para agregar un evento</p>
            <div class="eventos-editorial-dias-movil__rejilla">
                @foreach ($diasDeLaSemana as $dia)
                    <span aria-hidden="true" class="eventos-editorial-dias-movil__semana">{{ Str::ucfirst($dia->translatedFormat('D')) }}</span>
                @endforeach
                @foreach ($semanas as $semana)
                    @foreach ($semana as $dia)
                        @if ($dia->month === $mes->month)
                            <button type="button" data-fecha="{{ $dia->toDateString() }}"
                                    x-on:click="abrir($el.dataset.fecha)"
                                    @if ($dia->isToday()) aria-current="date" @endif
                                    class="eventos-editorial-dias-movil__dia"
                                    aria-label="Agregar evento el {{ $dia->translatedFormat('d \d\e F \d\e Y') }}">
                                {{ $dia->day }}
                                @if (! empty($porDia[$dia->toDateString()]))
                                    <span class="eventos-editorial-dias-movil__marca" aria-hidden="true"></span>
                                @endif
                            </button>
                        @else
                            <span aria-hidden="true"></span>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>

        {{--
            MÓVIL: la misma información como agenda vertical. Sin scroll horizontal
            y cada enlace con su objetivo de 44 px.

            Sin `hover:` en ninguna lista de aquí abajo: Tailwind 4 compila esa
            variante detrás de `(hover: hover)` y no de la puerta de puntero
            fino, así que en un híbrido el color se queda puesto tras el toque.
            Los hovers, el recuadro del evento y su acuse los dicta
            `eventos-editorial.css`; la atenuación al pulsar sigue siendo de
            `.enlace-accion`.
        --}}
        <ol class="eventos-editorial-agenda revelar mt-5 space-y-3 sm:hidden" data-revelar style="view-transition-name: calendario-agenda">
            @forelse ($agenda as $fecha => $eventosDelDia)
                @php ($dia = \Illuminate\Support\Carbon::parse($fecha))
                <li class="vidrio rounded-[1.25rem] p-4" @if ($dia->isToday()) aria-current="date" @endif>
                    <p class="text-2xs uppercase tracking-wide text-apagado">
                        {{ Str::ucfirst($dia->translatedFormat('l d \d\e F')) }}
                        @if ($dia->isToday())
                            <span class="ml-1 rounded-full bg-marca-500/15 px-2 py-0.5 font-medium text-acento-fuerte">Hoy</span>
                        @endif
                    </p>

                    <ul class="mt-2 space-y-2">
                        @foreach ($eventosDelDia as $evento)
                            <li>
                                <a href="{{ route('eventos.show', $evento) }}"
                                   class="eventos-editorial-agenda__evento enlace-accion flex min-h-11 flex-col justify-center text-sm font-medium text-tinta">
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
                    <p class="font-display text-base font-semibold">No hay eventos en {{ $tituloMes }}</p>
                    <a href="{{ route('eventos.index', ['cuando' => 'proximos']) }}"
                       class="eventos-editorial-proximos enlace-accion relative mt-3 inline-flex min-h-11 items-center text-sm font-medium text-acento">
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
                No hay eventos en {{ $tituloMes }}.
                <a href="{{ route('eventos.index', ['cuando' => 'proximos']) }}"
                   class="eventos-editorial-proximos enlace-accion font-medium text-acento">Ver los próximos eventos&nbsp;<x-publico.flecha /></a>
            </p>
        @endif
    </div>
    <x-publico.formulario-evento-comunitario />
    </div>
</x-layouts.publico>
