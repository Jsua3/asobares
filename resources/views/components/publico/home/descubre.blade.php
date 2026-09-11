@props(['destacados'])

@php
    use Illuminate\Support\Facades\Storage;

    $destacadosVisibles = $destacados->take(3);
@endphp

@if ($destacadosVisibles->isNotEmpty())
    <section class="home-editorial-descubre revelar mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8" data-revelar aria-labelledby="destacados">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="max-w-xl">
                <p class="home-editorial-kicker">{{ ajuste('portada_destacados_titulo') }}</p>
                <h2 id="destacados" class="sr-only">{{ ajuste('portada_destacados_titulo') }}</h2>
                <p class="mt-3 text-sm text-tenue sm:text-base">{{ ajuste('portada_destacados_texto') }}</p>
            </div>
            <a href="{{ route('directorio.index') }}"
               class="enlace-accion relative text-sm font-medium text-acento after:absolute after:inset-x-0 after:-inset-y-3 after:content-[''] hover:text-acento-fuerte">
                Ver el directorio completo&nbsp;<x-publico.flecha />
            </a>
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
            @foreach ($destacadosVisibles as $asociado)
                <article class="home-editorial-establecimiento group overflow-hidden rounded-2xl border border-linea bg-superficie">
                    <a href="{{ route('directorio.show', $asociado) }}" class="tarjeta-pulsable block">
                        <div class="relative aspect-[4/5] overflow-hidden bg-superficie-alta sm:aspect-[3/4]">
                            @if ($asociado->foto_portada)
                                <img src="{{ Storage::disk('public')->url($asociado->foto_portada) }}"
                                     alt="Portada de {{ $asociado->nombre }}"
                                     loading="lazy"
                                     decoding="async"
                                     width="640"
                                     height="800"
                                     class="imagen-viva h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
                            @else
                                <div class="h-full w-full bg-[linear-gradient(145deg,var(--asb-superficie-alta),var(--asb-fondo))]"></div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-fondo via-fondo/85 to-transparent p-5 pt-16">
                                @if ($asociado->categoria)
                                    <p class="antetitulo text-acento">{{ $asociado->categoria->nombre }}</p>
                                @endif
                                <h3 class="mt-1 font-display text-xl font-semibold text-fuerte">{{ $asociado->nombre }}</h3>
                                @if ($asociado->municipio)
                                    <p class="mt-1 text-sm text-tenue">{{ $asociado->municipio->nombre }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>
    </section>
@endif
