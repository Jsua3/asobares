@props(['variante' => 'color', 'alto' => 'h-10', 'doble' => false])

{{--
    Logo oficial de ASOBARES Capítulo Quindío, tal cual viene del kit de marca.

    El manual prohíbe reorganizar, deformar o recolorear la marca, así que el
    archivo se usa completo y sin filtros. `blanco` es la única alternativa
    permitida, para fondos rojos o fotografías.

    `doble` pinta el logotipo completo y el isotipo «ab» superpuestos en la
    misma caja: el CSS de la barra de escritorio los cruza según `data-estado`
    (logotipo en reposo, isotipo al hacer scroll). El isotipo es el archivo
    del kit sin recortar ni recolorear; en oscuro va rojo sobre negro, como el
    favicon, porque no existe versión blanca del isotipo.
--}}
{{--
    El archivo de color es PNG y no SVG a propósito, y no es una degradación de
    la marca: `logo-asobares.svg` nunca fue un vector. Era un <svg><image> que
    envolvía este mismo PNG en base64, así que costaba un 34 % más de bytes por
    la codificación, más un análisis de XML y una decodificación de base64
    extra antes de poder pintar. Los píxeles son exactamente los mismos: el PNG
    se extrajo de dentro de aquel archivo, sin recodificar nada.

    Importa porque el logo tiene que estar en el PRIMER pintado. Medido antes
    del cambio, en los dos temas, el <img> llegaba a `pagereveal` y al primer
    rAF con `naturalWidth` 0 y no terminaba hasta `load`: en cada navegación el
    logo se veía desaparecer y volver.
--}}
@php
    $archivo = $variante === 'blanco' ? 'img/logo-asobares-blanco.png' : 'img/logo-asobares.png';
@endphp

@if ($doble)
    <span {{ $attributes->merge(['class' => "logo-doble relative block {$alto}"]) }}>
        <img src="{{ asset($archivo) }}"
             alt="ASOBARES Capítulo Quindío"
             width="592" height="108"
             fetchpriority="high"
             class="logo-doble__completo h-full w-auto">
        {{-- alt vacío: la marca ya la anuncia el logotipo de al lado. --}}
        <img src="{{ asset('img/monograma-asobares.png') }}"
             alt=""
             width="156" height="108"
             class="logo-doble__isotipo absolute inset-y-0 left-0 h-full w-auto">
    </span>
@else
    <img src="{{ asset($archivo) }}"
         alt="ASOBARES Capítulo Quindío"
         width="592" height="108"
         fetchpriority="high"
         {{ $attributes->merge(['class' => "{$alto} w-auto"]) }}>
@endif
