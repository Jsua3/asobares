/**
 * El campo de puntos del fondo de la barra lateral (D-L24).
 *
 * Sua lo pidió el 7 sep: fondo de puntos que se apartan del cursor. Se dibuja
 * en un `<canvas>` y no con mil nodos, porque con 18 px de paso una columna de
 * 244 px de ancho por 1.000 de alto son más de setecientos puntos.
 *
 * Tres reglas que vienen de la decisión y no se tocan sin cambiarla:
 *
 * 1. Los colores salen de tokens, así que el campo invierte con el tema: la
 *    barra dejó de tener paleta privada y no la recupera.
 * 2. Bajo `prefers-reduced-motion` los puntos se quedan quietos. Es animación
 *    ligada al gesto.
 * 3. El lienzo no recibe puntero y no lleva texto: es decoración, y por eso no
 *    entra en ninguna cuenta de contraste.
 *
 * El bucle solo corre mientras hay algo que mover: con el puntero dentro, o
 * mientras los puntos vuelven a su sitio. Sin eso sería un `requestAnimationFrame`
 * eterno en una pantalla que casi siempre está quieta.
 */
const PASO = 18;
const RADIO_PUNTO = 1.1;
const ALCANCE = 90;
const EMPUJE = 14;
const REGRESO = 0.12;
const QUIETO_BASTANTE = 0.05;

const quieto = window.matchMedia('(prefers-reduced-motion: reduce)');

let lienzo = null;
let pincel = null;
let puntos = [];
let raton = { x: -9999, y: -9999 };
let corriendo = false;

const color = () => getComputedStyle(document.documentElement)
    .getPropertyValue('--asb-admin-barra-punto')
    .trim() || 'rgb(11 9 10 / 0.14)';

const sembrar = () => {
    const { width, height } = lienzo.getBoundingClientRect();
    const densidad = window.devicePixelRatio || 1;

    lienzo.width = Math.round(width * densidad);
    lienzo.height = Math.round(height * densidad);
    pincel.setTransform(densidad, 0, 0, densidad, 0, 0);

    puntos = [];

    for (let y = PASO / 2; y < height; y += PASO) {
        for (let x = PASO / 2; x < width; x += PASO) {
            puntos.push({ x, y, dx: 0, dy: 0 });
        }
    }
};

const pintar = () => {
    const { width, height } = lienzo.getBoundingClientRect();

    pincel.clearRect(0, 0, width, height);
    pincel.fillStyle = color();

    for (const punto of puntos) {
        pincel.beginPath();
        pincel.arc(punto.x + punto.dx, punto.y + punto.dy, RADIO_PUNTO, 0, Math.PI * 2);
        pincel.fill();
    }
};

/** Devuelve true mientras quede algo en movimiento. */
const mover = () => {
    let vivo = false;

    for (const punto of puntos) {
        const haciaX = punto.x - raton.x;
        const haciaY = punto.y - raton.y;
        const distancia = Math.hypot(haciaX, haciaY);

        let metaX = 0;
        let metaY = 0;

        if (distancia < ALCANCE && distancia > 0.001) {
            const fuerza = ((ALCANCE - distancia) / ALCANCE) ** 2 * EMPUJE;

            metaX = (haciaX / distancia) * fuerza;
            metaY = (haciaY / distancia) * fuerza;
        }

        punto.dx += (metaX - punto.dx) * REGRESO;
        punto.dy += (metaY - punto.dy) * REGRESO;

        if (Math.abs(punto.dx) > QUIETO_BASTANTE || Math.abs(punto.dy) > QUIETO_BASTANTE) {
            vivo = true;
        }
    }

    return vivo;
};

const bucle = () => {
    const vivo = mover();

    pintar();

    if (vivo) {
        requestAnimationFrame(bucle);
    } else {
        corriendo = false;
    }
};

const despertar = () => {
    if (corriendo || quieto.matches) {
        return;
    }

    corriendo = true;
    requestAnimationFrame(bucle);
};

const seguir = (evento) => {
    const caja = lienzo.getBoundingClientRect();

    raton = { x: evento.clientX - caja.left, y: evento.clientY - caja.top };
    despertar();
};

const soltar = () => {
    raton = { x: -9999, y: -9999 };
    despertar();
};

const montar = () => {
    lienzo = document.querySelector('.asb-barra-puntos');

    if (! lienzo || lienzo.dataset.sembrado === 'si') {
        return;
    }

    pincel = lienzo.getContext('2d');
    lienzo.dataset.sembrado = 'si';

    sembrar();
    pintar();

    const barra = lienzo.closest('.fi-sidebar');

    // `pointermove` y no `mousemove`: en híbridos el toque sintetiza mouse y
    // dejaría el campo empujado donde el dedo levantó.
    barra?.addEventListener('pointermove', seguir);
    barra?.addEventListener('pointerleave', soltar);

    new ResizeObserver(() => {
        sembrar();
        pintar();
    }).observe(lienzo);

    window.addEventListener('theme-changed', pintar);
    quieto.addEventListener('change', () => {
        raton = { x: -9999, y: -9999 };
        puntos.forEach((punto) => {
            punto.dx = 0;
            punto.dy = 0;
        });
        pintar();
    });
};

document.addEventListener('livewire:navigated', montar);
montar();
