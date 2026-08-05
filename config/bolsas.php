<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retención de datos personales (Ley 1581 de 2012)
    |--------------------------------------------------------------------------
    |
    | Los datos de quien busca empleo se guardan solo mientras sirvan para el
    | fin que autorizó. Pasado el plazo se borran solos: el cumplimiento no
    | puede depender de que alguien se acuerde de limpiar la bandeja.
    |
    | Las postulaciones cuentan desde que su vacante cerró o venció; los
    | perfiles del banco de talento, desde que la persona dio su
    | consentimiento (y se resella solo si vuelve a enviar el formulario:
    | una edición cualquiera desde el panel no cuenta).
    |
    | Un plazo de 0 (o negativo) NO desactiva la purga: `bolsas:depurar`
    | aborta con un error en vez de ejecutar, porque `now()->subMonths(0)`
    | es *ahora mismo* y eso convertiría el borrado en «borra todo», no en
    | «no borres nada». Para desactivar la purga hay que quitar la tarea
    | `bolsas:depurar` de `routes/console.php`, no vaciar estas variables.
    |
    */

    'retencion_postulaciones_meses' => (int) env('RETENCION_POSTULACIONES_MESES', 6),

    'retencion_aspirantes_meses' => (int) env('RETENCION_ASPIRANTES_MESES', 12),

];
