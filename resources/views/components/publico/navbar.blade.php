@php
    $enlaces = [
        ['ruta' => 'directorio.index', 'texto' => 'Directorio'],
        ['ruta' => 'guia.index', 'texto' => 'Abre tu negocio'],
        ['ruta' => 'empleo.index', 'texto' => 'Empleo'],
        ['ruta' => 'artistas.index', 'texto' => 'Artistas'],
        ['ruta' => 'proveedores.index', 'texto' => 'Proveedores'],
        ['ruta' => 'eventos.index', 'texto' => 'Eventos'],
        ['ruta' => 'boletin.index', 'texto' => 'Boletín'],
        ['ruta' => 'quienes-somos', 'texto' => 'Quiénes somos'],
    ];

    $usuario = auth()->user();
    $esDelEquipo = (bool) ($usuario?->esSuperAdmin() || $usuario?->esSubadmin());
@endphp

{{-- `menuMovil` y no `abierto`: el desplegable de configuración anida su propio
     x-data y dos propiedades con el mismo nombre se pisarían. --}}
<header x-data="{ menuMovil: false }"
        class="sticky top-0 z-40 border-b border-linea bg-fondo/85 backdrop-blur-md">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8"
         aria-label="Navegación principal">

        <a href="{{ route('inicio') }}" class="shrink-0" aria-label="Inicio — ASOBARES Capítulo Quindío">
            <x-publico.logo alto="h-8 sm:h-9" />
        </a>

        {{-- Escritorio --}}
        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($enlaces as $enlace)
                <a href="{{ route($enlace['ruta']) }}"
                   @class([
                       'rounded-lg px-3 py-2 text-sm transition-colors',
                       'text-acento' => request()->routeIs(str_replace('.index', '.*', $enlace['ruta'])),
                       'text-suave hover:text-fuerte' => ! request()->routeIs(str_replace('.index', '.*', $enlace['ruta'])),
                   ])>
                    {{ $enlace['texto'] }}
                </a>
            @endforeach
        </div>

        <div class="hidden items-center gap-2 lg:flex">
            {{-- Con sesión abierta el atajo vive dentro del desplegable, para no
                 repetir el mismo enlace dos veces en la misma barra. --}}
            @guest
                <a href="{{ route('mi-cuenta.index') }}"
                   class="rounded-lg px-3 py-2 text-sm text-tenue transition-colors hover:text-fuerte">
                    Mi cuenta
                </a>
            @endguest
            <a href="{{ route('afiliate') }}"
               class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                Afíliate
            </a>
            <x-publico.menu-usuario />
        </div>

        {{-- Móvil --}}
        <button type="button" x-on:click="menuMovil = ! menuMovil"
                class="rounded-lg p-2 text-suave lg:hidden"
                x-bind:aria-expanded="menuMovil ? 'true' : 'false'" aria-controls="menu-movil">
            <span class="sr-only">Abrir menú</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path x-show="! menuMovil" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                <path x-show="menuMovil" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
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
                    <button type="submit" class="text-sm text-acento underline">Cerrar sesión</button>
                </form>
            </div>
        </noscript>
    @endauth

    <div id="menu-movil" x-show="menuMovil" x-cloak x-collapse class="border-t border-linea bg-fondo lg:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($enlaces as $enlace)
                <a href="{{ route($enlace['ruta']) }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-tinta hover:bg-superficie-alta">
                    {{ $enlace['texto'] }}
                </a>
            @endforeach
            <a href="{{ route('contacto') }}" class="block rounded-lg px-3 py-2.5 text-sm text-tinta hover:bg-superficie-alta">Contacto</a>
            @guest
                <a href="{{ route('mi-cuenta.index') }}" class="block rounded-lg px-3 py-2.5 text-sm text-tinta hover:bg-superficie-alta">Mi cuenta</a>
            @endguest
            <a href="{{ route('afiliate') }}"
               class="mt-2 block rounded-lg bg-marca-500 px-3 py-2.5 text-center text-sm font-semibold text-white">
                Afíliate
            </a>
        </div>

        {{-- Apariencia y sesión: lo mismo que ofrece el desplegable de
             escritorio, desplegado porque en móvil no hay sitio para anidar. --}}
        <div class="border-t border-linea px-4 py-4">
            <p class="antetitulo text-apagado">Apariencia</p>
            <x-publico.selector-tema class="mt-2" />
        </div>

        @auth
            <div class="border-t border-linea px-4 py-4">
                <p class="truncate text-sm font-semibold text-fuerte">{{ $usuario->name }}</p>
                <div class="mt-2 space-y-1">
                    @if ($esDelEquipo)
                        <a href="{{ route('filament.admin.pages.dashboard') }}"
                           class="block rounded-lg px-3 py-2.5 text-sm text-suave hover:bg-superficie-alta hover:text-fuerte">
                            Ir al panel del gremio
                        </a>
                    @else
                        <a href="{{ route('mi-cuenta.index') }}" class="block rounded-lg px-3 py-2.5 text-sm text-suave hover:bg-superficie-alta hover:text-fuerte">
                            Mi cuenta
                        </a>
                    @endif
                    <form method="POST" action="{{ route('mi-cuenta.salir') }}">
                        @csrf
                        <button type="submit"
                                class="block w-full rounded-lg px-3 py-2.5 text-left text-sm text-suave hover:bg-superficie-alta hover:text-fuerte">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</header>
