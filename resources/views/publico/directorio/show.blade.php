@php
    $jsonLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BarOrPub',
        'name' => $asociado->nombre,
        'description' => $asociado->descripcion,
        'url' => route('directorio.show', $asociado),
        'image' => $asociado->foto_portada ? Storage::disk('public')->url($asociado->foto_portada) : null,
        'telephone' => $asociado->whatsapp,
        'openingHours' => $asociado->horario,
        'sameAs' => array_values(array_filter([$asociado->instagram_url, $asociado->sitio_web, $asociado->google_maps_url, $asociado->tripadvisor_url])),
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $asociado->direccion,
            'addressLocality' => $asociado->municipio->nombre,
            'addressRegion' => 'Quindío',
            'addressCountry' => 'CO',
        ]),
        'geo' => $asociado->tieneUbicacion() ? [
            '@type' => 'GeoCoordinates',
            'latitude' => $asociado->lat,
            'longitude' => $asociado->lng,
        ] : null,
    ]);
@endphp

<x-layouts.publico :titulo="$asociado->nombre.' — '.$asociado->categoria->nombre.' en '.$asociado->municipio->nombre"
                   :descripcion="Str::limit($asociado->descripcion, 155)"
                   ogTipo="business.business"
                   :ogImagen="$asociado->foto_portada ? Storage::disk('public')->url($asociado->foto_portada) : null">

    @push('jsonld')
        <x-publico.json-ld :datos="$jsonLd" />
    @endpush

    <nav aria-label="Ruta de navegación" class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <ol class="flex flex-wrap items-center gap-2 text-xs text-apagado">
            <li><a href="{{ route('inicio') }}" class="enlace-accion flex min-h-11 min-w-11 items-center justify-center hover:text-acento">Inicio</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('directorio.index') }}" class="enlace-accion flex min-h-11 min-w-11 items-center justify-center hover:text-acento">Directorio</a></li>
            <li aria-hidden="true">/</li>
            <li class="text-suave" aria-current="page">{{ $asociado->nombre }}</li>
        </ol>
    </nav>

    <article class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-3">

            {{-- Columna principal --}}
            <div class="lg:col-span-2">
                @if ($asociado->foto_portada)
                    <img src="{{ Storage::disk('public')->url($asociado->foto_portada) }}"
                         alt="Portada de {{ $asociado->nombre }}"
                         width="800" height="600" decoding="async"
                         style="view-transition-name: portada-asociado-{{ $asociado->id }}"
                         class="aspect-[4/3] w-full rounded-2xl border border-linea object-cover">
                @endif

                <div class="mt-7 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-marca-500/15 px-3 py-1 text-xs font-medium text-acento-fuerte">
                        {{ $asociado->categoria->nombre }}
                    </span>
                    <span class="rounded-full border border-linea px-3 py-1 text-xs text-tenue">
                        {{ $asociado->municipio->nombre }}
                    </span>
                    @if ($asociado->destacado)
                        <span class="rounded-full bg-marca-500 px-3 py-1 text-xs font-semibold text-white">Destacado</span>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-3xl font-bold tracking-tight sm:text-4xl">{{ $asociado->nombre }}</h1>

                @if ($asociado->descripcion)
                    <p class="mt-5 text-base leading-relaxed text-suave text-pretty">{{ $asociado->descripcion }}</p>
                @endif

                {{-- Galería --}}
                @if ($asociado->fotosAprobadas()->isNotEmpty())
                    <h2 class="mt-10 font-display text-lg font-semibold">Galería</h2>
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($asociado->fotosAprobadas() as $imagen)
                            <img src="{{ $imagen->getUrl('thumb') }}" alt="{{ $asociado->nombre }}"
                                 loading="lazy" decoding="async" width="400" height="300"
                                 class="aspect-[4/3] w-full rounded-xl border border-linea object-cover">
                        @endforeach
                    </div>
                @endif

                {{-- Vacantes del establecimiento --}}
                @if ($vacantes->isNotEmpty())
                    <h2 class="mt-10 font-display text-lg font-semibold">Vacantes abiertas aquí</h2>
                    <ul class="mt-4 space-y-3">
                        @foreach ($vacantes as $vacante)
                            <li class="tarjeta flex flex-wrap items-center justify-between gap-3 p-4">
                                <div>
                                    <p class="font-medium">{{ $vacante->cargo }}</p>
                                    <p class="mt-0.5 text-xs text-apagado">
                                        {{ $vacante->tipo->getLabel() }}
                                        @if ($vacante->franja_horaria) · {{ $vacante->franja_horaria }} @endif
                                    </p>
                                </div>
                                <a href="{{ route('empleo.show', $vacante) }}" class="enlace-accion relative text-sm text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                                    Ver en la bolsa&nbsp;<x-publico.flecha />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Barra lateral: solo campos públicos --}}
            <aside class="space-y-5">
                <div class="tarjeta p-6">
                    <h2 class="font-display text-base font-semibold">Cómo llegar y contactar</h2>

                    <dl class="mt-4 space-y-3.5 text-sm">
                        @if ($asociado->direccion)
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-apagado">Dirección</dt>
                                <dd class="mt-0.5 text-tinta">{{ $asociado->direccion }}</dd>
                            </div>
                        @endif
                        @if ($asociado->horario)
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-apagado">Horario</dt>
                                <dd class="mt-0.5 text-tinta">{{ $asociado->horario }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-5 space-y-2.5">
                        @if ($enlace = enlaceWhatsapp($asociado->whatsapp, "Hola {$asociado->nombre}, los vi en la página de ASOBARES Quindío."))
                            <x-publico.boton :href="$enlace" target="_blank" rel="noopener nofollow" class="w-full">
                                Escribir por WhatsApp
                            </x-publico.boton>
                        @endif

                        @foreach ([
                            'instagram_url' => 'Instagram',
                            'sitio_web' => 'Sitio web',
                            'google_maps_url' => 'Ver en Google Maps',
                            'tripadvisor_url' => 'Ver en TripAdvisor',
                        ] as $campo => $texto)
                            @if ($asociado->{$campo})
                                <a href="{{ $asociado->{$campo} }}" target="_blank" rel="noopener nofollow"
                                   class="pulsable block min-h-11 rounded-xl border border-linea px-4 py-2.5 text-center text-sm text-tinta hover:border-marca-500/50">
                                    {{ $texto }}&nbsp;<x-publico.flecha direccion="externa" />
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                @if ($asociado->tieneUbicacion())
                    <x-publico.mapa
                        :lat="$asociado->lat" :lng="$asociado->lng" :zoom="15"
                        alto="h-64"
                        :puntos="[[
                            'lat' => $asociado->lat,
                            'lng' => $asociado->lng,
                            'nombre' => $asociado->nombre,
                            'html' => '<strong>'.e($asociado->nombre).'</strong>',
                        ]]" />
                @endif
            </aside>
        </div>

        {{-- Otros del mismo municipio --}}
        @if ($similares->isNotEmpty())
            <section class="mt-16" aria-labelledby="similares">
                <h2 id="similares" class="font-display text-xl font-bold">
                    Más en {{ $asociado->municipio->nombre }}
                </h2>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($similares as $similar)
                        <x-publico.tarjeta-asociado :asociado="$similar" />
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</x-layouts.publico>
