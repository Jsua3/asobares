@props(['aliadosInstitucionales', 'aliadosComerciales'])

@php
    use Illuminate\Support\Facades\Storage;
@endphp

<section class="home-editorial-aliados revelar" data-revelar aria-labelledby="aliados">
    <div class="home-editorial-aliados__franja">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="home-editorial-eyebrow home-editorial-eyebrow--claro">{{ ajuste('portada_aliados_titulo') }}</p>
                    <h2 id="aliados" class="sr-only">{{ ajuste('portada_aliados_titulo') }}</h2>
                </div>
                <a href="{{ route('aliados.index') }}" class="home-editorial-enlace enlace-accion text-sm font-medium text-marca-600 hover:text-marca-700">
                    Ver todos los aliados&nbsp;<x-publico.flecha />
                </a>
            </div>

            @if ($aliadosInstitucionales->isNotEmpty())
                <p class="mt-4 text-2xs font-semibold uppercase tracking-wider text-neutral-500">{{ ajuste('portada_aliados_institucionales') }}</p>
                <ul class="mt-3 flex flex-wrap items-center gap-x-8 gap-y-4">
                    @foreach ($aliadosInstitucionales as $aliado)
                        <li class="flex items-center gap-3">
                            @if ($aliado->logo)
                                <img src="{{ Storage::disk('public')->url($aliado->logo) }}"
                                     alt="{{ $aliado->nombre }}"
                                     loading="lazy"
                                     decoding="async"
                                     width="120"
                                     height="64"
                                     class="h-10 w-auto max-w-[7rem] object-contain sm:h-12 sm:max-w-[8.5rem]">
                            @endif
                            <span class="sr-only">{{ $aliado->nombre }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($aliadosComerciales->isNotEmpty())
                <p class="mt-5 text-2xs font-semibold uppercase tracking-wider text-neutral-500">{{ ajuste('portada_aliados_comerciales') }}</p>
                <ul class="mt-3 flex snap-x items-center gap-6 overflow-x-auto pb-1">
                    @foreach ($aliadosComerciales as $aliado)
                        <li class="flex shrink-0 snap-start items-center">
                            @if ($aliado->logo)
                                <img src="{{ Storage::disk('public')->url($aliado->logo) }}"
                                     alt="{{ $aliado->nombre }}"
                                     loading="lazy"
                                     decoding="async"
                                     width="120"
                                     height="64"
                                     class="h-9 w-auto max-w-[6.5rem] object-contain opacity-90 sm:h-10 sm:max-w-[7.5rem]">
                            @else
                                <span class="text-sm font-medium text-neutral-700">{{ $aliado->nombre }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</section>
