@props(['variante' => 'color', 'alto' => 'h-10'])

{{--
    Logo oficial de ASOBARES Capítulo Quindío, tal cual viene del kit de marca.

    El manual prohíbe reorganizar, deformar o recolorear la marca, así que el
    archivo se usa completo y sin filtros. `blanco` es la única alternativa
    permitida, para fondos rojos o fotografías.
--}}
@php
    $archivo = $variante === 'blanco' ? 'img/logo-asobares-blanco.png' : 'img/logo-asobares.svg';
@endphp

<img src="{{ asset($archivo) }}"
     alt="ASOBARES Capítulo Quindío"
     width="592" height="108"
     {{ $attributes->merge(['class' => "{$alto} w-auto"]) }}>
