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
@endphp

<header x-data="{ abierto: false }"
        class="sticky top-0 z-40 border-b border-white/[.09] bg-noche-950/85 backdrop-blur-md">
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
                       'text-marca-400' => request()->routeIs(str_replace('.index', '.*', $enlace['ruta'])),
                       'text-noche-200 hover:text-white' => ! request()->routeIs(str_replace('.index', '.*', $enlace['ruta'])),
                   ])>
                    {{ $enlace['texto'] }}
                </a>
            @endforeach
        </div>

        <div class="hidden items-center gap-2 lg:flex">
            <a href="{{ route('mi-cuenta.index') }}"
               class="rounded-lg px-3 py-2 text-sm text-noche-300 transition-colors hover:text-white">
                Mi cuenta
            </a>
            <a href="{{ route('afiliate') }}"
               class="rounded-lg bg-marca-500 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-marca-600">
                Afíliate
            </a>
        </div>

        {{-- Móvil --}}
        <button type="button" @click="abierto = !abierto"
                class="rounded-lg p-2 text-noche-200 lg:hidden"
                :aria-expanded="abierto" aria-controls="menu-movil">
            <span class="sr-only">Abrir menú</span>
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path x-show="!abierto" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                <path x-show="abierto" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </nav>

    <div id="menu-movil" x-show="abierto" x-cloak x-collapse class="border-t border-white/[.09] lg:hidden">
        <div class="space-y-1 px-4 py-3">
            @foreach ($enlaces as $enlace)
                <a href="{{ route($enlace['ruta']) }}"
                   class="block rounded-lg px-3 py-2.5 text-sm text-noche-100 hover:bg-noche-900">
                    {{ $enlace['texto'] }}
                </a>
            @endforeach
            <a href="{{ route('contacto') }}" class="block rounded-lg px-3 py-2.5 text-sm text-noche-100 hover:bg-noche-900">Contacto</a>
            <a href="{{ route('mi-cuenta.index') }}" class="block rounded-lg px-3 py-2.5 text-sm text-noche-100 hover:bg-noche-900">Mi cuenta</a>
            <a href="{{ route('afiliate') }}"
               class="mt-2 block rounded-lg bg-marca-500 px-3 py-2.5 text-center text-sm font-semibold text-white">
                Afíliate
            </a>
        </div>
    </div>
</header>
