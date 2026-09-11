@props(['destacados'])

@php
    use Illuminate\Support\Facades\Storage;

    $destacadosVisibles = $destacados->take(3);
    $fallbacksEditoriales = collect(config('home_banco.establecimientos', []))
        ->map(fn (string $ruta) => asset($ruta))
        ->values();
@endphp

@if ($destacadosVisibles->isNotEmpty())
    <section class="home-editorial-descubre revelar" data-revelar aria-labelledby="destacados">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-lg">
                    <p class="home-editorial-eyebrow">{{ ajuste('portada_destacados_titulo') }}</p>
                    <h2 id="destacados" class="home-editorial-titulo mt-2 text-balance">
                        {{ ajuste('portada_destacados_subtitulo', 'Lugares que dan vida a nuestra ciudad.') }}
                    </h2>
                    <p class="mt-2 text-sm text-tenue">{{ ajuste('portada_destacados_texto') }}</p>
                </div>
                <a href="{{ route('directorio.index') }}"
                   class="home-editorial-enlace enlace-accion text-sm font-medium text-acento hover:text-acento-fuerte">
                    Ver el directorio completo&nbsp;<x-publico.flecha />
                </a>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                @foreach ($destacadosVisibles as $indice => $asociado)
                    @php
                        $fotoEditorial = $fallbacksEditoriales->get($indice % max($fallbacksEditoriales->count(), 1));
                    @endphp
                    <article class="home-editorial-establecimiento group">
                        <a href="{{ route('directorio.show', $asociado) }}" class="tarjeta-pulsable block">
                            <div class="home-editorial-establecimiento__foto relative aspect-[5/4] overflow-hidden rounded-xl sm:aspect-[4/3]">
                                @if ($asociado->foto_portada)
                                    <img src="{{ Storage::disk('public')->url($asociado->foto_portada) }}"
                                         alt="Portada de {{ $asociado->nombre }}"
                                         loading="lazy"
                                         decoding="async"
                                         width="480"
                                         height="360"
                                         class="home-editorial-establecimiento__img h-full w-full object-cover">
                                @elseif ($fotoEditorial)
                                    <img src="{{ $fotoEditorial }}"
                                         alt=""
                                         loading="lazy"
                                         decoding="async"
                                         width="480"
                                         height="360"
                                         class="home-editorial-establecimiento__img h-full w-full object-cover">
                                @else
                                    <div class="home-editorial-establecimiento__fallback h-full w-full" aria-hidden="true">
                                        <span class="home-editorial-establecimiento__monograma">A</span>
                                    </div>
                                @endif
                                <div class="home-editorial-establecimiento__velo" aria-hidden="true"></div>
                                <div class="home-editorial-establecimiento__overlay absolute inset-x-0 bottom-0 p-4 pt-12">
                                    @if ($asociado->categoria)
                                        <p class="text-2xs font-semibold uppercase tracking-wider text-white/80">{{ $asociado->categoria->nombre }}</p>
                                    @endif
                                    <h3 class="mt-0.5 font-display text-lg font-semibold text-white">{{ $asociado->nombre }}</h3>
                                    @if ($asociado->municipio)
                                        <p class="mt-0.5 text-xs text-white/75">{{ $asociado->municipio->nombre }}</p>
                                    @endif
                                </div>
                                <span class="home-editorial-establecimiento__flecha absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-black/35 text-white backdrop-blur-sm" aria-hidden="true">
                                    <x-publico.flecha />
                                </span>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
