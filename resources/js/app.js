import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

/*
 * Preferencia de tema del sitio público.
 *
 * El pintado ya lo resolvió el script síncrono del <head>; este store existe
 * solo para que los dos controles —el del desplegable de escritorio y el del
 * menú móvil— compartan estado y se marquen como activos a la vez.
 *
 * La clave `theme` es la misma que usa Filament, así que elegir aquí cambia
 * también el panel /admin.
 */
Alpine.store('tema', {
    preferencia: 'system',

    init() {
        this.preferencia = this.leer();
    },

    leer() {
        // Safari en navegación privada y los navegadores con almacenamiento
        // bloqueado lanzan al tocar localStorage.
        try {
            const guardado = localStorage.getItem('theme');

            // Un valor que no reconocemos vale lo mismo que no tener ninguno.
            return ['light', 'dark', 'system'].includes(guardado) ? guardado : 'system';
        } catch {
            return 'system';
        }
    },

    elegir(valor) {
        this.preferencia = valor;

        try {
            localStorage.setItem('theme', valor);
        } catch {
            // Sin almacenamiento el cambio dura lo que dure la página.
        }

        // Se pasa el valor en vez de dejar que lo relea: si la escritura de
        // arriba falló, releer devolvería el anterior y la página se quedaría
        // con un tema que ya no coincide con el botón marcado.
        window.aplicarTema?.(valor);
    },
});

window.Alpine = Alpine;
Alpine.start();

// Otra pestaña cambió el tema: el script del <head> ya repintó, aquí solo
// falta refrescar cuál de los tres botones se ve activo.
window.addEventListener('storage', (evento) => {
    if (evento.key === 'theme') {
        Alpine.store('tema').preferencia = Alpine.store('tema').leer();
    }
});
