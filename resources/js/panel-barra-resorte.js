/**
 * El resorte de los iconos del riel (D-L30).
 *
 * Sua lo pidió el 8 sep: «que al scrollear los botones tengan su animación
 * tipo resorte, que sean responsivas al movimiento del usuario». Lo que se
 * mueve responde al GESTO y no a un reloj: cada icono se retrasa respecto al
 * dedo y llega con muelle, y tanto más cuanto más rápido se desplaza la lista.
 * Eso es manipulación directa; una transición CSS no puede hacerlo, porque no
 * sabe a qué velocidad va la mano.
 *
 * Cuatro reglas que vienen de la decisión y no se tocan sin cambiarla:
 *
 * 1. Se mueve `translate` y nada más. Ni `top`, ni `margin`, ni `height`:
 *    cualquiera de esos mide la página en cada fotograma.
 * 2. El bucle se para solo cuando todo está en su sitio, y no arranca bajo
 *    `prefers-reduced-motion`. Mismo contrato que el campo de puntos.
 * 3. Nada que se esté moviendo más de `QUIETO_BASTANTE` recibe el dedo: un
 *    destino que huye del pulgar es peor que un destino quieto, así que
 *    mientras se mueve se le quita el puntero.
 * 4. Solo por debajo de 64 rem. En escritorio la lista es otra cosa y el
 *    riel no existe.
 */
/*
 * ARRASTRE calibrado el 8 sep midiendo con gestos reales, no a ojo. Con 0,55 un
 * desplazamiento de 24 px por fotograma —un pase normal del pulgar— ya saturaba
 * el tope y todos los iconos se quedaban en 14: la respuesta al gesto se
 * perdía justo donde importa. Con 0,35 el rango útil cubre de 5 a 40 px por
 * fotograma, que es donde vive un dedo.
 */
const ARRASTRE = 0.35;
const TENSION = 0.14;
const AMORTIGUACION = 0.72;
const MAXIMO = 14;
const QUIETO_BASTANTE = 0.4;
const ESCALONADO = 0.035;

const quieto = window.matchMedia('(prefers-reduced-motion: reduce)');
const telefono = window.matchMedia('(max-width: 63.999rem)');

let nav = null;
let piezas = [];
let previo = 0;
let corriendo = false;

/*
 * Lo que se mueve son los MÓDULOS, no lo que hay dentro. Se movían las filas y
 * Sua lo vio enseguida: el indicador rojo del apartado activo se quedaba
 * quieto mientras su fila se desplazaba, porque el indicador lo pinta el
 * módulo y la fila iba por su cuenta. Un módulo es un grupo o, para los
 * destinos sin grupo como «Tablero», el ítem suelto que Filament pinta
 * directamente en la lista.
 */
const encontrar = () => [...document.querySelectorAll('.fi-sidebar-group, .fi-sidebar-nav > .fi-sidebar-item')];

const soltarPunteros = (bloqueado) => {
    for (const pieza of piezas) {
        if (pieza.bloqueado !== bloqueado) {
            pieza.bloqueado = bloqueado;
            pieza.nodo.style.pointerEvents = bloqueado ? 'none' : '';
        }
    }
};

/** Escribe el desfase de cada pieza. Solo `translate`: nada que mida la página. */
const pintar = () => {
    for (const pieza of piezas) {
        pieza.nodo.style.translate = pieza.desfase === 0 ? '' : `0 ${pieza.desfase.toFixed(2)}px`;
    }
};

const bucle = () => {
    let vivo = false;

    for (const pieza of piezas) {
        // Muelle con masa: la velocidad tira hacia cero y se amortigua. No es
        // una curva, es una integración, y por eso responde al gesto.
        pieza.velocidad += -pieza.desfase * TENSION;
        pieza.velocidad *= AMORTIGUACION;
        pieza.desfase += pieza.velocidad;

        if (Math.abs(pieza.desfase) > QUIETO_BASTANTE || Math.abs(pieza.velocidad) > QUIETO_BASTANTE) {
            vivo = true;
        } else {
            pieza.desfase = 0;
            pieza.velocidad = 0;
        }
    }

    pintar();
    soltarPunteros(vivo);

    if (vivo) {
        requestAnimationFrame(bucle);
    } else {
        corriendo = false;
    }
};

const despertar = () => {
    if (corriendo) {
        return;
    }

    corriendo = true;
    requestAnimationFrame(bucle);
};

const alDesplazar = () => {
    if (quieto.matches || ! telefono.matches || ! nav) {
        return;
    }

    const ahora = nav.scrollTop;
    const gesto = ahora - previo;

    previo = ahora;

    if (gesto === 0) {
        return;
    }

    piezas.forEach((pieza, i) => {
        // Escalonado por posición: los de más abajo llegan un poco después, que
        // es lo que se lee como inercia de lista y no como cinco bloques.
        const empuje = Math.max(-MAXIMO, Math.min(MAXIMO, -gesto * ARRASTRE * (1 + i * ESCALONADO)));

        pieza.desfase += empuje;
        pieza.desfase = Math.max(-MAXIMO, Math.min(MAXIMO, pieza.desfase));
    });

    /*
     * Se pinta YA, en el mismo gesto, y no en el fotograma siguiente: la
     * respuesta tiene que salir con la mano, no detrás de ella. El bucle se
     * encarga del regreso.
     */
    pintar();
    soltarPunteros(true);
    despertar();
};

const detener = () => {
    for (const pieza of piezas) {
        pieza.desfase = 0;
        pieza.velocidad = 0;
    }

    pintar();
    soltarPunteros(false);
};

const montar = () => {
    nav = document.querySelector('.fi-sidebar-nav');

    if (! nav) {
        return;
    }

    piezas = encontrar().map((nodo) => ({ nodo, desfase: 0, velocidad: 0, bloqueado: false }));
    previo = nav.scrollTop;

    if (nav.dataset.resorteEscuchado === 'si') {
        return;
    }

    nav.dataset.resorteEscuchado = 'si';
    nav.addEventListener('scroll', alDesplazar, { passive: true });
};

quieto.addEventListener('change', detener);
telefono.addEventListener('change', detener);

document.addEventListener('livewire:navigated', montar);
montar();
