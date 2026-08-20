@php
    /*
     * La navegación se declara UNA vez y se pinta dos: escritorio con los
     * grupos plegados, móvil con los grupos abiertos bajo su encabezado. Que
     * las dos salgan del mismo arreglo es lo que impide que diverjan, que es
     * exactamente lo que ya había pasado: «Contacto» solo existía en móvil y en
     * el pie, y en escritorio no se alcanzaba desde ningún sitio.
     *
     * Tres directos y dos grupos, y el reparto no es estético: «Abre tu
     * negocio» es el módulo insignia y no puede quedar enterrado en un
     * desplegable, mientras que «Bolsas» es como se llama ese mismo grupo en el
     * menú del panel que ya usa el personal del gremio.
     */
    $enlacesDirectos = [
        ['ruta' => 'directorio.index', 'texto' => 'Directorio'],
        ['ruta' => 'guia.index', 'texto' => 'Abre tu negocio'],
        ['ruta' => 'eventos.index', 'texto' => 'Eventos'],
    ];

    $grupos = [
        [
            'titulo' => 'Bolsas',
            'enlaces' => [
                ['ruta' => 'empleo.index', 'texto' => 'Empleo'],
                ['ruta' => 'artistas.index', 'texto' => 'Artistas'],
                ['ruta' => 'proveedores.index', 'texto' => 'Proveedores'],
            ],
        ],
        [
            'titulo' => 'El gremio',
            'enlaces' => [
                ['ruta' => 'quienes-somos', 'texto' => 'Quiénes somos'],
                ['ruta' => 'boletin.index', 'texto' => 'Boletín'],
                ['ruta' => 'contacto', 'texto' => 'Contacto'],
            ],
        ],
    ];

    /** Una ruta `x.index` gobierna toda su sección; las sueltas se comparan tal cual. */
    $patron = static fn (string $ruta): string => str_replace('.index', '.*', $ruta);

    $usuario = auth()->user();
    $esDelEquipo = (bool) ($usuario?->esSuperAdmin() || $usuario?->esSubadmin());
@endphp

{{-- `menuMovil` y no `abierto`: el desplegable de configuración anida su propio
     x-data y dos propiedades con el mismo nombre se pisarían. --}}
{{-- Las tres salidas van en el <header> y no en el panel: el botón que
     alterna vive dentro del header, y si `click.outside` estuviera en el
     panel el clic del botón lo cerraría y lo abriría en el mismo gesto. --}}
{{-- `desplazado` gobierna la separación con el contenido: la barra solo se
     apoya —sombra en claro, filo de luz en oscuro— cuando hay algo pasando por
     debajo. El umbral de 8 px evita que el rebote elástico del scroll en iOS
     la encienda y apague sola en el tope. --}}
