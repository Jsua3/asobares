<x-layouts.publico :titulo="ajuste('proveedores_titulo').' — ASOBARES Quindío'"
                   descripcion="Hielo, licores, alimentos, aseo, seguridad y mantenimiento para bares y gastrobares del Quindío.">

    <x-publico.hero :titulo="ajuste('proveedores_titulo')" :subtitulo="ajuste('proveedores_intro')" compacto />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Filtro por categoría --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('proveedores.index') }}"
               @class([
                   'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                   'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => empty($filtros['categoria']),
                   'border-linea text-suave hover:border-marca-500/40' => ! empty($filtros['categoria']),
               ])
               @if (empty($filtros['categoria'])) style="view-transition-name: filtro-activo" @endif>Todas</a>

            @foreach ($categorias as $categoria)
                <a href="{{ route('proveedores.index', ['categoria' => $categoria->value]) }}"
                   @class([
                       'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                       'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => ($filtros['categoria'] ?? null) === $categoria->value,
                       'border-linea text-suave hover:border-marca-500/40' => ($filtros['categoria'] ?? null) !== $categoria->value,
                   ])
                   @if (($filtros['categoria'] ?? null) === $categoria->value) style="view-transition-name: filtro-activo" @endif>{{ $categoria->getLabel() }}</a>
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

                    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($grupo as $proveedor)
                            <article class="tarjeta flex flex-col p-6">
                                <h3 class="font-display text-base font-semibold">{{ $proveedor->nombre }}</h3>

                                @if ($proveedor->municipio)
                                    <p class="mt-1 text-xs text-apagado">{{ $proveedor->municipio->nombre }}</p>
                                @endif

                                <p class="mt-3 flex-1 text-sm leading-relaxed text-tenue">{{ $proveedor->descripcion }}</p>

                                {{-- OBS3-12: «y que si respondan, y que la informacion este
                                     actualizada» (R22 04:13). Un contacto sin fecha no vale mas
                                     que un contacto viejo: vale menos, porque el lector no sabe
                                     cual de los dos tiene. Mismo patron que RF-60 en la guia. --}}
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
                                    {{-- `wrap-anywhere` y no `break-words`: la tarjeta es un
                                         elemento flex con `min-width: auto`, así que su ancho lo
                                         fija el ancho MÍNIMO de su contenido, y
                                         `overflow-wrap: break-word` no reduce esa medida — solo
                                         parte al desbordar. Un correo largo se mide entero y
                                         empuja la tarjeta: medido a 320 px,
                                         `operaciones@vigilancianocturna.test` ocupa 299 px sin
                                         punto de corte y estiraba la tarjeta a 349, con la página
                                         desplazándose en horizontal. `anywhere` sí entra en el
                                         cálculo del mínimo. --}}
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

        {{-- CTA para entrar a la base --}}
        <section class="tarjeta mt-16 p-8 text-center">
            <h2 class="font-display text-xl font-semibold">¿Quieres aparecer aquí?</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                Si le vendes al sector nocturno del Quindío, escríbenos y te contamos cómo entrar a la
                bolsa de proveedores del gremio.
            </p>
            <x-publico.boton :href="route('proveedores.inscripcion')" class="mt-6">
                Inscribirme en la bolsa
            </x-publico.boton>
        </section>
    </div>
</x-layouts.publico>
