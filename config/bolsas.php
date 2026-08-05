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
    | perfiles del banco de talento, desde su última actualización.
    |
    */

    'retencion_postulaciones_meses' => (int) env('RETENCION_POSTULACIONES_MESES', 6),

    'retencion_aspirantes_meses' => (int) env('RETENCION_ASPIRANTES_MESES', 12),

];
