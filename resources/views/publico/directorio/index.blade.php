@php
    $hayFiltros = filled($filtros['q'] ?? null)
        || filled($filtros['municipio'] ?? null)
        || filled($filtros['categoria'] ?? null);

    $listado = $vista === 'mapa'
        ? route('directorio.index', ['vista' => 'mapa'])
        : route('directorio.index');

    $puntos = $vista === 'mapa'
        ? $asociados->filter->tieneUbicacion()->map(fn ($a) => [
            'lat' => $a->lat,
            'lng' => $a->lng,
            'nombre' => $a->nombre,
            'html' => '<strong>'.e($a->nombre).'</strong><br>'
                .e($a->categoria->nombre).' · '.e($a->municipio->nombre).'<br>'
                .'<a href="'.route('directorio.show', $a).'" style="display:inline-flex;min-height:44px;align-items:center">Ver ficha</a>',
        ])->values()->all()
        : [];

    $coleccionHero = $asociados instanceof \Illuminate\Pagination\AbstractPaginator
        ? $asociados->getCollection()
        : collect($asociados);

    $fotoHeroDirectorio = $coleccionHero
        ->first(fn ($asociado) => filled($asociado->foto_portada))
        ?->foto_portada;
@endphp

<x-layouts.publico :titulo="ajuste('seo_directorio_titulo', 'Directorio de establecimientos — ASOBARES Quindío')"
                   :descripcion="ajuste('seo_directorio_descripcion', 'Bares, gastrobares, cafés y discotecas afiliados al gremio en Armenia, Salento, Filandia y todo el Quindío.')">

    <x-publico.hero :titulo="ajuste('directorio_titulo', 'Directorio de establecimientos')" compacto atmosfera
                    :subtitulo="ajuste('directorio_intro', 'Bares, gastrobares, cafés y discotecas afiliados en el Quindío.')">
        @if ($fotoHeroDirectorio)
            <x-slot:medio>
                <img src="{{ Storage::disk('public')->url($fotoHeroDirectorio) }}"
                     alt=""
                     width="1600"
                     height="720"
                     class="opacity-55 saturate-125">
            </x-slot:medio>
        @endif
    </x-publico.hero>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"
         x-data="{
             panelVisible: true,
             drawerAbierto: false,
             abrirDrawer() {
                 this.drawerAbierto = true;
                 document.body.classList.add('overflow-hidden');
             },
             cerrarDrawer() {
                 this.drawerAbierto = false;
                 document.body.classList.remove('overflow-hidden');
             },
         }"
         x-on:keydown.escape.window="if (drawerAbierto) cerrarDrawer()">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:gap-8">

            {{-- Escritorio: panel lateral colapsable --}}
            <div class="hidden shrink-0 lg:block">
                <button type="button"
                        x-show="! panelVisible"
                        x-cloak
                        x-on:click="panelVisible = true"
                        class="pulsable sticky top-24 inline-flex min-h-11 items-center gap-2 rounded-xl border border-linea bg-superficie px-4 py-2.5 text-sm font-medium text-suave hover:text-fuerte"
                        aria-controls="directorio-filtros-panel"
                        aria-expanded="false">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/>
                    </svg>
                    Filtros
                </button>

                <div id="directorio-filtros-panel"
                     x-show="panelVisible"
                     x-collapse
                     class="w-72">
                    <div class="sticky top-24">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h2 class="font-display text-sm font-semibold text-fuerte">Filtros</h2>
                            <button type="button"
                                    x-on:click="panelVisible = false"
                                    class="pulsable inline-flex min-h-11 items-center gap-1.5 rounded-lg px-2 py-2 text-xs text-tenue hover:text-fuerte"
                                    aria-controls="directorio-filtros-panel"
                                    x-bind:aria-expanded="panelVisible ? 'true' : 'false'">
                                <span class="sr-only">Ocultar filtros</span>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
                                </svg>
                            </button>
                        </div>

                        <x-publico.filtros-directorio
                            :filtros="$filtros"
                            :municipios="$municipios"
                            :categorias="$categorias"
                            :vista="$vista"
                            :hay-filtros="$hayFiltros"
                            :listado="$listado"
                            class="vidrio grid gap-4 p-5" />
                    </div>
                </div>
            </div>

            {{-- Columna principal: pauta y resultados --}}
            <div class="min-w-0 flex-1">

                {{-- Móvil: botón que abre la hoja de filtros --}}
                <div class="mb-4 flex items-center justify-between gap-3 lg:hidden">
                    <button type="button"
                            x-on:click="abrirDrawer()"
                            class="pulsable inline-flex min-h-11 items-center gap-2 rounded-xl border border-linea bg-superficie px-4 py-2.5 text-sm font-medium text-suave hover:text-fuerte"
                            aria-controls="directorio-filtros-drawer"
                            x-bind:aria-expanded="drawerAbierto ? 'true' : 'false'">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/>
                        </svg>
                        Filtros
                        @if ($hayFiltros)
                            <span class="rounded-full bg-accion px-2 py-0.5 text-2xs font-semibold text-white" aria-hidden="true">·</span>
                            <span class="sr-only">Hay filtros activos</span>
                        @endif
                    </button>
                </div>

                {{-- Hoja móvil: mismo patrón visual que los desplegables del sitio --}}
                <div class="lg:hidden"
                     x-show="drawerAbierto"
                     x-cloak
                     class="fixed inset-0 z-50"
                     role="presentation">
                    <div class="absolute inset-0 bg-fondo/70"
                         x-on:click="cerrarDrawer()"
                         x-show="drawerAbierto"
                         x-transition:enter="transicion-desplegable ease-out duration-(--duracion-entrada)"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transicion-desplegable ease-out duration-(--duracion-salida)"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         aria-hidden="true"></div>

                    <div id="directorio-filtros-drawer"
                         role="dialog"
                         aria-modal="true"
                         aria-labelledby="directorio-filtros-titulo"
                         x-show="drawerAbierto"
                         x-transition:enter="transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)"
                         x-transition:enter-start="opacity-0 translate-y-full"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-full"
                         class="hoja-flotante absolute inset-x-0 bottom-0 max-h-[min(85svh,32rem)] touch-pan-y overflow-y-auto overscroll-contain rounded-t-2xl p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom,0px))]">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h2 id="directorio-filtros-titulo" class="font-display text-lg font-semibold">Filtros</h2>
                            <button type="button"
                                    x-on:click="cerrarDrawer()"
                                    class="pulsable inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl border border-linea text-tenue hover:text-fuerte"
                                    aria-label="Cerrar filtros">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <x-publico.filtros-directorio
                            :filtros="$filtros"
                            :municipios="$municipios"
                            :categorias="$categorias"
                            :vista="$vista"
                            :hay-filtros="$hayFiltros"
                            :listado="$listado"
                            class="grid gap-4" />
                    </div>
                </div>

                @if ($publicidadDirectorio)
                    <x-publico.publicidad :publicidad="$publicidadDirectorio" />
                @endif

                <div id="resultados">
                    {{-- Cambio de vista --}}
                    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                        <p class="text-sm text-tenue">
                            @if ($vista === 'mapa')
                                {{ $asociados->count() }} {{ Str::plural('establecimiento', $asociados->count()) }} en el mapa
                            @else
                                {{ $asociados->total() }} {{ Str::plural('establecimiento', $asociados->total()) }}
                            @endif
                        </p>

                        <div class="inline-flex rounded-xl border border-linea p-1" role="group" aria-label="Cambiar vista">
                            @foreach (['grid' => 'Tarjetas', 'mapa' => 'Mapa'] as $clave => $texto)
                                <a href="{{ request()->fullUrlWithQuery(['vista' => $clave, 'page' => null]) }}"
                                   @class([
                                       'pulsable inline-flex min-h-11 items-center rounded-lg px-4 text-sm',
                                       'bg-accion font-medium text-white' => $vista === $clave,
                                       'text-tenue hover:text-fuerte' => $vista !== $clave,
                                   ])
                                   @if ($vista === $clave) aria-current="true" @endif>
                                    {{ $texto }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Resultados --}}
                    @if ($asociados->isEmpty())
                        <div class="tarjeta mt-6 p-12 text-center">
                            @if ($hayFiltros)
                                <p class="font-display text-lg font-semibold">No encontramos establecimientos con ese filtro</p>
                                <p class="mt-2 text-sm text-tenue">Prueba con otro municipio o limpia la búsqueda.</p>
                            @else
                                <p class="font-display text-lg font-semibold">Todavía no hay establecimientos publicados</p>
                                <p class="mt-2 text-sm text-tenue">Cuando el gremio publique fichas, aparecen aquí.</p>
                            @endif
                            @if ($hayFiltros)
                                <a href="{{ $listado }}"
                                   class="pulsable mt-5 inline-block min-h-11 rounded-xl border border-linea-fuerte px-5 py-2.5 text-sm hover:border-marca-500/50">
                                    Ver todos
                                </a>
                            @endif
                        </div>
                    @elseif ($vista === 'mapa')
                        <x-publico.mapa :puntos="$puntos" class="mt-6" />
                        @if ($puntos === [])
                            <p class="mt-4 text-center text-sm text-apagado">
                                Ningún establecimiento de este filtro tiene ubicación registrada.
                            </p>
                        @endif
                    @else
                        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($asociados as $asociado)
                                <div>
                                    <x-publico.tarjeta-asociado :asociado="$asociado" />
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-10">{{ $asociados->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.publico>
