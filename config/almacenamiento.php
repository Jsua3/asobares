<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dónde viven los archivos que sube el gremio
    |--------------------------------------------------------------------------
    |
    | El sitio guarda dos clases de archivo y no son intercambiables:
    |
    |   público  — portadas de eventos, logos y la galería de cada asociado.
    |              Los sirve el navegador por URL.
    |
    |              ⚠️ Ojo desde OBS3-13 (31 ago 2026): esto decía «y no hay
    |              nada que proteger», y era cierto mientras la galería solo
    |              tuviera fotos que el gremio había cargado y aprobado. Ya no:
    |              el propietario sube desde /mi-cuenta/fotos y esas fotos
    |              nacen SIN aprobar. No salen en la ficha pública, pero el
    |              archivo queda servido por URL, y una foto devuelta por
    |              «exótica» no se borra al devolverla. El nombre es un ULID y
    |              no es enumerable, así que es exposición por oscuridad y no
    |              un agujero abierto — pero la decisión de moverlas al disco
    |              privado, o de aceptar el riesgo por escrito, está PENDIENTE.
    |              Ver §30.3 del prompt maestro.
    |   privado  — los formatos oficiales de la guía normativa. Los sirve
    |              `GuiaController`, que antes comprueba que el requisito esté
    |              publicado. En el disco público esa comprobación sería
    |              decorativa: el mismo PDF quedaría accesible por /storage sin
    |              pasar por ningún control.
    |
    | Aquí se elige el DISCO de cada clase, no el driver. Existe porque el
    | despliegue lo necesita: en un servidor serverless el disco de la máquina
    | es efímero —no sobrevive a un despliegue y no se comparte entre
    | instancias—, así que todo lo que suba el gremio desde el panel se pierde
    | en el siguiente despliegue. En desarrollo eso no se nota nunca, que es
    | justo lo que lo hace peligroso.
    |
    | En local se quedan los discos de siempre. En el servidor se apunta a
    | almacenamiento de objetos con dos variables:
    |
    |     DISCO_PUBLICO=s3
    |     DISCO_PRIVADO=s3-privado
    |
    | No basta con `FILESYSTEM_DISK=s3`: los puntos de subida nombran su disco
    | de forma explícita —tienen que hacerlo, porque la diferencia entre los
    | dos es de seguridad y no de configuración— y `FILESYSTEM_DISK` sólo
    | cambia el disco por defecto, que aquí no lo usa nadie.
    |
    */

    'publico' => env('DISCO_PUBLICO', 'public'),

    'privado' => env('DISCO_PRIVADO', 'local'),

];
