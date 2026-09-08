/**
 * El estado de la barra lateral del panel (D-L3 y D-L4).
 *
 * Escribe dos atributos en `<body>` y nada más:
 *
 *   data-barra-estado="inicial | scroll"      el material de los módulos
 *   data-barra-borde="ninguno|arriba|abajo|ambos"  el aviso de lista cortada
 *
 * Van en `<body>` y no en el nodo de la barra porque la barra es un componente
 * Livewire: su morph borra los atributos que no vienen del servidor, y el
 * estado desaparecería sin error y sin navegación.
 *
 * Manda el scroll INTERNO de `.fi-sidebar-nav`, no el del documento. En
 * escritorio la barra es `lg:sticky` y no se mueve con la página, así que
 * compactar por el documento sería efecto sin causa; y la lista desborda casi
 * siempre (medido el 7 sep: 1.312 px de contenido en 1.019 de hueco).
 *
 * El movimiento reducido se consulta en vivo con `matchMedia` y se escucha su
 * `change`. NO se copia `menosMovimiento()` de la barra pública: esa lee la
 * clase `sin-desplazamiento`, que la pone el `<head>` del layout público y en
 * el panel no existe, así que la guarda quedaría siempre en falso y sería un
 * verde silencioso.
 */
const HOLGURA = 4;

const quieto = window.matchMedia('(prefers-reduced-motion: reduce)');

const lista = () => document.querySelector('.fi-sidebar-nav');

/** Qué cantos ocultan lista, para el aviso de D-L15. */
const bordesDe = (nav) => {
    const desbordado = nav.scrollHeight - nav.clientHeight > HOLGURA;

    if (! desbordado) {
        return 'ninguno';
    }

    const arriba = nav.scrollTop > HOLGURA;
    const abajo = nav.scrollTop + nav.clientHeight < nav.scrollHeight - HOLGURA;

    if (arriba && abajo) {
        return 'ambos';
    }

    return arriba ? 'arriba' : (abajo ? 'abajo' : 'ninguno');
};

const sincronizar = () => {
    const nav = lista();

    if (! nav) {
        return;
    }

    const desplazada = nav.scrollTop > HOLGURA;

    document.body.dataset.barraEstado = quieto.matches || ! desplazada ? 'inicial' : 'scroll';
    document.body.dataset.barraBorde = bordesDe(nav);
};

/**
 * El grupo que contiene la página actual no puede quedar plegado (D-L14): quien
 * entra a Ajustes del sitio no vería ningún ítem marcado en toda la barra,
 * porque «Configuración» nace plegado. Se despliega abriendo el disparador del
 * grupo, que es lo que Filament escucha, en vez de tocarle el almacenamiento.
 */
const abrirElGrupoDeLaPagina = () => {
    const activo = document.querySelector('.fi-sidebar-group:has(.fi-sidebar-item.fi-active)');
    const items = activo?.querySelector('.fi-sidebar-group-items');

    if (! activo || ! items || items.offsetParent !== null) {
        return;
    }

    activo.querySelector('.fi-sidebar-group-btn, .fi-sidebar-group-collapse-btn')?.click();
};

/**
 * Filament guarda y restaura el `scrollTop` del nav dentro de un
 * `requestAnimationFrame` en `livewire:navigated`
 * (`vendor/filament/filament/resources/js/stores/sidebar.js`). Todo lo nuestro
 * se encola DESPUÉS de ese fotograma, o mediríamos la lista antes de que
 * vuelva a su sitio.
 */
const trasLaRestauracion = (hacer) => requestAnimationFrame(() => requestAnimationFrame(hacer));

const escuchar = () => {
    const nav = lista();

    if (! nav || nav.dataset.barraEscuchada === 'si') {
        return;
    }

    nav.dataset.barraEscuchada = 'si';
    nav.addEventListener('scroll', sincronizar, { passive: true });
};

const arrancar = () => {
    escuchar();
    abrirElGrupoDeLaPagina();
    sincronizar();
};

document.addEventListener('livewire:navigated', () => trasLaRestauracion(arrancar));
window.addEventListener('resize', sincronizar, { passive: true });
quieto.addEventListener('change', sincronizar);

trasLaRestauracion(arrancar);
