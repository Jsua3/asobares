/*
 * Movimiento gobernado por gesto.
 *
 * Todo el movimiento del sitio hasta hoy era una TRANSICIÓN DE ESTADO: arranca
 * en A, llega a B en un tiempo fijo y no se puede agarrar por el camino. Sirve
 * para un fundido de color y no sirve para nada que siga a un dedo, porque una
 * transición de CSS no acepta velocidad de entrada ni se puede redirigir a
 * mitad de vuelo sin dar un salto.
 *
 * `tokens.css` ya tenía la CURVA de un resorte real --dos `linear()`
 * muestreados de un oscilador amortiguado-- pero usada como `easing` de una
 * transición de duración fija: la forma del resorte sin el resorte. Esto es el
 * resorte.
 *
 * Nada de esto necesita una dependencia nueva, y eso es deliberado: el alcance
 * del proyecto está congelado y las dependencias no se tocan sin aprobación.
 * Un oscilador amortiguado son ocho líneas de aritmética.
 *
 * Referencia: «Designing Fluid Interfaces», WWDC 2018. La parametrización
 * (respuesta + amortiguación, en vez de masa/rigidez/rozamiento) y la función
 * de proyección son las que Apple publicó allí.
 */

/**
 * Movimiento reducido, leído EN VIVO y no una sola vez.
 *
 * El sitio público marca `sin-desplazamiento` en el <html> desde el IIFE de la
 * cabecera, pero esa clase se pone al cargar y no vuelve a mirarse. Un resorte
 * corre a 60 fotogramas por segundo, así que tiene que enterarse en el mismo
 * instante en que alguien cambia la preferencia del sistema, sin recargar. Es
 * además lo que ya hace `reduceMovimiento()` en `app.js`.
 */
const consultaMovimiento = window.matchMedia('(prefers-reduced-motion: reduce)');

export const menosMovimiento = () => consultaMovimiento.matches;

/**
 * Cuánto tiempo se le permite a un fotograma.
 *
 * Con la pestaña al fondo el navegador deja de llamar a `requestAnimationFrame`
 * y al volver el salto puede ser de segundos. Sin acotar, ese `dt` mete tanta
 * energía en la integración que el resorte sale disparado --literalmente:
 * `Infinity` y luego `NaN`-- y el elemento desaparece de la pantalla.
 */
const DT_MAXIMO = 0.05;

/**
 * Paso de integración. El `dt` acotado se parte en trozos de 4 ms como mucho.
 *
 * Acotar `dt` no basta por sí solo: con un resorte rápido (respuesta 0,15) y un
 * fotograma de 50 ms, un único paso de Euler diverge igual. Partirlo mantiene
 * la integración estable en el peor caso sin coste apreciable — son 13 vueltas
 * de un bucle de tres multiplicaciones.
 */
const PASO_MAXIMO = 0.004;

/**
 * Un resorte de verdad: interrumpible, con velocidad y sin duración fija.
 *
 * Lo que lo separa de una transición: se le puede cambiar `meta` a mitad de
 * vuelo y el movimiento sigue siendo continuo, porque la posición y la
 * velocidad actuales no se pierden. Eso es lo que permite agarrar una hoja que
 * se está cerrando y devolverla sin que dé un salto — el principio que la
 * charla de Apple llama el más importante de todos.
 *
 * Los dos parámetros son los de Apple y no los del libro de física:
 *
 * - `respuesta`: en segundos, lo que tarda en llegar. NO es una duración: un
 *   resorte no tiene final, tiene una cola que se asienta. Más bajo, más seco.
 * - `amortiguacion`: 1 es crítico y no sobreimpulsa; por debajo rebota. Apple
 *   usa 1 para casi todo y ~0,8 solo cuando el gesto traía momento.
 *
 * @param {{respuesta?: number, amortiguacion?: number, valor?: number}} opciones
 */
export function crearResorte({ respuesta = 0.4, amortiguacion = 1, valor = 0 } = {}) {
    let posicion = valor;
    let velocidad = 0;
    let meta = valor;

    return {
        respuesta,
        amortiguacion,

        get valor() {
            return posicion;
        },

        get velocidad() {
            return velocidad;
        },

        get meta() {
            return meta;
        },

        /** Cambiarla a mitad de vuelo es legal y es justo para lo que existe. */
        set meta(destino) {
            meta = destino;
        },

        /** Entrega de velocidad: lo que hace que soltar un gesto no tenga costura. */
        empujar(nueva) {
            velocidad = nueva;
        },

        /** Teletransporte sin movimiento: para arrancar y para tomar el mando en un arrastre. */
        fijar(destino) {
            meta = destino;
            posicion = destino;
            velocidad = 0;
        },

        /**
         * Ya no se mueve lo bastante como para que se note.
         *
         * Los dos umbrales hacen falta: solo por posición, un resorte que pasa
         * por delante de su meta a toda velocidad se daría por quieto en el
         * instante exacto del cruce.
         */
        get quieto() {
            return Math.abs(posicion - meta) < 0.0004 && Math.abs(velocidad) < 0.004;
        },

        /**
         * Un fotograma. Devuelve la posición nueva.
         *
         * Con movimiento reducido no se interpola: se salta al destino. No se
         * anula la llamada --quien la hace sigue necesitando el valor y sigue
         * necesitando que `quieto` acabe siendo cierto-- solo el recorrido.
         */
        paso(dt) {
            if (menosMovimiento()) {
                posicion = meta;
                velocidad = 0;

                return posicion;
            }

            const omega = (2 * Math.PI) / this.respuesta;
            const rigidez = omega * omega;
            const rozamiento = 2 * this.amortiguacion * omega;

            const trozos = Math.max(1, Math.ceil(dt / PASO_MAXIMO));
            const h = dt / trozos;

            for (let i = 0; i < trozos; i++) {
                velocidad += (-rigidez * (posicion - meta) - rozamiento * velocidad) * h;
                posicion += velocidad * h;
            }

            return posicion;
        },
    };
}

