@props(['fotos'])

@php
    $fotos = collect($fotos)->values();
@endphp

@if ($fotos->isNotEmpty())
    {{-- Decoración: los establecimientos ya se anuncian en «Destacados». --}}
    <div class="tarjeta-escena group relative mx-auto max-w-md lg:max-w-none"
         x-data="escena"
         x-on:pointermove="seguir($event)"
         x-on:pointerleave="salir()"
         x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`"
         aria-hidden="true">
        <div class="relative h-72 sm:h-80 lg:h-[26rem]">
            <div class="imagen-inclinable absolute inset-y-3 left-0 right-10 overflow-hidden rounded-[1.75rem] bg-superficie-alta">
                <img src="{{ $fotos->first() }}" alt=""
                     width="900" height="1100"
                     class="imagen-viva h-full w-full object-cover">
            </div>

            @if ($fotos->get(1))
                <div class="vidrio absolute bottom-0 right-0 w-[44%] overflow-hidden rounded-2xl">
                    <img src="{{ $fotos->get(1) }}" alt=""
                         width="480" height="360"
                         class="imagen-viva aspect-[4/3] w-full object-cover">
                </div>
            @endif

            @if ($fotos->get(2))
                <div class="absolute top-0 right-[16%] w-[30%] overflow-hidden rounded-xl border border-linea bg-superficie">
                    <img src="{{ $fotos->get(2) }}" alt=""
                         width="360" height="360"
                         class="imagen-viva aspect-square w-full object-cover">
                </div>
            @endif
        </div>
    </div>
@endif
