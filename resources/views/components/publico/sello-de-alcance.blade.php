@props(['beneficio'])

{{--
    De quién es el beneficio (Acta 07, A-01). Un solo componente para las tres
    pantallas que los pintan, porque tres copias del mismo sello es lo que hace
    que una se quede atrás el día que cambie.

    Si el gremio no lo ha clasificado, aquí no sale nada: el sistema no adivina
    de quién es un beneficio. Y el municipal se anuncia con el nombre de su
    municipio y no con la palabra «Municipal», que no le dice nada al lector.
--}}
{{--
    Simplificado el 9 de septiembre de 2026 (SUA-10), porque Sua leyó las
    tarjetas y las sintió cargadas. Y lo estaban: en la portada, cada beneficio
    apilaba un número grande, un icono dentro de una caja redondeada, el título,
    **una píldora con borde** y la descripción. Cinco objetos visuales para
    decir cinco cosas, y la píldora competía con el chip del icono por ser el
    elemento decorado de la tarjeta.

    Lo que se va: el borde, la forma de píldora, el relleno y el `inline-flex`.
    Lo que se queda: exactamente la misma información.

    Y no se inventa un estilo nuevo: pasa a `antetitulo`, que es la convención
    del sitio para rótulos pequeños en mayúsculas —la usan el hero, la banda de
    vídeos, el bloque de publicidad y los KPI del panel—. Mide lo mismo que
    medía (`--text-2xs` y `antetitulo` son los dos 0,6875rem), así que el cambio
    es de peso visual, no de tamaño.

    `text-apagado` no se toca: es el token cuyo contraste se midió en 4,53:1
    sobre el fondo de la página, y `BeneficiosPorAlcanceTest` lo vigila.
    `mt-1` en vez de `mt-2` para que se lea como coletilla del título y no como
    un bloque aparte.
--}}
@if ($etiqueta = $beneficio->etiquetaDeAlcance())
    <p class="asb-sello-alcance antetitulo mt-1 text-apagado">{{ $etiqueta }}</p>
@endif
