<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retención de mensajes de contacto y PQR (Ley 1581 de 2012)
    |--------------------------------------------------------------------------
    |
    | La bolsa de empleo ya se depura sola (ver `config/bolsas.php`), pero los
    | formularios públicos de contacto y PQR guardan nombre, correo, teléfono y
    | el texto del mensaje sin ninguna fecha de caducidad. Eso contradice el
    | principio de caducidad de la Ley 1581: los datos se guardan mientras
    | sirvan al fin que la persona autorizó, no para siempre.
    |
    | El plazo cuenta desde que el mensaje se respondió. Si nunca se respondió,
    | desde que entró: un mensaje abandonado no puede volverse inmortal por no
    | haberlo atendido.
    |
    | Las PQR llevan un plazo mayor porque tienen radicado y valor probatorio
    | ante la SIC; un mensaje de contacto corriente no.
    |
    | Igual que en las bolsas, un plazo menor que 1 no desactiva la purga: el
    | comando aborta, porque `now()->subMonths(0)` es *ahora mismo* y eso
    | convertiría el borrado en «borra todo». Para desactivarla hay que quitar
    | la tarea de `routes/console.php`.
    |
    */

    'contacto_meses' => (int) env('RETENCION_CONTACTO_MESES', 12),

    'pqr_meses' => (int) env('RETENCION_PQR_MESES', 24),

];
