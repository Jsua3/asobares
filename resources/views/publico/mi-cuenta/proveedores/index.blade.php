<x-layouts.publico titulo="Proveedores del gremio — ASOBARES Quindío"
                   descripcion="Directorio de proveedores verificados, exclusivo para establecimientos afiliados.">

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        <header>
            <a href="{{ route('mi-cuenta.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
                <x-publico.flecha direccion="izquierda" />&nbsp;Mi cuenta
            </a>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight">Proveedores del gremio</h1>
            <p class="mt-1.5 max-w-2xl text-sm text-tenue">
                Contactos verificados por la secretaría. Son un beneficio de tu afiliación: no aparecen
                en la parte pública del sitio.
            </p>
        </header>

        {{-- Filtro por categoría --}}
        <div class="mt-8 flex flex-wrap gap-2">
            <a href="{{ route('mi-cuenta.proveedores.index') }}"
               @class([
                   'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                   'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => empty($filtros['categoria']),
                   'border-linea text-suave hover:border-marca-500/40' => ! empty($filtros['categoria']),
               ])>Todas</a>

            @foreach ($categorias as $categoria)
                <a href="{{ route('mi-cuenta.proveedores.index', ['categoria' => $categoria->value]) }}"
                   @class([
                       'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                       'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => ($filtros['categoria'] ?? null) === $categoria->value,
                       'border-linea text-suave hover:border-marca-500/40' => ($filtros['categoria'] ?? null) !== $categoria->value,
                   ])>{{ $categoria->getLabel() }}</a>
            @endforeach
        </div>

        @if ($proveedores->isEmpty())
            <div class="tarjeta mt-8 p-12 text-center">
                <p class="font-display text-lg font-semibold">No hay proveedores en esta categoría</p>
                <p class="mt-2 text-sm text-tenue">Estamos ampliando la base.</p>
            </div>
        @else
            @foreach ($grupos as $clave => $grupo)
                @php $categoria = \App\Enums\CategoriaProveedor::from($clave); @endphp

                <section class="mt-12" aria-labelledby="cat-{{ $clave }}">
                    <h2 id="cat-{{ $clave }}" class="flex items-center gap-3 font-display text-xl font-bold">
                        <x-dynamic-component :component="$categoria->icono()" class="h-5 w-5 text-acento" />
                        {{ $categoria->getLabel() }}
                        <span class="text-sm font-normal text-apagado">({{ $grupo->count() }})</span>
                    </h2>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($grupo as $proveedor)
                            <article class="tarjeta tarjeta-hover flex flex-col p-6">
                                <h3 class="font-display text-base font-semibold">{{ $proveedor->nombre }}</h3>

                                @if ($proveedor->municipio)
                                    <p class="mt-1 text-xs text-apagado">{{ $proveedor->municipio->nombre }}</p>
                                @endif

                                <p class="mt-3 flex-1 text-sm leading-relaxed text-tenue">{{ $proveedor->descripcion }}</p>

                                {{-- OBS3-12: un contacto sin fecha no vale más que un contacto
                                     viejo: vale menos, porque el lector no sabe cuál de los dos
                                     tiene. Mismo patrón que RF-60 en la guía. --}}
                                <p class="mt-3 text-2xs">
                                    @if (! $proveedor->estaVerificado())
                                        <span class="text-aviso-suave">{{ ajuste('proveedores_sin_verificar') }}</span>
                                    @elseif ($proveedor->necesitaRevision())
                                        <span class="text-aviso-suave">
                                            {{ ajuste('proveedores_verificacion_vieja') }}
                                            {{ $proveedor->verificado_el->translatedFormat('F \d\e Y') }}
                                        </span>
                                    @else
                                        <span class="text-apagado">
                                            {{ ajuste('proveedores_verificado') }}
                                            {{ $proveedor->verificado_el->translatedFormat('d \d\e F \d\e Y') }}
                                        </span>
                                    @endif
                                </p>

                                <div class="mt-5 space-y-2">
                                    @if ($enlace = enlaceWhatsapp($proveedor->whatsapp, "Hola, los vi en la bolsa de proveedores de ASOBARES Quindío."))
                                        <x-publico.boton :href="$enlace" target="_blank" rel="noopener nofollow" class="w-full">
                                            WhatsApp
                                        </x-publico.boton>
                                    @endif
                                    @if ($proveedor->correo)
                                        <a href="mailto:{{ $proveedor->correo }}"
                                           class="pulsable block min-h-11 wrap-anywhere rounded-xl border border-linea px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
                                            {{ $proveedor->correo }}
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <div class="mt-10">{{ $proveedores->links() }}</div>
        @endif
    </div>
</x-layouts.publico>
