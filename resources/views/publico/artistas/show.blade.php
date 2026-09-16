<x-layouts.publico :titulo="$artista->nombre.' — '.$artista->genero_musical.' en el Quindío'"
                   :descripcion="Str::limit($artista->descripcion, 155)"
                   :ogImagen="$artista->foto ? Storage::disk('public')->url($artista->foto) : null">

    @push('cabeza')
        @vite(['resources/css/artistas-editorial.css'])
    @endpush

    <div class="artistas-editorial">
    <article class="artistas-editorial-cuerpo revelar max-w-5xl" data-revelar>
        <a href="{{ route('artistas.index') }}" class="enlace-accion relative inline-block text-sm text-apagado after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento"><x-publico.flecha direccion="izquierda" />&nbsp;Todos los artistas</a>

        <div class="artistas-editorial-ficha mt-6 grid gap-10 p-5 sm:p-7 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="artistas-editorial-chip">
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
                    <div class="artistas-editorial-video mt-4 aspect-video overflow-hidden rounded-[1.5rem]">
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
                    <div class="artistas-editorial-ficha__foto group"
                         x-data="escena"
                         x-on:pointermove="seguir($event)"
                         x-on:pointerleave="salir()"
                         x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`">
                        <img src="{{ Storage::disk('public')->url($artista->foto) }}" alt="{{ $artista->nombre }}"
                             width="500" height="500" decoding="async"
                             style="view-transition-name: portada-artista-{{ $artista->id }}"
                             class="imagen-inclinable aspect-square w-full">
                    </div>
                @endif

                <div class="artistas-editorial-contacto">
                    {{-- La tarifa no se publica: ver el comentario de `index.blade.php`. --}}
                    <p class="text-xs uppercase tracking-wide text-apagado">Tarifa</p>
                    <p class="mt-1 font-display text-2xl font-bold text-acento">
                        {{ ajuste('artistas_tarifa_leyenda') }}
                    </p>
                    <p class="mt-2 text-xs text-apagado">
                        El valor final depende de la duración, el montaje y el desplazamiento.
                    </p>

                    {{-- El contacto es contraprestación de la cuota, igual que el del
                         proveedor: quien contrata música en vivo es el establecimiento
                         afiliado. Lo que NO se toca es el escaparate de arriba --nombre,
                         foto, género, video--, que es el motivo por el que el artista se
                         inscribe. El enlace de abajo no se ramifica por sesión a propósito:
                         `/mi-cuenta/artistas` ya manda al login a quien no la tiene. --}}
                    <div class="mt-5 space-y-2.5">
                        <p class="text-xs leading-relaxed text-apagado">
                            El contacto de cada artista es información privada de los afiliados.
                        </p>
                        <x-publico.boton :href="route('mi-cuenta.artistas.index')" class="w-full">
                            Ver el contacto
                        </x-publico.boton>
                        <a href="{{ route('afiliate') }}"
                           class="pulsable block min-h-11 rounded-xl border border-linea px-4 py-2.5 text-center text-sm hover:border-marca-500/50">
                            Afíliate al gremio
                        </a>
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
                            <a href="{{ route('artistas.show', $similar) }}" class="artistas-editorial-similar tarjeta-hover tarjeta-pulsable block p-5">
                                <span class="block font-display text-sm font-semibold">{{ $similar->nombre }}</span>
                                <span class="mt-1 block text-xs text-acento">{{ $similar->genero_musical }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </article>
    </div>
</x-layouts.publico>
