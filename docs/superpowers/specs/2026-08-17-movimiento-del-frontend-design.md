# Diseño: Movimiento del frontend — sistema de tokens, portadores con nombre y doce piezas

**Fecha:** 2026-08-17
**Estado:** aprobado en conversación de diseño; pendiente plan de implementación.
**Objetivo declarado:** demo para la directiva del 22 de septiembre de 2026.
**Origen:** mapeo automático de las ocho zonas del frontend (8 agentes lectores + síntesis, 238 lecturas de archivo) contrastado contra las reglas de craft de Emil Kowalski instaladas en `~/.claude/skills/`.

## Contexto y problema

El sitio público tiene 59 vistas Blade sobre Laravel 13 + Livewire 3 + Alpine 3 + Tailwind 4, y el panel `/admin` sobre Filament 4. El color ya está resuelto como sistema: `resources/css/tokens.css` es fuente única para las dos superficies y una prueba de guardia prohíbe hexadecimales cableados en las vistas. El movimiento no tuvo esa suerte.

El mapeo encontró **86 piezas de movimiento existentes, 79 violaciones de craft y 48 oportunidades**. Ordenadas por lo que de verdad importa:

1. **No hay sistema de movimiento, hay 51 utilidades sueltas.** En todo el CSS propio: cero `cubic-bezier`, cero `@keyframes`, cero tokens `--ease-*`, cero tokens de duración. `tokens.css` dedica 209 líneas a color y tipografía y ni una a movimiento — exactamente el hueco que el diseño del panel administrativo ya había anotado como problema abierto («Animaciones y transparencia hoy no tienen dónde vivir»).

2. **Faltan las tres protecciones estructurales, no una.** Hay **160 `hover:` en 39 archivos y ninguno gateado** con `@media (hover: hover) and (pointer: fine)`; hay **cero `active:` en todo el repositorio**, así que ningún botón acusa la pulsación —incluidos los dos que mueven dinero—; y hay **un solo bloque `prefers-reduced-motion`**, en `filament/admin/theme.css:79-86`, que cubre exclusivamente `.vidrio-hover` del panel. Su gemela pública `.tarjeta-hover`, con 13 instancias en 11 vistas, corre sin guarda, igual que el `scroll-behavior: smooth` incondicional de `app.css:11`.

3. **De las dos curvas escritas a mano en todo el proyecto, una es la prohibida.** `menu-usuario.blade.php:83` usa `ease-in` en la salida del único desplegable del sitio, que aparece en las ~30 páginas a través del layout. `ease-in` arranca lento justo en el momento en que la persona más está mirando.

4. **El mismo control se comporta de dos maneras.** De 34 clones del botón primario repartidos en 23 archivos, 20 tienen transición y 14 no. Hoy `mi-cuenta/sesion-equivocada.blade.php:38` funde y su gemelo literal `mi-cuenta/vacantes/index.blade.php:15` salta.

5. **Un solo gesto dispara dos relojes.** En el directorio de bares, `tarjeta-asociado.blade.php:10` hace zoom de la portada en 500 ms dentro de una tarjeta cuyo borde y elevación van a 200 ms: la tarjeta termina de moverse en dos tiempos. Además 500 ms es más del triple del techo para un hover.

6. **El menú móvil reflowea el documento en cada fotograma.** `navbar.blade.php:84` usa `x-collapse` —interpolación de `height` a 250 ms— sobre un panel **en flujo dentro de un `<header sticky>`**, así que cada fotograma empuja `<main>`. Es el movimiento más frecuente del cromo y el peor implementado. Y no tiene cierre con Escape, ni con clic fuera, ni reseteo al pasar a escritorio.

7. **La pieza más cara del proyecto trabaja en contra de su propia pantalla.** El conteo animado de `components/panel/kpi.blade.php:38-62` gasta 600 ms de `requestAnimationFrame` reescribiendo texto por fotograma para retrasar la lectura de las cuatro cifras que son el motivo entero del observatorio. Tiene un bug visible —`mostrado` se inicializa con el valor real, `x-text` lo pinta, y `x-intersect` arranca después en cero, así que la cifra correcta aparece, salta a cero y vuelve a subir— y una inconsistencia de raíz: la línea 46 se rinde si el valor no es parseable, y el observatorio pinta sus cifras desde `ajuste('cifra_*')` como texto libre, de modo que en la misma fila unas tarjetas cuentan y otras no.

