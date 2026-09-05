@props(['pais'])

{{--
    Banderas dibujadas a mano. No hay activos en el repositorio, el subconjunto
    de Poppins no trae emoji, y un paquete de banderas tocaría composer.json.
    Colombia en sus tres franjas (amarillo 50 %, azul 25 %, rojo 25 %).
    Estados Unidos simplificada: trece franjas y cantón azul sin estrellas,
    que a 14 px de alto no se resuelven.

    Los colores son los de las banderas, no del tema: van como atributos
    `fill` del SVG y no como clases, a propósito.
--}}
@if ($pais === 'co')
    <svg data-pais="co" viewBox="0 0 20 14" width="20" height="14"
         {{ $attributes->merge(['class' => 'h-3.5 w-5 shrink-0 rounded-sm']) }} aria-hidden="true">
        <rect width="20" height="7" fill="#FCD116"/>
        <rect y="7" width="20" height="3.5" fill="#003893"/>
        <rect y="10.5" width="20" height="3.5" fill="#CE1126"/>
    </svg>
@elseif ($pais === 'us')
    <svg data-pais="us" viewBox="0 0 20 14" width="20" height="14"
         {{ $attributes->merge(['class' => 'h-3.5 w-5 shrink-0 rounded-sm']) }} aria-hidden="true">
        <rect width="20" height="14" fill="#FFFFFF"/>
        @for ($franja = 0; $franja < 13; $franja += 2)
            <rect y="{{ round($franja * 14 / 13, 3) }}" width="20" height="{{ round(14 / 13, 3) }}" fill="#B22234"/>
        @endfor
        <rect width="8" height="7.538" fill="#3C3B6E"/>
    </svg>
@endif
