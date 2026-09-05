{{--
    Chip de idioma de la barra de escritorio.

    Se ve y NO funciona a propósito: el sitio no tiene traducción (no existe
    lang/, cero __() en las vistas, y la tabla de ajustes es monolingüe).
    Traducirlo es otro subsistema con su propia spec y su acta. Este chip es
    su sitio reservado en la interfaz: Español activo, English deshabilitado
    con «próximamente». Cuando exista la traducción, `$idiomas` sale de la
    configuración y `$actual` del locale de la petición.

    Popover VERTICAL, con bandera y nombre del idioma en su propia lengua.
    Mismo disclosure que el control de tema.
--}}
@php
    $idiomas = [
        ['codigo' => 'es', 'siglas' => 'ES', 'nombre' => 'Español', 'pais' => 'co', 'disponible' => true],
        ['codigo' => 'en', 'siglas' => 'EN', 'nombre' => 'English', 'pais' => 'us', 'disponible' => false],
    ];

    $actual = $idiomas[0];
@endphp

<div x-data="{
        abierto: false,
        cierre: null,
        punteroFino() {
            return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        },
        asomar() {
            if (! this.punteroFino()) {
                return;
            }

            clearTimeout(this.cierre);
            this.abierto = true;
        },
        retirar() {
            if (! this.punteroFino()) {
                return;
            }

            clearTimeout(this.cierre);
            this.cierre = setTimeout(() => { this.abierto = false; }, 280);
        },
        cerrarYVolverAlFoco() {
            if (! this.abierto) {
                return;
            }

            this.abierto = false;
            this.$refs.disparador.focus();
        },
     }"
     x-on:mouseenter="asomar()"
     x-on:mouseleave="retirar()"
     x-on:click.outside="abierto = false"
     x-on:keydown.escape.window="cerrarYVolverAlFoco()"
     x-on:focusout="if (! $el.contains($event.relatedTarget)) abierto = false"
     class="relative">

    <button type="button"
            x-ref="disparador"
            x-on:click="abierto = ! abierto"
            x-bind:aria-expanded="abierto ? 'true' : 'false'"
            aria-controls="popover-idioma"
            {{-- El nombre accesible CONTIENE el texto visible: un `aria-label`
                 que lo sustituya deja sin efecto el mando de voz «pulsa ES»
                 (WCAG 2.5.3, Label in Name). --}}
            aria-label="Idioma del sitio: {{ $actual['siglas'] }}"
            class="pulsable flex h-11 min-w-11 items-center justify-center gap-1 rounded-full px-2 text-sm font-medium text-suave hover:text-fuerte">
        <span>{{ $actual['siglas'] }}</span>
        {{-- Galón SVG y no carácter: Poppins subconjuntada no trae el glifo. --}}
        <svg class="transicion-desplegable h-3.5 w-3.5 duration-(--duracion-salida) ease-out"
             x-bind:class="abierto ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div id="popover-idioma"
         x-show="abierto"
         x-cloak
         x-transition:enter="transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)"
         x-transition:enter-start="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         role="group"
         aria-label="Idioma del sitio"
         class="hoja-flotante absolute right-0 z-50 mt-2 w-52 origin-top-right rounded-2xl p-2">
        {{-- Hoy ninguna fila lleva `x-on:click`: ES ya es el idioma actual y EN
             está deshabilitado. Cuando un idioma sea elegible, su clic debe
             terminar en `cerrarYVolverAlFoco()`, como en el control de tema:
             elegir con teclado no puede dejar el foco en el <body>. --}}
        @foreach ($idiomas as $idioma)
            @php($esActual = $idioma['codigo'] === $actual['codigo'])
            <button type="button"
                    lang="{{ $idioma['codigo'] }}"
                    aria-pressed="{{ $esActual ? 'true' : 'false' }}"
                    @if (! $idioma['disponible']) disabled aria-disabled="true" @endif
                    @class([
                        'fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm',
                        'text-acento' => $esActual,
                        'text-suave hover:text-fuerte' => ! $esActual && $idioma['disponible'],
                        'text-apagado' => ! $idioma['disponible'],
                    ])>
                <x-publico.bandera :pais="$idioma['pais']" />
                <span>{{ $idioma['nombre'] }}</span>
                @if ($esActual)
                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-marca-500" aria-hidden="true"></span>
                @elseif (! $idioma['disponible'])
                    <span class="ml-auto text-2xs text-apagado">próximamente</span>
                @endif
            </button>
        @endforeach
    </div>
</div>