8. **`resources/views/welcome.blade.php` es código muerto que además ensucia el diagnóstico.** Cero referencias en `app/`, `routes/`, `tests/` y `resources/`; contiene un volcado compilado de Tailwind v4.0.7 con sus propias `--tw-*` ajenas al sistema de tokens; y es el **único** `transition: all` del repositorio.

9. **La única guardia de movimiento que existe no guarda nada.** `tests/Feature/Panel/ComponentesDelPanelTest.php:55` solo afirma que la cadena `prefers-reduced-motion: reduce` aparezca en `theme.css`: pasaría igual aunque se vaciara el bloque entero.

Y un reencuadre sobre lo que se pidió. El encargo fue «añadir animaciones varias en toda la interfaz». **El diagnóstico apunta al lado contrario y el diseño lo asume explícitamente:** buena parte de la excelencia aquí es negativa —borrar el contador del KPI, rechazar el scroll-reveal en las nueve secciones de la portada y en todas las rejillas, no animar el hero porque es el LCP, no animar navbar ni pie ni paginación porque su uso es constante—. El movimiento que sí entra son doce piezas quirúrgicas sobre una cimentación que hoy no existe. No es menos trabajo: es que el trabajo está en el sistema y en el criterio, no en el volumen.

## Decisiones tomadas

| Tema | Decisión | Quién |
|---|---|---|
| Alcance | **Fases 0–3 completas**, incluidos los dos cambios de comportamiento (menú móvil como superposición y transiciones de vista entre documentos). | Dueño |
| Arquitectura | **Tokens + portadores con nombre + prueba de guardia.** Las vistas dejan de escribir duraciones y curvas crudas. Se extrae `x-publico.boton`. | Dueño |
| Contador del KPI | **Se borra.** No se sustituye por una entrada de tarjeta. | Dueño |
| Librerías de animación | **Ninguna.** Todo sale con `transition` de CSS y el Alpine ya instalado. `package.json` no se toca. | Agente |
| Panel Filament | **Entra, con límite estricto**: solo `filament/admin/theme.css`, `components/panel/*` y el borrado del contador. Nada del movimiento nativo de Filament. | Agente |
| `welcome.blade.php` | **Se borra.** | Agente |
| `@keyframes` propios | **Ninguno.** Todo es `transition` o `@starting-style`, que es lo que las mordazas del cambio de tema ya cubren. | Agente |

## Principios

1. **El movimiento vive en un solo archivo, para el sitio y para el panel.** Cero duraciones y cero curvas cableadas en vistas, con prueba de guardia que lo verifique. Es el mismo principio que ya ordena el color.
2. **Bajo `prefers-reduced-motion` se anula el desplazamiento, no el reloj.** La regla dice quitar el movimiento y conservar las transiciones de opacidad y color, que ayudan a comprender. Poner las duraciones a cero mata también los fundidos, que es justo lo que debe sobrevivir.
3. **La frecuencia decide si algo se anima.** Uso constante (navegación, teclado, hover de listas densas) no se anima nunca. Ocasional (desplegables, alertas, envíos) lleva animación estándar.
4. **Toda animación tiene uno de cinco propósitos**: consistencia espacial, indicar estado, explicar, retroalimentar o evitar un cambio brusco. Lo que no cumple ninguno se rechaza o se borra.
5. **Solo se anima `transform` y `opacity`.** Nunca `height`, `width`, márgenes ni `all`.
6. **La salida es más rápida que la entrada**, y ninguna duración de interfaz pasa de 300 ms.
7. **Un rework de movimiento que no se puede ver no se puede aprobar.** La verificación incluye vídeo de un Chromium real, no solo aserciones.

---

## §1 · Capa de tokens de movimiento

### 1.1 Curvas — dentro del `@theme` existente de `tokens.css`

