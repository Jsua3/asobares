<x-layouts.publico :titulo="$vacante->cargo.' en '.$vacante->asociado->nombre.' — ASOBARES Quindío'"
                   :descripcion="Str::limit($vacante->descripcion ?? 'Vacante publicada por un establecimiento asociado a ASOBARES Capítulo Quindío.', 155)">

    {{-- Datos estructurados: que la vacante aparezca en la búsqueda de empleo. --}}
    <x-publico.json-ld :datos="[
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => $vacante->cargo,
        'description' => $vacante->descripcion ?? $vacante->cargo,
        'datePosted' => $vacante->created_at->toDateString(),
        'validThrough' => $vacante->fecha_limite?->toDateString(),
        'employmentType' => $vacante->tipo === \App\Enums\TipoVacante::TiempoCompleto ? 'FULL_TIME' : 'PART_TIME',
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name' => $vacante->asociado->nombre,
        ],
        'jobLocation' => [
            '@type' => 'Place',
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $vacante->asociado->municipio->nombre,
                'addressRegion' => 'Quindío',
                'addressCountry' => 'CO',
            ],
        ],
    ]" />

    <div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">

        <a href="{{ route('empleo.index') }}" class="text-sm text-acento hover:text-acento-fuerte">← Todas las vacantes</a>

        <header class="mt-4">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="rounded-full bg-marca-500/15 px-2.5 py-1 font-medium text-acento-fuerte">
                    {{ $vacante->tipo->getLabel() }}
                </span>
                <span class="rounded-full border border-linea px-2.5 py-1 text-tenue">
                    {{ $vacante->categoria_cargo->getLabel() }}
                </span>
                <span class="text-apagado">
                    {{ $vacante->asociado->municipio->nombre }} · publicada {{ $vacante->created_at->diffForHumans() }}
                </span>
            </div>

            <h1 class="mt-4 font-display text-3xl font-bold tracking-tight">{{ $vacante->cargo }}</h1>

            <p class="mt-2 text-sm text-tenue">
                en
                <a href="{{ route('directorio.show', $vacante->asociado) }}"
                   class="text-acento hover:text-acento-fuerte">{{ $vacante->asociado->nombre }}</a>
            </p>
        </header>

        @if ($vacante->descripcion)
            <div class="tarjeta mt-8 p-6">
                <p class="text-sm leading-relaxed text-suave">{{ $vacante->descripcion }}</p>
            </div>
        @endif

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
            @if ($vacante->franja_horaria)
                <div class="tarjeta p-5">
                    <dt class="text-[.65rem] font-semibold uppercase tracking-wider text-acento">Horario</dt>
                    <dd class="mt-1.5 text-sm">{{ $vacante->franja_horaria }}</dd>
                </div>
            @endif
            @if ($vacante->fecha_limite)
                <div class="tarjeta p-5">
                    <dt class="text-[.65rem] font-semibold uppercase tracking-wider text-acento">Se cierra el</dt>
                    <dd class="mt-1.5 text-sm">{{ $vacante->fecha_limite->translatedFormat('d \d\e F \d\e Y') }}</dd>
                </div>
            @endif
        </dl>

        {{-- Formulario de postulación --}}
        <section id="postularme" class="tarjeta mt-10 p-7 sm:p-9" aria-labelledby="titulo-postularme">
            <h2 id="titulo-postularme" class="font-display text-2xl font-bold">Postularme a esta vacante</h2>
            <p class="mt-2 text-sm text-tenue">
                Tus datos le llegan directamente al establecimiento. No necesitas cuenta.
            </p>

            @if (session('exito'))
                <x-publico.alerta class="mt-6">{{ session('exito') }}</x-publico.alerta>
            @endif

            <form method="POST" action="{{ route('empleo.postular', $vacante) }}" class="mt-7 space-y-5">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-publico.campo nombre="nombre" etiqueta="Nombre completo" requerido />
                    <x-publico.campo nombre="correo" etiqueta="Correo electrónico" tipo="email" requerido />
                    <x-publico.campo nombre="telefono" etiqueta="Teléfono o WhatsApp" tipo="tel" />
                </div>

                <x-publico.campo nombre="experiencia" etiqueta="Por qué encajas en el puesto" tipo="textarea" filas="4"
                                 placeholder="Cuéntale al establecimiento dónde has trabajado y qué sabes hacer."
                                 ayuda="Con dos o tres frases es suficiente." />

                <x-publico.habeas-data />

                <button type="submit"
                        class="w-full rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600 sm:w-auto">
                    Enviar mi postulación
                </button>
            </form>

            @if ($enlace = enlaceWhatsapp($vacante->whatsapp_contacto, "Hola, vi la vacante de {$vacante->cargo} en la bolsa de empleo de ASOBARES Quindío."))
                <p class="mt-6 text-xs text-apagado">
                    ¿Prefieres escribir?
                    <a href="{{ $enlace }}" target="_blank" rel="noopener nofollow"
                       class="text-acento hover:text-acento-fuerte">Contactar por WhatsApp ↗</a>
                </p>
            @endif
        </section>

        @if ($similares->isNotEmpty())
            <section class="mt-14" aria-labelledby="similares">
                <h2 id="similares" class="font-display text-xl font-bold">Otras vacantes del área</h2>
                <ul class="mt-5 space-y-3">
                    @foreach ($similares as $similar)
                        <li class="tarjeta tarjeta-hover p-5">
                            <a href="{{ route('empleo.show', $similar) }}" class="block">
                                <p class="font-display text-base font-semibold">{{ $similar->cargo }}</p>
                                <p class="mt-1 text-xs text-apagado">
                                    {{ $similar->asociado->nombre }} · {{ $similar->asociado->municipio->nombre }}
                                </p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-layouts.publico>
