import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { animar, crearRastro, crearResorte, goma, proyectar, vibrar } from './movimiento.js';

Alpine.plugin(collapse);

/*
 * Preferencia de tema del sitio público.
 *
 * El pintado ya lo resolvió el script síncrono del <head>; este store existe
 * para que el control de tema de la barra, que es el mismo en los dos anchos,
 * sepa qué preferencia marcar como activa y qué icono pintar.
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

// Dentro del documento: ni el rebote elástico de iOS por debajo de 0 ni el
// de más allá del final cuentan. El navegador acota scrollY con el alto
// VIGENTE del viewport, que en iOS crece al plegarse la barra de direcciones.
const posicionDelDocumento = () => Math.min(Math.max(window.scrollY, 0), Math.max(document.documentElement.scrollHeight - window.innerHeight, 0));

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
 * Desplegable de la barra, en los dos anchos: los dos grupos, la cuenta, el
 * tema y el idioma en escritorio; las dos hojas del módulo inferior, la
 * cuenta y el tema en móvil. Todos son el mismo «disclosure» —botón con aria-expanded y el panel
 * que controla— y se comportan igual: con puntero fino se asoma al pasar y
 * se retira con una gracia que perdona el camino hasta el panel; con dedo y
 * con teclado, al pulsar. Abrir uno avisa a los demás y esos ceden al
 * instante, así que nunca hay dos paneles abiertos a la vez, que era como el
 * popover de tema y el de idioma se pisaban al pasar del sol al chip.
 *
 * Los cableados (pointerenter, pointerleave, click.outside, pointerdown fuera,
 * focusout, Escape, el aviso, el scroll y el bfcache) van en cada vista y no
 * aquí: las guardias los leen crudos.
 */
const GRACIA_AL_RETIRAR_MS = 280;

/**
 * Deceleración con la que se proyecta el momento de la hoja al soltarla.
 *
 * NO es el 0,998 por defecto, que es el del scroll, y la diferencia no es
 * cosmética: 0,998 multiplica la velocidad por 499, y eso está calibrado para
 * una lista que se desplaza miles de píxeles. La hoja mide 155.
 *
 * Medido el 9 de septiembre de 2026 sobre la hoja de «Bolsas» (155 px):
 * arrastrarla 40 px despacio da unos 143 px/s, que con 0,998 proyectan 71 px
 * y llevan el reposo a 111 —o sea que un tirón suave y corto la CERRABA, que
 * es justo lo que no debe pasar—. Con 0,99 el multiplicador baja a 99, esos
 * mismos 143 px/s proyectan 14 px y la hoja vuelve a abrirse; y un golpe corto
 * y rápido (45 px en 32 ms, unos 1400 px/s) sigue proyectando 139 px y la
 * cierra, que es lo que se quiere.
 */
const DECELERACION_DE_LA_HOJA = 0.99;

