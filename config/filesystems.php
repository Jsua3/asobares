<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            /*
             * URL relativa a propósito.
             *
             * Si se ata a APP_URL, las imágenes apuntan al host y puerto que
             * diga el .env, y se rompen en cuanto el servidor levanta en otro
             * puerto (o detrás de un dominio distinto). Relativa funciona en
             * cualquiera. Donde hace falta absoluta —og:image— se absolutiza
             * en la vista con url().
             */
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         * Los dos discos de objetos son el mismo bucket leído de dos maneras, y
         * son dos y no uno por la misma razón por la que `public` y `local` son
         * dos: lo que distingue a los formatos de la guía normativa de una
         * portada de evento no es dónde se guardan, es quién puede leerlos.
         *
         * LOS PREFIJOS SON LA FRONTERA DE SEGURIDAD, Y ESTÁ MEDIDO.
         *
         * La forma natural de abrir un bucket es una política que conceda
         * `s3:GetObject` sobre `arn:aws:s3:::<bucket>/*`. Probado contra un
         * almacén compatible con S3, esa política deja los PDF de la guía
         * **descargables por cualquiera**: devuelven 200 sin pasar por
         * `GuiaController`, que es justo el control que existe para que no se
         * puedan bajar de un requisito sin publicar. La comprobación queda
         * decorativa exactamente igual que cuando vivían en el disco `public`.
         *
         * Por eso el disco público cuelga de `publico/` y la política sólo
         * puede abrir ESE prefijo:
         *
         *     "Resource": ["arn:aws:s3:::<bucket>/publico/*"]
         *
         * Con el comodín sobre la raíz del bucket, esta separación no existe.
         * El apartado 8 del runbook lo repite porque quien cree el bucket real
         * no va a leer este comentario.
         */
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'root' => 'publico',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3-privado' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            // Sin `url` a propósito: nadie debe poder construir la dirección de
            // un formato oficial. Se descargan por `GuiaController`, que
            // comprueba antes que el requisito esté publicado.
            'root' => 'privado',
            'visibility' => 'private',
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