Se insertan tras `--font-display` (línea 22):

```css
--ease-out:    cubic-bezier(0.23, 1, 0.32, 1);   /* entradas y salidas */
--ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);  /* movimiento en pantalla */
--ease-cajon:  cubic-bezier(0.32, 0.72, 0, 1);   /* menú móvil */
--ease-color:  ease;                             /* hover y color */
```

Van en `@theme` y no en `:root` porque `--ease-*` es un namespace real de Tailwind 4: genera utilidades usables en Blade y, al redefinir `--ease-out`, **pisa la utilidad nativa** (`cubic-bezier(0,0,0.2,1)`), lo que corrige retroactivamente cada `ease-out` ya escrito sin tocar una vista.

Riesgo asumido: la redefinición alcanza a todo el proyecto. El único consumidor vivo de `ease-out` es `menu-usuario.blade.php:80`, que es precisamente uno de los que el diseño corrige. Riesgo real: bajo.

### 1.2 Duraciones y desplazamientos — en el `:root` de `tokens.css`

Se insertan tras `--asb-sombra-tarjeta` (línea 128), en español como manda el proyecto:

```css
--duracion-instante: 100ms;  /* :active */
--duracion-boton:    140ms;  /* hover, color */
--duracion-salida:   160ms;  /* leave de desplegables */
--duracion-entrada:  200ms;  /* enter de desplegables, alertas */
--duracion-panel:    240ms;  /* menú móvil */

--asb-levante:               -2px;  /* hoy duplicado literal en app.css:63 y theme.css:75 */
--asb-desplazamiento-panel:  -4%;
--asb-desplazamiento-alerta: -25%;
```

No hay `--duracion-modal`: el plan no introduce ningún modal, y un token sin consumidor es una invitación a inventarle uso.

No existe namespace de Tailwind para duración, así que se alcanzan desde Blade con `duration-[--duracion-boton]`.

### 1.3 El interruptor de accesibilidad

Al final de `tokens.css`, después de `.dark`, para ganar por orden de aparición:

```css
@media (prefers-reduced-motion: reduce) {
    :root {
        --asb-levante: 0px;
        --asb-desplazamiento-panel: 0%;
        --asb-desplazamiento-alerta: 0%;
    }
}
```

Con esto la tarjeta del directorio **sigue fundiendo su borde a rojo en 140 ms pero ya no se eleva**, y el desplegable aparece con opacidad pero sin deslizarse. Es la aplicación literal del principio 2.

### 1.4 Red de seguridad para lo que no controlamos

Al final de `resources/css/app.css`, deliberadamente estrecha:

```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 1ms !important;
        animation-iteration-count: 1 !important;
    }
    html { scroll-behavior: auto !important; }
}
```

Solo `animation` y `scroll-behavior`. **No toca `transition`**, para no deshacer la precisión de §1.3. Cubre los `@keyframes` de Filament y cualquier cosa futura.

Leaflet no se trata con CSS sino en su propia API: `mapa.blade.php:28` pasa a leer `matchMedia('(prefers-reduced-motion: reduce)')` y desactivar `fadeAnimation`, `zoomAnimation` y `markerZoomAnimation`. Es donde el control existe de verdad.

### 1.5 El scroll suave, condicionado

`app.css:11` es hoy incondicional. Pasa a:

```css
html { scroll-behavior: auto; }
@media (prefers-reduced-motion: no-preference) {
    html { scroll-behavior: smooth; }
}
```

## §2 · Portadores con nombre

El movimiento deja de escribirse en las vistas y vive en cuatro portadores.

### 2.1 `.pulsable` — nuevo, en `@layer components` de `app.css`

```css
.pulsable { transition: transform var(--duracion-instante) var(--ease-out); }
.pulsable:active { transform: scale(0.97); transition-duration: 0ms; }
```

Bajar instantáneo, subir en 100 ms. **Sin puerta de hover fino a propósito**: en táctil el `:active` es el único acuse que existe.

### 2.2 `.tarjeta-hover` — reescritura de `app.css:55-64`

