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
        this.preferencia = this.resolver(this.leer());
    },

    leer() {
        // Safari en navegación privada y los navegadores con almacenamiento
        // bloqueado lanzan al tocar localStorage.
        try {
            const guardado = localStorage.getItem('theme');

            // Un valor que no reconocemos vale lo mismo que no tener ninguno.
            return ['light', 'dark'].includes(guardado) ? guardado : 'system';
        } catch {
            return 'system';
        }
    },

    resolver(valor) {
        if (valor === 'light' || valor === 'dark') {
            return valor;
        }

        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
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

const reduceMovimiento = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const punteroFino = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;

/*
 * Luz que sigue al puntero e inclinación mínima. Se apagan si pidieron
 * menos movimiento o si no hay puntero fino: en táctil el rastro se
 * quedaría pegado en el último toque.
 */
Alpine.data('escena', () => ({
    px: 50,
    py: 50,

    seguir(evento) {
        if (reduceMovimiento() || ! punteroFino()) {
            return;
        }

        const caja = this.$el.getBoundingClientRect();
        const ancho = caja.width || 1;
        const alto = caja.height || 1;

        this.px = ((evento.clientX - caja.left) / ancho) * 100;
        this.py = ((evento.clientY - caja.top) / alto) * 100;
    },

    salir() {
        this.px = 50;
        this.py = 50;
    },
}));

window.Alpine = Alpine;
Alpine.start();

const prepararRevelado = () => {
    const nodos = document.querySelectorAll('[data-revelar]');

    if (nodos.length === 0) {
        return;
    }

    if (reduceMovimiento()) {
        nodos.forEach((nodo) => nodo.classList.add('revelar-visto'));

        return;
    }

    const observador = new IntersectionObserver((entradas) => {
        for (const entrada of entradas) {
            if (entrada.isIntersecting) {
                entrada.target.classList.add('revelar-visto');
                observador.unobserve(entrada.target);
            }
        }
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

    nodos.forEach((nodo) => observador.observe(nodo));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', prepararRevelado);
} else {
    prepararRevelado();
}

// Otra pestaña cambió el tema: el script del <head> ya repintó, aquí solo
// falta refrescar cuál de los tres botones se ve activo.
window.addEventListener('storage', (evento) => {
    if (evento.key === 'theme') {
        const tema = Alpine.store('tema');
        tema.preferencia = tema.resolver(tema.leer());
    }
});
