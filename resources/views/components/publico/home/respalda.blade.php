@props(['beneficios'])

@php
    $beneficiosVisibles = $beneficios->take(5);
@endphp

@if ($beneficiosVisibles->isNotEmpty())
    <section class="home-editorial-respalda revelar border-y border-linea bg-superficie" data-revelar aria-labelledby="beneficios">
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
            <p class="home-editorial-kicker">{{ ajuste('portada_beneficios_titulo') }}</p>
            <h2 id="beneficios" class="sr-only">{{ ajuste('portada_beneficios_titulo') }}</h2>
            <p class="mt-3 max-w-2xl text-base text-suave">{{ ajuste('portada_beneficios_intro') }}</p>

            <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:gap-5">
                @foreach ($beneficiosVisibles as $beneficio)
                    <li class="home-editorial-beneficio rounded-xl border border-linea bg-fondo p-5">
                        <span class="inline-flex rounded-lg bg-marca-500/10 p-2 text-acento" aria-hidden="true">
                            <x-dynamic-component :component="$beneficio->icono" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 font-display text-base font-semibold leading-snug">{{ $beneficio->titulo }}</h3>
                        <x-publico.sello-de-alcance :beneficio="$beneficio" />
                        <p class="mt-3 line-clamp-2 text-sm text-tenue">{{ $beneficio->descripcion }}</p>
                    </li>
                @endforeach
            </ul>

            <div class="mt-8">
                <a href="{{ route('afiliate') }}" class="enlace-accion text-sm font-medium text-acento hover:text-acento-fuerte">
                    Conoce la afiliación&nbsp;<x-publico.flecha />
                </a>
            </div>
        </div>
    </section>
@endif