```css
.tarjeta-hover {
    transition:
        border-color var(--duracion-boton) var(--ease-color),
        transform var(--duracion-boton) var(--ease-out);
}
@media (hover: hover) and (pointer: fine) {
    .tarjeta-hover:hover {
        border-color: rgb(238 65 55 / 0.45);
        transform: translateY(var(--asb-levante));
    }
}
```

Su gemela `.vidrio-hover` de `filament/admin/theme.css:67-76` queda idéntica en estructura, y **se borra el parche de `theme.css:79-86`** (comentario incluido), que hoy es la única guarda de reduced-motion del proyecto y ya divergía de esta. El borrado es parte de la propuesta, no un efecto colateral.

### 2.3 `x-publico.boton` — nuevo

Variantes `primaria`, `contorno` y `fantasma`. Lleva `.pulsable` de fábrica. Absorbe los 34 clones repartidos en 23 archivos.

### 2.4 `x-publico.alerta` — ampliación de `components/publico/alerta.blade.php`

Gana la prop `:animado` (por defecto `true`) y la entrada descrita en §7.

## §3 · La guardia

Nueva `tests/Feature/Sitio/MovimientoTest.php`, en el espíritu del guardián de hexadecimales que este repositorio ya tiene. Cinco afirmaciones sobre el código fuente:

1. Los cuatro `--ease-*` y las cinco `--duracion-*` existen en `tokens.css` con los valores de §1.1 y §1.2.
2. Ningún Blade de `resources/views/` contiene `ease-in` (fuera de `ease-in-out`), `transition: all` ni `transition-all`.
3. Ningún Blade contiene `duration-` seguido de dígitos. Solo se admite `duration-[--duracion-*]`.
4. Todo bloque `:hover` que declare `transform` en `app.css` y en `filament/admin/theme.css` está dentro de un `@media (hover: hover)`.
5. `tokens.css` contiene el bloque `prefers-reduced-motion` con los tres desplazamientos a cero.

Y se **endurece** `ComponentesDelPanelTest.php:55`, que hoy solo comprueba la presencia de una cadena: reapunta a `tokens.css` y afirma los valores.

## §4 · El acuse de pulsación

`.pulsable` entra por `x-publico.boton` y el componente absorbe los 34 clones. Orden de aplicación por consecuencia:

| Orden | Botón | Razón |
|---|---|---|
| 1 | «Pagar ahora» — `mi-cuenta/index.blade.php:78` | Mueve dinero y hoy no acusa nada |
| 2 | Los dos de la pasarela — `pago/simulado.blade.php:78,82` | Ídem |
| 3 | «Entrar» — `mi-cuenta/entrar.blade.php:32` | Puerta del área privada |
| 4 | Envío de vacante, «Filtrar» de los seis listados, «Afíliate» | Volumen |

Un botón que no se hunde al pulsarlo se siente roto en móvil, donde no hay hover que confirme nada.

## §5 · El desplegable de sesión

Tres cambios en `components/publico/menu-usuario.blade.php:80-83`:

```blade
x-transition:enter="transition-[opacity,transform] ease-out duration-[--duracion-entrada]"
x-transition:leave="transition-[opacity,transform] ease-out duration-[--duracion-salida]"
```

1. **`ease-in` → `ease-out`** en la salida. Es la única curva prohibida escrita a mano del proyecto.
2. Duraciones a tokens: 200 ms entrada, 160 ms salida.
3. **`transition` a secas arrastra la lista completa de propiedades de Tailwind**, incluidas `box-shadow`, `filter` y `backdrop-filter`, sobre un panel con `shadow-xl`. Especificar `transition-[opacity,transform]` deja de animar una sombra grande en cada apertura.

No se toca `origin-top-right` (línea 86) ni el par `opacity-0 scale-95`: ya son correctos —escala desde el disparador, y nunca desde `scale(0)`.

## §6 · La tarjeta del directorio

`components/publico/tarjeta-asociado.blade.php:10` pasa de `duration-500` a `duration-[--duracion-boton] ease-out`, sincronizada con el borde y la elevación de la tarjeta que la contiene. `scale-105` se conserva: la propiedad ya era la correcta.