Alpine.data('desplegable', () => ({
    abierto: false,
    cierre: null,
    scrollAlAbrir: 0,

    // La identidad es `$root` y NO `$el`: dentro de un método, `$el` es el
    // elemento de la directiva que lo llamó, o sea el botón cuando se abre
    // por clic y la raíz cuando se abre por hover. Con `$el` el aviso del
    // clic llegaba con el botón, el propio componente lo tomaba por ajeno y
    // cerraba lo que acababa de abrir: Enter no abría nada (Chromium, 5 sep).
    abrir() {
        clearTimeout(this.cierre);
        // Si había un cierre por gesto en vuelo, se corta AQUÍ. Sin esto, su
        // resorte sigue corriendo, llega a su meta y ejecuta el `cerrar()` que
        // tenía pendiente: la hoja que se acaba de reabrir se cerraría sola.
        this.pararElReloj();
        // La hoja vuelve a nacer limpia. El estilo en línea que deja un cierre
        // por gesto NO se quita al cerrar --pisaría la transición de salida de
        // Alpine y la hoja daría un salto hacia arriba en pleno desvanecido--,
        // así que se quita aquí, que es el otro momento en que es seguro.
        this.soltarLaPintura();
        this.resorteDeLaHoja?.fijar(0);
        this.abierto = true;
        this.scrollAlAbrir = posicionDelDocumento();
        this.$dispatch('desplegable-abierto', this.$root);
    },

    cerrar() {
        clearTimeout(this.cierre);
        this.abierto = false;
    },

    // Desplazarse es cerrar: 24 px desde que se abrió, el umbral con el que el
    // header suelta la atención con dedo. Salvo con el foco DENTRO: quien
    // baja con una flecha o AvPág mientras recorre la hoja está usando el
    // teclado, no yéndose, y cerrarle el panel bajo el foco lo tira al body
    // (el defecto que el 5 sep se corrigió para Escape).
    cerrarSiSeDesplaza() {
        if (! this.abierto || this.$root.contains(document.activeElement)) {
            return;
        }

        if (Math.abs(posicionDelDocumento() - this.scrollAlAbrir) > 24) {
            this.cerrar();
        }
    },

    alternar() {
        if (this.abierto) {
            this.cerrar();

            return;
        }

        this.abrir();
    },

    // Solo el ratón asoma. En un equipo híbrido (ratón y pantalla táctil) la
    // consulta de puntero fino es verdadera, y un toque llega primero como
    // pointerenter de tipo touch y después como un mouseenter sintético: con
    // mouseenter el toque abría y el click del mismo gesto cerraba, y había
    // que tocar dos veces. Por eso las vistas cablean pointerenter y
    // pointerleave, y aquí solo cuenta el puntero que de verdad se posa.
    asomar(evento) {
        if (evento.pointerType !== 'mouse' || ! punteroFino()) {
            return;
        }

        this.abrir();
    },

    retirar(evento) {
        if (evento.pointerType !== 'mouse' || ! punteroFino()) {
            return;
        }

        clearTimeout(this.cierre);
        this.cierre = setTimeout(() => this.cerrar(), GRACIA_AL_RETIRAR_MS);
    },

    // El aviso llega también al que lo emitió: ese se queda como está.
    ceder(raiz) {
        if (raiz === this.$root) {
            return;
        }

        this.cerrar();
    },

    // El foco vuelve al disparador solo si estaba dentro del componente, y
    // se lee ANTES de cerrar. Sin esto el panel desaparece con el foco dentro
    // y el navegador lo tira al <body>, y el siguiente Tab reinicia desde el
    // principio; pero un panel abierto por hover mientras se escribe en un
    // campo no puede robarle el foco al campo.
    cerrarYVolverAlFoco() {
        if (! this.abierto) {
            return;
        }

        const teniaElFoco = this.$root.contains(document.activeElement);

        this.cerrar();

        if (! teniaElFoco) {
            return;
        }

        this.$refs.disparador.focus();
    },

    /*
     * ── La hoja del teléfono se cierra con el dedo ──────────────────────
     *
     * Hasta hoy la hoja solo se abría y se cerraba: no había forma de
     * empujarla. Esto le da lo que le faltaba —seguimiento 1:1, goma en el
     * borde, proyección de momento al soltar y entrega de velocidad al
     * resorte— usando el motor de `movimiento.js`.
     *
     * Solo existe donde existe `$refs.hoja`, que es la variante de pestaña.
     * En escritorio no se declara la referencia y todo esto queda inerte.
     *
     * Esto CAMBIA una decisión anterior: en vertical, un gesto que empieza
     * sobre la hoja ya no desplaza la página para cerrarla, sino que arrastra
     * la hoja. Desplazar la página desde cualquier otro sitio la sigue
     * cerrando, que es lo que hace `cerrarSiSeDesplaza()`.
     */

    /** Cuánto hay que mover el dedo antes de robarle el gesto al toque. */
    umbralDeArrastre: 10,

    arrastrando: false,
    reclamado: false,
    punteroDelArrastre: null,
    dedoAlEmpezar: 0,
    hojaAlEmpezar: 0,
    altoDeLaHoja: 0,
    resorteDeLaHoja: null,
    rastroDeLaHoja: null,
    quitarDelReloj: null,

    /** Escribe la posición de la hoja. Opacidad y desplazamiento van juntos. */
    pintarHoja(y) {
        const hoja = this.$refs.hoja;

        if (! hoja) {
            return;
        }

        const recorrido = Math.max(this.altoDeLaHoja, 1);
        const avance = Math.min(Math.max(y / recorrido, 0), 1);

        hoja.style.translate = `0 ${y}px`;
        // Se desvanece mientras baja, y llega a cero justo al final del
        // recorrido. Además de leerse mejor, evita que la hoja cruce por
        // encima de las pestañas al descender: el módulo inferior es su propio
        // contexto de apilamiento y la hoja va arriba. Al cuadrado y no lineal
        // para que los primeros píxeles del arrastre no la apaguen de golpe.
        hoja.style.opacity = `${Math.max(0, 1 - avance * avance)}`;
    },

    soltarLaPintura() {
        const hoja = this.$refs.hoja;

        if (hoja) {
            hoja.style.translate = '';
            hoja.style.opacity = '';
        }
    },

    tomarLaHoja(evento) {
        const hoja = this.$refs.hoja;

        // Solo el dedo o el ratón arrastran, y solo con la hoja abierta. El
        // botón secundario abre el menú del sistema: no es un arrastre.
        if (! hoja || ! this.abierto || evento.button > 0) {
            return;
        }

        const alto = hoja.offsetHeight;

        // Sin alto no hay nada que arrastrar, y además dividir por él es lo que
        // gradúa el desvanecido y la goma: con cero, el primer píxel apagaría
        // la hoja entera. Pasa mientras la transición de entrada aún no la ha
        // mostrado.
        if (alto === 0) {
            return;
        }

        this.arrastrando = true;
        this.reclamado = false;
        this.punteroDelArrastre = evento.pointerId;
        this.dedoAlEmpezar = evento.clientY;
        this.altoDeLaHoja = alto;

        if (this.resorteDeLaHoja === null) {
            this.resorteDeLaHoja = crearResorte({ respuesta: 0.35, amortiguacion: 0.82, valor: 0 });
            this.rastroDeLaHoja = crearRastro();
        }

        // Se toma el mando DESDE LA POSICIÓN EN PANTALLA, no desde la meta:
        // así se puede agarrar una hoja que ya se estaba yendo y devolverla
        // sin que dé un salto. Es la interrupción, que es de lo que va todo.
        this.hojaAlEmpezar = this.resorteDeLaHoja.valor;
        this.resorteDeLaHoja.fijar(this.hojaAlEmpezar);
        this.rastroDeLaHoja.limpiar();
        this.rastroDeLaHoja.anotar(this.hojaAlEmpezar);

        this.pararElReloj();
    },

    moverLaHoja(evento) {
        if (! this.arrastrando || evento.pointerId !== this.punteroDelArrastre) {
            return;
        }

        const recorrido = evento.clientY - this.dedoAlEmpezar;

        // Histéresis: hasta que el dedo no se mueve de verdad, esto sigue
        // siendo un toque y el enlace de debajo tiene que poder recibirlo.
        if (! this.reclamado) {
            if (Math.abs(recorrido) < this.umbralDeArrastre) {
                return;
            }

            this.reclamado = true;

            // Capturar puede fallar si el puntero ya no está activo --el dedo
            // se levantó entre este evento y el anterior, o el sistema se
            // llevó el gesto--. Sin captura el arrastre sigue funcionando
            // mientras el dedo no salga de la hoja, así que no es motivo para
            // abortar nada.
            try {
                this.$refs.hoja.setPointerCapture(evento.pointerId);
            } catch {
                // Se sigue arrastrando sin captura.
            }
        }

        let y = this.hojaAlEmpezar + recorrido;

        // Por encima de «abierta» no hay nada que enseñar: resiste con goma en
        // vez de plantarse en seco.
        if (y < 0) {
            y = -goma(-y, this.altoDeLaHoja);
        }

        this.resorteDeLaHoja.fijar(y);
        this.rastroDeLaHoja.anotar(y);
        this.pintarHoja(y);
    },

    soltarLaHoja(evento) {
        if (! this.arrastrando || (evento && evento.pointerId !== this.punteroDelArrastre)) {
            return;
        }

        const seArrastro = this.reclamado;

        this.arrastrando = false;
        this.reclamado = false;
        this.punteroDelArrastre = null;
        // `reclamado` ya vale falso cuando llega el `click`, que va después del
        // `pointerup`: hace falta una segunda marca que sobreviva a ese hueco.
        this.acabaDeArrastrar = seArrastro;

        if (! seArrastro) {
            return;
        }

        const velocidad = this.rastroDeLaHoja.velocidad();
        const reposo = this.resorteDeLaHoja.valor + proyectar(velocidad, DECELERACION_DE_LA_HOJA);

        // Decide la PROYECCIÓN y no dónde se soltó el dedo: un golpe corto y
        // rápido cierra aunque la hoja apenas se haya movido, que es justo lo
        // que espera quien lo hace.
        if (reposo > this.altoDeLaHoja / 2) {
            vibrar(8);
            this.cerrarConGesto(velocidad);

            return;
        }

        this.resorteDeLaHoja.meta = 0;
        this.resorteDeLaHoja.empujar(velocidad);
        this.correrElResorte();
    },

    /**
     * El cierre lo termina el resorte y no la transición de Alpine: una
     * transición no acepta la velocidad que traía el dedo, y sin ella se ve la
     * costura entre el arrastre y la animación.
     */
    cerrarConGesto(velocidad) {
        this.resorteDeLaHoja.meta = this.altoDeLaHoja;
        this.resorteDeLaHoja.empujar(velocidad);
        this.correrElResorte(() => {
            this.cerrar();
            this.soltarLaPintura();
            this.resorteDeLaHoja.fijar(0);
        });
    },

    correrElResorte(alAcabar = null) {
        this.pararElReloj();

        this.quitarDelReloj = animar((dt) => {
            this.resorteDeLaHoja.paso(dt);
            this.pintarHoja(this.resorteDeLaHoja.valor);

            if (! this.resorteDeLaHoja.quieto) {
                return;
            }

            this.pararElReloj();

            if (alAcabar) {
                alAcabar();

                return;
            }

            // Asentada en su sitio: se devuelve el estilo a la hoja para que
            // las transiciones de Alpine vuelvan a mandar en el próximo cierre.
            this.soltarLaPintura();
        });
    },

    pararElReloj() {
        if (this.quitarDelReloj) {
            this.quitarDelReloj();
            this.quitarDelReloj = null;
        }
    },

    acabaDeArrastrar: false,

    /**
     * Un arrastre no puede acabar en navegación. Sin esto, soltar el dedo
     * encima de una fila después de empujar la hoja abre ese enlace.
     *
     * Se traga UN solo clic y la marca se apaga en el acto: si se quedara
     * puesta, el siguiente toque legítimo sobre la misma fila tampoco
     * navegaría y la hoja parecería rota.
     */
    tragarElClicDelArrastre(evento) {
        if (! this.acabaDeArrastrar) {
            return;
        }

        this.acabaDeArrastrar = false;
        evento.preventDefault();
        evento.stopPropagation();
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

Alpine.data('bandaEstablecimientos', () => ({
    avanzar(direccion) {
        const pista = this.$refs.pista;

        if (! pista) {
            return;
        }

        const tarjeta = pista.querySelector('.home-editorial-establecimiento');
        const estilo = tarjeta ? window.getComputedStyle(pista) : null;
        const hueco = estilo ? Number.parseFloat(estilo.columnGap || estilo.gap) || 16 : 16;
        const paso = tarjeta
            ? tarjeta.getBoundingClientRect().width + hueco
            : pista.clientWidth * 0.8;

        pista.scrollBy({
            left: direccion * paso,
            behavior: reduceMovimiento() ? 'auto' : 'smooth',
        });
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

const leerCifraColombiana = (molde) => {
    const cuerpo = String(molde).trim().replace(/^\$/, '').replace(/\s*%$/, '').trim();

    return Number(cuerpo.replace(/\./g, '').replace(',', '.'));
};

const formatearCifraAlMolde = (valor, molde) => {
    const original = String(molde).trim();
    const conPesos = original.startsWith('$');
    const conPorcentaje = original.includes('%');
    const espacioAntesDePorcentaje = original.includes(' %');
    const cuerpo = original.replace(/^\$/, '').replace(/\s*%$/, '').trim();
    const decimales = cuerpo.includes(',') ? cuerpo.split(',')[1].length : 0;
    const [entero, decimal = ''] = valor.toFixed(decimales).split('.');
    const enteroConPuntos = entero.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    let texto = (conPesos ? '$' : '') + enteroConPuntos;

    if (decimales > 0) {
        texto += `,${decimal}`;
    }

    if (conPorcentaje) {
        texto += espacioAntesDePorcentaje ? ' %' : '%';
    }

    return texto;
};

const animarCifra = (nodo) => {
    const final = nodo.getAttribute('data-cifra-final') ?? '';
    const destino = leerCifraColombiana(final);

    if (! Number.isFinite(destino) || final === '') {
        nodo.textContent = final;

        return;
    }

    const duracion = 1100;
    const inicio = performance.now();

    const cuadro = (ahora) => {
        const t = Math.min((ahora - inicio) / duracion, 1);
        const ease = 1 - ((1 - t) ** 3);

        if (t === 1) {
            nodo.textContent = final;

            return;
        }

        nodo.textContent = formatearCifraAlMolde(destino * ease, final);
        requestAnimationFrame(cuadro);
    };

    requestAnimationFrame(cuadro);
};

const prepararCifras = () => {
    const nodos = document.querySelectorAll('[data-cifra-final]');

    if (nodos.length === 0) {
        return;
    }

    const aplicarFinal = (nodo) => {
        nodo.textContent = nodo.getAttribute('data-cifra-final') ?? nodo.textContent;
    };

    if (reduceMovimiento()) {
        nodos.forEach(aplicarFinal);

        return;
    }

    const observador = new IntersectionObserver((entradas) => {
        for (const entrada of entradas) {
            if (! entrada.isIntersecting) {
                continue;
            }

            animarCifra(entrada.target);
            observador.unobserve(entrada.target);
        }
    }, { threshold: 0.35, rootMargin: '0px 0px -8% 0px' });

    nodos.forEach((nodo) => observador.observe(nodo));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        prepararRevelado();
        prepararCifras();
    });
} else {
    prepararRevelado();
    prepararCifras();
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
