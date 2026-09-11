@props(['aliadosInstitucionales', 'aliadosComerciales'])

@php
    use Illuminate\Support\Facades\Storage;
@endphp

<section class="home-editorial-aliados revelar" data-revelar aria-labelledby="aliados">
    <div class="home-editorial-aliados__franja">
        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <div class="home-editorial-aliados__cabecera flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="home-editorial-eyebrow home-editorial-eyebrow--claro">{{ ajuste('portada_aliados_titulo') }}</p>
                    <h2 id="aliados" class="sr-only">{{ ajuste('portada_aliados_titulo') }}</h2>
                </div>
                <a href="{{ route('aliados.index') }}" class="home-editorial-enlace home-editorial-aliados__cta enlace-accion shrink-0 text-sm font-semibold text-marca-600 hover:text-marca-700">
                    Ver todos los aliados&nbsp;<x-publico.flecha />
                </a>
            </div>

            @if ($aliadosInstitucionales->isNotEmpty())
                <div class="home-editorial-aliados__nivel mt-4">
                    <p class="home-editorial-aliados__etiqueta">{{ ajuste('portada_aliados_institucionales') }}</p>
                    <ul class="home-editorial-aliados__logos home-editorial-aliados__logos--institucionales mt-2">
                        @foreach ($aliadosInstitucionales as $aliado)
                            <li class="home-editorial-aliados__item">
                                @if ($aliado->logo)
                                    <img src="{{ Storage::disk('public')->url($aliado->logo) }}"
                                         alt="{{ $aliado->nombre }}"
                                         loading="lazy"
                                         decoding="async"
                                         width="160"
                                         height="80"
                                         class="home-editorial-aliados__logo">
                                @endif
                                <span class="home-editorial-aliados__nombre">{{ $aliado->nombre }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($aliadosComerciales->isNotEmpty())
                <div class="home-editorial-aliados__nivel mt-4">
                    <p class="home-editorial-aliados__etiqueta">{{ ajuste('portada_aliados_comerciales') }}</p>
                    <ul class="home-editorial-aliados__logos home-editorial-aliados__logos--comerciales mt-2">
                        @foreach ($aliadosComerciales as $aliado)
                            <li class="home-editorial-aliados__item home-editorial-aliados__item--comercial">
                                @if ($aliado->logo)
                                    <img src="{{ Storage::disk('public')->url($aliado->logo) }}"
                                         alt="{{ $aliado->nombre }}"
                                         loading="lazy"
                                         decoding="async"
                                         width="140"
                                         height="72"
                                         class="home-editorial-aliados__logo home-editorial-aliados__logo--comercial">
                                @endif
                                <span class="home-editorial-aliados__nombre">{{ $aliado->nombre }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</section>