## §7 · La alerta

`components/publico/alerta.blade.php`, 20 instancias en 15 vistas, hoy aparece de golpe. Entrada por `transition` + `@starting-style`, **no `@keyframes`**:

```css
.alerta-animada {
    transition:
        opacity var(--duracion-entrada) var(--ease-out),
        transform var(--duracion-entrada) var(--ease-out);
}
@starting-style {
    .alerta-animada {
        opacity: 0;
        transform: translateY(var(--asb-desplazamiento-alerta));
    }
}
```

Porcentaje y no píxeles: la alerta cambia de alto según el mensaje. **Sin retardo** — `role="status"` es región viva y el anuncio del lector de pantalla no puede depender de una animación. La prop `:animado` se pone a `false` en la alerta estática de descargo de `guia/index.blade.php:131`, que no es un acuse sino texto fijo.

## §8 · El estado de envío en los dos botones que cobran

Único JavaScript nuevo del plan:

```blade
x-data="{ enviando: false }"
x-on:submit="enviando = true"
```

Con `x-bind:disabled="enviando"` en **los dos** botones de la pasarela, no solo en el pulsado: eso es lo que impide el doble intento desde la interfaz. Contenido a `opacity: .55` en `var(--duracion-instante)`, texto a «Abriendo la pasarela…». El servidor ya se protege en `MiCuentaController::cobroVigente`; lo que falta es que la interfaz lo cuente.

**Prerrequisito de producto:** `mi-cuenta/index.blade.php` no pinta `session('error')`, así que el único mensaje de fallo de pago del sistema se descarta en silencio. Animar una alerta que nunca se renderiza no sirve de nada. Se arregla en la misma tanda.

## §9 · Borrado del contador del KPI

Se eliminan las líneas 38-62 de `components/panel/kpi.blade.php`: el bloque `x-data`, el `x-intersect.once` y el `x-text`. El párrafo queda pintando `{{ $valor }}` directamente.

Justificación en el punto 7 del contexto. La alternativa de sustituirlo por una entrada de la tarjeta se evaluó y se descartó: el movimiento correcto para una cifra de tablero es destellar cuando **cambia**, y eso aquí es imposible porque no hay polling en ninguna parte del panel —las cuatro cifras nunca cambian mientras alguien mira.

Consecuencia colateral, verificada: **`@alpinejs/intersect` nunca estuvo instalado.** `package.json` solo trae `alpinejs` y `@alpinejs/collapse`, y `resources/js/app.js` únicamente registra `collapse`. El `x-intersect` de la línea 60 funciona hoy porque `<x-panel.kpi>` solo se usa dentro de `/admin` (cuatro veces, en `filament/pages/observatorio.blade.php`), donde el Alpine empaquetado de Livewire 3 sí lo trae. Es decir: **el componente moriría en silencio si alguien lo usara en el sitio público.** Al borrar el contador desaparece el único `x-intersect` del repositorio y con él esa trampa.

## §10 · Lo que no se anima

Rechazos explícitos, para que no se reabran en revisión:

