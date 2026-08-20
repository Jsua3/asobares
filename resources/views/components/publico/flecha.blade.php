@props(['direccion' => 'derecha'])

@php
    /*
     * Las flechas del sitio dejan de ser caracteres.
     *
     * El subconjunto que compila `bunny('Poppins', …)` en `vite.config.js`
     * trae 217 glifos por peso y ninguno es una flecha: faltan incluso los
     * codepoints U+2191 y U+2193, que el propio `@font-face` promete en su
     * `unicode-range`. Los tres que usaba el sitio (U+2192, U+2190 y U+2197)
     * ni siquiera entran en ese rango, así que el navegador no llegaba a
     * intentar Poppins: caía a `ui-sans-serif` y los pintaba la fuente del
     * sistema, con otro grosor, otra caja y otra posición sobre la línea
     * base, distinta en cada equipo. Ampliar el rango no arreglaría nada:
     * la familia no dibuja esos glifos, no es que el recorte se los comiera.
     *
     * Trazo de Heroicons 24/outline, la misma familia que ya dibujan
     * `alerta.blade.php` y el paginador. Con `currentColor` hereda el color
     * del enlace y, con él, el fundido que declara `.enlace-accion`: el
     * valor heredado sigue al que anima el padre, sin transición propia.
     */
    $trazo = match ($direccion) {
        'izquierda' => 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18',
        'externa' => 'm4.5 19.5 15-15m0 0H8.25m11.25 0v11.25',
        default => 'M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3',
    };
@endphp

{{--
    `align-[-0.125em]` y no `align-middle`: la caja es cuadrada de 1 em y sin
    ese descenso se apoya en la línea base y flota alta sobre la minúscula.
    Donde el portador ya es `inline-flex items-center` la declaración es
    inerte y manda el centrado del flex, que también es correcto.

    NUNCA añadir aquí utilidades de transición, de duración ni de curva: son
    de la capa utilities y en Tailwind 4 pisan siempre al portador
    `.enlace-accion`, que vive en la capa components. Tampoco hacen falta:
    el color ya llega heredado y ya viene animado por el padre.

    Y una que cuesta encontrar: el espacio de no separación que llevan las
    llamadas NO impide que el icono caiga solo al renglón siguiente. Medido en
    Chromium: la línea se parte igual delante de una caja atómica, incluso sin
    ningún espacio de por medio. Con el carácter de antes sí bastaba; con un
    SVG no. Donde el rótulo va dentro de un párrafo y el ancho aprieta, la
    única defensa es `whitespace-nowrap` en el portador, y solo hay dos sitios
    que lo necesiten (`inicio` y `empleo/show`, ambos comentados allí). El
    espacio duro se queda porque sigue siendo el hueco visible entre palabra e
    icono, no porque frene nada.
--}}
<svg {{ $attributes->merge(['class' => 'inline-block size-[1em] shrink-0 align-[-0.125em]']) }}
     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $trazo }}"/>
</svg>
