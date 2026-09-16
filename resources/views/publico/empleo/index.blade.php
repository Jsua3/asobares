<x-layouts.publico :titulo="ajuste('seo_empleo_titulo', ajuste('empleo_titulo').' — ASOBARES Quindío')"
                   :descripcion="ajuste('seo_empleo_descripcion', 'Vacantes de bartender, chef, mesero y administrador en bares y gastrobares del Quindío. Publican solo los establecimientos asociados.')">

    @push('cabeza')
        @vite(['resources/css/empleo-editorial.css'])
    @endpush

    <div class="empleo-editorial">
        <x-publico.hero-empleo
            :titulo="ajuste('empleo_titulo')"
            :subtitulo="ajuste('empleo_intro')"
            :cta-perfil="ajuste('empleo_cta_perfil', 'Déjanos tu perfil')"
            :cta-vacantes="ajuste('empleo_cta_vacantes', 'Ver vacantes')" />

        <div class="empleo-editorial-cuerpo">

            <section id="vacantes" aria-labelledby="titulo-vacantes">
                <div class="empleo-editorial-seccion__cabeza">
                    <h2 id="titulo-vacantes">{{ ajuste('empleo_vacantes_titulo', 'Vacantes abiertas') }}</h2>
                    <p class="empleo-editorial-seccion__aviso">{{ ajuste('empleo_aviso') }}</p>
                </div>

                {{--
                    Sin nada que filtrar, la caja de filtros sobra: prometía cortar
                    algo cuando no hay nada que cortar. Es el estado real de
                    producción hoy —cero vacantes publicadas— y era lo primero que
                    veía quien entraba a la bolsa.

                    La condición mira las OPCIONES y no las vacantes de la página:
                    así el formulario sigue en pie cuando un filtro deja la lista
                    vacía, que es justo cuando hace falta para volver atrás.
                --}}
                @if ($municipios->isNotEmpty() || filled($categorias))
                    <form method="GET"
                          action="{{ route('empleo.index') }}#vacantes"
                          class="empleo-editorial-filtros">
                        <x-publico.campo nombre="categoria" etiqueta="Área" tipo="select"
                                         :valor="$filtros['categoria'] ?? null"
                                         :opciones="['' => 'Todas las áreas'] + collect($categorias)->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()" />
                        <x-publico.campo nombre="municipio" etiqueta="Municipio" tipo="select"
                                         :valor="$filtros['municipio'] ?? null"
                                         :opciones="['' => 'Todos los municipios'] + $municipios->pluck('nombre', 'slug')->all()" />
                        <div class="empleo-editorial-filtros__acciones">
                            <x-publico.boton class="flex-1">
                                Filtrar
                            </x-publico.boton>
                            @if (array_filter($filtros ?? []))
                                <a href="{{ route('empleo.index') }}"
                                   class="empleo-editorial-filtros__limpiar pulsable">Limpiar</a>
                            @endif
                        </div>
                    </form>
                @endif

                @if ($vacantes->isEmpty())
                    <div class="empleo-editorial-vacio">
                        @if (array_filter($filtros ?? []))
                            <p>No hay vacantes con ese filtro</p>
                            <p>Prueba otro municipio o área, o deja tu perfil abajo para que te avisemos.</p>
                        @else
                            <p>Todavía no hay vacantes abiertas</p>
                            <p>Deja tu perfil abajo y te avisamos cuando aparezca una que encaje.</p>
                        @endif
                    </div>
                @else
                    <ul class="empleo-editorial-cartelera">
                        @foreach ($vacantes as $vacante)
                            <x-publico.vacante-ficha :vacante="$vacante" />
                        @endforeach
                    </ul>

                    <div class="empleo-editorial-pagina">{{ $vacantes->links() }}</div>
                @endif
            </section>

            <section id="perfil" class="empleo-editorial-bloque" aria-labelledby="titulo-perfil">
                <h2 id="titulo-perfil">{{ ajuste('empleo_perfil_titulo', 'Déjanos tu perfil') }}</h2>
                <p>
                    {{ ajuste('empleo_perfil_texto', 'Cuando un establecimiento asociado busque tu cargo, te contactamos. No necesitas cuenta.') }}
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
                                         :opciones="collect($categoriasPerfil)->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all()" />
                    </div>

                    <x-publico.campo nombre="experiencia" etiqueta="Tu experiencia" tipo="textarea" filas="3"
                                     :placeholder="ajuste('empleo_perfil_experiencia_placeholder', 'Cuéntanos en pocas líneas dónde has trabajado y qué sabes hacer.')"
                                     :ayuda="ajuste('empleo_perfil_experiencia_ayuda', 'Con dos o tres frases es suficiente.')" />

                    {{-- Ley 1581: el perfil no lo ve solo la secretaría, lo ve cualquier
                         establecimiento afiliado. Eso se dice aquí, junto a la casilla, y
                         no solo en la política. --}}
                    <p class="text-xs leading-relaxed text-apagado">
                        {{ ajuste('empleo_perfil_privacidad', 'Tu perfil quedará visible para los establecimientos afiliados a ASOBARES Capítulo Quindío, que podrán contactarte directamente para ofrecerte trabajo.') }}
                    </p>

                    <x-publico.habeas-data />

                    <x-publico.boton class="w-full sm:w-auto">
                        Registrar mi perfil
                    </x-publico.boton>
                </form>
            </section>
        </div>
    </div>
</x-layouts.publico>