- **`:focus-visible`** (`app.css:36-40`) y el enlace «Saltar al contenido» (`layouts/publico.blade.php:161`). Teclado puro, cientos de veces al día. Hoy son instantáneos y **es lo único de movimiento accesible que el proyecto ya acierta**: hay que protegerlo del barrido, no mejorarlo.
- **El hero** (`components/publico/hero.blade.php`). Contiene el `h1` sobre el pliegue, candidato a LCP, en las siete páginas institucionales. Animarlo es meter una animación de carga en cada navegación de un sitio de recarga completa.
- **Navbar, pie, migas, paginación y logo.** Uso constante. Solo color, con curva tokenizada, jamás `transform`. En el paginador se retira además la `transition-colors` muerta que heredan las pastillas inerte y actual (`vendor/pagination/tailwind.blade.php:11`).
- **Scroll-reveal y escalonado de rejillas.** Portada (nueve secciones), rejillas de directorio, artistas y proveedores, listados de empleo, eventos y boletín, y las cuatro tarjetas KPI. Se repintan con cada filtro y cada paginación: es el caso de manual de «se ve mil veces». Única excepción defendible, no incluida en este alcance: la tira de cifras del Observatorio en `inicio.blade.php:43`, una sola vez y con `unobserve`.
- **El cambio de tema.** La mordaza de `layouts/publico.blade.php:99-117` y su gemela de `panel-graficas.js:85-115` es una decisión ya tomada y bien razonada. Animarlo es pelear contra ella.
- **Badges e insignias de estado.** Datos de servidor que nunca cambian en pantalla.
- **Errores de validación por campo** (`campo.blade.php:59-65`). Llegan en recarga completa: un formulario con seis errores daría seis entradas simultáneas, o sea ruido. Lo que falta ahí no es movimiento sino **reservar la altura** en el contenedor de ayuda y error —hoy la ayuda desaparece y la rejilla salta— y llevar el foco al primer inválido. El foco no se anima.
- **Filtros y conmutadores que recargan la página.** Cualquier transición mentiría sobre lo que ocurre, salvo lo que cubre §12.
- **Modales, tablas, notificaciones y barra lateral de Filament.** Traen su propio movimiento y sus propias curvas.
- **Texturas de fondo** (`.trama-puntos`, `.resplandor-marca`) y el carrusel de aliados.

## §11 · El menú móvil sale del flujo

`components/publico/navbar.blade.php`. Se retira `x-collapse` de la línea 84 y el panel pasa a superposición:

```
absolute inset-x-0 top-full origin-top border-t border-linea bg-fondo shadow-lg
max-h-[calc(100dvh-4rem)] overflow-y-auto lg:hidden
```

`bg-fondo` opaco es obligatorio: el header es `bg-fondo/85 backdrop-blur-md` y el contenido se vería a través. El `sticky` del header ya establece bloque contenedor, así que no hace falta añadir `relative`.

```blade
x-transition:enter="transition-[opacity,transform] ease-cajon duration-[--duracion-panel]"
x-transition:enter-start="opacity-0 translate-y-[--asb-desplazamiento-panel]"
x-transition:leave="transition-[opacity,transform] ease-cajon duration-[--duracion-salida]"
```

**Coste real, y es la mitad del trabajo.** Al superponerse deja de ser un acordeón y pasa a ser una capa, así que el `x-data` del `<header>` (línea 19) necesita tres comportamientos que hoy no tiene:

```blade
x-on:keydown.escape.window="menuMovil = false"
x-on:click.outside="menuMovil = false"
x-on:resize.window="if (window.innerWidth >= 1024) menuMovil = false"
```

`click.outside` va en el `<header>` y no en el panel: el botón que alterna vive dentro del header, y si el manejador estuviera en el panel el clic del botón lo cerraría y lo abriría en el mismo gesto.

Sin estas tres líneas el rework **empeora** la accesibilidad en vez de mejorarla. Por eso va en PR aparte y con pruebas.

De paso, el cruce hamburguesa↔X de las líneas 64-65, hoy un corte seco de `x-show` sobre dos `<path>`, pasa a fundido con `rotate` de ±90° en `var(--duracion-salida)`.

**No se toca** el bloque `<noscript>` de las líneas 73-82: cerrar sesión no puede depender de Alpine, y esa es una decisión deliberada ya tomada.

**Deuda que deja esta fase, verificada:** la línea 84 es el **único** consumidor de `x-collapse` en todo el repositorio. Al retirarlo, `@alpinejs/collapse` queda como dependencia muerta y `resources/js/app.js` seguiría importándola y registrándola en sus líneas 2 y 4. Este trabajo **no** la desinstala —`package.json` no se toca, según la decisión de arriba—, pero la fase 3 debe dejarlo anotado para que se resuelva con aprobación explícita. Un import registrado sin consumidores es exactamente la clase de cosa que sobrevive años.

## §12 · Transiciones de vista entre documentos

En un sitio de recarga completa, filtrar, paginar y abrir un detalle son siempre navegación: no hay estado de cliente que animar. Las view transitions son la única palanca real y degradan a nada donde no haya soporte.

