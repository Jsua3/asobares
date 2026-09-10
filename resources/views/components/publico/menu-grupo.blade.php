{{--
    Grupo desplegable de la navegación principal.

    Existe porque los ocho enlaces de primer nivel no cabían: «Abre tu negocio»
    y «Quiénes somos» partían en dos líneas a 1280, 1440 y 1600 px y dejaban la
    barra en 83,38 px de alto. La decisión de producto fue REAGRUPAR y no
    acortar etiquetas, y el reparto no se inventó aquí: «Bolsas» es como se
    llama ese mismo grupo en el menú del panel que ya usa el personal del
    gremio, así que /admin y el sitio público nombran igual las mismas cosas.

    Es un «disclosure», no un menú ARIA — igual que `menu-usuario.blade.php`, de
    donde sale entera esta solución. Por eso no lleva `aria-haspopup` ni
    `role="menu"`, que anunciarían navegación con flechas que este panel no
    implementa: aquí se recorre con Tab, que es lo que un lector de pantalla
    espera de un disclosure.

    Desde el 6 sep pinta también la pestaña del módulo inferior del móvil
    (Parte II §6.3, `variante="pestana"`): icono sobre rótulo, hoja hacia
    ARRIBA con id propio (`-movil`, para no pisar el panel de escritorio) y
    las filas de pie que llegan desde los arreglos de `navbar.blade.php`,
    porque la navegación se declara una vez. La rama de escritorio es la de
    siempre, letra a letra: las guardias la leen cruda.
--}}
@props(['titulo', 'enlaces', 'variante' => 'barra', 'icono' => null, 'pie' => []])

@php
    /**
     * Una ruta `x.index` gobierna toda su sección (`x.*`), y las sueltas
     * —`quienes-somos`, `contacto`— se comparan tal cual. Es la misma regla que
     * ya usaban los enlaces sueltos de la barra, y aquí se aplica dos veces:
     * al enlace y, por acumulación, al disparador que lo contiene.
     */
    $patron = static fn (string $ruta): string => str_replace('.index', '.*', $ruta);

    $grupoActivo = collect($enlaces)->contains(
        static fn (array $enlace): bool => request()->routeIs($patron($enlace['ruta']))
    );

    $esPestana = $variante === 'pestana';
    $panel = 'menu-'.Str::slug($titulo).($esPestana ? '-movil' : '');

    /* Las filas de pie solo existen en la pestaña, y `'solo' => 'guest'` las
       reserva al anónimo: la entrada del afiliado en el teléfono (D-M4). */
    $filasDePie = $esPestana
        ? collect($pie)->filter(static fn (array $fila): bool => ($fila['solo'] ?? null) !== 'guest' || auth()->guest())
        : collect();
@endphp

{{-- El comportamiento es el `Alpine.data('desplegable')` de app.js, el mismo
     de la cuenta, el tema y el idioma: con ratón se asoma al pasar y se
     retira con gracia (Sua, 5 sep: «así tal cual el de modo oscuro»); con
     dedo y con teclado, al pulsar; abrir uno cierra a los demás.

     La salida por `focusout` es la que el teclado necesita: sin ella,
     tabular de un grupo al siguiente dejaba el primero abierto, y con el
     ratón eso no pasaba nunca porque `click.outside` lo tapa.

     La raíz de la pestaña es estática a propósito: el bloque contenedor de
     su hoja es el módulo inferior fijo, no la pestaña de 72 px, o la hoja
     desbordaría por un lado. --}}
