@php
    $fondo = file_exists(public_path('videos/asobares-institucional.jpg'))
        ? asset('videos/asobares-institucional.jpg')
        : asset('img/og-asobares.jpg');
@endphp

<section class="home-editorial-cta revelar relative overflow-hidden" data-revelar aria-labelledby="cta-afiliacion">
    <div class="absolute inset-0">
        <img src="{{ $fondo }}"
             alt=""
             width="1600"
             height="900"
             class="imagen-viva home-editorial-cta__foto h-full w-full object-cover"
             loading="lazy"
             decoding="async">
        <div class="home-editorial-cta__velo absolute inset-0"></div>
    </div>

    <div class="relative mx-auto max-w-3xl px-4 py-16 text-center sm:px-6 sm:py-20">
        <p class="home-editorial-eyebrow text-white/65">{{ ajuste('cta_final_titulo') }}</p>
        <h2 id="cta-afiliacion" class="mt-3 font-display text-3xl font-bold text-balance text-white sm:text-4xl lg:text-[2.75rem] lg:leading-tight">
            {{ ajuste('cta_editorial_frase', 'La noche es más fuerte cuando tiene voz.') }}
        </h2>
        <p class="mx-auto mt-4 max-w-xl text-sm text-white/75 text-pretty">
            {{ Str::limit(strip_tags((string) ajuste('cta_final_texto')), 120) }}
        </p>
        <x-publico.boton :href="route('afiliate')" class="mt-7">
            {{ ajuste('cta_final_boton', 'Quiero afiliarme') }}
        </x-publico.boton>
    </div>
</section>
