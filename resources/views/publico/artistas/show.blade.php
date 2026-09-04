<x-layouts.publico :titulo="$artista->nombre.' — '.$artista->genero_musical.' en el Quindío'"
                   :descripcion="Str::limit($artista->descripcion, 155)"
                   :ogImagen="$artista->foto ? Storage::disk('public')->url($artista->foto) : null">

    <article class="revelar mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8" data-revelar>
        <a href="{{ route('artistas.index') }}" class="enlace-accion relative inline-block text-sm text-apagado after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento"><x-publico.flecha direccion="izquierda" />&nbsp;Todos los artistas</a>

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
                    <div class="mt-4 aspect-video overflow-hidden rounded-[1.5rem] border border-linea">
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

            <aside class="space-y-5 lg:sticky lg:top-24 lg:self-start">
                @if ($artista->foto)
                    <div class="tarjeta-escena group overflow-hidden rounded-[1.75rem] border border-linea bg-superficie-alta"
                         x-data="escena"
                         x-on:pointermove="seguir($event)"
                         x-on:pointerleave="salir()"
                         x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`">
                        <img src="{{ Storage::disk('public')->url($artista->foto) }}" alt="{{ $artista->nombre }}"
                             width="500" height="500" decoding="async"
                             style="view-transition-name: portada-artista-{{ $artista->id }}"
                             class="imagen-viva imagen-inclinable aspect-square w-full object-cover">
                    </div>
                @endif

                <div class="vidrio rounded-[1.5rem] p-6">
                    {{-- OBS3-08: ver el comentario de `index.blade.php`. --}}
                    <p class="text-xs uppercase tracking-wide text-apagado">Tarifa</p>
                    <p class="mt-1 font-display text-2xl font-bold text-acento">
                        {{ ajuste('artistas_tarifa_leyenda') }}
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
                               class="pulsable block min-h-11 rounded-xl border border-linea px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
                                Instagram&nbsp;<x-publico.flecha direccion="externa" />
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
                            <a href="{{ route('artistas.show', $similar) }}" class="vidrio tarjeta-hover tarjeta-pulsable block rounded-[1.25rem] p-5">
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
