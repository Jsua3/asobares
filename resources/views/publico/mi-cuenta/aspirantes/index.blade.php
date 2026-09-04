<x-layouts.publico titulo="Banco de talento — ASOBARES Quindío"
                   descripcion="Perfiles de personas que buscan trabajo en el sector, visibles solo para establecimientos afiliados.">

    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

        <header>
            <a href="{{ route('mi-cuenta.index') }}" class="enlace-accion text-sm text-acento hover:text-acento-fuerte">
                <x-publico.flecha direccion="izquierda" />&nbsp;Mi cuenta
            </a>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-tight">Banco de talento</h1>
            <p class="mt-1.5 max-w-2xl text-sm text-tenue">
                Personas que dejaron su perfil buscando trabajo en el sector. Contáctalas directamente:
                el gremio no intermedia ni garantiza referencias.
            </p>
        </header>

        {{-- Filtro por cargo --}}
        <div class="mt-8 flex flex-wrap gap-2">
            <a href="{{ route('mi-cuenta.aspirantes.index') }}"
               @class([
                   'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                   'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => empty($filtros['categoria']),
                   'border-linea text-suave hover:border-marca-500/40' => ! empty($filtros['categoria']),
               ])>Todos</a>

            @foreach ($categorias as $categoria)
                <a href="{{ route('mi-cuenta.aspirantes.index', ['categoria' => $categoria->value]) }}"
                   @class([
                       'pulsable inline-flex min-h-11 items-center rounded-xl border px-4 text-sm',
                       'border-marca-500 bg-marca-500/10 font-medium text-acento-fuerte' => ($filtros['categoria'] ?? null) === $categoria->value,
                       'border-linea text-suave hover:border-marca-500/40' => ($filtros['categoria'] ?? null) !== $categoria->value,
                   ])>{{ $categoria->getLabel() }}</a>
            @endforeach
        </div>

        @if ($aspirantes->isEmpty())
            <div class="tarjeta mt-10 p-12 text-center">
                <p class="font-display text-lg font-semibold">Todavía no hay perfiles en este cargo</p>
                <p class="mt-2 text-sm text-tenue">
                    El banco se llena con quien deja su hoja de vida en la bolsa de empleo.
                </p>
            </div>
        @else
            <ul class="mt-10 space-y-4">
                @foreach ($aspirantes as $aspirante)
                    <li class="tarjeta p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                        {{ $aspirante->categoria_cargo->getLabel() }}
                                    </span>
                                    <span class="text-apagado">
                                        Registrado el {{ $aspirante->created_at->translatedFormat('d \d\e F \d\e Y') }}
                                    </span>
                                </div>

                                <h2 class="mt-3 font-display text-lg font-semibold">{{ $aspirante->nombre }}</h2>
                                <p class="mt-1 text-sm text-tenue">{{ $aspirante->cargo_interes }}</p>

                                @if ($aspirante->experiencia)
                                    <p class="mt-3 text-sm leading-relaxed text-tenue">{{ $aspirante->experiencia }}</p>
                                @endif
                            </div>

                            <div class="flex w-full shrink-0 flex-col gap-2 sm:w-56">
                                @if ($enlace = enlaceWhatsapp($aspirante->telefono, "Hola, vi tu perfil en el banco de talento de ASOBARES Quindío."))
                                    <x-publico.boton :href="$enlace" target="_blank" rel="noopener nofollow" class="w-full">
                                        WhatsApp
                                    </x-publico.boton>
                                @endif
                                <a href="mailto:{{ $aspirante->correo }}"
                                   class="pulsable block min-h-11 wrap-anywhere rounded-xl border border-linea px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
                                    {{ $aspirante->correo }}
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-10">{{ $aspirantes->links() }}</div>
        @endif

        <p class="mt-10 text-xs leading-relaxed text-apagado">
            Estos datos son personales y te los entrega el gremio para un fin concreto: contratar.
            Usarlos para otra cosa, o pasarlos a un tercero, te saca de la Ley 1581 de 2012 y de este
            beneficio.
        </p>
    </div>
</x-layouts.publico>