```css
@view-transition { navigation: auto; }

::view-transition-old(root),
::view-transition-new(root) {
    animation-duration: 180ms;
    animation-timing-function: var(--ease-out);
}
```

Más dos elementos compartidos con `view-transition-name`: la pastilla de filtro activa, y la portada del listado al detalle en directorio, artistas y eventos.

Las view transitions viven en un árbol de pseudoelementos aparte, así que **`*, *::before, *::after` no las alcanza** y el barrido de §1.4 no las cubriría. Necesitan su propia regla:

```css
@media (prefers-reduced-motion: reduce) {
    ::view-transition-group(*),
    ::view-transition-old(*),
    ::view-transition-new(*) { animation: none !important; }
}
```

Nota sobre las mordazas del cambio de tema: apagan `transition`, no `animation`. Como el plan **no introduce ningún `@keyframes` propio** —todo es `transition` o `@starting-style`—, no hay que ampliarlas. Si en el futuro entra algún keyframe, las dos mordazas tienen que cambiar en el mismo commit o divergen.

## §13 · Verificación

`CLAUDE.md` exige que todo cambio quede probado programáticamente, y en animación eso no es obvio. Tres capas, de la más barata a la más cara.

**Capa 1 · Guardias PHP** (§3). Sobre el código fuente, deterministas, corren con la suite en cada cambio. Cubren la disciplina.

**Capa 2 · Playwright sobre Chromium real.** Cubre lo que el código fuente no puede probar: el valor computado y el comportamiento. `playwright-cli` está instalado global y `.playwright-cli` ya está en el `.gitignore`. El servidor de desarrollo es la configuración `asobares` de `.claude/launch.json`, puerto 8123.

- `playwright-cli eval "getComputedStyle(document.querySelector('.tarjeta-hover')).transitionTimingFunction" --raw` debe devolver `cubic-bezier(0.23, 1, 0.32, 1)` en los dos temas.
- Con `run-code` se dispone de la API completa, así que `page.emulateMedia({ reducedMotion: 'reduce' })` permite probar **la precisión de §1.3**: que bajo esa preferencia `--asb-levante` computa `0px` y el `transform` del hover es `none`, mientras `border-color` sigue fundiendo a 140 ms. Es la afirmación que distingue este enfoque del martillazo, y es comprobable.
- Comportamiento de §11: `press Escape` cierra, `resize 1280 800` cierra, clic fuera cierra.
- Comprobar que el `transition-property` del desplegable de §5 no incluye `box-shadow`.

Trampa conocida del entorno: `playwright-cli --help` y `--version` imprimen su salida correcta y **después** revientan con código distinto de cero. Los comandos reales salen con 0. No encadenar con `&&` sobre `--help`.

**Capa 3 · Vídeo.** `video-start`, `video-chapter` por pieza, `video-stop`. Responde a la pregunta que ninguna aserción responde: *¿se siente bien?* Es imprescindible aquí porque el panel del navegador de esta máquina no compone fotogramas, así que sin Playwright un rework de movimiento se aprobaría a ciegas.

## §14 · Orden de ejecución

| Fase | Contenido | Riesgo |
|---|---|---|
| **0** — un commit, ~1 h | §1 completo, endurecer el test de `ComponentesDelPanelTest`, borrar `theme.css:80-86`, borrar `welcome.blade.php` | Nulo. Cierra las tres violaciones altas que afectan a las ~30 páginas a la vez |
| **1** — ~medio día | §2.1, §2.2, §3, §5, §6, §9 | Bajo. Cero JavaScript nuevo |
| **2** — ~un día | §2.3, §2.4, §4, §7, §8 y el arreglo de `session('error')` | **El más alto del plan**: toca 23 archivos de vista. Se mitiga con capturas Playwright de las seis páginas principales antes y después |
| **3** — PR aparte | §11 y §12 | Cambio de comportamiento. Pruebas propias |

Cada fase termina con la suite en verde (`php artisan test --compact` filtrado a lo tocado) y con `vendor/bin/pint --dirty --format agent` sobre los PHP modificados.
