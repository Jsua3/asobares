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
    Sello en `antetitulo`, sin borde ni píldora: la tarjeta ya tiene su
    elemento decorado en el icono. `antetitulo` es la convención del sitio para
    rótulos pequeños en mayúsculas, y mide lo mismo que `--text-2xs` (los dos
    0,6875rem): el sello pesa menos sin cambiar de tamaño.

    `text-apagado` no se toca: su contraste lo mide `ContrasteDelTextoTenueTest`
    contra tokens.css, y `BeneficiosPorAlcanceTest` fija que el sello lo siga
    usando.
    `mt-1` en vez de `mt-2` para que se lea como coletilla del título y no como
    un bloque aparte.
--}}
@if ($etiqueta = $beneficio->etiquetaDeAlcance())
    <p class="asb-sello-alcance antetitulo mt-1 text-apagado">{{ $etiqueta }}</p>
@endif
