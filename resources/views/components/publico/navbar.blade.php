@php
    /*
     * La navegación se declara UNA vez y se pinta dos: escritorio con los
     * grupos plegados en la bandeja, y móvil en el módulo inferior con las
     * mismas cinco secciones y los grupos como hojas que suben al tocar. Que
     * las dos salgan del mismo arreglo es lo que impide que diverjan, que es
     * exactamente lo que ya había pasado: «Contacto» solo existía en móvil y en
     * el pie, y en escritorio no se alcanzaba desde ningún sitio.
     *
     * Tres directos y dos grupos, y el reparto no es estético: «Abre tu
     * negocio» es el módulo insignia y no puede quedar enterrado en un
     * desplegable, mientras que «Bolsas» es como se llama ese mismo grupo en el
     * menú del panel que ya usa el personal del gremio.
     *
     * Los iconos son los que el panel asigna a los mismos conceptos, porque la
     * secretaría los ve a diario (Parte II, D-M16). Y la entrada del afiliado
     * en el teléfono va aquí, como fila de pie de «El gremio» solo sin sesión
     * (D-M4): un destino más de la navegación, declarado con los demás.
     */
    $enlacesDirectos = [
        ['ruta' => 'directorio.index', 'texto' => 'Directorio', 'icono' => 'building-storefront'],
        ['ruta' => 'guia.index', 'texto' => 'Abre tu negocio', 'icono' => 'clipboard-document-check'],
        ['ruta' => 'eventos.index', 'texto' => 'Eventos', 'icono' => 'calendar-days'],
    ];

    $grupos = [
        [
            'titulo' => 'Bolsas',
            'icono' => 'briefcase',
            'enlaces' => [
                ['ruta' => 'empleo.index', 'texto' => 'Empleo'],
                ['ruta' => 'artistas.index', 'texto' => 'Artistas'],
                ['ruta' => 'proveedores.index', 'texto' => 'Proveedores'],
            ],
        ],
        [
            'titulo' => 'El gremio',
            'icono' => 'user-group',
            'enlaces' => [
                ['ruta' => 'quienes-somos', 'texto' => 'Quiénes somos'],
                ['ruta' => 'boletin.index', 'texto' => 'Boletín'],
                ['ruta' => 'contacto', 'texto' => 'Contacto'],
            ],
            'pie' => [['ruta' => 'mi-cuenta.entrar', 'texto' => 'Entrar como afiliado', 'solo' => 'guest']],
        ],
    ];

    /** Una ruta `x.index` gobierna toda su sección; las sueltas se comparan tal cual. */
    $patron = static fn (string $ruta): string => str_replace('.index', '.*', $ruta);
@endphp

{{-- `desplazado` gobierna la separación con el contenido: la barra solo se
     apoya —sombra en claro, filo de luz en oscuro— cuando hay algo pasando por
     debajo. El umbral de 8 px evita que el rebote elástico del scroll en iOS
     la encienda y apague sola en el tope. --}}
{{-- Tres estados de escritorio, resueltos aquí y pintados por CSS desde
     `data-estado`: `inicial` en el tope, `scroll` al bajar, `atencion` cuando
     el usuario pide la barra entera (ratón encima, toque en el módulo
     principal, o teclado dentro, que lo resuelve CSS con :focus-within).
     Bajo 64rem no hay atención: `scroll` lo decide la DIRECCIÓN del
     desplazamiento con ancla e histéresis (Parte II §4.2), y `data-teclado`
     retira el módulo inferior ante el teclado virtual.
     Spec: docs/ingenieria/navbar-tres-estados-diseno.md. --}}
