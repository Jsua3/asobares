@props(['beneficios', 'destacados'])

@php
    $beneficiosVisibles = $beneficios->take(5);
    $fotoBeneficios = asset(config('home_banco.beneficios', 'img/home/beneficios-gremio.png'));
@endphp

@if ($beneficiosVisibles->isNotEmpty())
    <section class="home-editorial-respalda revelar" data-revelar aria-labelledby="beneficios">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-start gap-8 lg:grid-cols-12 lg:gap-10">
                <div class="lg:col-span-5">
                    <p class="home-editorial-eyebrow">{{ ajuste('portada_beneficios_titulo') }}</p>
                    <h2 id="beneficios" class="home-editorial-titulo mt-2 text-balance">
                        {{ ajuste('portada_beneficios_subtitulo', 'Más beneficios. Más oportunidades.') }}
                    </h2>
                    <p class="mt-3 text-sm leading-relaxed text-suave sm:text-base">{{ ajuste('portada_beneficios_intro') }}</p>

                    <div class="home-editorial-respalda__foto relative mt-6 hidden aspect-[4/3] overflow-hidden rounded-xl lg:block">
                        <img src="{{ $fotoBeneficios }}"
                             alt=""
                             loading="lazy"
                             decoding="async"
                             width="560"
                             height="420"
                             class="home-editorial-respalda__img h-full w-full object-cover">
                        <div class="home-editorial-respalda__velo" aria-hidden="true"></div>
                    </div>

                    <a href="{{ route('afiliate') }}" class="home-editorial-enlace enlace-accion mt-6 inline-flex text-sm font-medium text-acento hover:text-acento-fuerte">
                        Conoce la afiliación&nbsp;<x-publico.flecha />
                    </a>
                </div>

                <ul class="home-editorial-beneficios lg:col-span-7">
                    @foreach ($beneficiosVisibles as $beneficio)
                        <li class="home-editorial-beneficio">
                            <span class="home-editorial-beneficio__icono" aria-hidden="true">
                                <x-dynamic-component :component="$beneficio->icono" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-display text-sm font-semibold leading-snug sm:text-base">{{ $beneficio->titulo }}</h3>
                                <x-publico.sello-de-alcance :beneficio="$beneficio" />
                                <p class="home-editorial-copy mt-1 text-sm leading-relaxed text-tenue">{{ $beneficio->descripcion }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
@endif
