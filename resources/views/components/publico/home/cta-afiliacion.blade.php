@php
    $fondo = file_exists(public_path('videos/asobares-institucional.jpg'))
        ? asset('videos/asobares-institucional.jpg')
        : asset('img/og-asobares.jpg');
@endphp

<section class="home-editorial-cta revelar relative overflow-hidden border-t border-linea" data-revelar aria-labelledby="cta-afiliacion">
    <div class="absolute inset-0">
        <img src="{{ $fondo }}"
             alt=""
             width="1600"
             height="900"
             class="imagen-viva h-full w-full object-cover"
             loading="lazy"
             decoding="async">
        <div class="home-editorial-cta__velo absolute inset-0"></div>
    </div>

    <div class="relative mx-auto max-w-3xl px-4 py-20 text-center sm:px-6 sm:py-24">
        <p class="antetitulo text-white/70">{{ ajuste('cta_final_titulo') }}</p>
        <h2 id="cta-afiliacion" class="mt-4 font-display text-3xl font-bold text-balance text-white sm:text-4xl lg:text-5xl">
            {{ ajuste('cta_editorial_frase', 'La noche es más fuerte cuando tiene voz.') }}
        </h2>
        <p class="mx-auto mt-5 max-w-2xl text-sm text-white/78 sm:text-base text-pretty">
            {{ ajuste('cta_final_texto') }}
        </p>
        <x-publico.boton :href="route('afiliate')" class="mt-8">
            {{ ajuste('hero_cta_afiliate') }}
        </x-publico.boton>
    </div>
</section>
