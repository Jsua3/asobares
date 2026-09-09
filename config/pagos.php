<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pasarela activa
    |--------------------------------------------------------------------------
    |
    | `fake` usa la pasarela simulada interna, que permite demostrar el flujo
    | completo sin credenciales. `bold` activa la integración real.
    |
    | A propósito SIN valor por defecto: un despliegue al que se le olvide la
    | variable tiene que romper en el arranque, no degradarse en silencio a la
    | pasarela simulada, que da por bueno cualquier pago.
    |
    */

    'driver' => env('PAYMENT_DRIVER'),

    /*
    |--------------------------------------------------------------------------
    | Montos de referencia del gremio
    |--------------------------------------------------------------------------
    */

    /*
     * Lo usa `Cartera::abonar()` para repartir un abono parcial en meses de mora.
     *
     * `afiliacion` (VALOR_AFILIACION) se retiró el 9 de septiembre de 2026: estaba
     * declarada aquí y documentada en los dos `.env.example`, y **ningún archivo
     * del proyecto la leía**. Una variable así es una trampa: alguien la pone en
     * producción creyendo que fija el valor de la afiliación y no fija nada. El
     * día que el sitio tenga que decir cuánto cuesta afiliarse, la cifra entra con
     * su fuente --el §12 no deja publicar un costo sin documento oficial-- y con
     * la vista que la pinta.
     */
    'mensualidad' => (int) env('VALOR_MENSUALIDAD', 50000),

    /*
    |--------------------------------------------------------------------------
    | Bold
    |--------------------------------------------------------------------------
    */

    'bold' => [
        'api_key' => env('BOLD_API_KEY', ''),
        'secret' => env('BOLD_SECRET', ''),
        'url' => env('BOLD_API_URL', 'https://integrations.api.bold.co'),

        /*
        | El sandbox es la única situación en que se acepta una llave de firma
        | vacía, así que su valor por defecto tiene que ser `false`: si fuera
        | `true`, un despliegue al que se le olvide la variable calcularía la
        | firma del webhook con llave vacía, y eso lo puede reproducir
        | cualquiera. Hay que pedirlo a propósito.
        */
        'sandbox' => filter_var(env('BOLD_SANDBOX', false), FILTER_VALIDATE_BOOLEAN),
    ],

];
