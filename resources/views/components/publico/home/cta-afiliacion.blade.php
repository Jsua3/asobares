@php
    $fondo = urlDeFotoDeLaHome(null, config('home_banco.cta'));
@endphp

<section class="home-editorial-cta revelar relative overflow-hidden" data-revelar aria-labelledby="cta-afiliacion">
    <div class="absolute inset-0">
        @if ($fondo)
            <img src="{{ $fondo }}"
                 alt=""
                 width="1200"
                 height="900"
                 class="home-editorial-cta__foto h-full w-full object-cover"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="home-editorial-cta__fallback h-full w-full" aria-hidden="true"></div>
        @endif
        <div class="home-editorial-cta__velo absolute inset-0" aria-hidden="true"></div>
    </div>

    <div class="relative mx-auto max-w-2xl px-4 py-14 text-center sm:px-6 sm:py-16">
        <p class="home-editorial-eyebrow text-white/75">{{ ajuste('cta_final_titulo') }}</p>
        <h2 id="cta-afiliacion" class="mt-3 font-display text-3xl font-bold text-balance text-white sm:text-4xl">
            {{ ajuste('cta_editorial_frase', 'La noche es más fuerte cuando tiene voz.') }}
        </h2>
        <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-white/88 sm:text-base">
            {{ ajuste('cta_final_texto') }}
        </p>
        <x-publico.boton :href="route('afiliate')" class="mt-6">
            {{ ajuste('cta_final_boton', 'Quiero afiliarme') }}
        </x-publico.boton>
    </div>
</section>
