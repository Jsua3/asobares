<x-layouts.publico :titulo="ajuste('empleo_titulo').' — ASOBARES Quindío'"
                   descripcion="Vacantes de bartender, chef, mesero y administrador en bares y gastrobares del Quindío. Publican solo los establecimientos asociados.">

    <x-publico.hero :titulo="ajuste('empleo_titulo')" :subtitulo="ajuste('empleo_intro')" compacto>
        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
            <x-publico.boton href="#perfil">
                Déjanos tu perfil
            </x-publico.boton>
            <x-publico.boton variante="contorno" href="#vacantes">
                Ver vacantes
            </x-publico.boton>
        </div>
    </x-publico.hero>

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">

        {{-- Muro de vacantes --}}
        <section id="vacantes" aria-labelledby="titulo-vacantes">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <h2 id="titulo-vacantes" class="font-display text-2xl font-bold">Vacantes abiertas</h2>
                <p class="text-xs text-apagado">{{ ajuste('empleo_aviso') }}</p>
            </div>

            <form method="GET" action="{{ route('empleo.index') }}#vacantes" class="tarjeta mt-6 grid gap-4 p-5 sm:grid-cols-3">
                <x-publico.campo nombre="categoria" etiqueta="Área" tipo="select"
                                 :valor="$filtros['categoria'] ?? null"
                                 :opciones="['' => 'Todas las áreas'] + collect($categorias)->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()" />
                <x-publico.campo nombre="municipio" etiqueta="Municipio" tipo="select"
                                 :valor="$filtros['municipio'] ?? null"
                                 :opciones="['' => 'Todos los municipios'] + $municipios->pluck('nombre', 'slug')->all()" />
                <div class="flex items-end gap-2">
                    <x-publico.boton class="flex-1">
                        Filtrar
                    </x-publico.boton>
                    @if (array_filter($filtros ?? []))
                        <a href="{{ route('empleo.index') }}"
                           class="rounded-xl border border-linea px-4 py-2.5 text-sm text-tenue hover:text-fuerte">Limpiar</a>
                    @endif
                </div>
            </form>

            @if ($vacantes->isEmpty())
                <div class="tarjeta mt-6 p-12 text-center">
                    @if (array_filter($filtros ?? []))
                        <p class="font-display text-lg font-semibold">No hay vacantes con ese filtro</p>
                        <p class="mt-2 text-sm text-tenue">
                            Prueba otro municipio o área, o deja tu perfil abajo para que te avisemos.
                        </p>
                    @else
                        <p class="font-display text-lg font-semibold">Todavía no hay vacantes abiertas</p>
                        <p class="mt-2 text-sm text-tenue">
                            Deja tu perfil abajo y te avisamos cuando aparezca una que encaje.
                        </p>
                    @endif
                </div>
            @else
                <ul class="mt-6 space-y-4">
                    @foreach ($vacantes as $vacante)
                        <li class="tarjeta tarjeta-hover p-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2 text-xs">
                                        <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                                            {{ $vacante->tipo->getLabel() }}
                                        </span>
                                        <span class="text-apagado">
                                            {{ $vacante->asociado->municipio->nombre }} · publicada {{ $vacante->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 font-display text-lg font-semibold">
                                        <a href="{{ route('empleo.show', $vacante) }}" class="hover:text-acento">
                                            {{ $vacante->cargo }}
                                        </a>
                                    </h3>

                                    <p class="mt-1 text-sm text-tenue">
                                        en
                                        @if ($vacante->asociado->estaPublicado())
                                            <a href="{{ route('directorio.show', $vacante->asociado) }}"
                                               class="enlace-accion text-acento hover:text-acento-fuerte">{{ $vacante->asociado->nombre }}</a>
                                        @else
                                            {{ $vacante->asociado->nombre }}
                                        @endif
                                    </p>

                                    @if ($vacante->descripcion)
                                        <p class="mt-3 text-sm leading-relaxed text-suave">{{ $vacante->descripcion }}</p>
                                    @endif

                                    @if ($vacante->franja_horaria)
                                        <p class="mt-3 text-xs text-apagado">🕒 {{ $vacante->franja_horaria }}</p>
                                    @endif

                                    @if ($vacante->fecha_limite)
                                        <p class="mt-1 text-xs text-apagado">
                                            📅 Se cierra el {{ $vacante->fecha_limite->translatedFormat('d \d\e F') }}
                                        </p>
                                    @endif
                                </div>

                                <x-publico.boton :href="route('empleo.show', $vacante)" class="shrink-0">
                                    Ver y postularme
                                </x-publico.boton>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-10">{{ $vacantes->links() }}</div>
            @endif
        </section>

        {{-- Formulario de aspirante --}}
        <section id="perfil" class="tarjeta mt-16 p-7 sm:p-9" aria-labelledby="titulo-perfil">
            <h2 id="titulo-perfil" class="font-display text-2xl font-bold">Déjanos tu perfil</h2>
            <p class="mt-2 text-sm text-tenue">
                Cuando un establecimiento asociado busque tu cargo, te contactamos. No necesitas cuenta.
            </p>

            @if (session('exito'))
                <x-publico.alerta class="mt-6">{{ session('exito') }}</x-publico.alerta>
            @endif

            <form method="POST" action="{{ route('empleo.aspirante') }}" class="mt-7 space-y-5">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-publico.campo nombre="nombre" etiqueta="Nombre completo" requerido />
                    <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email" requerido />
                    <x-publico.campo nombre="telefono" etiqueta="Teléfono o WhatsApp" tipo="tel" />
                    <x-publico.campo nombre="cargo_interes" etiqueta="Cargo que buscas" requerido
                                     placeholder="Bartender, mesero, chef, administrador…" />
                    <x-publico.campo nombre="categoria_cargo" etiqueta="Área del establecimiento" tipo="select" requerido
                                     :opciones="collect($categorias)->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()" />
                </div>

                <x-publico.campo nombre="experiencia" etiqueta="Tu experiencia" tipo="textarea" filas="3"
                                 placeholder="Cuéntanos en pocas líneas dónde has trabajado y qué sabes hacer."
                                 ayuda="Con dos o tres frases es suficiente." />

                <x-publico.habeas-data />

                <x-publico.boton class="w-full sm:w-auto">
                    Registrar mi perfil
                </x-publico.boton>
            </form>
        </section>
    </div>
</x-layouts.publico>
