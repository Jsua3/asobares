/*
 * Las gráficas del panel siguen el tema.
 *
 * Lo que no seguía el tema eran los ticks, la rejilla y la leyenda, que
 * Chart.js pinta en un gris fijo con poco contraste sobre el fondo oscuro.
 *
 * El relleno de una gráfica de una sola serie se queda en Pub Red: como
 * relleno funciona en los dos temas (la restricción AA del token `acento` es
 * para texto). Las gráficas categóricas son otra cosa: siete rellenos que
 * tienen que distinguirse del fondo Y entre sí, y la paleta del manual está
 * pensada sobre Ambient White. Sobre Pub Black, Wine y Pub Grey se apagan y
 * Pub Black ES el fondo. Por eso esos rellenos salen de `--asb-serie-N`
 * (tokens.css, con valores propios en `.dark`) y no del hexadecimal que
 * mandó el servidor, que queda como reserva para cuando no hay JS.
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

        /*
         * Solo repinta lo que pidió su ranura: un conjunto sin
         * `asobaresSerie` conserva el color que mandó el servidor. Así una
         * gráfica de una sola serie no se ve afectada, y si el token faltara
         * se queda el hexadecimal de reserva en vez de pintar transparente.
         */
        for (const conjunto of grafica.data?.datasets ?? []) {
            if (! conjunto.asobaresSerie) continue

            const relleno = leerToken(`--asb-serie-${conjunto.asobaresSerie}`)
            if (relleno) conjunto.backgroundColor = relleno
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

    /*
     * La retirada se programa ANTES de tocar las gráficas, y no depende de
     * que `update()` salga bien: a diferencia del sitio público —donde entre
     * el `appendChild` y la programación del retiro solo hay un toggle de
     * clase, que no puede lanzar—, aquí en medio hay que repintar cada
     * gráfica, y una ya destruida por un remontaje de Livewire que nunca
     * pasó por `stop()` puede lanzar. Si la programación fuera después del
     * `forEach`, esa excepción la cortaría antes de llegar, y la mordaza se
     * quedaría pegada con `transition:none !important` en todo el panel
     * hasta el siguiente cambio de tema que sí saliera bien.
     *
     * Doble respaldo, igual que en el sitio público: sin el temporizador la
     * mordaza se quedaría puesta si el cambio llega con la pestaña en
     * segundo plano (el caso real es el evento `storage` entre /admin y el
     * sitio) y el navegador no ejecuta requestAnimationFrame.
     */
    requestAnimationFrame(() => requestAnimationFrame(quitarMordaza))
    setTimeout(quitarMordaza, 250)

    graficas.forEach((grafica) => grafica.update('none'))
}).observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class'],
})
