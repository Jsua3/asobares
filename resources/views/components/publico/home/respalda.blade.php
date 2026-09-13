@props(['beneficios', 'destacados'])

@php
    $beneficiosVisibles = $beneficios->take(5);
    $fotoBeneficios = urlDeFotoDeLaHome(null, config('home_banco.beneficios'));
@endphp

@if ($beneficiosVisibles->isNotEmpty())
    <section class="home-editorial-respalda revelar" data-revelar aria-labelledby="beneficios">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="home-editorial-respalda__cuerpo">
                <div class="home-editorial-respalda__media">
                    <p class="home-editorial-eyebrow">{{ ajuste('portada_beneficios_titulo') }}</p>
                    <h2 id="beneficios" class="home-editorial-titulo mt-2 text-balance">
                        {{ ajuste('portada_beneficios_subtitulo', 'Más beneficios. Más oportunidades.') }}
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-suave sm:text-base">{{ ajuste('portada_beneficios_intro') }}</p>

                    <div class="home-editorial-respalda__foto relative mt-6 overflow-hidden rounded-xl">
                        @if ($fotoBeneficios)
                            <img src="{{ $fotoBeneficios }}"
                                 alt=""
                                 loading="lazy"
                                 decoding="async"
                                 width="560"
                                 height="420"
                                 class="home-editorial-respalda__img h-full w-full object-cover">
                        @else
                            <div class="home-editorial-establecimiento__fallback h-full w-full" aria-hidden="true">
                                <span class="home-editorial-establecimiento__monograma">A</span>
                            </div>
                        @endif
                        <div class="home-editorial-respalda__velo" aria-hidden="true"></div>
                    </div>
                </div>

                <ul class="home-editorial-beneficios">
                    @foreach ($beneficiosVisibles as $beneficio)
                        <li class="home-editorial-beneficio">
                            <span class="home-editorial-beneficio__icono" aria-hidden="true">
                                <x-dynamic-component :component="$beneficio->icono" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="home-editorial-beneficio__titulo font-display text-sm font-semibold leading-snug sm:text-base">{{ $beneficio->titulo }}</h3>
                                <x-publico.sello-de-alcance :beneficio="$beneficio" />
                                <p class="home-editorial-copy mt-1 text-sm leading-relaxed text-tenue">{{ $beneficio->descripcion }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('afiliate') }}"
                   class="home-editorial-respalda__cta home-editorial-enlace enlace-accion inline-flex text-sm font-medium text-acento hover:text-acento-fuerte">
                    Conoce la afiliación&nbsp;<x-publico.flecha />
                </a>
            </div>
        </div>
    </section>
@endif
