<x-layouts.publico :titulo="$vacante->cargo.' en '.$vacante->asociado->nombre.' — ASOBARES Quindío'"
                   :descripcion="Str::limit($vacante->descripcion ?? 'Vacante publicada por un establecimiento asociado a ASOBARES Capítulo Quindío.', 155)">

    @push('cabeza')
        @vite(['resources/css/empleo-editorial.css'])
    @endpush

    @php
        $jsonLd = [
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
        ];
    @endphp

    @push('jsonld')
        <x-publico.json-ld :datos="$jsonLd" />
    @endpush

    <div class="empleo-editorial">
        <div class="empleo-editorial-ficha">

            <a href="{{ route('empleo.index') }}" class="empleo-editorial-retorno enlace-accion"><x-publico.flecha direccion="izquierda" />&nbsp;Todas las vacantes</a>

            <header class="mt-2">
                <div class="empleo-editorial-oferta__meta">
                    <span class="empleo-editorial-chip empleo-editorial-chip--tipo">{{ $vacante->tipo->getLabel() }}</span>
                    <span class="empleo-editorial-chip empleo-editorial-chip--area">{{ $vacante->categoria_cargo->getLabel() }}</span>
                    <span class="empleo-editorial-chip empleo-editorial-chip--estado">Abierta</span>
                </div>

                <h1 class="empleo-editorial-ficha__cargo">{{ $vacante->cargo }}</h1>

                <p class="empleo-editorial-oferta__empresa">
                    @if ($vacante->asociado->estaPublicado())
                        <a href="{{ route('directorio.show', $vacante->asociado) }}" class="enlace-accion">{{ $vacante->asociado->nombre }}</a>
                    @else
                        {{ $vacante->asociado->nombre }}
                    @endif
                </p>

                <p class="empleo-editorial-oferta__datos">
                    {{ $vacante->asociado->municipio->nombre }}
                    · publicada {{ $vacante->created_at->diffForHumans() }}
                </p>
            </header>

            @if ($vacante->descripcion)
                <div class="empleo-editorial-ficha__descripcion">
                    <p>{{ $vacante->descripcion }}</p>
                </div>
            @endif

            <dl class="empleo-editorial-condiciones">
                <div>
                    <dt>Contrato</dt>
                    <dd>{{ $vacante->tipo->getLabel() }}</dd>
                </div>
                <div>
                    <dt>Área</dt>
                    <dd>{{ $vacante->categoria_cargo->getLabel() }}</dd>
                </div>
                @if ($vacante->franja_horaria)
                    <div>
                        <dt>Horario</dt>
                        <dd>{{ $vacante->franja_horaria }}</dd>
                    </div>
                @endif
                @if ($vacante->fecha_limite)
                    <div>
                        <dt>Se cierra el</dt>
                        <dd>{{ $vacante->fecha_limite->translatedFormat('d \d\e F \d\e Y') }}</dd>
                    </div>
                @endif
            </dl>

            <section id="postularme" class="empleo-editorial-bloque" aria-labelledby="titulo-postularme">
                <h2 id="titulo-postularme">Postularme a esta vacante</h2>
                <p>
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

                    <x-publico.boton class="w-full sm:w-auto">
                        Enviar mi postulación
                    </x-publico.boton>
                </form>

                @if ($enlace = enlaceWhatsapp($vacante->whatsapp_contacto, "Hola, vi la vacante de {$vacante->cargo} en la bolsa de empleo de ASOBARES Quindío."))
                    <p class="empleo-editorial-whatsapp">
                        ¿Prefieres escribir?
                        <a href="{{ $enlace }}" target="_blank" rel="noopener nofollow"
                           class="enlace-accion">Contactar por WhatsApp&nbsp;<x-publico.flecha direccion="externa" /></a>
                    </p>
                @endif
            </section>

            @if ($similares->isNotEmpty())
                <section class="empleo-editorial-similares" aria-labelledby="similares">
                    <h2 id="similares">Otras vacantes del área</h2>
                    <ul class="empleo-editorial-cartelera">
                        @foreach ($similares as $similar)
                            <x-publico.vacante-ficha :vacante="$similar" compacta />
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</x-layouts.publico>
