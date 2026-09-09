<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ¿El sitio se deja indexar por los buscadores? (D-08)
    |--------------------------------------------------------------------------
    |
    | Mientras el gremio no tenga dominio propio, no. Y no es una precaución
    | abstracta: hasta el 9 de septiembre de 2026 el sitio servía `Allow: /`,
    | publicaba su sitemap y clavaba una etiqueta canónica apuntando al host
    | temporal de Laravel Cloud. Es decir, le decía a Google todos los días que
    | la versión autorizada de cada página del gremio vive en una dirección
    | desechable que nadie va a conservar. Cuando llegue el dominio de verdad,
    | esas URLs ya estarán indexadas y compitiendo con las buenas.
    |
    | Va por variable y no por código para que abrirlo el día del dominio
    | (semana 8 del cronograma) sea poner esto en `true` y redesplegar, sin
    | tocar un archivo ni pasar por una revisión.
    |
    | **El valor por defecto es `false` a propósito.** Un despliegue al que se
    | le olvide la variable tiene que quedarse fuera de Google, no dentro:
    | salir del índice cuesta semanas, entrar cuesta un despliegue.
    |
    | Lo que esto NO toca: las zonas privadas --panel, portal del afiliado y
    | pasarela-- siguen prohibidas en `robots.txt` pase lo que pase, y el
    | sitemap se sigue generando para poder entregarlo el mismo día que se abra.
    |
    */

    'indexable' => filter_var(env('SITIO_INDEXABLE', false), FILTER_VALIDATE_BOOLEAN),

];
