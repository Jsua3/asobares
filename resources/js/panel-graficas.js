/*
 * Las gráficas del panel siguen el tema.
 *
 * El relleno de las series se queda en Pub Red: como relleno funciona en los
 * dos temas (la restricción AA del token `acento` es para texto). Lo que no
 * seguía el tema eran los ticks, la rejilla y la leyenda, que Chart.js pinta
 * en un gris fijo con poco contraste sobre el fondo oscuro.
 *
 * El plugin lleva su propio registro de instancias (`start`/`stop`) en vez de
 * leer `Chart.instances`: Filament empaqueta Chart.js como módulo y no expone
 * ese global de forma fiable.
 */

const graficas = new Set()

const leerToken = (nombre) =>
    getComputedStyle(document.documentElement).getPropertyValue(nombre).trim()

const plugin = {
    id: 'asobaresTema',

    start(grafica) {
        graficas.add(grafica)
    },

    stop(grafica) {
        graficas.delete(grafica)
    },

    beforeUpdate(grafica) {
        const tinta = leerToken('--asb-tinta')
        const linea = leerToken('--asb-linea')

        if (! tinta) return

        grafica.options.color = tinta

        const leyenda = grafica.options.plugins?.legend?.labels
        if (leyenda) leyenda.color = tinta

        for (const eje of Object.values(grafica.options.scales ?? {})) {
            if (eje.ticks) eje.ticks.color = tinta
            if (eje.grid) eje.grid.color = linea
            if (eje.border) eje.border.color = linea
        }
    },
}

window.filamentChartJsPlugins ??= []
window.filamentChartJsPlugins.push(plugin)

/*
 * Filament conmuta la clase `dark` en <html>. Chart.js no redibuja por eso
 * solo, así que hay que pedírselo: sin esto la gráfica se queda con el color
 * del tema anterior hasta que algo más la fuerce a repintar.
 */
new MutationObserver(() => {
    graficas.forEach((grafica) => grafica.update('none'))
}).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
})
