<x-layouts.publico :titulo="$artista->nombre.' — '.$artista->genero_musical.' en el Quindío'"
                   :descripcion="Str::limit($artista->descripcion, 155)"
                   :ogImagen="$artista->foto ? Storage::disk('public')->url($artista->foto) : null">

    <article class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('artistas.index') }}" class="text-sm text-noche-400 hover:text-marca-400">← Todos los artistas</a>

        <div class="mt-6 grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-marca-500/15 px-3 py-1 font-medium text-marca-300">
                        {{ $artista->tipo->getLabel() }}
                    </span>
                    @if ($artista->municipio)
                        <span class="rounded-full border border-white/10 px-3 py-1 text-noche-300">{{ $artista->municipio->nombre }}</span>
                    @endif
                </div>

                <h1 class="mt-4 font-display text-3xl font-bold tracking-tight sm:text-4xl">{{ $artista->nombre }}</h1>
                <p class="mt-2 text-lg text-marca-400">{{ $artista->genero_musical }}</p>

                @if ($artista->descripcion)
                    <p class="mt-5 text-base leading-relaxed text-noche-200 text-pretty">{{ $artista->descripcion }}</p>
                @endif

                {{-- Video: se embebe solo el ID extraído, nunca la URL cruda --}}
                @if ($id = $artista->youtubeId())
                    <h2 class="mt-10 font-display text-lg font-semibold">Escúchalo</h2>
                    <div class="mt-4 aspect-video overflow-hidden rounded-2xl border border-white/[.09]">
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
                         class="aspect-square w-full rounded-2xl border border-white/[.09] object-cover">
                @endif

                <div class="tarjeta p-6">
                    <p class="text-xs uppercase tracking-wide text-noche-400">Tarifa desde</p>
                    <p class="mt-1 font-display text-2xl font-bold text-marca-400">
                        {{ $artista->tarifa_desde ? pesos($artista->tarifa_desde) : 'A convenir' }}
                    </p>
                    <p class="mt-2 text-xs text-noche-400">
                        El valor final depende de la duración, el montaje y el desplazamiento.
                    </p>

                    <div class="mt-5 space-y-2.5">
                        @if ($enlace = enlaceWhatsapp($artista->whatsapp, "Hola {$artista->nombre}, te vi en el directorio de artistas de ASOBARES Quindío."))
                            <a href="{{ $enlace }}" target="_blank" rel="noopener nofollow"
                               class="block rounded-xl bg-marca-500 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-marca-600">
                                Contactar por WhatsApp
                            </a>
                        @endif
                        @if ($artista->instagram_url)
                            <a href="{{ $artista->instagram_url }}" target="_blank" rel="noopener nofollow"
                               class="block rounded-xl border border-white/10 px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
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
                                <span class="mt-1 block text-xs text-marca-400">{{ $similar->genero_musical }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
</x-layouts.publico>
