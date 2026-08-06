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
 * Chromium no reinicia una transición cuando lo que cambia es la custom
 * property que hay detrás del valor: la propiedad se queda congelada en el
 * color del tema anterior. Es el mismo bug que ya se cerró en el sitio
 * público (`publico.blade.php`), y el panel lo hereda en cualquier clase con
 * `transition-colors` que dependa de un token (por ejemplo, el enlace
 * «Revisar» de `<x-panel.cola>`, que usa `text-tinta`).
 *
 * El panel no controla el instante en que Filament cambia la clase `dark`
 * —vive dentro de su propio Alpine—, así que la mordaza reacciona al mismo
 * MutationObserver que ya repinta las gráficas en vez de envolver el cambio
 * como hace el sitio público.
 */
const mordaza = document.createElement('style')
mordaza.textContent = '*,*::before,*::after{transition:none !important}'

const quitarMordaza = () => mordaza.remove()

/*
 * Filament conmuta la clase `dark` en <html>. Chart.js no redibuja por eso
 * solo, así que hay que pedírselo: sin esto la gráfica se queda con el color
 * del tema anterior hasta que algo más la fuerce a repintar.
 */
new MutationObserver(() => {
    document.head.appendChild(mordaza)
    void document.documentElement.offsetHeight

    graficas.forEach((grafica) => grafica.update('none'))

    // Doble respaldo, igual que en el sitio público: sin el temporizador la
    // mordaza se quedaría puesta si el cambio llega con la pestaña en
    // segundo plano (el caso real es el evento `storage` entre /admin y el
    // sitio) y el navegador no ejecuta requestAnimationFrame.
    requestAnimationFrame(() => requestAnimationFrame(quitarMordaza))
    setTimeout(quitarMordaza, 250)
}).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
})
