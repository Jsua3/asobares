<x-layouts.publico :titulo="ajuste('proveedores_titulo').' — ASOBARES Quindío'"
                   descripcion="Hielo, licores, alimentos, aseo, seguridad y mantenimiento para bares y gastrobares del Quindío.">

    <x-publico.hero :titulo="ajuste('proveedores_titulo')" :subtitulo="ajuste('proveedores_intro')" compacto />

    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Filtro por categoría --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('proveedores.index') }}"
               @class([
                   'rounded-xl border px-4 py-2 text-sm transition-colors',
                   'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => empty($filtros['categoria']),
                   'border-linea text-suave hover:border-marca-500/40' => ! empty($filtros['categoria']),
               ])>Todas</a>

            @foreach ($categorias as $categoria)
                <a href="{{ route('proveedores.index', ['categoria' => $categoria->value]) }}"
                   @class([
                       'rounded-xl border px-4 py-2 text-sm transition-colors',
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

                    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($grupo as $proveedor)
                            <article class="tarjeta flex flex-col p-6">
                                <h3 class="font-display text-base font-semibold">{{ $proveedor->nombre }}</h3>

                                @if ($proveedor->municipio)
                                    <p class="mt-1 text-xs text-apagado">{{ $proveedor->municipio->nombre }}</p>
                                @endif

                                <p class="mt-3 flex-1 text-sm leading-relaxed text-tenue">{{ $proveedor->descripcion }}</p>

                                <div class="mt-5 space-y-2">
                                    @if ($enlace = enlaceWhatsapp($proveedor->whatsapp, "Hola, los vi en la bolsa de proveedores de ASOBARES Quindío."))
                                        <x-publico.boton :href="$enlace" target="_blank" rel="noopener nofollow" class="w-full">
                                            WhatsApp
                                        </x-publico.boton>
                                    @endif
                                    @if ($proveedor->correo)
                                        <a href="mailto:{{ $proveedor->correo }}"
                                           class="block rounded-xl border border-linea px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
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