<header x-data="{ menuMovil: false, desplazado: false }"
        x-init="desplazado = window.scrollY > 8"
        x-on:scroll.window.passive="desplazado = window.scrollY > 8"
        x-on:keydown.escape.window="menuMovil = false"
        x-on:click.outside="menuMovil = false"
        x-on:resize.window="if (window.innerWidth >= 1024) menuMovil = false"
        x-bind:class="(desplazado || menuMovil) ? 'cromo-apoyado' : ''"
        class="cromo sticky top-0 z-40">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8"
         aria-label="Navegación principal">

        {{-- `-my-1.5 py-1.5` es padding negativo óptico: el logo mide 32 px de
             alto en móvil y el relleno lo lleva a 44, mientras el margen
             negativo devuelve al flujo esos mismos 32. La barra no cambia de
             alto y el logo no se mueve un píxel. --}}
        <a href="{{ route('inicio') }}" class="pulsable -my-1.5 flex shrink-0 items-center py-1.5" aria-label="Inicio — ASOBARES Capítulo Quindío">
            <x-publico.logo alto="h-8 sm:h-9" />
        </a>

        {{-- Escritorio --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($enlacesDirectos as $enlace)
                @php($actual = request()->routeIs($patron($enlace['ruta'])))
                <a href="{{ route($enlace['ruta']) }}"
                   @if ($actual) aria-current="page" @endif
                   @class([
                       'enlace-accion -my-1 rounded-lg px-3 py-3 text-sm',
                       'text-acento' => $actual,
                       'text-suave hover:text-fuerte' => ! $actual,
                   ])>
                    {{ $enlace['texto'] }}
                </a>
            @endforeach

            @foreach ($grupos as $grupo)
                <x-publico.menu-grupo :titulo="$grupo['titulo']" :enlaces="$grupo['enlaces']" />
            @endforeach
        </div>

        <div class="hidden items-center gap-2 lg:flex">
            {{-- Con sesión abierta el atajo vive dentro del desplegable, para no
                 repetir el mismo enlace dos veces en la misma barra. --}}
            @guest
                <a href="{{ route('mi-cuenta.index') }}"
                   class="enlace-accion -my-1 rounded-lg px-3 py-3 text-sm text-tenue hover:text-fuerte">
                    Mi cuenta
                </a>
            @endguest
            <a href="{{ route('afiliate') }}"
               {{-- ::after y no padding: es la única pastilla pintada de la barra
                    y agrandarla se vería. `-inset-y-1` da 37,7 + 8 = 45,7 px de
                    área pulsable sin tocar el dibujo ni el alto del header.

                    `.pulsable` convive con ese pseudoelemento: el `scale(0.97)`
                    del `:active` encoge también el `::after`. Medido con el
                    ratón abajo: el área efectiva pasa de 46,4 a 45,2 px, o sea
                    que sigue por encima del mínimo, y para entonces el
                    navegador ya fijó el destino del clic en el `pointerdown`.
                    A cambio, la utilidad de fundido de color se fue: pisaba al
                    portador y con ella moría la duración cero de su `:active`.
                    Se nombra y no se pega porque la guardia lee este archivo
                    crudo, comentarios incluidos. --}}
               class="pulsable relative rounded-lg bg-marca-500 px-4 py-2 text-sm font-semibold text-white after:absolute after:inset-x-0 after:-inset-y-1 after:content-[''] hover:bg-marca-600">
                Afíliate
            </a>
            <x-publico.menu-usuario />
        </div>

        {{-- Móvil --}}
        <button type="button" x-on:click="menuMovil = ! menuMovil"
                class="pulsable -m-0.5 rounded-lg p-2.5 text-suave lg:hidden"
                x-bind:aria-expanded="menuMovil ? 'true' : 'false'" aria-controls="menu-movil">
            <span class="sr-only">Abrir menú</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                {{-- Los dos trazos se cruzan girando en sentidos opuestos. El
                     origen es el centro del lienzo de 24×24, no del <path>. --}}
                <path x-show="! menuMovil"
                      x-transition:enter="transicion-desplegable ease-out duration-(--duracion-salida)"
                      x-transition:enter-start="opacity-0 -rotate-90"
                      x-transition:leave="transicion-desplegable ease-out duration-(--duracion-salida)"
                      x-transition:leave-end="opacity-0 -rotate-90"
                      style="transform-origin: 12px 12px"
                      stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                <path x-show="menuMovil"
                      x-cloak
                      x-transition:enter="transicion-desplegable ease-out duration-(--duracion-salida)"
                      x-transition:enter-start="opacity-0 rotate-90"
                      x-transition:leave="transicion-desplegable ease-out duration-(--duracion-salida)"
                      x-transition:leave-end="opacity-0 rotate-90"
                      style="transform-origin: 12px 12px"
                      stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </nav>

    {{-- Sin JavaScript el desplegable nunca llega a abrirse —x-cloak lo deja
         oculto—, y con él se iría la única salida de sesión que queda en el
         sitio. Cerrar sesión no puede depender de Alpine. --}}
    @auth
        <noscript>
            <div class="border-t border-linea px-4 py-2 text-center">
                <form method="POST" action="{{ route('mi-cuenta.salir') }}">
                    @csrf
                    <button type="submit" class="enlace-accion inline-flex min-h-11 items-center text-sm text-acento underline">Cerrar sesión</button>
                </form>
            </div>
        </noscript>
    @endauth

    <div id="menu-movil"
         x-show="menuMovil"
         x-cloak
         x-transition:enter="transicion-desplegable ease-cajon duration-(--duracion-panel)"
         x-transition:enter-start="opacity-0 translate-y-(--asb-desplazamiento-panel)"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-(--asb-desplazamiento-panel)"
         class="absolute inset-x-0 top-full max-h-[calc(100dvh-4rem)] origin-top overflow-y-auto border-t border-linea bg-fondo shadow-lg lg:hidden">
        {{-- Los dos grupos se abren en PLANO, bajo un encabezado, y no como
             desplegables anidados. En escritorio el desplegable compra
             horizontal, que es justo lo que falta ahí; aquí sobra vertical y lo
             escaso es el número de toques: anidar cobraría un toque más por
             cada destino y metería una animación dentro de un panel que ya se
             está animando. Es además el patrón que este mismo panel ya usaba
             para «Apariencia»: un antetítulo y debajo lo suyo.

             `fila-pulsable` y no `pulsable`: una fila a lo ancho del panel
             encogida un 3 % se lee como si el panel se arrugara. Tiñe el fondo,
             que es además el único acuse que existe en táctil.

             El portador se lleva dentro el fondo de hover que estas filas
             declaraban aquí, y no es cosmética: una utilidad de
             `@layer utilities` gana siempre a `@layer components`, así que
             pintaba el fondo del `:active` mientras el puntero estaba encima.
             Las dos manos las tiene que dictar la misma regla.

             Y ojo al editar: `MovimientoTest` lee este archivo CRUDO,
             comentarios incluidos, así que la utilidad se nombra y no se
             pega. --}}
        <div class="px-4 py-3">
            <div class="space-y-1">
                @foreach ($enlacesDirectos as $enlace)
                    @php($actual = request()->routeIs($patron($enlace['ruta'])))
                    <a href="{{ route($enlace['ruta']) }}"
                       @if ($actual) aria-current="page" @endif
                       @class([
                           'fila-pulsable block rounded-lg px-3 py-3 text-sm text-tinta',
                           'text-acento' => $actual,
                       ])>
                        {{ $enlace['texto'] }}
                    </a>
                @endforeach
            </div>

            @foreach ($grupos as $grupo)
                {{-- Encabezado de verdad y no un párrafo con pinta de tal: un
                     lector de pantalla salta por encabezados, y es así como se
                     recorre una lista larga sin oírla entera. --}}
                <h2 class="antetitulo mt-5 px-3 text-apagado">{{ $grupo['titulo'] }}</h2>
                <div class="mt-1 space-y-1">
                    @foreach ($grupo['enlaces'] as $enlace)
                        @php($actual = request()->routeIs($patron($enlace['ruta'])))
                        <a href="{{ route($enlace['ruta']) }}"
                           @if ($actual) aria-current="page" @endif
                           @class([
                               'fila-pulsable block rounded-lg px-3 py-3 text-sm text-tinta',
                               'text-acento' => $actual,
                           ])>
                            {{ $enlace['texto'] }}
                        </a>
                    @endforeach
                </div>
            @endforeach

            <div class="mt-5 space-y-1">
                @guest
                    <a href="{{ route('mi-cuenta.index') }}" class="fila-pulsable block rounded-lg px-3 py-3 text-sm text-tinta">Mi cuenta</a>
                @endguest
                <a href="{{ route('afiliate') }}"
                   class="pulsable mt-2 block rounded-lg bg-marca-500 px-3 py-3 text-center text-sm font-semibold text-white">
                    Afíliate
                </a>
            </div>
        </div>

        {{-- Apariencia y sesión: lo mismo que ofrece el desplegable de
             escritorio, desplegado porque en móvil no hay sitio para anidar. --}}
        <div class="border-t border-linea px-4 py-4">
            <h2 class="antetitulo text-apagado">Apariencia</h2>
            <x-publico.selector-tema class="mt-2" />
        </div>

        @auth
            <div class="border-t border-linea px-4 py-4">
                <p class="truncate text-sm font-semibold text-fuerte">{{ $usuario->name }}</p>
                <div class="mt-2 space-y-1">
                    @if ($esDelEquipo)
                        <a href="{{ route('filament.admin.pages.dashboard') }}"
                           class="fila-pulsable block rounded-lg px-3 py-3 text-sm text-suave hover:text-fuerte">
                            Ir al panel del gremio
                        </a>
                    @else
                        <a href="{{ route('mi-cuenta.index') }}" class="fila-pulsable block rounded-lg px-3 py-3 text-sm text-suave hover:text-fuerte">
                            Mi cuenta
                        </a>
                    @endif
                    <form method="POST" action="{{ route('mi-cuenta.salir') }}">
                        @csrf
                        <button type="submit"
                                class="fila-pulsable block w-full rounded-lg px-3 py-3 text-left text-sm text-suave hover:text-fuerte">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</header>
