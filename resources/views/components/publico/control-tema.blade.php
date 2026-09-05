{{--
    Control de tema de la barra de escritorio.

    El botón muestra el tema RESUELTO —sol o luna— y nunca el monitor: lo que
    el visitante ve pintado es lo que el icono tiene que decir. Lo resuelve
    CSS por la clase `dark`, no Alpine: así el icono es correcto desde el
    primer pintado. El popover de debajo ofrece las tres preferencias,
    Sistema incluida (decisión de Sua del 3 sep 2026; antes la prueba lo
    prohibía a propósito).

    Es un «disclosure» como el resto de desplegables de la barra: botón con
    aria-expanded y el panel que controla. El comportamiento es el
    `Alpine.data('desplegable')` de app.js, el mismo del idioma, la cuenta y
    los grupos: con ratón se asoma al pasar y se retira con gracia (por
    pointerenter, no por mouseenter: en un híbrido el toque también lo
    sintetiza); con dedo y con teclado, al pulsar; abrir uno cierra a los
    demás, que es lo que impide que este popover y el de idioma se pisen.

    `fila-pulsable` y no `pulsable` en las filas, sin ningún hover:bg-*: el
    fondo lo trae el portador detrás de la puerta táctil. `MovimientoTest`
    lee este archivo crudo.
--}}
@php
    $opciones = [
        ['valor' => 'light', 'etiqueta' => 'Claro', 'icono' => 'heroicon-o-sun'],
        ['valor' => 'dark', 'etiqueta' => 'Oscuro', 'icono' => 'heroicon-o-moon'],
        ['valor' => 'system', 'etiqueta' => 'Sistema', 'icono' => 'heroicon-o-computer-desktop'],
    ];
@endphp

<div x-data="desplegable"
     x-on:pointerenter="asomar($event)"
     x-on:pointerleave="retirar($event)"
     x-on:desplegable-abierto.window="ceder($event.detail)"
     x-on:click.outside="cerrar()"
     x-on:keydown.escape.window="cerrarYVolverAlFoco()"
     x-on:focusout="if (! $el.contains($event.relatedTarget)) cerrar()"
     class="relative">

    <button type="button"
            x-ref="disparador"
            x-on:click="alternar()"
            x-bind:aria-expanded="abierto ? 'true' : 'false'"
            aria-controls="popover-tema"
            aria-label="Apariencia del sitio"
            class="pulsable flex h-11 w-11 items-center justify-center rounded-full text-suave hover:text-fuerte">
        {{-- El icono lo decide CSS por la clase `dark` del <html>, que el
             <head> pone antes del primer pintado y que `elegir()` cambia al
             instante: es el tema resuelto sin esperar a Alpine, así que no hay
             destello del icono equivocado. --}}
        <x-heroicon-o-sun class="h-5 w-5 dark:hidden" aria-hidden="true" />
        <x-heroicon-o-moon class="hidden h-5 w-5 dark:block" aria-hidden="true" />
    </button>

    <div id="popover-tema"
         x-show="abierto"
         x-cloak
         x-transition:enter="transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)"
         x-transition:enter-start="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         role="group"
         aria-label="Apariencia del sitio"
         class="hoja-flotante absolute right-0 z-50 mt-2 w-44 origin-top-right rounded-2xl p-2">
        @foreach ($opciones as $opcion)
            <button type="button"
                    x-on:click="$store.tema.elegir('{{ $opcion['valor'] }}'); cerrarYVolverAlFoco()"
                    x-bind:aria-pressed="$store.tema.preferencia === '{{ $opcion['valor'] }}' ? 'true' : 'false'"
                    x-bind:class="$store.tema.preferencia === '{{ $opcion['valor'] }}' ? 'text-acento' : 'text-suave hover:text-fuerte'"
                    class="fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm">
                <x-dynamic-component :component="$opcion['icono']" class="h-4 w-4 shrink-0" aria-hidden="true" />
                <span>{{ $opcion['etiqueta'] }}</span>
                <span x-show="$store.tema.preferencia === '{{ $opcion['valor'] }}'"
                      x-cloak
                      class="ml-auto h-1.5 w-1.5 rounded-full bg-marca-500"
                      aria-hidden="true"></span>
            </button>
        @endforeach
    </div>
</div>
