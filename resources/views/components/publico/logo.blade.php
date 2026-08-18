@props(['variante' => 'color', 'alto' => 'h-10'])

{{--
    Logo oficial de ASOBARES Capítulo Quindío, tal cual viene del kit de marca.

    El manual prohíbe reorganizar, deformar o recolorear la marca, así que el
    archivo se usa completo y sin filtros. `blanco` es la única alternativa
    permitida, para fondos rojos o fotografías.
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

<img src="{{ asset($archivo) }}"
     alt="ASOBARES Capítulo Quindío"
     width="592" height="108"
     fetchpriority="high"
     {{ $attributes->merge(['class' => "{$alto} w-auto"]) }}>
