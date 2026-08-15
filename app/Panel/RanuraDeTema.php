<?php

namespace App\Panel;

use stdClass;

/**
 * La ranura vacía que un ChartWidget le deja al plugin de tema.
 *
 * `panel-graficas.js` solo escribe donde ya hay clave, así que cada gráfica
 * declara `ticks`, `grid` o `labels` aunque no traigan nada. Pero un array
 * PHP vacío se serializa a JSON como `[]` —un array de JavaScript— y
 * Chart.js no envuelve arrays en su resolutor de opciones: la gráfica se
 * crea y pinta una vez, y el primer `update()` —cambio de tema,
 * ResizeObserver, refresco de datos— muere sin capturar con
 * «this.options.ticks.setContext is not a function» y la deja congelada.
 * Un `stdClass` vacío llega como `{}`, que es lo que Chart.js espera.
 *
 * `RanurasDelPluginDeTemaTest` vigila que ninguna gráfica vuelva al `[]`.
 */
final class RanuraDeTema
{
    public static function vacia(): stdClass
    {
        return new stdClass;
    }
}