/*
 * Un solo reloj para todo el sitio.
 *
 * Un `requestAnimationFrame` por elemento multiplica las llamadas y desordena
 * el momento en que cada uno lee y escribe el DOM. Con uno solo, todos los
 * resortes vivos avanzan con el MISMO `dt` y en el mismo fotograma.
 *
 * Y el bucle se para solo cuando no queda nadie en vuelo: dejar un rAF corriendo
 * en vacío es batería regalada, que en un teléfono --por donde va a entrar casi
 * todo el mundo a este sitio-- no es un detalle.
 */
const enVuelo = new Set();
let corriendo = false;
let ultimoSello = 0;

function bucle(sello) {
    const dt = Math.min((sello - ultimoSello) / 1000, DT_MAXIMO);
    ultimoSello = sello;

    // Sobre una copia: un paso puede darse de baja a sí mismo al asentarse, y
    // modificar el Set mientras se recorre se salta al siguiente.
    for (const paso of [...enVuelo]) {
        paso(dt);
    }

    if (enVuelo.size === 0) {
        corriendo = false;

        return;
    }

    requestAnimationFrame(bucle);
}

/**
 * Mete un paso en el reloj y devuelve cómo sacarlo.
 *
 * @param {(dt: number) => void} paso
 * @returns {() => void}
 */
export function animar(paso) {
    enVuelo.add(paso);

    if (! corriendo) {
        corriendo = true;
        ultimoSello = performance.now();
        requestAnimationFrame(bucle);
    }

    return () => enVuelo.delete(paso);
}

/**
 * Dónde acabaría el movimiento si se le dejara decelerar solo.
 *
 * Es la función exacta de la charla de Apple, y NO la del libro de física
 * (`v² / 2a`): la deceleración de un scroll es exponencial, no uniforme, y con
 * la fórmula del libro el gesto se queda corto y se siente pesado.
 *
 * Sirve para decidir a qué punto de anclaje va una hoja al soltarla. La
 * decisión la toma la PROYECCIÓN y no dónde estaba el dedo: así un golpe corto
 * y rápido cierra, que es lo que quien lo hace espera, aunque el elemento
 * apenas se haya movido.
 *
 * @param {number} velocidad en px/s
 * @param {number} deceleracion 0,998 es el tacto del scroll normal; 0,99 va más seco
 */
export function proyectar(velocidad, deceleracion = 0.998) {
    return (velocidad / 1000) * deceleracion / (1 - deceleracion);
}

/**
 * Resistencia progresiva más allá de un borde.
 *
 * Un tope duro se lee como «esto se ha congelado»; una resistencia que crece
 * se lee como «responde, pero aquí no hay más». Cuanto más te pasas, menos te
 * sigue, y nunca llega a soltarse del todo.
 *
 * @param {number} exceso cuánto se ha pasado del borde, en px
 * @param {number} dimension el tamaño del elemento, que fija cuánto cuesta estirarlo
 */
export function goma(exceso, dimension, constante = 0.55) {
    return (exceso * dimension * constante) / (dimension + constante * Math.abs(exceso));
}

/**
 * Historial corto de posiciones, para sacar la velocidad REAL al soltar.
 *
 * La diferencia entre los dos últimos eventos no vale: `pointermove` llega a
 * ráfagas irregulares y un par de muestras separadas por 2 ms da velocidades
 * absurdas. Con seis puntos la medida se estabiliza sin llegar a retrasarse.
 */
export function crearRastro(maximo = 6) {
    const puntos = [];

    return {
        anotar(valor) {
            puntos.push({ valor, sello: performance.now() });

            if (puntos.length > maximo) {
                puntos.shift();
            }
        },

        limpiar() {
            puntos.length = 0;
        },

        /** px/s entre el punto más viejo del historial y el más nuevo. */
        velocidad() {
            if (puntos.length < 2) {
                return 0;
            }

            const viejo = puntos[0];
            const nuevo = puntos[puntos.length - 1];
            const dt = (nuevo.sello - viejo.sello) / 1000;

            // Un intervalo por debajo del milisegundo no es una medida: es ruido
            // dividido por casi cero.
            return dt > 0.001 ? (nuevo.valor - viejo.valor) / dt : 0;
        },
    };
}

/**
 * Háptica, y solo en momentos con significado.
 *
 * La regla de «Designing Audio-Haptic Experiences» que más se incumple es la
 * tercera, la utilidad: vibrar en todo enseña a ignorar la vibración. Aquí se
 * reserva para el enganche de un gesto y el commit de una acción.
 *
 * Va envuelto porque `navigator.vibrate` no existe en iOS y porque en un
 * documento sin interacción previa algunos navegadores lanzan en vez de
 * devolver `false`. Que no haya háptica nunca puede romper nada visual.
 */
export function vibrar(ms) {
    if (menosMovimiento()) {
        return;
    }

    try {
        navigator.vibrate?.(ms);
    } catch {
        // Sin háptica se sigue viendo todo: no hay nada que reportar.
    }
}