<div x-data="desplegable"
     x-on:pointerenter="asomar($event)"
     x-on:pointerleave="retirar($event)"
     x-on:desplegable-abierto.window="ceder($event.detail)"
     x-on:click.outside="cerrar()"
     x-on:pointerdown.outside="cerrar()"
     x-on:keydown.escape.window="cerrarYVolverAlFoco()"
     x-on:focusout="if (! $el.contains($event.relatedTarget)) cerrar()"
     x-on:scroll.window.passive="cerrarSiSeDesplaza()"
     x-on:pageshow.window="if ($event.persisted) cerrar()"
     {{ $attributes->class(['relative' => ! $esPestana, 'flex flex-1' => $esPestana]) }}>

    @if ($esPestana)
        {{-- Contorno en reposo, sólido en la sección activa: la forma dice lo
             mismo que el color, para quien no distingue el rojo. Se decide en
             el servidor, como aria-current. Lleva `aria-current="true"` (el
             valor genérico que el sitio ya usa en dos filtros) y nunca `page`:
             el `page` es de la fila de la hoja, y la guardia de la navegación
             cuenta dos por página. Sin galón: la pestaña dice si está abierta
             por aria-expanded y por el icono sólido. --}}
        <button type="button"
                x-ref="disparador"
                x-on:click="alternar()"
                x-bind:aria-expanded="abierto ? 'true' : 'false'"
                aria-controls="{{ $panel }}"
                @if ($grupoActivo) aria-current="true" @endif
                @class([
                    'pestana fila-pulsable flex min-h-11 w-full flex-col items-center justify-center rounded-xl px-0.5 text-center text-2xs font-medium',
                    'text-acento' => $grupoActivo,
                    'text-suave' => ! $grupoActivo,
                ])>
            {{-- La misma gota que los enlaces directos de `navbar.blade.php`,
                 colgada del icono por el mismo motivo: la pinta la pestaña
                 activa y ninguna otra, de modo que su nombre de transición es
                 único en el documento. --}}
            <span class="pestana__icono">
                @if ($grupoActivo)
                    <span class="pestana__gota" aria-hidden="true"></span>
                @endif
                <x-dynamic-component :component="'heroicon-'.($grupoActivo ? 's' : 'o').'-'.$icono" class="h-6 w-6 shrink-0" aria-hidden="true" />
            </span>
            <span class="pestana__rotulo"><span class="text-balance">{{ $titulo }}</span></span>
        </button>
    @else
        {{-- Misma geometría que los enlaces sueltos de la barra: `py-3` lleva la
             caja a 45,7 px y el `-my-1` devuelve al flujo los 37,7 de antes, así
             que el disparador cumple los 44 px sin subir el alto del header. --}}
        <button type="button"
                x-ref="disparador"
                x-on:click="alternar()"
                x-bind:aria-expanded="abierto ? 'true' : 'false'"
                aria-controls="{{ $panel }}"
                @class([
                    'nav-enlace enlace-accion -my-1 inline-flex items-center gap-1 rounded-lg px-3 py-3 text-sm',
                    'text-acento' => $grupoActivo,
                    'text-suave hover:text-fuerte' => ! $grupoActivo,
                ])>
            {{ $titulo }}
            {{-- El galón es un SVG y no un carácter por lo mismo que las flechas
                 del sitio: el subconjunto de Poppins no trae ese glifo y lo
                 pintaría la fuente del sistema. Gira con el portador de los
                 desplegables, que es el único que anima `rotate` de verdad. --}}
            <svg class="transicion-desplegable h-4 w-4 duration-(--duracion-salida) ease-out"
                 x-bind:class="abierto ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
            </svg>
        </button>
    @endif

    <div id="{{ $panel }}"
         x-show="abierto"
         x-cloak
         @if ($esPestana)
             {{-- Se cierra con el dedo (9 sep 2026). La referencia es lo que
                  enciende el arrastre: en escritorio no se declara y todos los
                  métodos de `app.js` quedan inertes.

                  El clic va en fase de CAPTURA para poder tragárselo antes de
                  que llegue a la fila: soltar encima de un enlace después de
                  empujar la hoja no puede navegar. --}}
             x-ref="hoja"
             x-on:pointerdown="tomarLaHoja($event)"
             x-on:pointermove="moverLaHoja($event)"
             x-on:pointerup="soltarLaHoja($event)"
             x-on:pointercancel="soltarLaHoja($event)"
             x-on:click.capture="tragarElClicDelArrastre($event)"
         @endif
         @if ($esPestana)
             {{-- Sube desde la barra con el resorte vivo de los popovers y sale
                  por el cajón: la receta de #popover-tema con el signo del
                  desplazamiento invertido. Cuelga del módulo inferior, que es
                  fijo, así que la acompaña al compactarse. --}}
             x-transition:enter="transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)"
             x-transition:enter-start="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-hoja)"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-hoja)"
             role="group"
             aria-label="{{ $titulo }}"
             class="hoja-flotante hoja-inferior absolute inset-x-2 bottom-full z-50 mx-auto mb-2 max-w-sm origin-bottom rounded-2xl p-2"
         @else
             x-transition:enter="transicion-desplegable ease-out duration-(--duracion-entrada)"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transicion-desplegable ease-out duration-(--duracion-salida)"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             {{-- `origin-top-left` y no el centro: el panel cuelga del borde
                  izquierdo del disparador, así que de ahí tiene que nacer y por ese
                  mismo camino tiene que irse. La salida es la entrada al revés y
                  más rápida, que es la regla de duraciones del proyecto. --}}
             class="hoja-flotante absolute left-0 z-50 mt-2 w-56 origin-top-left rounded-2xl p-2"
         @endif>
        @foreach ($enlaces as $enlace)
            @php($actual = request()->routeIs($patron($enlace['ruta'])))
            {{-- `fila-pulsable` y no `pulsable`: una fila a lo ancho del panel
                 encogida se lee como si el panel se arrugara. El fondo de hover
                 lo trae el portador detrás de la puerta táctil, así que aquí no
                 se declara —y la utilidad se nombra, no se pega, porque
                 `MovimientoTest` lee este archivo crudo. --}}
            <a href="{{ route($enlace['ruta']) }}"
               @if ($actual) aria-current="page" @endif
               @class([
                   'fila-pulsable block rounded-lg px-3 py-3 text-sm',
                   'text-acento' => $actual,
                   'text-suave hover:text-fuerte' => ! $actual,
               ])>
                {{ $enlace['texto'] }}
            </a>
        @endforeach

        @if ($filasDePie->isNotEmpty())
            <div class="my-1 border-t border-linea" role="presentation"></div>
            @foreach ($filasDePie as $fila)
                <a href="{{ route($fila['ruta']) }}"
                   class="fila-pulsable block rounded-lg px-3 py-3 text-sm text-suave hover:text-fuerte">
                    {{ $fila['texto'] }}
                </a>
            @endforeach
        @endif
    </div>
</div>
