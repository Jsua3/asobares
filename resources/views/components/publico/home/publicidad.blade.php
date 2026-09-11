@props(['publicidad'])

<section class="home-editorial-publicidad revelar mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" data-revelar aria-label="Publicidad">
    <x-publico.publicidad :publicidad="$publicidad" class="home-editorial-publicidad__tarjeta overflow-hidden rounded-2xl border border-linea" />
</section>
