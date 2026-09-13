<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ¿El sitio se deja indexar por los buscadores?
    |--------------------------------------------------------------------------
    |
    | Mientras el gremio no tenga dominio propio, no. Indexar un host temporal
    | hace que Google tome esas URLs como las canónicas de cada página, y
    | cuando llegue el dominio definitivo compiten con las suyas.
    |
    | Conmuta dos cosas: `Allow: /` o `Disallow: /` en `robots.txt`, y la
    | etiqueta `noindex, nofollow` del layout público. La canónica y la línea
    | `Sitemap:` de `robots.txt` salen siempre.
    |
    | Va por variable y no por código para que abrirlo el día del dominio sea
    | poner esto en `true` y redesplegar, sin tocar un archivo ni pasar por una
    | revisión.
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
