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
    /* Lo que el usuario eligió: light, dark o system. */
    preferencia: 'system',

    /* Lo que está pintado: light o dark. Alimenta el icono sol/luna. */
    resuelto: 'light',

    init() {
        this.preferencia = this.leer();
        this.resuelto = this.resolver(this.preferencia);

        // Con «sistema» elegido, el <head> repinta solo cuando cambia el SO;
        // aquí solo hace falta enterarse para que el icono siga al pintado.
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.preferencia === 'system') {
                this.resuelto = this.resolver('system');
            }
        });
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
        // con un tema que ya no coincide con el botón marcado. El <head>
        // entiende 'system' como «seguir al sistema»; se resuelve DESPUÉS de
        // pintar, leyendo la clase que el <head> acaba de poner.
        window.aplicarTema?.(valor);
        this.resuelto = this.resolver(valor);
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

/*
 * Video del hero. El elemento sale del servidor sin `autoplay` y con
 * `preload="none"`: si pidieron menos movimiento, aquí no se toca nada y el
 * visitante se queda con el póster sin haber descargado el video. Solo cuando
 * el movimiento está permitido se pide la descarga y se intenta reproducir, y
 * solo si el navegador acepta --las políticas de reproducción automática
 * rechazan la promesa sin avisar de otra forma-- se funde la capa encima del
 * póster. Un `catch` vacío dejaría el póster, que es exactamente lo correcto.
 */
Alpine.data('videoHero', () => ({
    listo: false,

    init() {
        if (reduceMovimiento()) {
            return;
        }

        // Las políticas de reproducción automática solo perdonan el video mudo.
        this.$el.muted = true;

        /*
         * Pedir la descarga es cambiar `preload`; NO se llama a `load()`.
         * Comprobado en el navegador: con `load()` delante, el `play()` que
         * viene detrás se rechaza --la carga en curso lo aborta--, así que
         * `listo` se quedaba en falso y el video aparecía parado detrás del
         * póster **con el archivo entero ya descargado** (`readyState` 4,
         * `paused` true). Sin `load()`, `play()` resuelve.
         */
        this.$el.preload = 'auto';

        this.arrancar();

        // Si todavía no había datos, el primer intento se rechaza; se reintenta
        // en cuanto el navegador dice que puede. `arrancar()` se protege sola.
        this.$el.addEventListener('canplay', () => this.arrancar());
    },

    arrancar() {
        if (this.listo) {
            return;
        }

        const intento = this.$el.play();

        // Navegadores viejos no devuelven promesa: se mira el estado y ya.
        if (! intento) {
            this.listo = ! this.$el.paused;

            return;
        }

        intento.then(() => { this.listo = true; }).catch(() => { this.listo = false; });
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
        tema.preferencia = tema.leer();
        tema.resuelto = tema.resolver(tema.preferencia);
    }
});
