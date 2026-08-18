<x-layouts.publico :titulo="$artista->nombre.' — '.$artista->genero_musical.' en el Quindío'"
                   :descripcion="Str::limit($artista->descripcion, 155)"
                   :ogImagen="$artista->foto ? Storage::disk('public')->url($artista->foto) : null">

    <article class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('artistas.index') }}" class="enlace-accion text-sm text-apagado hover:text-acento">← Todos los artistas</a>

        <div class="mt-6 grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-marca-500/15 px-3 py-1 font-medium text-acento-fuerte">
                        {{ $artista->tipo->getLabel() }}
                    </span>
                    @if ($artista->municipio)
                        <span class="rounded-full border border-linea px-3 py-1 text-tenue">{{ $artista->municipio->nombre }}</span>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-3xl font-bold tracking-tight sm:text-4xl">{{ $artista->nombre }}</h1>
                <p class="mt-2 text-lg text-acento">{{ $artista->genero_musical }}</p>

                @if ($artista->descripcion)
                    <p class="mt-5 text-base leading-relaxed text-suave text-pretty">{{ $artista->descripcion }}</p>
                @endif

                {{-- Video: se embebe solo el ID extraído, nunca la URL cruda --}}
                @if ($id = $artista->youtubeId())
                    <h2 class="mt-10 font-display text-lg font-semibold">Escúchalo</h2>
                    <div class="mt-4 aspect-video overflow-hidden rounded-2xl border border-linea">
                        <iframe class="h-full w-full"
                                src="https://www.youtube-nocookie.com/embed/{{ $id }}"
                                title="Video de {{ $artista->nombre }}"
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                    </div>
                @endif
            </div>

            <aside class="space-y-5">
                @if ($artista->foto)
                    <img src="{{ Storage::disk('public')->url($artista->foto) }}" alt="{{ $artista->nombre }}"
                         width="400" height="400" decoding="async"
                         style="view-transition-name: portada-artista-{{ $artista->id }}"
                         class="aspect-square w-full rounded-2xl border border-linea object-cover">
                @endif

                <div class="tarjeta p-6">
                    <p class="text-xs uppercase tracking-wide text-apagado">Tarifa desde</p>
                    <p class="mt-1 font-display text-2xl font-bold text-acento">
                        {{ $artista->tarifa_desde ? pesos($artista->tarifa_desde) : 'A convenir' }}
                    </p>
                    <p class="mt-2 text-xs text-apagado">
                        El valor final depende de la duración, el montaje y el desplazamiento.
                    </p>

                    <div class="mt-5 space-y-2.5">
                        @if ($enlace = enlaceWhatsapp($artista->whatsapp, "Hola {$artista->nombre}, te vi en el directorio de artistas de ASOBARES Quindío."))
                            <x-publico.boton :href="$enlace" target="_blank" rel="noopener nofollow" class="w-full">
                                Contactar por WhatsApp
                            </x-publico.boton>
                        @endif
                        @if ($artista->instagram_url)
                            <a href="{{ $artista->instagram_url }}" target="_blank" rel="noopener nofollow"
                               class="block rounded-xl border border-linea px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
                                Instagram ↗
                            </a>
                        @endif
                    </div>
                </div>
            </aside>
        </div>

        @if ($similares->isNotEmpty())
            <section class="mt-16" aria-labelledby="similares">
                <h2 id="similares" class="font-display text-xl font-bold">Otros {{ Str::lower($artista->tipo->getLabel()) }}</h2>
                <ul class="mt-6 grid gap-4 sm:grid-cols-3">
                    @foreach ($similares as $similar)
                        <li>
                            <a href="{{ route('artistas.show', $similar) }}" class="tarjeta tarjeta-hover block p-5">
                                <span class="block font-display text-sm font-semibold">{{ $similar->nombre }}</span>
                                <span class="mt-1 block text-xs text-acento">{{ $similar->genero_musical }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
</x-layouts.publico>
