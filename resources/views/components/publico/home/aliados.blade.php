@props(['aliadosInstitucionales', 'aliadosComerciales'])

@php
    use Illuminate\Support\Facades\Storage;
@endphp

<section class="home-editorial-aliados revelar border-y border-linea-fuerte" data-revelar aria-labelledby="aliados">
    <div class="home-editorial-aliados__franja mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
        <p class="home-editorial-kicker home-editorial-kicker--claro">{{ ajuste('portada_aliados_titulo') }}</p>
        <h2 id="aliados" class="sr-only">{{ ajuste('portada_aliados_titulo') }}</h2>

        @if ($aliadosInstitucionales->isNotEmpty())
            <p class="antetitulo mt-6 text-marca-600 dark:text-marca-400">{{ ajuste('portada_aliados_institucionales') }}</p>
            <ul class="mt-6 grid grid-cols-2 gap-8 sm:grid-cols-4">
                @foreach ($aliadosInstitucionales as $aliado)
                    <li class="flex flex-col items-center gap-4 text-center">
                        @if ($aliado->logo)
                            <img src="{{ Storage::disk('public')->url($aliado->logo) }}"
                                 alt="{{ $aliado->nombre }}"
                                 loading="lazy"
                                 decoding="async"
                                 width="192"
                                 height="108"
                                 class="h-16 w-full max-w-[10rem] object-contain">
                        @endif
                        <p class="text-sm font-semibold text-balance text-neutral-900 dark:text-white">{{ $aliado->nombre }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($aliadosComerciales->isNotEmpty())
            <p class="antetitulo mt-10 text-neutral-600 dark:text-neutral-300">{{ ajuste('portada_aliados_comerciales') }}</p>
            <ul class="mt-6 flex snap-x gap-5 overflow-x-auto pb-2">
                @foreach ($aliadosComerciales as $aliado)
                    <li class="flex w-48 shrink-0 snap-start flex-col items-center gap-3 rounded-xl border border-neutral-200 bg-white/80 p-4 dark:border-neutral-700 dark:bg-neutral-900/60">
                        @if ($aliado->logo)
                            <img src="{{ Storage::disk('public')->url($aliado->logo) }}"
                                 alt="{{ $aliado->nombre }}"
                                 loading="lazy"
                                 decoding="async"
                                 width="160"
                                 height="96"
                                 class="h-14 w-full object-contain">
                        @endif
                        <p class="text-center text-sm font-medium text-neutral-900 dark:text-white">{{ $aliado->nombre }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="mt-8 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
            <a href="{{ route('aliados.index') }}" class="enlace-accion font-medium text-marca-600 hover:text-marca-700 dark:text-marca-400 dark:hover:text-marca-300">
                Ver aliados&nbsp;<x-publico.flecha />
            </a>
        </div>
    </div>
</section>