<header x-data="{
            desplazado: false,
            atendiendo: false,
            compacta: false,
            teclado: false,
            esEscritorio: true,
            ancla: 0,
            altoReferencia: 0,
            cierre: null,
            scrollAlAtender: 0,
            campo: 'input:not([type=checkbox]):not([type=radio]):not([type=submit]), textarea, select, [contenteditable=true]',
            init() {
                // Una sola frontera y es la del CSS: 64rem, no un ancho en píxeles.
                // Cruzarla (girar un iPad) devuelve la barra a inicial y cierra
                // todas las hojas: ceder(null) cierra a todos, null no es raíz de nadie.
                const consulta = window.matchMedia('(min-width: 64rem)');
                this.esEscritorio = consulta.matches;
                consulta.addEventListener('change', (evento) => {
                    this.esEscritorio = evento.matches;
                    this.compacta = false;
                    this.$dispatch('desplegable-abierto', null);
                });
                this.ancla = this.posicion();
                this.altoReferencia = window.visualViewport?.height ?? window.innerHeight;
                window.matchMedia('(orientation: portrait)').addEventListener('change', () => {
                    this.altoReferencia = window.visualViewport?.height ?? window.innerHeight;
                });
                window.visualViewport?.addEventListener('resize', () => this.medirTeclado());
            },
            get estado() {
                if (! this.desplazado) {
                    return 'inicial';
                }

                // Por debajo de 64rem no hay puntero que atender: compacta o no.
                // Por ancho y no por puntero: un ratón en una ventana estrecha sí
                // dispara mouseenter y pasar por la barra la descompactaría.
                if (! this.esEscritorio) {
                    return this.compacta ? 'scroll' : 'inicial';
                }

                return this.atendiendo ? 'atencion' : 'scroll';
            },
            punteroFino() {
                return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            },
            menosMovimiento() {
                return document.documentElement.classList.contains('sin-desplazamiento');
            },
            // Dentro del documento. El navegador acota scrollY con el alto
            // VIGENTE del viewport (innerHeight, que en iOS crece al plegarse la
            // barra de direcciones): con el alto del bloque contenedor inicial
            // el tope quedaba 80-110 px por encima del real y el rebote
            // elástico del final pasaba el clamp.
            posicion() {
                const tope = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);

                return Math.min(Math.max(window.scrollY, 0), tope);
            },
            sincronizar() {
                const actual = Math.max(window.scrollY, 0);

                this.desplazado = actual > 8;

                if (! this.esEscritorio) {
                    this.compactar();
                }

                // Con dedo, desplazarse es soltar: 24 px desde que se abrió.
                if (this.atendiendo && ! this.punteroFino() && Math.abs(actual - this.scrollAlAtender) > 24) {
                    this.atendiendo = false;
                }
            },
            // La DIRECCIÓN decide, con histéresis. El ancla sigue al EXTREMO del
            // recorrido en el sentido vigente (el punto más bajo bajando, el más
            // alto subiendo), así que volver cuesta siempre 12 px desde donde el
            // dedo paró, e irse 24: el temblor y el subpíxel inercial no llegan.
            // Los dos extremos del documento son zona muerta (rebote elástico),
            // un salto mayor de 200 px no es un gesto, y bajo movimiento
            // reducido no hay estado scroll: compactar es animación ligada al
            // gesto (WCAG 2.3.3), y no se pierde nada por no hacerlo.
            compactar() {
                if (this.menosMovimiento()) {
                    this.compacta = false;

                    return;
                }

                const dentro = this.posicion();
                const tope = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);
                const recorrido = dentro - this.ancla;

                if (dentro <= 8) {
                    this.compacta = false;
                    this.ancla = dentro;

                    return;
                }

                if (dentro >= tope || Math.abs(recorrido) > 200) {
                    this.ancla = dentro;

                    return;
                }

                if ((this.compacta && recorrido > 0) || (! this.compacta && recorrido < 0)) {
                    this.ancla = dentro;

                    return;
                }

                if (recorrido > 24) {
                    this.compacta = true;
                    this.ancla = dentro;

                    return;
                }

                if (recorrido < -12) {
                    this.compacta = false;
                    this.ancla = dentro;
                }
            },
            campoEnfocado() {
                return document.activeElement?.matches(this.campo) ?? false;
            },
            esCampo(elemento) {
                return elemento?.matches?.(this.campo) ?? false;
            },
            // Teclado virtual: las DOS señales a la vez (foco en un campo y
            // viewport visual encogido más de 150 px). La referencia es el alto
            // MAYOR visto en esta orientación, no el del layout: donde el
            // teclado encoge también el viewport de layout la resta contra él
            // daba cero y la barra se pegaba encima del teclado.
            medirTeclado() {
                const visual = window.visualViewport;

                if (visual !== undefined && visual.height > this.altoReferencia) {
                    this.altoReferencia = visual.height;
                }

                this.teclado = ! this.esEscritorio
                    && this.campoEnfocado()
                    && visual !== undefined
                    && (this.altoReferencia - visual.height) > 150;
            },
            atender() {
                if (! this.punteroFino()) {
                    return;
                }

                clearTimeout(this.cierre);
                this.atendiendo = true;
            },
            soltar() {
                if (! this.punteroFino()) {
                    return;
                }

                clearTimeout(this.cierre);
                this.cierre = setTimeout(() => {
                    this.atendiendo = false;
                }, 280);
            },
            alternarAtencion() {
                if (this.punteroFino()) {
                    return;
                }

                this.atendiendo = ! this.atendiendo;
                this.scrollAlAtender = Math.max(window.scrollY, 0);
            },
        }"
        x-init="sincronizar()"
        x-on:mouseenter="atender()"
        x-on:mouseleave="soltar()"
        x-on:scroll.window.passive="sincronizar()"
        x-on:focusin.window="$nextTick(() => medirTeclado())"
        x-on:focusout.window="if (! esCampo($event.relatedTarget)) teclado = false"
        x-bind:data-estado="estado"
        x-bind:data-teclado="teclado ? 'abierto' : null"
        x-bind:class="{ 'cromo-apoyado': desplazado }"
        data-estado="inicial"
        class="cromo cromo-fijo z-40">
    {{-- La <nav> es la píldora exterior y la escena del brillo: `escena` ya
         existe en app.js y escribe --puntero-x/y; con dedo o con movimiento
         reducido no hace nada, que es lo que se quiere.

         En escritorio es una rejilla `1fr auto 1fr` y no un flex con
         `justify-between`: así el módulo principal queda en el centro de la
         PANTALLA en los tres estados, gane lo que gane el logo al encogerse
         o la cuenta con un nombre largo. Con `justify-between` caía en el
         punto medio entre los otros dos, 120 px a la izquierda en scroll
         (medido a 1440, 1280 y 1024 el 5 sep).

         Bajo 64rem es el módulo superior (Parte II §6.1) y solo expone logo
         y cuenta: su nombre de landmark lo dice por ancho, y «Navegación
         principal» pasa al módulo inferior, que es donde están las secciones. --}}
    <nav x-data="escena"
         x-on:pointermove="seguir($event)"
         x-on:pointerleave="salir()"
         x-on:keydown.escape.window="atendiendo = false"
         x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`"
         x-bind:aria-label="esEscritorio ? 'Navegación principal' : 'Marca y cuenta'"
         class="bandeja mx-auto flex max-w-7xl items-center justify-between px-4 py-2 sm:px-6 lg:grid lg:grid-cols-[1fr_auto_1fr] lg:px-3"
         aria-label="Navegación principal">

        {{-- Módulo 1: la marca. `-my-1.5 py-1.5` es padding negativo óptico: el
             logo mide 28 px de alto en móvil y `min-h-11` lleva el objetivo a
             44, mientras el margen negativo devuelve al flujo lo de antes. Con
             sesión, `marca-compacta` cruza al isotipo en los dos estados: el
             logotipo no cabe junto al nombre, el rango y el tema (D-M7). --}}
        <a href="{{ route('inicio') }}"
           @class([
               'modulo modulo-logo pulsable -my-1.5 flex shrink-0 items-center py-1.5 lg:justify-self-start lg:px-3',
               'min-h-11',
               'marca-compacta' => auth()->check(),
           ])
           aria-label="Inicio — ASOBARES Capítulo Quindío">
            <x-publico.logo doble alto="h-7 sm:h-8" />
        </a>

        {{-- Módulo 2: los cinco controles, siempre los cinco y en este orden.
             Dos de ellos se pliegan en scroll por CSS; nada sale del DOM. Con
             dedo, tocar los huecos del módulo o el indicador alterna el estado
             de atención; tocar un enlace o un grupo hace lo suyo y nada más. --}}
        <div class="modulo modulo-principal hidden min-h-11 items-center gap-1 px-2 lg:flex lg:justify-self-center"
             x-on:click="if (! $event.target.closest('a, button')) alternarAtencion()"
             x-on:click.outside="if (! punteroFino()) atendiendo = false">
            @foreach ($enlacesDirectos as $enlace)
                @php($actual = request()->routeIs($patron($enlace['ruta'])))
                <a href="{{ route($enlace['ruta']) }}"
                   @if ($actual) aria-current="page" @endif
                   @class([
                       'nav-enlace enlace-accion -my-1 rounded-lg px-3 py-3 text-sm',
                       'control-plegable' => $enlace['ruta'] === 'guia.index',
                       'text-acento' => $actual,
                       'text-suave hover:text-fuerte' => ! $actual,
                   ])>
                    {{ $enlace['texto'] }}
                </a>
            @endforeach

            @foreach ($grupos as $grupo)
                <x-publico.menu-grupo :titulo="$grupo['titulo']"
                                      :enlaces="$grupo['enlaces']"
                                      :class="$grupo['titulo'] === 'El gremio' ? 'control-plegable' : ''" />
            @endforeach

            {{-- Solo con dedo y solo en scroll: la señal de que hay más. --}}
            <span class="indicador-mas -my-1 flex min-h-11 min-w-11 items-center justify-center rounded-lg px-2 py-3 text-apagado" aria-hidden="true">
                <x-heroicon-o-ellipsis-horizontal class="h-4 w-4" />
            </span>
        </div>

        {{-- Módulo 3: la cuenta, el tema y el idioma, el mismo DOM en los dos
             anchos desde el 6 sep (Parte II): bajo 64rem es la derecha del
             módulo superior. Con sesión abierta el atajo vive dentro del
             desplegable, para no repetir el mismo enlace dos veces en la misma
             barra; y «Afíliate» tampoco se ofrece: quien ya es del gremio no
             se afilia (Sua, 5 sep). `min-w-0` deja que el chip encoja en vez
             de empujar al tema fuera de la bandeja; el relleno lateral es de
             escritorio, porque a 320 px son 16 px que faltan. --}}
        <div class="modulo modulo-cuenta flex min-w-0 items-center gap-2 whitespace-nowrap lg:justify-self-end lg:px-2">
            @guest
                {{-- El enlace de texto es de escritorio: en el teléfono la entrada
                     del afiliado es una fila de la hoja de El gremio (D-M4). --}}
                <a href="{{ route('mi-cuenta.index') }}"
                   class="nav-enlace enlace-accion -my-1 rounded-lg px-3 py-3 text-sm text-tenue hover:text-fuerte max-lg:hidden">
                    Mi cuenta
                </a>
            <a href="{{ route('afiliate') }}"
               {{-- ::after y no padding: es la única pastilla pintada de la barra
                    y agrandarla se vería. `-inset-y-1.5` da 33,7 + 12 = 45,7 px de
                    área pulsable sin tocar el dibujo ni el alto del header (la
                    escala tipográfica había dejado la pastilla en 33,7 y con 4 px
                    por lado el área medía 42; medido el 6 sep a 1440 y a 390).

                    `.pulsable` convive con ese pseudoelemento: el `scale(0.97)`
                    del `:active` encoge también el `::after`. Medido con el
                    ratón abajo: el área efectiva pasa de 46,4 a 45,2 px, o sea
                    que sigue por encima del mínimo, y para entonces el
                    navegador ya fijó el destino del clic en el `pointerdown`.
                    A cambio, la utilidad de fundido de color se fue: pisaba al
                    portador y con ella moría la duración cero de su `:active`.
                    Se nombra y no se pega porque la guardia lee este archivo
                    crudo, comentarios incluidos. --}}
               {{-- Sin `bg-*` ni `hover:bg-*`: el relleno lo pone `.cta-vivo`
                    en app.css, porque al pulsar se vidria y el fondo depende de
                    `--vidriado`. Una utilidad los pisaría. `relative` tampoco
                    hace falta ya —el portador lo declara— pero se queda porque
                    el ::after del área pulsable lo necesitaba antes que él. --}}
               class="pulsable cta-vivo relative rounded-lg px-4 py-1.5 text-sm font-semibold after:absolute after:inset-x-0 after:-inset-y-1.5 after:content-['']">
                Afíliate
            </a>
            @endguest
            @auth
                <x-publico.menu-usuario />
            @endauth
            <x-publico.control-tema />
            <x-publico.control-idioma />
        </div>
    </nav>

    {{-- Sin JavaScript la hoja de cuenta nunca llega a abrirse —x-cloak la deja
         oculta—, y con ella se iría la única salida de sesión que queda en el
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

    {{-- Módulo inferior: la navegación del teléfono (Parte II §6.2). Segunda
         pintura de los mismos arreglos; hermano de la bandeja e hijo del
         <header>, que es donde las guardias cuentan. Fijo al viewport porque
         `.cromo` ya no lleva ningún desplazamiento que lo haga bloque
         contenedor. Landmark propio y con el nombre principal: por debajo de
         64rem la <nav> de arriba solo expone logo y cuenta. Ningún hijo lleva
         «modulo» ni «gap-1» en su clase: la guardia de escritorio cuenta por
         ahí sus tres módulos y su bloque de controles. --}}
    <nav id="menu-movil"
         class="modulo-inferior lg:hidden"
         aria-label="Navegación principal">
        <div class="pestanas mx-auto flex w-full max-w-xl items-stretch">
            @foreach ($enlacesDirectos as $enlace)
                @php($actual = request()->routeIs($patron($enlace['ruta'])))
                {{-- `fila-pulsable` y no `pulsable`: una pestaña de 72×68 encogida
                     un 3 % se lee como una arruga de la barra entera; el tinte
                     del `:active` es el único acuse en táctil. Contorno en
                     reposo, sólido en la sección actual: la forma dice lo mismo
                     que el color. Se decide en el servidor, como aria-current. --}}
                <a href="{{ route($enlace['ruta']) }}"
                   @if ($actual) aria-current="page" @endif
                   @class([
                       'pestana fila-pulsable flex min-h-11 flex-1 flex-col items-center justify-center rounded-xl px-0.5 text-center text-2xs font-medium',
                       'text-acento' => $actual,
                       'text-suave' => ! $actual,
                   ])>
                    {{-- La gota: el único objeto de la barra que se mueve. Solo
                         la pinta la pestaña activa, así que en el documento hay
                         siempre una y su nombre de transición no se duplica. --}}
                    @if ($actual)
                        <span class="pestana__gota" aria-hidden="true"></span>
                    @endif
                    <x-dynamic-component :component="'heroicon-'.($actual ? 's' : 'o').'-'.$enlace['icono']"
                                         class="h-6 w-6 shrink-0" aria-hidden="true" />
                    <span class="pestana__rotulo"><span class="text-balance">{{ $enlace['texto'] }}</span></span>
                </a>
            @endforeach

            @foreach ($grupos as $grupo)
                <x-publico.menu-grupo variante="pestana"
                                      :titulo="$grupo['titulo']"
                                      :enlaces="$grupo['enlaces']"
                                      :icono="$grupo['icono']"
                                      :pie="$grupo['pie'] ?? []" />
            @endforeach
        </div>
    </nav>
</header>
