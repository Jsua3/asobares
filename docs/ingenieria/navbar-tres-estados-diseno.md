# Navbar de escritorio en tres estados — diseño aprobado

**3 de septiembre de 2026** · Persona 1 (Sua) con Claude Code · rama `p1-navbar-alternativa` · **no se fusiona ni se despliega**: es la opción B que se lleva a la reunión con la dirección junto a la opción A (la barra de la Persona 2, hoy en producción).

> **5 sep 2026:** Sua eligió esta barra (D-30) y `main` la absorbió y la desplegó el mismo día sobre la portada a pantalla completa de la Persona 2 (bitácora §39.9-§39.11). **6 sep 2026:** este archivo gana una **Parte II**, al final, con el diseño de la barra móvil 2.1, aprobado por Sua ese día; hasta que la Parte II se construya y llegue a `main`, la Parte I sigue describiendo el móvil tal como está en producción («móvil intacto»), y al construirse gana las notas fechadas que la Parte II §7 enumera.

Este documento es la especificación de diseño. Se escribió **antes** de la primera línea de código, tras un análisis de contexto de seis lectores en paralelo sobre roles, tema, movimiento, idiomas, pruebas y marca, y tras siete decisiones tomadas por Sua en la misma sesión (§2). El plan de implementación se deriva de aquí; lo que no está aquí no se construye.

---

## 1. Qué es y qué no es

**Es** una reescritura de la barra de navegación pública **en escritorio (≥ 1024 px)** con tres estados —inicial, scroll, atención—, tres módulos que se separan al hacer scroll, un selector de tema con popover dentro de la barra, un chip de idioma, y un módulo de cuenta consciente del rol. Con la calidad de movimiento del ecosistema iOS 26: rebote con asentamiento, refracción, naturalidad.

**No es**:

- Un rediseño del móvil. Por debajo de 1024 px la hamburguesa y el panel en plano actuales **no se tocan** (decisión de Sua: el móvil se rediseña después, cuando el escritorio esté perfecto).
- La internacionalización del sitio. El chip de idioma **se ve y no funciona a propósito**: inglés aparece deshabilitado con «próximamente». Traducir el sitio es otro subsistema, con su propia spec y su acta (§10).
- Una ampliación de alcance del encargo: la barra ya existía y se rediseña. La única decisión de producto que cambia es la opción «Sistema» del tema (§7.2), que se anota fechada en `encargo.md` §13.

## 2. Decisiones tomadas (3 sep 2026, Sua)

| # | Decisión | Elegido |
|---|---|---|
| D1 | Chip de idioma en esta fase | Visible, con popover completo; **EN deshabilitado, «próximamente»**. Idiomas se diseña aparte con acta |
| D2 | Estado-atención con puntero grueso a ≥ 1024 px | **Un toque sobre el módulo principal alterna** el estado; toque fuera, Escape o scroll lo cierran; el módulo compacto lleva un indicador `···` solo con puntero grueso |
| D3 | Móvil | **Intacto** |
| D4 | Vuelta al estado inicial | **Al volver al tope** (`scrollY ≤ 8`), con la fusión animada al revés |
| D5 | Enfoque de movimiento | **A: resortes nativos en CSS con `linear()`**, todo declarativo, sin dependencias |
| D6 | Módulo del logo en scroll | **Isotipo «ab» solo** (`public/img/monograma-asobares.png`, 156×108, rojo) |
| D7 | Duración del rebote | Hasta **520 ms** de reloj con token `--duracion-rebote` y excepción anotada en la suite. **5 sep:** el cambio de estado sube a **620 ms** con token propio `--duracion-estado`, a petición de Sua («sutilmente más lento»); los popovers siguen en 520 |
| D8 | Banderas del chip de idioma | **Colombia** para ES, **Estados Unidos** para EN |

## 3. Restricciones que gobiernan el diseño

Salen del mapa de contexto y no se negocian aquí; si una cambia, cambia el diseño.

1. **Un solo `<header>`, un solo `<nav>`, los cinco controles de escritorio en un mismo `div` y en este orden:** Directorio, Abre tu negocio, Eventos, Bolsas, El gremio. `aria-current="page"` exactamente dos veces en el header (escritorio + móvil). Cada etiqueta declarada **una vez** como `'texto' => 'Etiqueta'` en `navbar.blade.php`. (`NavegacionAgrupadaTest`, siete pruebas.) → **Los tres módulos salen del mismo DOM por CSS; nunca hay una segunda navegación.**
2. **Toda duración y curva sale de tokens.** Prohibido en vistas: `duration-N`, `duration-[…]`, `ease-[…]`, `ease-in` suelto, `transition-all`, flechas Unicode (también en comentarios Blade). Los portadores `pulsable`, `fila-pulsable`, `enlace-accion`, `tarjeta-pulsable` no comparten elemento con utilidades de reloj. `transicion-desplegable` + `duration-(--duracion-*)` + `ease-*` es el patrón sancionado para `x-transition`. (`MovimientoTest`.)
3. **Todo movimiento nuevo es geometría tokenizada** (`--asb-*`) que `prefers-reduced-motion: reduce` pone a cero o uno. Las duraciones no se tocan bajo movimiento reducido: se anula la geometría. (`tokens.css:411-428`, `MovimientoTest`.)
4. **El vidrio es `var(--asb-cromo-velo)` + `var(--asb-cromo-desenfoque)`** (o los `--asb-vidrio-*`), nunca `blur()` literal: así `prefers-reduced-transparency` lo vuelve sólido.
5. **El tema se cambia solo por `$store.tema.elegir(valor)`**, que llama a `window.aplicarTema(valor)` con el valor explícito. La clave `localStorage.theme` la comparte el panel de Filament, que entiende `light`, `dark` y `system`. Nunca alternar la clase `dark` a mano.
6. **La marca no se recolorea ni se recorta** (manual de marca). Solo existe isotipo rojo. En oscuro va rojo sobre negro, como el favicon.
7. **Los nombres Alpine `menuMovil` y `abierto`, los manejadores literales del panel móvil y las cadenas de clases medidas del objetivo táctil** están fijados por pruebas que leen los archivos crudos (`MenuMovilTest`, `NavegacionAgrupadaTest`, `ObjetivoTactilTest`). Se conservan o se actualizan **con re-medición en Chromium** y en el mismo commit.
8. **Anónimo no ve** `menu-cuenta`, «Cerrar sesión», «Ir al panel del gremio» ni «Configuración del sitio». Los popovers de tema e idioma usan ids propios (`popover-tema`, `popover-idioma`) y van **antes** de `div#menu-movil` en el header, porque `MenuMovilTest` recorta desde ahí hasta `</header>` y prohíbe `aria-expanded` y la palabra «Apariencia» dentro.
9. Sin dependencias nuevas (ni Composer ni npm). Sin carpetas nuevas en `docs/`.

## 4. Arquitectura

### 4.1 DOM

```
<header data-estado="inicial|scroll|atencion" x-data="{ … }" class="cromo sticky top-0 z-40">
  <nav class="bandeja …">                       ← píldora exterior (vidrio solo en inicial)
    <a class="modulo modulo-logo …">            ← módulo 1
      <img logotipo completo>  <img isotipo>    ← los dos siempre en el DOM
    </a>
    <div class="modulo modulo-principal gap-1"> ← módulo 2: los cinco controles, en orden
      Directorio · Abre tu negocio · Eventos · Bolsas ▾ · El gremio ▾
      <span class="indicador-mas">···</span>    ← solo visible con puntero grueso en scroll
    </div>
    <div class="modulo modulo-cuenta …">        ← módulo 3
      @guest  Mi cuenta · Afíliate
      @auth   <x-publico.menu-usuario />        ← disparador con nombre y prefijo de rol
      <control tema>  <chip idioma>
      <div id="popover-tema">  <div id="popover-idioma">
    </div>
    <button hamburguesa lg:hidden>
  </nav>
  @auth <noscript> cerrar sesión </noscript>
  <div id="menu-movil">  …intacto…  </div>
</header>
```

Los **tres hijos del `<nav>` son los tres módulos**. En `inicial` la píldora exterior lleva el vidrio y los módulos son transparentes; en `scroll` y `atencion` la píldora exterior se vuelve transparente (fondo, borde, sombra) y cada módulo enciende su propio vidrio. El `gap` del `<nav>` crece de `0` a `var(--asb-separacion-modulos)`. Ningún nodo se añade ni se quita al cambiar de estado. **Desde el 5 sep** la `<nav>` es, de 64rem para arriba, una rejilla `1fr auto 1fr` (`lg:grid lg:grid-cols-[1fr_auto_1fr]`) y cada módulo se justifica en su celda (`justify-self` start · center · end): el principal queda en el centro de la **pantalla** en los tres estados, gane lo que gane el logo al encogerse. Con `justify-between` caía en el punto medio entre los otros dos (56 px a la izquierda en inicial, 120 en scroll; medido en Chromium). La cuenta lleva `whitespace-nowrap` para que la rejilla respete su mínimo (313 px) en vez de envolver «Mi cuenta», y el módulo del logo lleva `min-width: max-content` en el bloque de 64rem porque en rejilla `shrink-0` es inerte y sin ese mínimo la pista aplastaba el logotipo entre 1024 y 1190 px (24 px de ancho a 1024). **Desde la fusión con la portada de la Persona 2 (5 sep, mediodía)** el header es `cromo-fijo` (`position: fixed`) y no sticky: flota sobre el video a pantalla completa, y las demás páginas apartan 7rem su primera sección. Y por debajo de 64rem la `.bandeja` conserva el vidrio de `main` (velo, desenfoque, apoyo): el móvil sigue siendo la barra de siempre, ahora fija. **Esta barra es la desplegada desde el 5 sep** (D-30, resuelta por Sua).

### 4.2 Estado en Alpine

El `x-data` inline del `<header>` conserva `menuMovil`, `desplazado` y los manejadores literales que exige `MenuMovilTest`, y añade:

| Propiedad / método | Qué hace |
|---|---|
| `estado` | `'inicial'` \| `'scroll'` \| `'atencion'`; se escribe en `data-estado` con `x-bind` |
| `atendiendo` | booleano: el usuario pidió atención (hover, toque o foco) |
| `sincronizar()` | `desplazado = scrollY > 8`; recalcula `estado` |
| `punteroFino()` | `matchMedia('(hover: hover) and (pointer: fine)').matches` |
| `atender()` | con puntero fino, en `mouseenter` del header: `atendiendo = true`, cancela el cierre pendiente |
| `soltar()` | con puntero fino, en `mouseleave`: `atendiendo = false` tras 280 ms |
| `alternarAtencion()` | con puntero grueso, al tocar el módulo principal: `atendiendo = !atendiendo` |
| `recalcular()` | `estado = !desplazado ? 'inicial' : (atendiendo ? 'atencion' : 'scroll')` |

Cierres del estado-atención con puntero grueso: `x-on:click.outside` sobre el módulo principal, `x-on:keydown.escape.window`, y el propio scroll (`sincronizar()` pone `atendiendo = false` si `scrollY` cambia más de 24 px desde que se abrió). Con teclado, `:focus-within` del header equivale a atención y lo resuelve CSS, no Alpine.

Bajo `html.sin-desplazamiento` (movimiento reducido, clase que ya pone el `<head>`) todo funciona igual; solo cambia la geometría (§5.3).

## 5. Vocabulario de movimiento

### 5.1 Curvas (en el `@theme` de `tokens.css`, junto a las cuatro existentes)

Dos resortes reales, calculados con la ecuación del oscilador amortiguado y codificados como `linear()` con 25 paradas. El respaldo para navegadores sin `linear()` **no puede ser una segunda declaración**: si el valor de `var(--ease-rebote-suave)` no se entiende, la propiedad no cae a la declaración anterior sino a su valor inicial (`ease`), porque `var()` es inválido en tiempo de cómputo y no en tiempo de análisis. El mecanismo correcto es `@supports`:

```css
@theme {
    --ease-rebote-suave: cubic-bezier(0.32, 0.72, 0, 1);  /* respaldo: la curva del cajón */
    --ease-rebote-vivo: cubic-bezier(0.32, 0.72, 0, 1);
}

@supports (animation-timing-function: linear(0, 1)) {
    :root {
        --ease-rebote-suave: linear(…);
        --ease-rebote-vivo: linear(…);
    }
}
```

Los usos escriben solo `var(--ease-rebote-suave)`: el token ya trae su respaldo. (Corregido el 5 sep al escribir el plan.)

**`--ease-rebote-suave`** — amortiguación ζ = 0,70, sobreimpulso 4,6 % en el 54 % del recorrido. Para separar y fundir módulos, cambiar anchos, plegar controles.

```
linear(0, 0.050 4%, 0.168 8%, 0.319 12%, 0.475 17%, 0.620 21%, 0.744 25%, 0.845 29%,
       0.922 33%, 0.977 38%, 1.013 42%, 1.034 46%, 1.044 50%, 1.046 54%, 1.043 58%,
       1.037 62%, 1.030 67%, 1.023 71%, 1.017 75%, 1.011 79%, 1.006 83%, 1.003 88%,
       1.001 92%, 0.999 96%, 1)
```

**`--ease-rebote-vivo`** — ζ = 0,55, sobreimpulso 12,5 % en el 33 %. Para popovers al aparecer, el cruce logotipo ↔ isotipo, el indicador `···`.

```
linear(0, 0.086 4%, 0.283 8%, 0.516 12%, 0.735 17%, 0.910 21%, 1.030 25%, 1.099 29%,
       1.125 33%, 1.121 38%, 1.099 42%, 1.071 46%, 1.042 50%, 1.018 54%, 1.000 58%,
       0.990 62%, 0.985 67%, 0.984 71%, 0.986 75%, 0.990 79%, 0.994 83%, 0.997 88%,
       0.999 92%, 1.001 96%, 1)
```

### 5.2 Duraciones y geometría (en `:root` de `tokens.css`)

| Token | Valor | Uso |
|---|---|---|
| `--duracion-rebote` | `520ms` | Los popovers y toda transición con curva de rebote que no sea el cambio de estado. El movimiento «llega» hacia los 250 ms; el resto es asentamiento |
| `--duracion-estado` | `620ms` | La geometría del cambio de estado: separación, caída, plegado de los dos controles, cruce del logo, indicador. Un punto más lento que el rebote de los popovers (Sua, 5 sep) |
| `--asb-separacion-modulos` | `0.75rem` | `gap` del `<nav>` en scroll/atención. **Es layout, no movimiento: no se anula** bajo movimiento reducido |
| `--asb-caida-modulo` | `6px` | `translate` vertical de los módulos al separarse (bajan y asientan) |
| `--asb-escala-popover` | `0.92` | escala de arranque de los popovers de tema e idioma |
| `--asb-desplazamiento-popover` | `-6px` | `translate` de arranque de los popovers |
| `--asb-escala-isotipo` | `0.9` | escala de arranque de la imagen entrante en el cruce del logo |

`--duracion-cromo` (520 ms, de la barra de la Persona 2) **se conserva**: lo sigue usando `.tema-lateral__cuerpo`, que se queda en móvil. Las reglas de escritorio que se reemplazan pasan a `--duracion-rebote`; los dos tokens valen lo mismo y significan cosas distintas (uno abre la barra lateral, otro asienta un resorte). **Desde el 5 sep** la geometría del cambio de estado usa `--duracion-estado` (620 ms) y `--duracion-rebote` se queda para los popovers.

### 5.3 Movimiento reducido

En el bloque `@media (prefers-reduced-motion: reduce)` de `tokens.css`, tras el existente:

```css
--asb-caida-modulo: 0px;
--asb-escala-popover: 1;
--asb-desplazamiento-popover: 0px;
--asb-escala-isotipo: 1;
--ease-rebote-suave: var(--ease-cajon);   /* el rebote ES geometría: sin sobreimpulso */
--ease-rebote-vivo: var(--ease-cajon);
```

Las duraciones no se tocan (regla del proyecto). Resultado: los módulos siguen separándose (layout) pero por fundido y cambio de `gap` sin caída ni rebote; los popovers aparecen por opacidad.

### 5.4 Qué transiciona cada cosa

| Elemento | Propiedades | Curva |
|---|---|---|
| `<nav>` (píldora exterior) | `gap`, `background-color`, `border-color`, `box-shadow` | suave |
| `.modulo` | `background-color`, `border-color`, `box-shadow`, `border-radius`, `translate` | suave |
| `.control-plegable` (Abre tu negocio, El gremio) | `max-width`, `opacity`, `padding-inline` | suave |
| `.modulo-logo img` | `opacity`, `scale` | vivo |
| `#popover-tema`, `#popover-idioma` | `opacity`, `scale`, `translate` vía `x-transition` con `transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)` al entrar y `ease-cajon duration-(--duracion-salida)` al salir | vivo / cajón |
| `.indicador-mas` | `opacity`, `scale` | vivo |

Todo `translate`/`scale` se escribe con las propiedades individuales, no con `transform`: Tailwind 4 compila `translate-*`/`scale-*` a esas propiedades, y una `transition` que nombre `transform` no las anima (defecto ya pagado tres veces en el proyecto).

### 5.5 Refracción y brillo

- Cada `.modulo` en scroll/atención: `background-color: var(--asb-cromo-velo)`, `backdrop-filter: var(--asb-cromo-desenfoque)`, `border: 1px solid var(--asb-linea)`, `box-shadow: var(--asb-cromo-apoyo), inset 0 1px 0 rgb(255 255 255 / 0.26)` (el filo de luz que ya usa la bandeja actual).
- **Brillo especular** (`.modulo::before`): `radial-gradient` de luz blanca al 14 % centrado en `var(--puntero-x) var(--puntero-y)`, solo dentro de `@media (hover: hover) and (pointer: fine)`. Las variables las escribe el `Alpine.data('escena')` que ya existe en `app.js` —se reutiliza en el `<nav>` sin cambios—. Con puntero grueso el brillo queda fijo en el centro-arriba.
- **Lente de borde** (`.modulo::after`): anillo interior de 1 px con `linear-gradient` de blanco al 18 % a transparente, que da el efecto de canto de cristal. `pointer-events: none`.
- Nada de esto lleva `blur()` literal: `prefers-reduced-transparency` lo vuelve sólido con los tokens.

## 6. Los tres módulos

### 6.1 Módulo del logo

Las dos imágenes viven en el mismo `<a href="{{ route('inicio') }}">`, superpuestas: `logo-asobares.png` (592×108) e `monograma-asobares.png` (156×108). En `inicial` el logotipo tiene `opacity: 1; scale: 1` y el isotipo `opacity: 0; scale: var(--asb-escala-isotipo)`; en `scroll`/`atencion` se invierten. El `<a>` conserva la cadena `-my-1.5 flex shrink-0 items-center py-1.5` y su caja se estrecha con `max-width` en rebote suave. Ambos `<img>` llevan `width`/`height` reales y `alt` una sola vez (el isotipo `alt=""` para no anunciar la marca dos veces). El `<head>` precarga el isotipo con `<link rel="preload" as="image" href="img/monograma-asobares.png" media="(min-width: 64rem)">`, o vuelve el parpadeo documentado en la bitácora.

### 6.2 Módulo principal

Los cinco controles como hoy (tres enlaces directos y dos `x-publico.menu-grupo`). «Abre tu negocio» y «El gremio» llevan además la clase `control-plegable`; en `scroll` el CSS los pliega (`max-width: 0; opacity: 0; padding-inline: 0; visibility: hidden` al final de la transición) y en `atencion` los despliega. Nada se saca del DOM: `NavegacionAgrupadaTest` sigue encontrando los cinco.

El `.indicador-mas` (`···`, tres puntos dibujados con Heroicons `ellipsis-horizontal`, no con caracteres) es un `<span aria-hidden="true">` al final del módulo, visible solo cuando `data-estado="scroll"` **y** `@media (hover: none)`. El módulo entero es el objetivo del toque (`x-on:click` con guarda `!punteroFino()`), con `min-height: 44px`.

### 6.3 Módulo de cuenta

Orden de izquierda a derecha: `[cuenta] [tema] [idioma]`.

**Cuenta, por rol** (evaluación acumulativa, no `match` excluyente: un usuario puede tener dos roles, `FormulariosPublicosTest:413`):

| Situación | Disparador visible en la barra | Desplegable |
|---|---|---|
| Anónimo | `Mi cuenta` (enlace a `mi-cuenta.index`) · `Afíliate` (pastilla) | ninguno |
| Asociado | avatar de iniciales + `name` | el `menu-usuario` actual: nombre, nombre del establecimiento, Mi cuenta, Mis vacantes, Cerrar sesión |
| Subadmin (secretaría) | `Sec. {name}` | nombre, «Secretaría del gremio», Ir al panel del gremio, Cerrar sesión |
| Super admin (dirección) | `Admin {name}` | nombre, «Dirección del gremio», Ir al panel del gremio, Cerrar sesión |

El prefijo se resuelve en `menu-usuario.blade.php` a partir de `esSuperAdmin()` / `esSubadmin()`; si tiene los dos, gana `Admin`. Las cadenas largas «Secretaría del gremio» / «Dirección del gremio» siguen **dentro** del panel porque las pruebas las exigen ahí. El cierre de sesión sigue siendo `POST` con `@csrf` a `mi-cuenta.salir`, y el `<noscript>` del header se conserva.

**Tema.** Un `<button>` de 44×44 con `aria-label="Apariencia del sitio"`, `aria-expanded`, `aria-controls="popover-tema"`. Muestra `heroicon-o-sun` si el tema **resuelto** es claro y `heroicon-o-moon` si es oscuro; **nunca el monitor**. El icono lo decide CSS por la clase `dark` del `<html>` (`dark:hidden` / `hidden dark:block`), no `x-show`: el `<head>` pone esa clase antes del primer pintado y `elegir()` la cambia al instante, así que es el tema resuelto sin destello del icono equivocado antes de que arranque Alpine (corregido el 5 sep tras la revisión de la tarea 5). Con puntero fino se abre al posarse el ratón (280 ms de gracia al salir); con grueso, al tocar; con teclado, al pulsar. Desde el 5 sep el comportamiento es el `Alpine.data('desplegable')` compartido (§6.4), que asoma por `pointerenter` de tipo ratón y cierra a los demás al abrirse. El popover `#popover-tema` es una `hoja-flotante` **debajo** de la barra, alineada al botón, con tres filas `fila-pulsable` de 44 px: `☀ Claro`, `🌙 Oscuro`, `🖥 Sistema` (iconos Heroicons `sun`, `moon`, `computer-desktop` con `<span class="sr-only">` y texto visible), cada una con `aria-pressed` ligado a `$store.tema.preferencia` y un punto indicador en la activa. Cierra con clic fuera, Escape (devuelve el foco al botón) y `focusout`.

**Idioma.** Un `<button>` de 44×44 con el texto `ES` (siglas ISO 639-1 del idioma actual) y un galón, `aria-label="Idioma del sitio: ES"` (siglas del idioma actual), `aria-controls="popover-idioma"`. El nombre accesible contiene el texto visible (WCAG 2.5.3, corregido el 5 sep tras la revisión final). El popover `#popover-idioma` es **vertical**: dos filas `fila-pulsable` con bandera SVG inline de 20×14 + nombre del idioma en su propia lengua: `🇨🇴 Español` (activa, `aria-pressed="true"`, punto indicador) y `🇺🇸 English` (`disabled`, `aria-disabled="true"`, con «próximamente» en `text-2xs text-apagado`). Las banderas son un componente `x-publico.bandera` con `pais="co"|"us"`: Colombia en tres franjas (amarillo 50 %, azul 25 %, rojo 25 %); Estados Unidos **simplificada** (trece franjas y cantón azul sin estrellas, que a 14 px no se resuelven). No se instala ningún paquete.

**6.4 El desplegable compartido (5 sep).** Los dos grupos, la cuenta, el tema y el idioma usan el mismo `Alpine.data('desplegable')` de `app.js` (`x-data="desplegable"`), con los cableados en cada vista para que las guardias los lean: `pointerenter` a `asomar($event)` y `pointerleave` a `retirar($event)` (280 ms de gracia; los dos salen si `pointerType` no es `mouse` o no hay puntero fino), el `click` del disparador a `alternar()`, `click.outside` y el `focusout` fuera de la raíz a `cerrar()`, `Escape` a `cerrarYVolverAlFoco()`, y `desplegable-abierto.window` a `ceder($event.detail)`. `abrir()` despacha `desplegable-abierto` con **`$root`** como identidad y los demás ceden al instante: nunca hay dos paneles abiertos, que era como el popover de tema y el de idioma se pisaban al pasar del sol al chip. La identidad es `$root` y no `$el` porque dentro de un método `$el` es el elemento de la directiva que lo llamó (el botón, al abrir por clic o teclado): con `$el` el propio componente se tomaba por ajeno y Enter no abría nada. Se asoma por `pointerenter` y no por `mouseenter` porque en un equipo híbrido (ratón y pantalla táctil) la consulta de puntero fino es verdadera y el toque llega también como `mouseenter` sintético: abría y el `click` del mismo gesto cerraba. Escape devuelve el foco al disparador **solo si estaba dentro del componente**, leído antes de cerrar: un panel abierto por hover mientras se escribe en un campo no roba el foco al campo. «Bolsas», «El gremio» y la cuenta abren así al pasar el cursor, como el tema (pedido de Sua del 5 sep); la cuenta gana la salida por `focusout`, que antes no necesitaba por ser el último control de la barra. Guardias: `NavbarTresEstadosTest` (los cuerpos enteros de los métodos en `app.js`, los siete cableados en cada una de las cuatro vistas, `$root` en el aviso y en `ceder`, ningún `mouseenter` en las vistas) y `NavegacionAgrupadaTest` (las salidas).

**La barra lateral de la Persona 2** (`x-publico.barra-tema`) **se conserva solo por debajo de 1024 px** con `lg:hidden` en su `<aside>`: el móvil no pierde el control de tema y sigue literalmente igual.

## 7. El store de tema

### 7.1 Cambio

`Alpine.store('tema')` en `app.js` pasa a distinguir **preferencia** de **resuelto**:

| Antes | Después |
|---|---|
| `preferencia ∈ {light, dark}` (colapsa `system` al pintado) | `preferencia ∈ {light, dark, system}` (lo que el usuario eligió) |
| — | `resuelto ∈ {light, dark}` (lo que está pintado; alimenta el icono sol/luna) |
| `elegir('light'|'dark')` | `elegir('light'|'dark'|'system')`: escribe el valor literal en `localStorage.theme` y llama a `window.aplicarTema(valor)` |
| listener `storage` actualiza `preferencia` | actualiza `preferencia` y `resuelto` |
| — | listener de `change` en `matchMedia('(prefers-color-scheme: dark)')` que actualiza `resuelto` cuando `preferencia === 'system'` (el `<head>` ya repinta; el store solo se entera) |

El script del `<head>` **no cambia**: ya trata `'system'` y cualquier otro valor como «seguir al sistema». Filament ya escribe y lee `'system'`.

### 7.2 Decisión de producto que cambia

OBS3-03 (1 sep) fijó «el sitio arranca en el tema del dispositivo» y la prueba `test_el_selector_ofrece_solo_claro_y_oscuro` prohibía exponer «Sistema» como opción. Sua decide el 3 sep que el popover ofrece las tres. El arranque sigue siendo el del dispositivo (no cambia); lo que cambia es que el usuario puede **volver** a él tras haber forzado uno. Se anota fechada en `encargo.md` §13 y la prueba se reescribe (§9).

## 8. Archivos

| Archivo | Cambio |
|---|---|
| `resources/views/components/publico/navbar.blade.php` | Reescritura del bloque de escritorio: tres módulos, `data-estado`, indicador, controles de tema e idioma, popovers **antes** de `#menu-movil`. Panel móvil, hamburguesa y `<noscript>` intactos |
| `resources/views/components/publico/menu-usuario.blade.php` | Prefijo `Sec.`/`Admin` en el disparador; lógica de rol acumulativa; sin duplicar el panel |
| `resources/views/components/publico/logo.blade.php` | Acepta `doble` para pintar logotipo + isotipo superpuestos |
| `resources/views/components/publico/bandera.blade.php` | **Nuevo**: SVG inline `co` / `us` |
| `resources/views/components/publico/barra-tema.blade.php` | `lg:hidden` en el `<aside>`; marca activo por `$store.tema.resuelto` en vez de `preferencia` (tres enlaces), consecuencia del store de §7 |
| `resources/views/components/layouts/publico.blade.php` | `preload` del isotipo con `media="(min-width: 64rem)"` |
| `resources/css/tokens.css` | Dos curvas `linear()`, `--duracion-rebote`, `--duracion-estado` (5 sep), cinco tokens de geometría, bloque de movimiento reducido ampliado; `--duracion-cromo` se conserva |
| `resources/css/app.css` | Reglas `.bandeja`, `.modulo*`, `.control-plegable`, `.indicador-mas`, brillo y lente; **retira** `.cromo-bandeja`, `.cromo-compacto`, `.cromo-expandido`, `.cromo-desplegable`, sustituidas; conserva `.cromo`, `.cromo-apoyado`, `.cromo-oculto`, `.nav-enlace` (subrayado de la sección actual), `.hoja-flotante`, `.tema-lateral*` |
| `resources/js/app.js` | Store de tema (§7.1); `Alpine.data('escena')` sin cambios, reutilizado; `Alpine.data('desplegable')` (§6.4, 5 sep) |
| `tests/Feature/NavbarTresEstadosTest.php` | **Nuevo** (§9.1) |
| `tests/Feature/TemaClaroOscuroTest.php`, `ObjetivoTactilTest.php`, `MovimientoTest.php` | Actualizaciones justificadas (§9.2) |
| `material/encargo.md` §13 | Nota fechada: el popover ofrece «Sistema» |

`selector-tema.blade.php` (huérfano desde `e82edc8`, vigilado por una fila de `ObjetivoTactilTest`) **se borra** y su fila del DataProvider con él, en el mismo commit.

## 9. Pruebas

Regla del proyecto: cada aserción nueva se ve **roja** rompiendo el código a propósito antes de darla por buena.

### 9.1 `NavbarTresEstadosTest` (nueva)

| Prueba | Qué protege | Cómo se rompe para verla roja |
|---|---|---|
| el header declara los tres estados | `navbar.blade.php` contiene `x-bind:data-estado`, `'inicial'`, `'scroll'`, `'atencion'` y `alternarAtencion()` | quitar `alternarAtencion` |
| los cinco controles siguen en un solo bloque | XPath sobre `/contacto`: cinco controles en orden, y **exactamente dos** (`Abre tu negocio`, `El gremio`) llevan `control-plegable` | añadir la clase a `Eventos` |
| el isotipo viaja y se precarga | `/contacto` contiene `monograma-asobares.png` dos veces (img + preload) y la precarga lleva `media="(min-width: 64rem)"` | quitar el `media` |
| el rebote es un token | `tokens.css` contiene `--ease-rebote-suave: linear(`, `--ease-rebote-vivo: linear(`, `--duracion-rebote: 520ms`, y tras `prefers-reduced-motion: reduce` contiene `--asb-caida-modulo: 0px`, `--asb-escala-popover: 1`, `--ease-rebote-suave: var(--ease-cajon)` | borrar la anulación de `--asb-caida-modulo` |
| el rebote lleva respaldo | en `tokens.css`, `--ease-rebote-suave` y `--ease-rebote-vivo` se declaran dos veces: una con `cubic-bezier(0.32, 0.72, 0, 1)` fuera de todo `@supports` y otra con `linear(` dentro de `@supports (animation-timing-function: linear(0, 1))` | borrar la declaración de respaldo |
| el vidrio usa tokens | `.modulo` en `app.css` contiene `var(--asb-cromo-desenfoque)` y ningún `blur(` literal en las reglas nuevas | escribir `blur(20px)` |
| el brillo tiene puerta táctil | la regla `.modulo::before` que usa `--puntero-x` está dentro de un bloque `@media (hover: hover) and (pointer: fine)` (se acota a esa regla: la escena de la portada ya usa `--puntero-x` fuera de la puerta y no es de esta spec) | sacar `.modulo::before` de la puerta |
| el store acepta sistema | `app.js` contiene `resuelto` y `elegir` escribe `'system'`; `/contacto` contiene `$store.tema.elegir('system')` y `>Sistema<` | quitar la fila Sistema |
| el icono nunca es el monitor | el `<button>` de tema de `/contacto` referencia `sun`/`moon` con `x-bind` sobre `$store.tema.resuelto` y no contiene `computer-desktop` fuera de `#popover-tema` | poner el monitor en el botón |
| el idioma se ve y no funciona | `/contacto` contiene `>ES<`, `Español`, `English`, `próximamente`, y la fila de English lleva `disabled` y `aria-disabled="true"` | quitar `disabled` |
| las banderas son las decididas | `bandera.blade.php` contiene los dos `<svg>` con `data-pais="co"` y `data-pais="us"` | cambiar `co` por `es` |
| el módulo de cuenta por rol | anónimo ve `Mi cuenta` y `Afíliate` en el módulo y no `menu-cuenta`; subadmin ve `Sec.` en el disparador; super admin ve `Admin`; los dos a la vez ven `Admin` | invertir el orden del `match` |
| los popovers van antes del panel móvil | en el HTML de `/contacto`, `strpos('popover-tema') < strpos('id="menu-movil"')` | moverlos al final |
| el indicador solo con puntero grueso | `.indicador-mas` en `app.css` está bajo `@media (hover: none)` y `[data-estado="scroll"]` | quitar la puerta |
| la barra lateral solo en móvil | `barra-tema.blade.php` contiene `lg:hidden` | quitarlo |

### 9.2 Actualizaciones con su porqué

| Prueba | Antes | Después | Porqué |
|---|---|---|---|
| `TemaClaroOscuroTest::test_el_selector_ofrece_solo_claro_y_oscuro` | prohíbe `>Sistema<` | pasa a `..._ofrece_claro_oscuro_y_sistema` y lo exige | decisión de Sua del 3 sep (§7.2) |
| `TemaClaroOscuroTest::test_el_control_de_tema_vive_en_una_barra_lateral_fija` | exige `tema-lateral fixed` | exige además `lg:hidden` en el `<aside>` y `popover-tema` en la página | el control cambió de sitio en escritorio, no en móvil |
| `ObjetivoTactilTest` filas de `navbar`, `menu-usuario` | cadenas actuales | cadenas nuevas de los controles nuevos (tema, idioma, disparador con prefijo), **re-medidas en Chromium con playwright-cli ≥ 44×44**; se borra la fila de `selector-tema.blade.php` | el archivo se borra y hay controles nuevos |
| `MovimientoTest` comentario «nada pasa de 300 ms» | techo implícito | excepción anotada: `--duracion-rebote` es la única duración > 300 ms y se justifica por el asentamiento del resorte; se añade guardia literal `--duracion-rebote: 520ms` (5 sep: `--duracion-estado: 620ms` es la segunda, con su guardia) | D7 |

### 9.3 Verificación en navegador (fuera de PHPUnit)

Con `playwright-cli`, sobre `localhost`, grabación y medidas: (1) escritorio 1440×900: los tres estados con ratón, anchos de la píldora y de cada módulo antes/después, opacidad de los dos controles plegables; (2) teclado: Tab → atención por `:focus-within`; (3) iPad Pro 11 horizontal con táctil: toque abre, toque fuera cierra, indicador visible, **verificando dentro de la página** que `pointer: coarse` es `true`; (4) movimiento reducido: sin caída ni sobreimpulso (medir que `--asb-caida-modulo` computa `0px`); (5) transparencia reducida: `backdrop-filter: none` computado. Las capturas se comparan con la grabación de la barra de la Persona 2 hecha el mismo día.

## 10. Fuera de alcance, y dónde queda anotado

- **Idiomas**: subsistema aparte. Requiere `lang/`, middleware de locale, traducir vistas y volver multilingüe la tabla de ajustes. Es ampliación de alcance: acta antes de codificar. El chip de esta spec es su sitio reservado en la interfaz.
- **Móvil**: rediseño posterior, cuando el escritorio esté perfecto (D3).
- **Inercia al invertir un gesto** (conservar velocidad): descartada con el enfoque A (D5). Si en la demo se echa en falta, el híbrido C es el siguiente paso y no toca este diseño.
- **Isotipo blanco para oscuro**: no existe; hay que pedirlo al kit de marca. Mientras, rojo sobre negro.

## 11. Riesgos conocidos

- Tres módulos con `backdrop-filter` superpuestos al hero con video: coste de composición. Mitigación: `isolation: isolate` por módulo y medir con el video corriendo antes de dar por buena la demo.
- `linear()` en navegadores viejos: cubierto por el respaldo `--ease-cajon` en cada declaración (§5.1) y vigilado por prueba.
- El `gap` animado hace *reflow* en cada fotograma: son tres hijos, es asumible; si se nota, se cambia a `translate` por módulo con la misma curva.
- Alguien reintroduce `duration-300` o una flecha en un comentario Blade: `MovimientoTest` lo caza; es la red, no un riesgo abierto.

---

# Parte II · Navbar 2.1: el móvil en dos módulos — diseño aprobado

**6 de septiembre de 2026** · Persona 1 (Sua) con Claude Code · sobre `main` en `09e8c17` · **aprobada por Sua el 6 sep 2026**: las dieciocho decisiones de §2, con la recomendación en cada una. Se escribió **antes** de la primera línea de código, como la Parte I; se construye en la rama `p1-navbar-movil` siguiendo la Parte II del plan (`navbar-tres-estados-plan.md`). Encargo de Sua del 5 sep, textual: «la navBar 2.1 integrará un rediseño a la interfaz móvil [...] compuesta por dos módulos: el superior y el inferior, siendo el inferior el principal [...] en el módulo superior el logo, configuraciones de modo oscuro/claro y el botón afiliarse para personas sin cuenta; para las personas con cuenta su nombre y su rango [...] en el módulo inferior los botones de directorio, abre tu negocio y los desplegables de bolsas y empleo, que se desplegarán al presionar [...] los estados conocidos de la de escritorio salvo atención [...] el estado scroll solo se compactará al hacer scroll hacia abajo; al hacer scroll hacia arriba volverá al tamaño de estado inicial».

Salió de un taller de dieciocho agentes sobre el repositorio en `09e8c17`: seis lectores (guardias, CSS y layout, Alpine, cuenta y roles, móvil real, marca e iconos), tres diseñadores con ángulos distintos (un solo DOM, la interacción táctil, el oficio del movimiento), tres jueces, una síntesis, cuatro críticos adversarios (guardias y DOM, iOS y Android reales, accesibilidad, fidelidad y oficio) y un revisor final que verificó cada crítica en el repositorio; §11 y §12 dicen cuáles entraron y cuáles no. Habla el idioma de la Parte I (cromo, bandeja, módulo, hoja, portador, guardia, rotura, token) y respeta sus restricciones salvo la primera, que aquí cambia y se dice por qué (§3.2, D-M9). Sua aprobó las dieciocho recomendaciones el 6 sep («desde D-M1 hasta la D-M18 apruebo y apruebo todas las recomendaciones que propones»); el plan de implementación se deriva de aquí y lo que no está aquí no se construye.
---

## 1. Qué es y qué no es

**Es** el rediseño de la barra pública **por debajo de 64rem**: dos módulos de vidrio de la familia de la píldora de escritorio (velo y desenfoque por token, filo de luz, apoyo, resortes `linear()`), un **módulo superior** con marca, tema y cuenta, y un **módulo inferior** siempre visible con los cinco destinos de primer nivel y dos hojas que suben al tocar. Dos estados, `inicial` y `scroll`; el segundo compacta al bajar y devuelve al subir. El inferior es el módulo principal, como pidió Sua.

**No es**:

- Un cambio del escritorio. De 64rem para arriba no cambia ni una clase pinzada de la `<nav>` (`NavbarTresEstadosTest.php:500-504`); lo único que el escritorio gana es que sus hojas se cierran al desplazar 24 px sin foco dentro y al volver del bfcache (§6.3, D-M12).
- Una segunda navegación. Todo se pinta desde `$enlacesDirectos` y `$grupos` de `navbar.blade.php:14-37`, como hoy lo hace el panel (`:292-323`).
- Una ampliación de alcance: la barra móvil ya existía (cabecera de 56 px, hamburguesa, panel en plano, barra lateral de tema) y se rediseña. La decisión de producto que cambia es una: seis destinos plegados pasan de un toque a dos a cambio de una barra siempre visible (D-M11), y se anota fechada en `encargo.md` §13.

**Punto de partida, medido en el código**: header `cromo cromo-fijo z-40` (`navbar.blade.php:123`) con el vidrio en la propia `<nav class="bandeja">` (`app.css:473-482`), cabecera de 56 px por suma de utilidades (`navbar.blade.php:139,146-148,220-223`; `app.css:449-453`), panel `#menu-movil` absoluto bajo la cabecera (`:260-268`), hamburguesa (`:220-243`) y barra lateral de tema fija abajo a la derecha (`barra-tema.blade.php:23`), que ofrece Claro y Oscuro a un toque (`:29-47`).

## 2. Decisiones tomadas (6 sep 2026, Sua)

Dieciocho, presentadas a Sua con opciones y recomendación el 6 sep; **Sua eligió la recomendación en las dieciocho** ese mismo día. Las cinco que el encargo obligaba a plantear son D-M1 (qué son «bolsas y empleo»), D-M2 (Eventos), D-M4 (la entrada del anónimo a su cuenta), D-M5 (el chip de idioma) y D-M6 (la barra lateral de tema). Tres nacieron de la crítica: D-M16 (iconos en las pestañas), D-M17 (tema en popover o a un toque) y D-M18 (el velo del vidrio móvil). Las opciones se conservan como historia de lo que se consideró.

| # | Pregunta | Opciones consideradas | Elegido (la recomendación) y porqué |
|---|---|---|---|
| **D-M1** | «Los desplegables de bolsas y empleo» del brief: ¿son Bolsas y El gremio, o un desplegable Empleo? | (1) Dos hojas, Bolsas (Empleo, Artistas, Proveedores) y El gremio (Quiénes somos, Boletín, Contacto), los dos grupos reales de navbar.blade.php:20-37 · (2) Un desplegable Empleo aparte (deja sin sitio a Artistas, Proveedores, Quiénes somos, Boletín y Contacto en móvil y diverge del escritorio) | **Bolsas y El gremio.** Empleo ya es la primera fila de Bolsas, y NavegacionAgrupadaTest:43-46 y :199-214 prohíben a propósito que escritorio y móvil lean arreglos distintos. |
| **D-M2** | Eventos no está en la lista de Sua para el módulo inferior, pero es destino de primer nivel y /eventos exige dos aria-current en el header. ¿Dónde va? | (1) Quinta pestaña, en el orden de escritorio: Directorio, Abre tu negocio, Eventos, Bolsas, El gremio (64 px por pestaña a 320, 72 a 360, 78 a 390) · (2) Cuatro pestañas y Eventos como primera fila de la hoja de El gremio solo en móvil (un toque más a un destino de primer nivel; la guardia sigue verde) · (3) Cuatro pestañas y Eventos solo en el pie (NavegacionAgrupadaTest:343-344 en rojo; cambiarla escondería la pérdida) | **Quinta pestaña.** Mismo reparto que el escritorio, ningún destino de primer nivel a dos toques en el aparato de la demo, y quitarlo no gana ancho: «Abre tu negocio» tampoco cabe en una línea con cuatro. |
| **D-M3** | «Abre tu negocio» mide 88-91 px a 11 px y no cabe en una línea en ninguna pestaña de teléfono. ¿Cómo se pinta? (depende de D-M16) | (1) Dos líneas («Abre tu / negocio») con text-balance y sin recorte; todas las pestañas reservan dos líneas de la escala (36,3 px) para alinear los iconos; nada se acorta · (2) Clave 'corto' => 'Tu negocio' en el mismo arreglo, solo para la pestaña (rompe «reagrupar y no acortar» de menu-grupo.blade.php:4-9, da dos nombres accesibles al mismo destino y a 320 tampoco cabe) | **Dos líneas.** Conserva la etiqueta que el gremio decidió, cabe a 320 y sobrevive al espaciado de texto de WCAG 1.4.12 porque el rótulo no se recorta. |
| **D-M4** | El brief no nombra «Mi cuenta» para el anónimo y con el logotipo, la pastilla y el tema no cabe un cuarto control en 360 px. ¿Dónde entra un afiliado sin sesión desde el teléfono? | (1) Fila «Entrar como afiliado» al final de la hoja de El gremio, declarada en el arreglo $grupos de navbar.blade.php (solo @guest, a mi-cuenta.entrar), más un enlace «Entrar a mi cuenta» en la columna El gremio del pie · (2) Icono user-circle de 44x44 en el módulo superior, a la izquierda de Afíliate; obliga al isotipo por debajo de 384 px, así que el teléfono de 360 pierde el logotipo en inicial · (3) Solo en el pie | **Fila en la hoja de El gremio más el pie.** Respeta la lista de Sua para el módulo superior, no le quita el logotipo al teléfono más común, se declara una sola vez con el resto de la navegación, y entrar es una acción rara que queda a dos toques en la zona buena del pulgar. |
| **D-M5** | ¿Se ve el chip de idioma (ES, que se ve y no funciona a propósito) en móvil? | (1) Oculto por debajo de 64rem (max-lg:hidden en el literal de la raíz de control-idioma.blade.php:31); sigue en el DOM para las guardias · (2) Visible, quitando 50 px al nombre o a Afíliate | **Oculto en móvil.** Sua no lo listó, no hace nada todavía (D1 de la Parte I) y en 360 px no sobra un solo control. |
| **D-M6** | ¿Se retira barra-tema.blade.php con sus reglas .tema-lateral* y el token --duracion-cromo? | (1) Sí: el tema vive en el módulo superior por control-tema; se borran el aside, sus 80 líneas de CSS y el token sin consumidor; se reescriben las dos guardias que la exigían y el selector del apartado de 7rem en el mismo commit · (2) Conservarla oculta por si acaso (código muerto con dos guardias que mienten y la esquina del módulo inferior ocupada) | **Sí.** Ocupa la esquina del módulo inferior, su vidrio va dentro de un filter que lo anula, y dejarla es el tipo de resto que este proyecto ya pagó. |
| **D-M7** | La marca en el módulo superior: ¿logotipo o isotipo, en cada estado y con sesión? | (1) Logotipo completo (h-7) para el anónimo en inicial; isotipo «ab» en scroll (el cruce logo-doble de la D6 de escritorio), con sesión siempre y por debajo de 360 px siempre; precarga del isotipo sin media · (2) Logotipo siempre y el nombre truncado por debajo de 96 px (corta casi todos los nombres reales) · (3) Logotipo reducido a 24 px de alto (baja la marca por debajo de cualquier tamaño que hoy se pinta) | **Isotipo en scroll, con sesión y por debajo de 360; logotipo para el anónimo en inicial.** Es el mismo gesto que el escritorio con los dos archivos del kit sin recortar ni recolorear, y es lo único que honra «nombre y rango» sin truncar nombres reales. |
| **D-M8** | Con sesión, «aparecerá su nombre y su rango»: ¿se toca el chip, y qué rango lleva el asociado? | (1) El chip (avatar + nombre + rango) es el botón de la hoja de cuenta de siempre (#menu-cuenta con Cerrar sesión por POST y @csrf), anclada al módulo en móvil para no desbordar a 320; el rango del asociado es el nombre de su establecimiento truncado por CSS, con «Afiliado» de respaldo; con dos roles muestra el mayor · (2) Chip como texto plano (un dueño en el teléfono no tendría cómo cerrar sesión con JavaScript) · (3) Rango genérico «Afiliado» siempre y el establecimiento solo en la hoja | **El chip abre la hoja de cuenta; rango = establecimiento truncado.** Es el mismo componente y la misma hoja del escritorio sin duplicar ids ni formularios, y el establecimiento es la identidad que le importa al dueño. |
| **D-M9** | El módulo inferior: ¿un segundo <nav> con aria-label propio, o un <div> para no tocar la restricción «un solo <nav>» de la Parte I? ¿Y cómo se llaman los dos landmarks? | (1) Segundo <nav> dentro del header llamado «Navegación principal» (bajo 64rem es el único que contiene navegación) y la primera <nav> cambia su etiqueta a «Marca y cuenta» por x-bind en móvil; NavbarTresEstadosTest:394 pasa a contar dos y la restricción 1 de la Parte I se actualiza con fecha · (2) Segundo <nav aria-label="Secciones del sitio"> dejando «Navegación principal» a la primera (que bajo 64rem solo contiene logo y cuenta: el rotor de VoiceOver cae en un bloque sin secciones) · (3) Un <div role="navigation"> (esquiva la guardia con ARIA en vez del elemento nativo) | **Segundo <nav> llamado «Navegación principal» con la primera renombrada por ancho.** Los lectores de pantalla listan los landmarks por su etiqueta; es lo correcto en móvil y lo honesto con la guardia. |
| **D-M10** | ¿Se declara viewport-fit=cover con env(safe-area-inset-*)? | (1) Sí: el vidrio corre hasta el borde físico; el inferior paga el inset abajo fuera de la caja de 44 px y de toda transición, el header el de arriba, y body, header e inferior pagan los laterales para todo el documento en apaisado (con cover, main, pie y formularios también quedan bajo la muesca) · (2) No: en iPhone con la barra de Safari plegada el indicador de inicio se pisa con las pestañas y el vidrio termina antes del borde, pero nada del documento queda bajo el hardware | **Sí.** Es el patrón nativo que el brief imita; el coste real (compensar todo el documento, no solo los módulos) queda resuelto en una regla de body y se mide en 844x390; todo funciona igual sin cover porque cada env() lleva respaldo 0. |
| **D-M11** | MenuMovilTest protege un panel que desaparece y prohíbe exactamente lo que el brief pide; los seis destinos plegados pasan de un toque a dos. ¿Se acepta el coste y se reescribe la guardia? | (1) Sí: un toque más en seis destinos a cambio de una barra siempre visible; MenuMovilTest se renombra con git mv a NavbarMovilTest y se reescribe entera con la decisión invertida en el docblock (CLAUDE.md exige esta aprobación para retirar pruebas) · (2) No: volver al panel en plano bajo la cabecera (el panel y las hojas no pueden convivir: aria-current y Afíliate saldrían tres veces) | **Sí, renombrar y reescribir.** Es lo que el brief pide; la guardia vieja lo documentó como decisión medida y la nueva lo escribe al revés, con la razón, para que nadie lo deshaga sin saberlo. |
| **D-M12** | Las hojas del móvil se cierran tras 24 px de scroll (salvo con el foco dentro), al volver del bfcache y por pointerdown fuera. ¿También las de escritorio? | (1) Sí, uniforme: los tres cableados van en las cuatro vistas con desplegable; un popover de escritorio abierto por hover se cierra tras 24 px de rueda, pero nunca con el foco de teclado dentro · (2) Solo bajo 64rem, con una condición de ancho en cada vista | **Uniforme.** Es lo que hace un menú nativo, evita una segunda rama por ancho en cuatro archivos, y es un cambio de escritorio pequeño que se enseña a Sua y se graba en la verificación de 1440x900. |
| **D-M13** | En el estado scroll el módulo inferior pasa de 68 a 48 px plegando los rótulos: quedan iconos solos. ¿Se acepta, o se compacta solo el relleno? (depende de D-M16) | (1) Plegar los rótulos (grid-template-rows a 0fr más opacidad): la única compactación real que no toca los 44 px del objetivo; se deshace con 12 px hacia arriba desde cualquier parada; abrir una hoja no cambia el tamaño · (2) Solo altura (68 a 56) conservando los rótulos; compactación mínima | **Plegar los rótulos.** Es lo que el brief describe (compactar, sutil, sin separar módulos) y lo que hace una barra de pestañas nativa; si en la demo se echan en falta los nombres, la salida es no plegar y solo cambia un bloque de CSS. |
| **D-M14** | Teléfono apaisado (844x390): 56 + 68 + 21 px de cromo sobre 390 de alto. | (1) Compacto de nacimiento por media query (48 + 48, rótulos plegados, hojas con max-height en svh y desplazamiento interno con pinch-zoom conservado); y a menos de 20rem de alto (zoom al 400 %) el inferior vuelve al flujo · (2) Volver en apaisado a la cabecera única de hoy (dos diseños para un mismo teléfono según cómo se sostenga) | **Compacto de nacimiento.** Un solo diseño en las dos orientaciones con las mismas piezas; son dos líneas de tokens y devuelven 24 px al hero donde de verdad faltan. |
| **D-M15** | El usuario demo de secretaría se llama literalmente «Secretaría del capítulo» (UsuarioSeeder.php:44): el chip diría «Sec. Secretaría del c…» a 360 px. | (1) Renombrarlo a un nombre de persona en el sembrador antes de la demo · (2) Dejarlo y aceptar la redundancia en la pantalla del directivo | **Renombrarlo.** Es un dato de semilla, no de producción, y la demo se hace en el teléfono con ese usuario. |
| **D-M16** | Anatomía de la pestaña: el brief dice «botones» y no nombra iconos; el escritorio no los lleva. ¿El módulo inferior lleva icono sobre rótulo, y cuáles? | (1) Icono de 24 px sobre rótulo de 11 px con los cinco iconos que el panel ya asigna a los mismos conceptos (building-storefront, clipboard-document-check, calendar-days, briefcase, user-group), contorno en reposo y sólido en la sección actual; módulo de 68 px · (2) Solo texto, como el escritorio, en una o dos líneas; módulo de 48 px (D-M3 y D-M13 cambian) | **Icono sobre rótulo con los iconos del panel.** Es la anatomía de una barra de pestañas nativa, la secretaría ya ve esos iconos a diario en /admin, y con los rótulos plegados en scroll el icono es lo único que queda: sin él no hay compactación posible. |
| **D-M17** | El tema en el módulo superior: hoy la barra lateral cambia Claro/Oscuro a UN toque; control-tema abre un popover de tres filas (dos toques) con Sistema. | (1) Popover con Claro, Oscuro y Sistema (el mismo control-tema del escritorio, con id único y un solo vocabulario) · (2) Botón de un toque que alterna el tema resuelto y deja Sistema fuera del móvil (un segundo componente y un segundo vocabulario) | **Popover.** Coherente con el escritorio y con la decisión del 3 sep de ofrecer Sistema; el toque de más lo paga quien cambia de tema, que es raro, y Sua lo verá en el teléfono antes de la demo. |
| **D-M18** | Material del vidrio móvil: los rótulos de 11 px van sobre un velo pensado para 14 px sobre fondo de página (72 % claro / 62 % oscuro, tokens.css:180 y :334) y no llegan a 4,5:1 sobre fotos; y el desenfoque de 20 px se aprobó el 5 sep (c650f3a). | (1) Velo móvil al 88 % claro / 85 % oscuro por reasignación de --asb-cromo-velo bajo 64rem (mínimo calculado para AA del acento a 11 px, con guardia que lo recalcula como VeloDelHeroTest), rango del chip en text-tenue, material sólido bajo prefers-contrast: more; el desenfoque se queda en 20 px hasta medirlo con el video corriendo · (2) Conservar el 72/62 % y pintar los rótulos en text-tinta/text-fuerte con el rojo solo en el icono (dos vocabularios de sección por ancho) y bajar el desenfoque a 14 px de antemano | **Velo 88/85 con guardia; desenfoque intacto hasta medir.** WCAG 1.4.3 no es negociable a 11 px, el precedente del proyecto es el velo del hero calibrado y guardado, y bajar el desenfoque sin medir tocaría el vidrio aprobado por una cifra que nadie tiene. |
## 3. Restricciones que gobiernan el diseño

1. **Todo lo que se cuenta se cuenta dentro del primer `<header>`**: los nueve `href` (`NavegacionAgrupadaTest.php:159-176`), los dos `aria-current="page"` (`:339-344`), el `href="/afiliate"` (`NavbarTresEstadosTest.php:632-636`), el enlace encendido del calendario (`CalendarioDeEventosTest.php:383-388`). El módulo inferior es descendiente del `<header>`.
2. **Los tres módulos de escritorio son los tres hijos con «modulo» en la clase de la primera `<nav>`** (`NavbarTresEstadosTest.php:395`) y el primer `//nav/div[gap-1]` es el bloque de escritorio (`NavegacionAgrupadaTest.php:96`). **La restricción 1 de la Parte I («un solo `<nav>`») cambia**: el móvil gana un segundo `<nav>`, porque por debajo de 64rem la primera solo contiene logo y cuenta (el módulo principal es `hidden lg:flex`, `navbar.blade.php:155`) y la navegación real quedaría sin landmark. `:394` se actualiza con esa razón (D-M9).
3. **`.cromo` no puede ser bloque contenedor**: lleva `transform: translateY(0)` (`app.css:414`) con transición (`:417-418`), que solo sirven a `.cromo-oculto` (`:594-597`, `:1251-1254`), clase que ninguna vista usa. Se retiran los tres; `isolation: isolate` (`:412`) se queda.
4. **Ningún ancestro de una hoja de vidrio es raíz de fondo** (Filter Effects 2): ni `backdrop-filter` (`.bandeja`, `app.css:476-477`), ni `filter` (la lección de `.tema-lateral`, `:778`), ni `opacity` < 1, **ni `view-transition-name`**: CSS View Transitions 1 §2.1.1 dice que todo elemento cuyo `view-transition-name` computado no sea `none` «at any time» forma contexto de apilamiento, se aplana en 3D **y forma un backdrop root**. Por eso el vidrio de cada módulo va en su `::before` y ningún módulo lleva nombre de transición de vista (crítica confirmada, §12).
5. **Toda duración y curva sale de tokens; toda geometría nueva es `--asb-*` con anulación bajo movimiento reducido; el vidrio es `var(--asb-cromo-velo)` + `var(--asb-cromo-desenfoque)`; el tema se cambia solo por `$store.tema.elegir()`; la marca no se recolorea ni se recorta; anónimo no ve `menu-cuenta` ni «Cerrar sesión»; cerrar sesión es `POST` con `@csrf` y `<noscript>`; sin dependencias ni carpetas nuevas.** Idénticas a la Parte I §3.2-3.9.
6. **`regla()` de `NavbarTresEstadosTest.php:691-698` toma la PRIMERA aparición de cada `selector {`** y `strpos` casa dentro de selectores compuestos. Todo el CSS móvil nuevo va en un **segundo** bloque `@media (max-width: 63.999rem)` colocado **después** del bloque `@media (hover: hover) and (pointer: fine)` (`app.css:749-757`), dentro de `@layer components` (`:183`). El primer bloque móvil (`:473-482`) solo cambia lo que su guardia lee (§8.1).
7. **Las utilidades de `@layer utilities` ganan a `@layer components`** (`app.css:256-257`, `:295`): una regla de componente no pisa a `px-4`, `scroll-pt-24` ni `relative`. Por eso los insets laterales van en `.cromo-fijo` (sin utilidades de relleno) y no en `.bandeja`, el `scroll-padding-top` móvil exige que `<html>` pase a `lg:scroll-pt-24`, y el reancle de la hoja de cuenta se hace con `max-lg:static` (utilidad contra utilidad).
8. **Las guardias que leen archivos crudos leen también los comentarios**: `assertStringNotContainsString` sobre un archivo se pone rojo con un comentario que nombre la cadena prohibida. Todo comentario nuevo «nombra y no pega» (`navbar.blade.php:206-207`, `menu-grupo.blade.php:96-97`).
9. **La escala tipográfica gobierna**: `--text-2xs` es 0.6875rem con `line-height: 1.65` (`app.css:72-74`) y ninguna vista nueva escribe `leading-*` (`:62-66`: cada `leading-*` suelto desgobierna la escala y hay 74 por retirar). Las cifras de alto se calculan con 1.65, no con 1.25.
10. **Las cifras de este documento son aritmética sobre dimensiones declaradas** y sobre dos lectores de la Poppins servida (fontTools y canvas, que difieren hasta 3 px). Ninguna entra en `ObjetivoTactilTest` ni en el expediente sin medirse en Chromium ese día a 320, 360 y 390 (§8.3).

## 4. Arquitectura

### 4.1 DOM

```
<header data-estado="inicial|scroll" x-data="{ ... }" class="cromo cromo-fijo z-40">
  <nav class="bandeja ..." x-bind:aria-label="esEscritorio ? 'Navegación principal' : 'Marca y cuenta'"
       aria-label="Navegación principal">                                          = MODULO SUPERIOR bajo 64rem
    <a class="modulo modulo-logo ... min-h-11 [marca-compacta]">  logotipo + isotipo (logo-doble)
    <div class="modulo modulo-principal hidden ... lg:flex">      solo escritorio, intacto
    <div class="modulo modulo-cuenta flex min-w-0 ...">           VISIBLE en los dos anchos
      @guest  Mi cuenta (max-lg:hidden) · Afíliate (pastilla)
      @auth   <x-publico.menu-usuario />   chip avatar + nombre + rango; hoja #menu-cuenta
      <x-publico.control-tema />           sol/luna; hoja #popover-tema
      <x-publico.control-idioma />         raíz con max-lg:hidden
  </nav>
  @auth <noscript> cerrar sesión POST + @csrf </noscript>                            intacto
  <nav id="menu-movil" class="modulo-inferior lg:hidden" aria-label="Navegación principal">   = MODULO INFERIOR
    <div class="pestanas mx-auto flex w-full max-w-xl items-stretch">
      <a class="pestana ...">Directorio</a> <a>Abre tu negocio</a> <a>Eventos</a>
      <x-publico.menu-grupo variante="pestana" ... :pie="$grupo['pie'] ?? []">  Bolsas, El gremio
    </div>
  </nav>
</header>
```

Por qué así:

- **Un solo DOM para la cuenta, el tema y el idioma.** El `modulo-cuenta` deja de ser `hidden lg:flex` (`navbar.blade.php:188`): ya trae ids únicos, `@guest`/`@auth`, el `POST` con `@csrf` (`menu-usuario.blade.php:132-143`) y los siete cableados. No hay segundo `menu-usuario`, ni `popover-tema-movil`, ni segundo formulario.
- **Lo que no puede compartirse.** El `modulo-principal` está fijado letra a letra (`NavbarTresEstadosTest.php:504`, `ObjetivoTactilTest.php:181-185`) y sus hojas cuelgan hacia abajo (`NavegacionAgrupadaTest.php:291`). El inferior se pinta por segunda vez **desde los mismos arreglos**, incluida la fila «Entrar como afiliado», que se declara en `$grupos` (§6.3) y no en el componente.
- **Los landmarks dicen la verdad por ancho.** Bajo 64rem la primera `<nav>` solo expone logo y cuenta: VoiceOver y TalkBack listan los puntos de referencia por etiqueta, y quien eligiera «Navegación principal» caería en un bloque sin secciones. El inferior se llama «Navegación principal» (de 64rem arriba es `lg:hidden` y no existe para el lector) y la primera cambia su etiqueta por `x-bind` sobre el `esEscritorio` reactivo de §4.2 (el `<nav>` tiene `x-data="escena"`, `navbar.blade.php:134`, que anida en el del header y lo alcanza por la cadena de ámbitos). Sin JavaScript quedan dos «Navegación principal»: degradación aceptable. Ninguna guardia pinza esas etiquetas (grep en `tests/`: solo tema e idioma).
- **El id `menu-movil` se hereda** al `<nav>` inferior: `NavbarTresEstadosTest.php:424-437` lo usa como marcador de recorte y sigue valiendo.
- **Ningún hijo del segundo `<nav>` lleva «modulo» ni «gap-1»**: `:395` sigue contando tres y `NavegacionAgrupadaTest.php:96` sigue tomando el bloque de escritorio.
- **Hojas de grupo con id propio** (`menu-bolsas-movil`, `menu-el-gremio-movil`): `NavegacionAgrupadaTest.php:233` exige un solo `#menu-bolsas`, `:147` compara sus filas exactas y el regex de `:242` cierra con la comilla.

Qué pasa con cada pieza de hoy:

| Pieza | Destino | Razón |
|---|---|---|
| Hamburguesa (`navbar.blade.php:220-243`) | Se retira | Sin panel no hay nada que abrir. Su fila de `ObjetivoTactilTest.php:124-128` se borra en el mismo commit |
| Panel `#menu-movil` (`:260-367`) | Se retira; el id pasa al `<nav>` inferior | El brief invierte «un toque por destino, sin anidar» de `MenuMovilTest.php:50-65` (D-M11) |
| `menuMovil` y sus tres salidas (`:61`, `:117-119`, `:121`) | Se retiran | Sin panel no hay capa que cerrar. `MenuMovilTest.php:45-47` y `NavbarTresEstadosTest.php:371-372` cambian juntas |
| `x-on:resize.window` (`:119`) | Se sustituye por `matchMedia('(min-width: 64rem)')` en `init()` | Una sola frontera, la del CSS; `resize` dispara al plegarse la barra de Safari y al abrirse el teclado |
| `<noscript>` de cerrar sesión (`:249-258`) | Intacto | Única salida sin JavaScript. Sin JS el header crece una fila: 56 + 44 = 100 < 112 del apartado |
| `barra-tema.blade.php`, `<x-publico.barra-tema />` (`publico.blade.php:196`), `.tema-lateral*` (`app.css:777-859`), `--duracion-cromo` (`tokens.css:205`) | Se borran (D-M6) | El tema vive en el superior. Su vidrio vivía bajo `filter: drop-shadow` (`:778`) que lo dejaba sin nada que desenfocar; ocupa la esquina del inferior; el token quedaría sin consumidor |
| `.cromo-fijo + .tema-lateral + main > section:first-child:not(.hero-portada)` (`app.css:430-432`) | Se reescribe con `~` | Sin el `<aside>` el combinador adyacente deja de casar y todas las páginas salvo la portada pierden el apartado, sin que nada se ponga rojo (grep de `7rem` en `tests/`: cero) |
| `.cromo` `opacity`/`transform` y `.cromo-oculto` | Se retiran | §3.3. La Parte I §8 decía «conserva `.cromo-oculto`»: se corrige con fecha |
| Precarga del isotipo con `media="(min-width: 64rem)"` (`publico.blade.php:51`) | Pierde el `media` | El móvil cruza al isotipo en `scroll` y con sesión (§6.1). `NavbarTresEstadosTest.php:190` cambia y la prueba de `:172` se renombra |
| `<html class="scroll-pt-24">` (`publico.blade.php:2`) | Pasa a `lg:scroll-pt-24` | Bajo 64rem el `scroll-padding-top` lo da el token del alto del superior más el inset (§6.4); una utilidad sin variante ganaría siempre al componente (§3.7) |
| `<body class="min-h-screen ...">` (`:189`) | Pasa a `min-h-svh` | `100vh` en iOS es el viewport grande: /pago/estado, /mi-cuenta/entrar, /sesion-equivocada y /errors/404 (`min-h-[65..80vh]`, en flujo) se desplazan 80-110 px sin contenido, y con la máquina por dirección ese gesto compactaría la barra en una página de un párrafo. `svh` es estable y es lo que ya usa la portada (`app.css:890`). Ninguna guardia lo pinza |

### 4.2 Estado en Alpine

Se conservan letra a letra los literales de `NavbarTresEstadosTest.php:341-368`: `get estado() {`, `punteroFino() {`, `sincronizar() {`, `atender() {`, `soltar() {`, `alternarAtencion() {`, `return this.atendiendo ? 'atencion' : 'scroll';`, `this.desplazado = actual > 8;`, `Math.abs(actual - this.scrollAlAtender) > 24`, `}, 280);`, los tres cableados y `data-estado="inicial"` servido. `menuMovil` desaparece; `cromo-compacto`/`cromo-expandido` siguen vetados (`:373-374`) y no hacen falta.

```blade
<header x-data="{
            desplazado: false,
            atendiendo: false,
            compacta: false,
            teclado: false,
            esEscritorio: true,
            ancla: 0,
            altoReferencia: 0,
            cierre: null,
            scrollAlAtender: 0,
            campo: 'input:not([type=checkbox]):not([type=radio]):not([type=submit]), textarea, select, [contenteditable=true]',
            init() {
                // Una sola frontera y es la del CSS: 64rem, no un ancho en píxeles.
                // Cruzarla (girar un iPad) devuelve la barra a inicial y cierra
                // todas las hojas: ceder(null) cierra a todos, null no es raíz de nadie.
                const consulta = window.matchMedia('(min-width: 64rem)');
                this.esEscritorio = consulta.matches;
                consulta.addEventListener('change', (evento) => {
                    this.esEscritorio = evento.matches;
                    this.compacta = false;
                    this.$dispatch('desplegable-abierto', null);
                });
                this.ancla = this.posicion();
                this.altoReferencia = window.visualViewport?.height ?? window.innerHeight;
                window.matchMedia('(orientation: portrait)').addEventListener('change', () => {
                    this.altoReferencia = window.visualViewport?.height ?? window.innerHeight;
                });
                window.visualViewport?.addEventListener('resize', () => this.medirTeclado());
            },
            get estado() {
                if (! this.desplazado) {
                    return 'inicial';
                }

                // Por debajo de 64rem no hay puntero que atender: compacta o no.
                // Por ancho y no por puntero: un ratón en una ventana estrecha sí
                // dispara mouseenter y pasar por la barra la descompactaría.
                if (! this.esEscritorio) {
                    return this.compacta ? 'scroll' : 'inicial';
                }

                return this.atendiendo ? 'atencion' : 'scroll';
            },
            punteroFino() {
                return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            },
            menosMovimiento() {
                return document.documentElement.classList.contains('sin-desplazamiento');
            },
            // Dentro del documento. El navegador acota scrollY con el alto
            // VIGENTE del viewport (innerHeight, que en iOS crece al plegarse la
            // barra de direcciones): con clientHeight el tope quedaba 80-110 px
            // por encima del real y el rebote elástico del final pasaba el clamp.
            posicion() {
                const tope = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);

                return Math.min(Math.max(window.scrollY, 0), tope);
            },
            sincronizar() {
                const actual = Math.max(window.scrollY, 0);

                this.desplazado = actual > 8;

                if (! this.esEscritorio) {
                    this.compactar();
                }

                // Con dedo, desplazarse es soltar: 24 px desde que se abrió.
                if (this.atendiendo && ! this.punteroFino() && Math.abs(actual - this.scrollAlAtender) > 24) {
                    this.atendiendo = false;
                }
            },
            // La DIRECCIÓN decide, con histéresis. El ancla sigue al EXTREMO del
            // recorrido en el sentido vigente (el punto más bajo bajando, el más
            // alto subiendo), así que volver cuesta siempre 12 px desde donde el
            // dedo paró, e irse 24: el temblor y el subpíxel inercial no llegan.
            // Los dos extremos del documento son zona muerta (rebote elástico),
            // un salto mayor de 200 px no es un gesto, y bajo movimiento
            // reducido no hay estado scroll: compactar es animación ligada al
            // gesto (WCAG 2.3.3), y no se pierde nada por no hacerlo.
            compactar() {
                if (this.menosMovimiento()) {
                    this.compacta = false;

                    return;
                }

                const dentro = this.posicion();
                const tope = Math.max(document.documentElement.scrollHeight - window.innerHeight, 0);
                const recorrido = dentro - this.ancla;

                if (dentro <= 8) {
                    this.compacta = false;
                    this.ancla = dentro;

                    return;
                }

                if (dentro >= tope || Math.abs(recorrido) > 200) {
                    this.ancla = dentro;

                    return;
                }

                if ((this.compacta && recorrido > 0) || (! this.compacta && recorrido < 0)) {
                    this.ancla = dentro;

                    return;
                }

                if (recorrido > 24) {
                    this.compacta = true;
                    this.ancla = dentro;

                    return;
                }

                if (recorrido < -12) {
                    this.compacta = false;
                    this.ancla = dentro;
                }
            },
            campoEnfocado() {
                return document.activeElement?.matches(this.campo) ?? false;
            },
            esCampo(elemento) {
                return elemento?.matches?.(this.campo) ?? false;
            },
            // Teclado virtual: las DOS señales a la vez (foco en un campo y
            // viewport visual encogido más de 150 px). La referencia es el alto
            // MAYOR visto en esta orientación, no innerHeight: donde el teclado
            // encoge también el viewport de layout la resta contra innerHeight
            // daba cero y la barra se pegaba encima del teclado.
            medirTeclado() {
                const visual = window.visualViewport;

                if (visual !== undefined && visual.height > this.altoReferencia) {
                    this.altoReferencia = visual.height;
                }

                this.teclado = ! this.esEscritorio
                    && this.campoEnfocado()
                    && visual !== undefined
                    && (this.altoReferencia - visual.height) > 150;
            },
            atender() { ...sin cambios... },
            soltar() { ...sin cambios, con su `}, 280);`... },
            alternarAtencion() { ...sin cambios... },
        }"
        x-init="sincronizar()"
        x-on:mouseenter="atender()"
        x-on:mouseleave="soltar()"
        x-on:scroll.window.passive="sincronizar()"
        x-on:focusin.window="$nextTick(() => medirTeclado())"
        x-on:focusout.window="if (! esCampo($event.relatedTarget)) teclado = false"
        x-bind:data-estado="estado"
        x-bind:data-teclado="teclado ? 'abierto' : null"
        x-bind:class="{ 'cromo-apoyado': desplazado }"
        data-estado="inicial"
        class="cromo cromo-fijo z-40">
```

Notas: Alpine ejecuta `init()` antes de `x-init`, así que `sincronizar()` (`navbar.blade.php:113`) encuentra el ancla puesta. Escribir el mismo valor no repinta. `desplazado` sigue mandando sobre `cromo-apoyado`: a media página, subir devuelve `inicial` sin apagar el apoyo, que es lo que Sua describió. Al cargar con `#ancla` o al volver por bfcache la barra arranca desplegada (dirección, no posición). Sin JavaScript el servidor sirve `inicial`. **No hay `reposar()`**: abrir una hoja no cambia el tamaño de la barra (dos resortes a la vez sobre el mismo canto, y el rótulo desplegándose bajo el dedo, era un movimiento que Sua no pidió); la hoja se ancla al módulo tal como esté. `focusout` no apaga la retirada si el foco pasa a otro campo: en /afiliate son cinco parpadeos de la barra evitados. `menosMovimiento()` lee la clase que el `<head>` pone antes del primer pintado (`publico.blade.php:149-151`), la misma señal de `.revelar` (`app.css:1045`).

## 5. Vocabulario de movimiento

Lo que cambia de tamaño va con el resorte suave y el reloj de estado; lo que aparece, con el resorte vivo y el reloj de los popovers; lo que se va, por el cajón y rápido. Ningún reloj ni curva nuevos.

### 5.1 Duraciones y curvas (`tokens.css:28-40`, `:200-207`)

| Elemento | Propiedades | Reloj | Curva |
|---|---|---|---|
| `.bandeja` (superior) | `height` | `--duracion-estado` 620 | `--ease-rebote-suave` |
| `.pestanas` (fila del inferior) | `height` | `--duracion-estado` 620 | suave |
| `.pestana__rotulo` | `grid-template-rows`, `min-height` | 620 | suave |
| `.pestana__rotulo` | `opacity` | `--duracion-entrada` 200 / `--duracion-salida` 160 | `--ease-color` |
| `.logo-doble*` | `max-width`, `opacity`, `scale` | los de hoy (`app.css:557-568`) | suave / vivo |
| Hojas del inferior | `opacity`, `scale`, `translate` vía `x-transition` | `--duracion-rebote` 520 entrar, `--duracion-salida` 160 salir | `--ease-rebote-vivo` / `--ease-cajon` (receta de `control-tema.blade.php:58-63`) |
| `.modulo-inferior` ante el teclado | `translate`, `opacity`, `visibility` | `--duracion-panel` 240 | cajón |
| Rayas de apoyo | `opacity` | `--duracion-boton` 140 | color |
| Acuse de pestaña y fila | tinte de `.fila-pulsable:active` (`app.css:316-319`) | 0 ms al bajar, 140 al soltar | color |

`--duracion-rebote` sigue prohibido en `app.css` (`NavbarTresEstadosTest.php:310`). `--duracion-cromo` se retira con su único consumidor. `--duracion-panel` se conserva y su comentario pasa a nombrar sus **tres** consumidores reales: el revelado de la portada (`app.css:1040-1042`), la capa del video (`:1140`) y la retirada del inferior. El inset de la zona segura **no entra en ninguna propiedad transicionada**: en iOS salta de 0 a 34 px en mitad del gesto y una altura que lo incluyera se animaría 620 ms con sobreimpulso mientras el `padding` cambia de golpe. Nada anima `backdrop-filter` ni el color del vidrio.

### 5.2 Geometría y materiales nuevos (`tokens.css`, tras `--asb-escala-isotipo` en `:270`)

```css
    /*
     * Barra móvil en dos módulos. Los cuatro altos son LAYOUT, como
     * --asb-separacion-modulos: no se anulan bajo movimiento reducido (allí
     * simplemente no existe el estado scroll, §5.3). El estado `scroll` los
     * cambia reasignando los dos primeros sobre el header.
     */
    --asb-alto-modulo-superior: 3.5rem;            /* 56 px: lo que la cabecera mide hoy */
    --asb-alto-modulo-superior-compacto: 3rem;     /* 48 px: un control de 44 con 2 px por lado */
    --asb-alto-modulo-inferior: 4.25rem;           /* 68 px: icono 24 + 3 + rótulo a dos líneas de la escala (36,3) */
    --asb-alto-modulo-inferior-compacto: 3rem;     /* 48 px: icono solo, pestaña de 44 en flujo */
    --asb-alto-rotulo-pestana: calc(2 * 1.65 * 0.6875rem); /* la reserva de dos líneas de --text-2xs, sin leading-* suelto */

    /* Movimiento: la hoja sube desde la barra (gemelo positivo de
       --asb-desplazamiento-popover) y la barra se retira ante el teclado. */
    --asb-desplazamiento-hoja: 6px;
    --asb-retirada-barra: 100%;

    /* Velo de las hojas, una sola receta: `.dark` es el propio <html>
       (tokens.css:273), así que el var() de --asb-superficie ya resuelve al
       tema. Hasta hoy .hoja-flotante lo cableaba y la transparencia reducida
       no lo alcanzaba. */
    --asb-hoja-velo: color-mix(in oklab, var(--asb-superficie) 84%, transparent);

    /* Apoyo de un módulo que cuelga del canto INFERIOR: la receta del cromo,
       invertida. Dos recetas, como --asb-cromo-apoyo. */
    --asb-cromo-apoyo-inferior:
        0 -1px 0 rgb(11 9 10 / 0.07),
        0 -8px 24px rgb(11 9 10 / 0.06);
```

En `.dark` (tras `:337`): `--asb-cromo-apoyo-inferior: 0 -1px 0 rgb(255 255 255 / 0.1), 0 -8px 28px rgb(0 0 0 / 0.55);`.

**El velo del móvil se calibra para texto** (D-M18). Los rótulos de 11 px y el rango del chip van sobre el vidrio, y el único velo del sitio calibrado para texto es el del hero (`tokens.css:145-163`: mínimo 0,579 / 0,681 para AA, fijado en 0,8 con `VeloDelHeroTest`). El cromo va al 72 % claro (`:180`) y 62 % oscuro (`:334`), pensado para una barra de escritorio con texto de 14 px sobre fondo de página, no para 11 px sobre una foto del directorio. Aritmética con composición alfa sobre los hex de `tokens.css:100` y `:289` (se mide ese día): `--asb-acento` claro #b71f18 da 5,86:1 sobre fondo sólido (`:98-99`) y unos 3,0:1 sobre el velo al 72 % con negro detrás; en oscuro #f27166 da unos 1,9:1 sobre el 62 % con blanco detrás. Para que el acento a 11 px conserve 4,5:1 contra blanco y negro puros hacen falta unos 0,88 en claro y 0,85 en oscuro. Antes del bloque de transparencia reducida (`:360`), para que este siga ganando por orden:

```css
/* El velo del móvil sostiene texto de 11 px sobre fotos: mínimo calculado,
   no gusto (§5.2 de la Parte II). `NavbarMovilTest` lo recalcula leyendo
   este archivo. El desenfoque no cambia hasta medirlo con el video (D-M18). */
@media (max-width: 63.999rem) {
    :root {
        --asb-cromo-velo: color-mix(in oklab, var(--asb-fondo) 88%, transparent);
    }

    :root.dark {
        --asb-cromo-velo: color-mix(in oklab, var(--asb-fondo) 85%, transparent);
    }
}
```

En `@media (prefers-reduced-transparency: reduce)` (`:360-371`): `--asb-hoja-velo: var(--asb-superficie);`. En `@media (prefers-contrast: more)` (`:373-388`), en `:root` y en `:root.dark`: `--asb-cromo-velo: var(--asb-fondo); --asb-cromo-desenfoque: none; --asb-hoja-velo: var(--asb-superficie);` (hoy sube tenue y apagado pero deja el velo translúcido: quien pide más contraste seguiría leyendo 11 px a través de una foto). En `@media (prefers-reduced-motion: reduce)` (`:445-473`), tras `--asb-escala-isotipo: 1;`:

```css
        /* Barra móvil: sube la hoja y se retira la barra, movimiento; los
           altos son layout y se quedan. */
        --asb-desplazamiento-hoja: 0px;
        --asb-retirada-barra: 0%;
```

**Se retira `--asb-desplazamiento-panel`** (`:215`, `:448`): su único consumidor era el panel. Lo afirman tres pruebas, no dos: `MovimientoTest.php:46` y `:98`, y **`tests/Feature/Panel/ComponentesDelPanelTest.php:70`** (el panel de Filament nunca lo consumió: grep en `resources/css/filament`, cero); las tres pasan a `--asb-desplazamiento-hoja: 6px` / `0px`. Se reutilizan `--asb-escala-popover`, `--asb-escala-isotipo`, `--asb-cromo-desenfoque` (sin cambio: `blur(20px) saturate(180%)`, `:181`, el vidrio que Sua aprobó el 5 sep en `c650f3a`), `--asb-cromo-apoyo`, `--asb-fila-pulsada`.

### 5.3 Movimiento reducido

Bajo `prefers-reduced-motion` **no hay estado `scroll` en móvil**: `compactar()` devuelve `inicial` siempre (§4.2). Compactar por dirección es un movimiento repetido y ligado al gesto de desplazar (WCAG 2.3.3, «Animation from Interactions»), el precedente del proyecto es que `escena.seguir()` no hace nada bajo `reduceMovimiento()` (`app.js:88-91`) y el video no arranca (`:218`), y no se pierde nada por no compactar. Los altos siguen siendo tokens de layout (el apaisado compacto de nacimiento no es animación y se conserva, D-M14). Se anulan `--asb-desplazamiento-hoja` y `--asb-retirada-barra` (la barra se retira igual ante el teclado, por `visibility` y fundido: es funcional). Las duraciones no se tocan (`MovimientoTest.php:92-105`, `tokens.css:209-212`).

## 6. Los módulos

### 6.1 Módulo superior

| | Marca | Derecha |
|---|---|---|
| Anónimo, `inicial`, ≥ 22.5rem | logotipo `h-7` (28 × 153,5) | «Afíliate» (pastilla) · tema 44×44 |
| Anónimo, `scroll` | isotipo «ab» (cruce `logo-doble`, 40×28) | igual |
| Anónimo, < 22.5rem | isotipo siempre | igual |
| Con sesión, los dos estados | isotipo | chip: avatar + nombre + rango · tema |

Presupuesto (aritmética, §3.10): `px-4` 32 + logotipo 153,5 + pastilla 82 + `gap-2` 8 + tema 44 = **319,5**: cabe en 360 y 390; en 320 falta 0,5 px y por eso bajo 22.5rem el logo cruza. Con sesión y logotipo quedan 78 px para el nombre a 360: por eso **con sesión el módulo lleva el isotipo** (D-M7).

**Marca.** El cruce es el `logo-doble` que existe (`logo.blade.php:33-45`, `app.css:557-573`); sus reglas de estado viven solo en el bloque de 64rem (`:718-734`) y se duplican en el bloque móvil con `[data-estado="scroll"] .logo-doble` y `.marca-compacta .logo-doble`. D6 de la Parte I llevada al móvil: dos archivos del kit, ninguno recortado ni recoloreado. La precarga pierde su `media`. El enlace del logo gana `min-h-11` al final de su lista (hoy mide 40 px: `navbar.blade.php:146-148`); las cadenas pinzadas (`NavbarTresEstadosTest.php:503`, `ObjetivoTactilTest.php:104-108`) siguen siendo subcadenas.

```blade
<a href="{{ route('inicio') }}"
   @class([
       'modulo modulo-logo pulsable -my-1.5 flex shrink-0 items-center py-1.5 lg:justify-self-start lg:px-3',
       'min-h-11',
       'marca-compacta' => auth()->check(),
   ])
   aria-label="Inicio — ASOBARES Capítulo Quindío">
    <x-publico.logo doble alto="h-7 sm:h-8" />
</a>
```

**Tema (D-M17).** `x-publico.control-tema` tal cual (`control-tema.blade.php:31-82`): 44×44 medidos (`ObjetivoTactilTest.php:158-162`), sol o luna por la clase `dark`, popover con Claro, Oscuro y Sistema. Es un cambio respecto a hoy que se decide con Sua: la barra lateral ofrece Claro y Oscuro a **un** toque (`barra-tema.blade.php:29-47`, con `mouseenter`, patrón desterrado de las vistas, `:17-18`) y el popover cuesta dos; a cambio gana Sistema (decisión del 3 sep) y un solo vocabulario con el escritorio. El popover cuelga hacia abajo del superior y, con el vidrio de la bandeja en `::before`, desenfoca la página.

**«Afíliate» (anónimo).** La pastilla de escritorio dentro de `@guest`, cadena medida `after:absolute after:inset-x-0 after:-inset-y-1 after:content-['']` (`ObjetivoTactilTest.php:119-123`) y sus cuatro piezas (`:314-348`); se re-mide en la bandeja de 56 y de 48. El `href="/afiliate"` en el header pasa de **2 a 1** para el anónimo (`NavbarTresEstadosTest.php:632-636`).

**La entrada del anónimo (D-M4).** Una fila «Entrar como afiliado» al final de la hoja de El gremio, **declarada en `navbar.blade.php` dentro del grupo** (§6.2) y pintada solo en la variante `pestana` y solo `@guest`, enlazando a `mi-cuenta.entrar` (ahorra el 302 de `bootstrap/app.php:44-46`); más un enlace «Entrar a mi cuenta» en la columna «El gremio» del pie. El enlace «Mi cuenta» de escritorio sigue en el DOM con `max-lg:hidden` (`NavbarTresEstadosTest.php:441-442` lo sigue encontrando).

**Nombre y rango con sesión: el chip (D-M8).** Es el disparador de `menu-usuario.blade.php` (ya es `desplegable`, `:43-52`). El nombre deja de ser `hidden ... lg:block` (`:69`), sin `leading-*` (§3.9):

```blade
<button type="button"
        x-ref="disparador"
        x-on:click="alternar()"
        x-bind:aria-expanded="abierto ? 'true' : 'false'"
        aria-controls="menu-cuenta"
        class="pulsable -m-1 flex items-center gap-2 rounded-full p-1 min-w-0 text-tenue hover:text-tinta">
    {{-- El texto visible va PRIMERO y completo en el nombre accesible (WCAG
         2.5.3, la regla del chip de idioma): «pulsa Sec. Natalia» tiene que
         casar por prefijo. --}}
    <span class="sr-only">@if ($prefijoRol){{ $prefijoRol }} @endif{{ $usuario->name }}@if ($rol), {{ $rol }}@endif: configuración y sesión</span>
    <span aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-marca-500 text-xs font-bold tracking-wide text-white">{{ $iniciales }}</span>
    {{-- min-w-0: un hijo flex sin mínimo no encoge y empuja al tema fuera de
         la bandeja. Se trunca por CSS, nunca en el servidor (Str::take daría
         «Secretaría del»). El nombre entero sigue en la hoja. --}}
    <span aria-hidden="true" class="min-w-0 max-w-40 pr-1 text-left">
        <span class="block truncate text-sm font-medium">@if ($prefijoRol)<span class="text-apagado">{{ $prefijoRol }}</span> @endif{{ $usuario->name }}</span>
        <span class="block truncate text-2xs text-tenue lg:hidden">{{ $rangoCorto }}</span>
    </span>
</button>
```

con `$rangoCorto = $rol === 'Establecimiento afiliado' ? 'Afiliado' : $rol;`. El rango va en `text-tenue`, no `text-apagado`: 11 px en apagado sobre el vidrio no llega a 4,5:1 ni con el velo subido. Alto del texto con la escala: 14 × 1,55 + 11 × 1,65 = 39,9 px (mayor que el avatar de 36: el botón mide 47,9 y `-m-1` devuelve 39,9 al flujo; cabe en la bandeja de 56 y de 48 sin recorte porque nada lleva `overflow: hidden`; hipótesis a medir). `>Sec.<` y `>Admin<` se siguen emitiendo pegados (`NavbarTresEstadosTest.php:50-81`). **La hoja `#menu-cuenta` (`w-64`, `absolute right-0`, `menu-usuario.blade.php:88`) se ancla en móvil al módulo de cuenta y no al chip**: anclada al chip, a 320 px su borde derecho queda en 320 − 16 − 44 − 8 = 252 y la hoja de 256 desborda 4 px por la izquierda. La raíz de `menu-usuario` pasa a `class="relative min-w-0 max-lg:static"` (el `.modulo` ya es `position: relative`, `app.css:485`; utilidad contra utilidad, §3.7), y la hoja queda de 48 a 304 a 320 px; `focusout` y `click.outside` miran el DOM, no la posición. El módulo de cuenta pasa a `modulo modulo-cuenta flex min-w-0 items-center gap-2 whitespace-nowrap lg:justify-self-end lg:px-2`. Tras salir, el acuse es que el chip vuelve a ser «Afíliate» (`SesionAsociadoController.php:113-127` redirige a inicio); no se añade `destino=entrar`.

**Idioma (D-M5).** `control-idioma.blade.php:31` abre su raíz con `class="relative"` literal y **no usa `$attributes`** (solo `menu-grupo.blade.php:50` fusiona atributos): el literal pasa a `class="relative max-lg:hidden"` en el propio componente, y la guardia lee ese archivo.

**Altura.** 56 en `inicial` y 48 en `scroll` por `height` desde el token sobre `.bandeja`; `py-2` se queda en la cadena de la `<nav>` (`NavbarTresEstadosTest.php:500`) y `items-center` centra los controles de 44. El alto no depende del contenido: si Poppins llega tarde, la barra no salta (`NavegacionAgrupadaTest.php:14-19`).

### 6.2 Módulo inferior

**Cinco pestañas en el orden de escritorio** (D-M2): Directorio · Abre tu negocio · Eventos · Bolsas · El gremio. En `/eventos` tiene que haber dos `aria-current="page"` con texto «Eventos» en el header (`NavegacionAgrupadaTest.php:315`, `:343-344`). «Bolsas y empleo» del brief se lee como **Bolsas y El gremio** (D-M1): Empleo es la primera fila de Bolsas (`navbar.blade.php:24`) y `NavegacionAgrupadaTest.php:43-46` y `:199-214` prohíben a propósito que escritorio y móvil lean arreglos distintos.

**Anchos.** Sin relleno lateral en teléfono (solo `env(safe-area-inset-left/right)`): pestaña de **64 px a 320 · 72 a 360 · 78 a 390 · 86 a 430**; en tableta la fila se limita a `max-w-xl`. Cada pestaña `px-0.5`: 60 útiles a 320. Rótulos en Poppins 500 a 11 px (fontTools): Directorio 55,8 · Abre tu negocio 91,3 · Eventos 44,6 · Bolsas 37,4 · El gremio 53,3. **«Abre tu negocio» no cabe en una línea hasta 430 px, ni con cinco ni con cuatro pestañas**: son dos decisiones distintas (D-M2 y D-M3). Para la etiqueta: **dos líneas** con `text-balance`, **sin `line-clamp` ni `overflow: hidden` en el texto**: con el espaciado de texto de WCAG 1.4.12 (`letter-spacing: 0.12em`) «Directorio» pasa de 55,8 a unos 69 px y un `line-clamp` la terminaría en puntos suspensivos a 320 y 360; el rótulo lleva `overflow-wrap: anywhere` como último recurso (parte antes que desaparecer) y el plegado en `scroll` lo hace la pista de rejilla a `0fr`, que sí lleva `overflow: hidden` en su envoltorio. Acortar a «Tu negocio» rompería «reagrupar y no acortar» (`menu-grupo.blade.php:4-9`): descartado.

**Anatomía: icono sobre rótulo (D-M16).** El brief dice «botones» y no nombra iconos; el escritorio no los lleva (`navbar.blade.php:158-176`, `menu-grupo.blade.php:55-75` son texto y galón). Es un vocabulario visual nuevo con carga semántica (un maletín para Bolsas, un portapapeles para Abre tu negocio) y por eso es decisión de Sua; D-M3 y D-M13 dependen de ella. Recomendación: iconos de `blade-heroicons` 2.7.0 (ya viene con Filament; pares `o-`/`s-` comprobados en el vendor), **los mismos que el panel asigna a los mismos conceptos**, porque la secretaría los ve a diario: Directorio `building-storefront` (`AsociadoResource.php:20`), Abre tu negocio `clipboard-document-check` (`RequisitoAperturaResource.php:20`), Eventos `calendar-days` (`EventoResource.php:20`), Bolsas `briefcase` (`VacanteResource.php:22`), El gremio `user-group`. Trazo 1,5 uniforme a 24 px. Si Sua elige solo texto, el módulo baja a 48 con rótulo a una línea salvo «Abre tu negocio», y §6.2 cambia entero.

```blade
{{-- navbar.blade.php: los arreglos ganan 'icono' y, en El gremio, 'pie'.
     'texto' se declara una vez; la fila de invitado también. --}}
@php
    $enlacesDirectos = [
        ['ruta' => 'directorio.index', 'texto' => 'Directorio', 'icono' => 'building-storefront'],
        ['ruta' => 'guia.index', 'texto' => 'Abre tu negocio', 'icono' => 'clipboard-document-check'],
        ['ruta' => 'eventos.index', 'texto' => 'Eventos', 'icono' => 'calendar-days'],
    ];

    $grupos = [
        ['titulo' => 'Bolsas', 'icono' => 'briefcase', 'enlaces' => [ ...como hoy... ]],
        [
            'titulo' => 'El gremio',
            'icono' => 'user-group',
            'enlaces' => [ ...como hoy... ],
            // Solo en la pestaña y solo sin sesión: la entrada del afiliado
            // en el teléfono (D-M4). Aquí y no en el componente: la
            // navegación se declara una vez.
            'pie' => [['ruta' => 'mi-cuenta.entrar', 'texto' => 'Entrar como afiliado', 'solo' => 'guest']],
        ],
    ];
@endphp

<nav id="menu-movil"
     class="modulo-inferior lg:hidden"
     aria-label="Navegación principal">
    <div class="pestanas mx-auto flex w-full max-w-xl items-stretch">
        @foreach ($enlacesDirectos as $enlace)
            @php($actual = request()->routeIs($patron($enlace['ruta'])))
            <a href="{{ route($enlace['ruta']) }}"
               @if ($actual) aria-current="page" @endif
               @class([
                   'pestana fila-pulsable flex min-h-11 flex-1 flex-col items-center justify-center rounded-xl px-0.5 text-center text-2xs font-medium',
                   'text-acento' => $actual,
                   'text-suave' => ! $actual,
               ])>
                {{-- Contorno en reposo, sólido en la sección actual: la forma
                     dice lo mismo que el color. Se decide en el servidor. --}}
                <x-dynamic-component :component="'heroicon-'.($actual ? 's' : 'o').'-'.$enlace['icono']"
                                     class="h-6 w-6 shrink-0" aria-hidden="true" />
                <span class="pestana__rotulo"><span class="text-balance">{{ $enlace['texto'] }}</span></span>
            </a>
        @endforeach

        @foreach ($grupos as $grupo)
            <x-publico.menu-grupo variante="pestana"
                                  :titulo="$grupo['titulo']"
                                  :enlaces="$grupo['enlaces']"
                                  :icono="$grupo['icono']"
                                  :pie="$grupo['pie'] ?? []" />
        @endforeach
    </div>
</nav>
```

**Portador y acuse**: `fila-pulsable`, no `pulsable`: una pestaña de 72×68 encogida un 3 % se lee como una arruga de la barra. Ni `hover:bg-*` ni utilidad de reloj en el mismo elemento (`MovimientoTest.php:807-893`). Sin galón: la pestaña dice si está abierta por `aria-expanded` y por el icono sólido.

**Estado activo**: (1) la pestaña directa de la página actual lleva `aria-current="page"`, `text-acento` e icono `s-`; (2) la pestaña de grupo cuya sección está activa lleva `text-acento`, icono `s-` y **`aria-current="true"`** (el valor genérico, que el proyecto ya usa en `guia/index.blade.php:31` y `conmutador-eventos.blade.php:70`; `NavegacionAgrupadaTest.php:339-344` cuenta solo `[@aria-current="page"]`, así que el conjunto en `/empleo` sigue siendo `['Empleo']` y dos): sin esto, para el lector «Bolsas» en /empleo sonaba igual que en /contacto, y en el teléfono es la única barra; (3) la fila de la hoja lleva el `aria-current="page"`. El disparador de escritorio no lo anuncia (`:325-331`): hallazgo de la Parte I, aparte.

**Alturas.** `inicial`: icono 24 + `row-gap` 3 + rótulo de dos líneas de la escala (2 × 11 × 1,65 = 36,3, `--asb-alto-rotulo-pestana`) = 63,3, centrada en **68 px** (`--asb-alto-modulo-inferior`, 4.25rem; 64 dejaba 0,7 px de aire, §3.9); todas las pestañas reservan las dos líneas. `scroll`: el rótulo pliega por `grid-template-rows` 1fr a 0fr, el icono queda centrado en **48** (D-M13). El inset de la zona segura va en el `padding-bottom` del `<nav>`, fuera de la fila y fuera de toda transición.

### 6.3 Hojas

**Un solo comportamiento: `Alpine.data('desplegable')`** (`app.js:121-203`). Las hojas de Bolsas y El gremio, la de cuenta y el popover de tema son el mismo disclosure: botón con `aria-expanded` y `aria-controls`, panel con `role="group"`, sin `role="menu"` (`menu-grupo.blade.php:11-15`), sin `inert`, sin atrapar el foco, sin velo. Con dedo, `asomar`/`retirar` retornan (`:157-172`); abrir una avisa con `$root` y las demás ceden (`:175-181`); Escape devuelve el foco (`:188-202`). No se bloquea el scroll: **desplazarse ES cerrar**, y por eso en vertical la hoja **no es contenedor de scroll** (tres filas de 45,7 px no desbordan): un `overflow-y: auto` con `overscroll-behavior: contain` en un contenedor ya en su límite impide encadenar el gesto al documento, `window` no recibe `scroll`, y la hoja no se cerraría arrastrando sobre ella, que es la zona más probable bajo el pulgar; y `touch-action: pan-y` mataría el pinch-zoom sobre texto de 14 px. Esas declaraciones van solo en apaisado (§6.4).

**Piezas nuevas en `app.js`, sin tocar los cuerpos pinzados** (`NavbarTresEstadosTest.php:552-593`; de `abrir()` solo la línea del `$dispatch`, `:546`):

```js
Alpine.data('desplegable', () => ({
    abierto: false,
    cierre: null,
    scrollAlAbrir: 0,

    abrir() {
        clearTimeout(this.cierre);
        this.abierto = true;
        this.scrollAlAbrir = posicionDelDocumento();
        this.$dispatch('desplegable-abierto', this.$root);
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

    ...cerrar, alternar, asomar, retirar, ceder, cerrarYVolverAlFoco sin cambios...
}));
```

`posicionDelDocumento()` es una función de módulo junto a `punteroFino()` (`app.js:76-77`) con el mismo clamp de §4.2 (`Math.min(Math.max(window.scrollY, 0), scrollHeight - innerHeight)`): sin él, una hoja abierta con la página al final se cerraba con un tirón de 25 px que no desplazaba nada. El header, que no importa de `app.js`, lleva la suya (`posicion()`), como ya duplica `punteroFino()` (`navbar.blade.php:73-75`). El tramo entre `Alpine.data('desplegable'` y `Alpine.data('videoHero'` sigue sin `this.$el` (`:558-565`).

**Tres cableados nuevos en la raíz de las cuatro vistas** con `desplegable` (`menu-grupo`, `menu-usuario`, `control-tema`, `control-idioma`), para el bucle de `NavbarTresEstadosTest.php:596-614`:

```blade
x-on:pointerdown.outside="cerrar()"
x-on:scroll.window.passive="cerrarSiSeDesplaza()"
x-on:pageshow.window="if ($event.persisted) cerrar()"
```

El primero es la reserva del toque fuera en iOS: `.outside` de Alpine escucha en `document` (`node_modules/alpinejs/src/utils/on.js:41-42`) y Safari no despacha `click` ahí cuando el toque cae en fondo sin oyente; los pointer events llegan siempre. Se conserva el `click.outside`. El tercero es el bfcache (el layout ya trata `pageshow`, `publico.blade.php:164-170`). Los tres actúan también en escritorio (D-M12).

**`menu-grupo` con variante `pestana`.** Un componente, dos pinturas. La rama de escritorio queda letra a letra (`origin-top-left`, `scale-95`, `duration-(--duracion-entrada)`: `NavegacionAgrupadaTest.php:289-298`).

```blade
@props(['titulo', 'enlaces', 'variante' => 'barra', 'icono' => null, 'pie' => []])

@php
    $patron = static fn (string $ruta): string => str_replace('.index', '.*', $ruta);
    $grupoActivo = collect($enlaces)->contains(
        static fn (array $enlace): bool => request()->routeIs($patron($enlace['ruta']))
    );
    $esPestana = $variante === 'pestana';
    $panel = 'menu-'.Str::slug($titulo).($esPestana ? '-movil' : '');
    $filasDePie = $esPestana
        ? collect($pie)->filter(static fn (array $fila): bool => ($fila['solo'] ?? null) !== 'guest' || auth()->guest())
        : collect();
@endphp

<div x-data="desplegable"
     x-on:pointerenter="asomar($event)"
     x-on:pointerleave="retirar($event)"
     x-on:desplegable-abierto.window="ceder($event.detail)"
     x-on:click.outside="cerrar()"
     x-on:pointerdown.outside="cerrar()"
     x-on:keydown.escape.window="cerrarYVolverAlFoco()"
     x-on:focusout="if (! $el.contains($event.relatedTarget)) cerrar()"
     x-on:scroll.window.passive="cerrarSiSeDesplaza()"
     x-on:pageshow.window="if ($event.persisted) cerrar()"
     {{ $attributes->class(['relative' => ! $esPestana, 'flex flex-1' => $esPestana]) }}>

    @if ($esPestana)
        {{-- Raíz estática a propósito: el bloque contenedor de la hoja es el
             módulo inferior fijo, no la pestaña de 72 px, o desbordaría. --}}
        <button type="button"
                x-ref="disparador"
                x-on:click="alternar()"
                x-bind:aria-expanded="abierto ? 'true' : 'false'"
                aria-controls="{{ $panel }}"
                @if ($grupoActivo) aria-current="true" @endif
                @class([
                    'pestana fila-pulsable flex min-h-11 w-full flex-col items-center justify-center rounded-xl px-0.5 text-center text-2xs font-medium',
                    'text-acento' => $grupoActivo,
                    'text-suave' => ! $grupoActivo,
                ])>
            <x-dynamic-component :component="'heroicon-'.($grupoActivo ? 's' : 'o').'-'.$icono" class="h-6 w-6 shrink-0" aria-hidden="true" />
            <span class="pestana__rotulo"><span class="text-balance">{{ $titulo }}</span></span>
        </button>
    @else
        ...el botón de escritorio de hoy, intacto (menu-grupo.blade.php:55-75)...
    @endif

    <div id="{{ $panel }}"
         x-show="abierto"
         x-cloak
         @if ($esPestana)
             x-transition:enter="transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)"
             x-transition:enter-start="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-hoja)"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-hoja)"
             role="group"
             aria-label="{{ $titulo }}"
             class="hoja-flotante hoja-inferior absolute inset-x-2 bottom-full z-50 mx-auto mb-2 max-w-sm origin-bottom rounded-2xl p-2"
         @else
             ...las clases y transiciones de escritorio de hoy (menu-grupo.blade.php:77-90)...
         @endif>
        @foreach ($enlaces as $enlace)
            ...las filas de hoy, sin cambios (menu-grupo.blade.php:91-107)...
        @endforeach

        @if ($filasDePie->isNotEmpty())
            <div class="my-1 border-t border-linea" role="presentation"></div>
            @foreach ($filasDePie as $fila)
                <a href="{{ route($fila['ruta']) }}"
                   class="fila-pulsable block rounded-lg px-3 py-3 text-sm text-suave hover:text-fuerte">
                    {{ $fila['texto'] }}
                </a>
            @endforeach
        @endif
    </div>
</div>
```

**Geometría de la hoja inferior.** `bottom-full` con `mb-2`, `inset-x-2`, `max-w-sm`, `origin-bottom`: nace del canto superior del módulo y lo acompaña al compactarse. Ningún ancestro lleva `opacity`, `filter`, `backdrop-filter` ni `view-transition-name`: el vidrio del módulo está en su `::before` (§3.4). Material: `.hoja-flotante` (`app.css:765-775`), que pasa a consumir `--asb-hoja-velo`, más `.hoja-inferior`, que mueve el resplandor rojo al canto inferior. Si la medición de §8.4 dijera que la hoja no desenfoca la página, `--asb-hoja-velo` sube al 94 % bajo 64rem por token.

**Foco y orden.** El inferior vive dentro del `<header>`, al principio del documento: con Tab y VoiceOver se lee tras la cabecera y antes de `main`. Cada hoja va inmediatamente después de su disparador; el anillo (`outline-offset: 3px`, `app.css:176-180`) no se recorta porque el módulo no lleva `overflow: hidden`.

### 6.4 Geometría y colisiones

**Viewport y zona segura (D-M10).** `viewport-fit=cover` en `publico.blade.php:5`. Hoy no hay ningún `env(safe-area-inset-*)` en `resources/` (grep: cero), y con `cover` **todo el documento** se mete bajo la muesca en apaisado, no solo los módulos: `<main>` y sus secciones con `px-4` (`hero.blade.php:40`), el pie (`footer.blade.php:6`), los campos de /afiliate y /contacto. Se compensa en un solo sitio para todo el documento: `body { padding-inline: env(safe-area-inset-left, 0px) env(safe-area-inset-right, 0px) }` bajo 64rem (el `<body>` no lleva utilidades de relleno). El header y el inferior son `inset-inline: 0` respecto al viewport y llevan su propio `padding-inline` con los mismos `env()`: en el header va en `.cromo-fijo` (sin utilidades, §3.7) y el `px-4` de la bandeja suma dentro; el `.bandeja::before` se extiende con insets negativos para que el vidrio cubra hasta el canto. El inset inferior va en el `padding-bottom` del `<nav>`, fuera de la caja de 44 y de toda transición. Todo funciona igual sin `cover` (respaldo 0). Si el coste de compensar no convence, la alternativa honesta es no declarar `cover`.

**Apartados del documento.** Arriba: `.cromo-fijo ~ main > section:first-child:not(.hero-portada) { padding-top: calc(7rem + env(safe-area-inset-top, 0px)) }`; `html { scroll-padding-top: calc(var(--asb-alto-modulo-superior) + env(safe-area-inset-top, 0px) + 2.5rem) }` bajo 64rem (con `cover` y muesca la cabecera mide 103-115 px y los 96 de `scroll-pt-24` dejaban el ancla, el «Saltar al contenido» y el `campo:invalid` bajo el vidrio); `<html>` pasa a `lg:scroll-pt-24`. Las veinte vistas cuyo `main` no empieza por `<section>` ya nacen bajo la cabecera fija desde el 4 sep: corrección aparte (§9). Abajo: `padding-bottom` en **`body`** (el pie es hermano de `main`, `publico.blade.php:198-202`) y `scroll-padding-bottom` en `html`.

**Portada.** A 390×844 el rótulo del video termina en 688 y un módulo de 68 (776-844) solo tapa video; a 390×664 el hero desborda y el rótulo queda bajo el módulo hasta desplazar, como hoy bajo la barra lateral. `hero.blade.php:41` pasa de `pb-20 pt-28 sm:pb-24 sm:pt-32 lg:pt-36` a `pb-28 pt-28 sm:pt-32 lg:pb-24 lg:pt-36` (112 px cubren 68 + 34). Ninguna guardia fija esa cadena. Se vuelve a medir tras `ContenidoOficialSeeder` y se cierra con Ingrid junto al rótulo (D-29).

**Teclado virtual.** Con `data-teclado="abierto"` (§4.2) el inferior se retira. No se toca `interactive-widget`. Al cerrarse el teclado en iOS `scrollY` salta: el filtro de 200 px reancla sin decidir.

**iPad vertical y teléfono apaisado (D-M14).** Tableta: fila `max-w-xl` centrada; logotipo completo; rotar de 820 a 1180 cruza 64rem y el `matchMedia` cierra las hojas. Apaisado bajo (`(orientation: landscape) and (max-height: 30rem)`): módulos compactos por token, hojas acotadas en `svh` con desplazamiento interno y `touch-action: pan-y pinch-zoom`. **Viewports muy cortos** (`(max-height: 20rem)`: 400 % de zoom en un portátil da 320×180 CSS px, WCAG 1.4.10): dos módulos fijos de 48 se comen 96 de 180; el inferior deja de ser fijo (`position: static` tras la bandeja, se lee y se toca igual y se va con el scroll) y `body { padding-bottom: 0 }`.

**Vidrio sobre el video de la portada.** Dos franjas de `backdrop-filter` sobre un video de `100svh` obligan al compositor a releer esa región cada fotograma. Mitigaciones en el diseño: vidrio solo en los dos `::before`, `isolation: isolate`, ningún `will-change`, ninguna animación del vidrio. El desenfoque **no** se baja de antemano (D-M18): se construye con el `blur(20px)` desplegado, se mide con el video corriendo en el teléfono del directivo, y solo si el compositor pasa de 4 ms o la franja sale negra se baja el token con la cifra medida y su guardia (o la portada apaga el desenfoque por token, como la transparencia reducida). Sombras en `box-shadow`, nunca por filtro.

**Lo que no colisiona.** No hay toasts fijos, ni chip de WhatsApp, ni paginación pegada. El mapa apila hasta z 800 dentro de un `z-0` (`mapa.blade.php:66`): el inferior, dentro del contexto `z-40` del header, gana. Si llega el chip de WhatsApp, nace encima del módulo por el token.

**El CSS del móvil, entero.** Tres retoques fuera de todo `@media` y un bloque nuevo tras `app.css:749-757` (§3.6). Los comentarios nombran y no pegan (§3.8).

```css
/* .cromo: se retiran la opacidad, el desplazamiento vertical identidad y sus
   dos transiciones (solo servían a la clase de ocultación del cromo, que
   ninguna vista usaba). Con ellas el header era bloque contenedor de todo
   descendiente fijo y el módulo inferior se pegaba a la cabecera. */
.cromo {
    background-color: transparent;
    box-shadow: none;
    isolation: isolate;
    transition: box-shadow var(--duracion-entrada) var(--ease-color);
}

/* Hermano general y no adyacente: la barra lateral de tema ya no está entre
   el header y main, y el apartado no puede depender del orden del layout. */
.cromo-fijo ~ main > section:first-child:not(.hero-portada) {
    padding-top: calc(7rem + env(safe-area-inset-top, 0px));
}

/* .hoja-flotante: el velo pasa a token (sólido bajo transparencia reducida y
   bajo más contraste). Escritorio píxel a píxel igual. */
.hoja-flotante {
    background:
        radial-gradient(16rem circle at 18% 0%, rgb(238 65 55 / 0.09), transparent 54%),
        var(--asb-hoja-velo);
    ...el resto como hoy (app.css:769-774)...
}

/* Primer bloque móvil (app.css:473-482): el vidrio de la bandeja pasa a su
   pseudoelemento para que las hojas que cuelgan de ella desenfoquen la página
   y no el interior de la bandeja (raíz de fondo). */
@media (max-width: 63.999rem) {
    .bandeja {
        z-index: 2;
        height: var(--asb-alto-modulo-superior);
        transition:
            height var(--duracion-estado) var(--ease-rebote-suave),
            gap var(--duracion-estado) var(--ease-rebote-suave);
    }

    .bandeja::before {
        content: '';
        pointer-events: none;
        position: absolute;
        inset: calc(-1 * env(safe-area-inset-top, 0px)) calc(-1 * env(safe-area-inset-right, 0px)) 0 calc(-1 * env(safe-area-inset-left, 0px));
        z-index: -1;
        background-color: var(--asb-cromo-velo);
        -webkit-backdrop-filter: var(--asb-cromo-desenfoque);
        backdrop-filter: var(--asb-cromo-desenfoque);
        box-shadow:
            var(--asb-cromo-apoyo),
            inset 0 1px 0 rgb(255 255 255 / 0.26);
    }
}

/* ... .modulo, .logo-doble, .cromo::before, el bloque de 64rem, (hover: none)
   y (hover: hover) and (pointer: fine), sin cambios ... */

/*
 * La barra móvil en dos módulos (Parte II). Va DESPUÉS del bloque de
 * escritorio y de la puerta táctil a propósito: las guardias leen la primera
 * regla de cada selector. Ningún módulo lleva nombre de transición de vista:
 * un elemento con nombre es raíz de fondo (View Transitions 1, §2.1.1) y
 * dejaría a sus hojas sin página que desenfocar.
 */
@media (max-width: 63.999rem) {
    /* El estado cambia DOS tokens y nada más; toda la geometría los lee. */
    .cromo[data-estado="scroll"] {
        --asb-alto-modulo-superior: var(--asb-alto-modulo-superior-compacto);
        --asb-alto-modulo-inferior: var(--asb-alto-modulo-inferior-compacto);
    }

    /* Los insets van aquí y no en la bandeja: su px-4 es utilidad y ganaría
       a cualquier padding de componente. */
    .cromo-fijo {
        padding-top: env(safe-area-inset-top, 0px);
        padding-inline: env(safe-area-inset-left, 0px) env(safe-area-inset-right, 0px);
    }

    .bandeja {
        touch-action: manipulation;
    }

    /* El cruce del logo: la D6 de escritorio, sin `atencion`, y además con
       sesión (D-M7). */
    [data-estado="scroll"] .logo-doble,
    .marca-compacta .logo-doble {
        max-width: 2.9rem;
    }

    [data-estado="scroll"] .logo-doble__completo,
    .marca-compacta .logo-doble__completo {
        opacity: 0;
        scale: var(--asb-escala-isotipo);
    }

    [data-estado="scroll"] .logo-doble__isotipo,
    .marca-compacta .logo-doble__isotipo {
        opacity: 1;
        scale: 1;
    }

    @media (max-width: 22.499rem) {
        .logo-doble { max-width: 2.9rem; }
        .logo-doble__completo { opacity: 0; scale: var(--asb-escala-isotipo); }
        .logo-doble__isotipo { opacity: 1; scale: 1; }
    }

    /* Módulo inferior. Fijo al viewport, z-index dentro del contexto del
       header. El alto lo lleva la fila (.pestanas); el nav solo paga la zona
       segura, fuera de la caja de toque y fuera de toda transición: en iOS el
       inset salta de 0 a 34 px a mitad de gesto. Sin recorte de pintado: la
       hoja cuelga fuera de la caja. */
    .modulo-inferior {
        position: fixed;
        inset-inline: 0;
        bottom: 0;
        z-index: 1;
        isolation: isolate;
        display: flex;
        align-items: stretch;
        padding-bottom: env(safe-area-inset-bottom, 0px);
        padding-inline: env(safe-area-inset-left, 0px) env(safe-area-inset-right, 0px);
        touch-action: manipulation;
        opacity: 1;
        translate: 0 0;
        visibility: visible;
        transition:
            translate var(--duracion-panel) var(--ease-cajon),
            opacity var(--duracion-salida) var(--ease-color),
            visibility 0s linear 0s;
    }

    .pestanas {
        height: var(--asb-alto-modulo-inferior);
        transition: height var(--duracion-estado) var(--ease-rebote-suave);
    }

    /* El vidrio, en el pseudoelemento. Sombra en box-shadow. El filo de luz
       arriba: es el canto que mira al contenido. */
    .modulo-inferior::before {
        content: '';
        pointer-events: none;
        position: absolute;
        inset: 0;
        z-index: -1;
        background-color: var(--asb-cromo-velo);
        -webkit-backdrop-filter: var(--asb-cromo-desenfoque);
        backdrop-filter: var(--asb-cromo-desenfoque);
        box-shadow:
            var(--asb-cromo-apoyo-inferior),
            inset 0 1px 0 rgb(255 255 255 / 0.26);
    }

    /* La raya roja de apoyo en el canto superior, encendida por el mismo
       cromo-apoyado que la del superior. */
    .modulo-inferior::after {
        content: '';
        pointer-events: none;
        position: absolute;
        inset: 0 0 auto;
        height: 1px;
        opacity: 0;
        background: linear-gradient(90deg, transparent, rgb(238 65 55 / 0.36), transparent);
        transition: opacity var(--duracion-boton) var(--ease-color);
    }

    .cromo-apoyado .modulo-inferior::after {
        opacity: 1;
    }

    .cromo[data-teclado="abierto"] .modulo-inferior {
        visibility: hidden;
        opacity: 0;
        translate: 0 var(--asb-retirada-barra);
        transition:
            translate var(--duracion-panel) var(--ease-cajon),
            opacity var(--duracion-salida) var(--ease-color),
            visibility 0s linear var(--duracion-panel);
    }

    .pestana {
        row-gap: 0.1875rem;
    }

    /* El rótulo pliega por pista de rejilla, nunca por `height`. El texto no
       se recorta (WCAG 1.4.12): la reserva de dos líneas sale de la escala. */
    .pestana__rotulo {
        display: grid;
        grid-template-rows: 1fr;
        min-height: var(--asb-alto-rotulo-pestana);
        opacity: 1;
        transition:
            grid-template-rows var(--duracion-estado) var(--ease-rebote-suave),
            min-height var(--duracion-estado) var(--ease-rebote-suave),
            opacity var(--duracion-entrada) var(--ease-color);
    }

    .pestana__rotulo > span {
        min-height: 0;
        overflow: hidden;
        overflow-wrap: anywhere;
    }

    [data-estado="scroll"] .pestana__rotulo {
        grid-template-rows: 0fr;
        min-height: 0;
        opacity: 0;
        transition:
            grid-template-rows var(--duracion-estado) var(--ease-rebote-suave),
            min-height var(--duracion-estado) var(--ease-rebote-suave),
            opacity var(--duracion-salida) var(--ease-color);
    }

    /* La hoja que sube: solo el resplandor al canto que mira a la barra. En
       vertical NO es contenedor de scroll: desplazarse es cerrar. */
    .hoja-inferior {
        background-image: radial-gradient(16rem circle at 18% 100%, rgb(238 65 55 / 0.09), transparent 54%);
    }

    body {
        padding-inline: env(safe-area-inset-left, 0px) env(safe-area-inset-right, 0px);
        padding-bottom: calc(var(--asb-alto-modulo-inferior) + env(safe-area-inset-bottom, 0px));
    }

    html {
        scroll-padding-top: calc(var(--asb-alto-modulo-superior) + env(safe-area-inset-top, 0px) + 2.5rem);
        scroll-padding-bottom: calc(var(--asb-alto-modulo-inferior) + env(safe-area-inset-bottom, 0px) + 1rem);
    }

    @media (min-width: 48rem) {
        .pestanas {
            max-width: 36rem;
        }
    }

    /* Teléfono apaisado: compacto de nacimiento (D-M14); aquí sí las hojas
       pueden desbordar y desplazan por dentro sin perder el zoom. */
    @media (orientation: landscape) and (max-height: 30rem) {
        :root {
            --asb-alto-modulo-superior: var(--asb-alto-modulo-superior-compacto);
            --asb-alto-modulo-inferior: var(--asb-alto-modulo-inferior-compacto);
        }

        .pestana__rotulo {
            grid-template-rows: 0fr;
            min-height: 0;
            opacity: 0;
        }

        .hoja-flotante {
            max-height: calc(100svh - var(--asb-alto-modulo-superior) - var(--asb-alto-modulo-inferior) - env(safe-area-inset-bottom, 0px) - 1.5rem);
            overflow-y: auto;
            overscroll-behavior: contain;
            touch-action: pan-y pinch-zoom;
        }
    }

    /* Viewport muy corto (zoom al 400 %): dos barras fijas se comían 96 de
       180 px. El inferior vuelve al flujo. */
    @media (max-height: 20rem) {
        .modulo-inferior {
            position: static;
        }

        body {
            padding-bottom: 0;
        }
    }
}
```

Se retiran `.cromo-oculto` (`:594-597`) con su anulación (`:1251-1254`) y `.tema-lateral*` (`:777-859`). El comentario de `:467-472` se reescribe. `.bandeja { z-index: 2 }` sobre `isolation: isolate` (`:456`): las hojas del superior pasan por encima del inferior en apaisado.

## 7. Archivos

| Archivo | Cambio |
|---|---|
| `navbar.blade.php` | `'icono'` en los cinco controles y `'pie'` en El gremio. `x-data`: fuera `menuMovil`, sus tres salidas, `resize.window` y `\|\| menuMovil`; entran `compacta`, `teclado`, `esEscritorio`, `ancla`, `altoReferencia`, `campo`, `init()`, `menosMovimiento()`, `posicion()`, `compactar()`, `campoEnfocado()`, `esCampo()`, `medirTeclado()` y las ramas móviles; cableados `focusin.window`, `focusout.window`, `x-bind:data-teclado`. `<nav>` superior con `x-bind:aria-label`. Logo con `min-h-11` y `marca-compacta`. Módulo de cuenta visible; «Mi cuenta» `max-lg:hidden`. Fuera la hamburguesa y el panel; entra el `<nav id="menu-movil">`. `<noscript>` intacto. Comentarios sin la palabra del estado viejo ni la del ancho en píxeles |
| `menu-grupo.blade.php` | Props `variante`, `icono`, `pie`; id `-movil`; raíz por variante; botón con `aria-current="true"` si el grupo está activo (solo pestaña); filas de pie desde el prop; tres cableados. Rama de escritorio letra a letra |
| `menu-usuario.blade.php` | Nombre visible en los dos anchos, rango `text-2xs text-tenue lg:hidden`, `$rangoCorto`, `sr-only` con texto visible primero, raíz `relative min-w-0 max-lg:static`; tres cableados. Docblock corregido |
| `control-tema.blade.php`, `control-idioma.blade.php` | Tres cableados; `control-idioma` raíz `class="relative max-lg:hidden"` (literal, no hay `$attributes`); docblocks corregidos |
| `barra-tema.blade.php` | **Se borra** |
| `hero.blade.php` | Rama `$portada`: `pb-28 pt-28 sm:pt-32 lg:pb-24 lg:pt-36` |
| `footer.blade.php` | Fila «Entrar a mi cuenta» en la columna «El gremio», con `flex min-h-11 items-center` |
| `layouts/publico.blade.php` | `viewport-fit=cover`; `<html class="lg:scroll-pt-24">`; `<body class="min-h-svh ...">`; precarga sin `media`; fuera `<x-publico.barra-tema />` |
| `tokens.css` | §5.2: nueve tokens con sus recetas, el velo móvil, las dos anulaciones, `--asb-hoja-velo` sólido bajo transparencia reducida y más contraste, el velo y desenfoque sólidos bajo más contraste; fuera `--duracion-cromo` y `--asb-desplazamiento-panel`; comentario de `--duracion-panel` con sus tres consumidores |
| `app.css` | `.cromo` sin transform; fuera `.cromo-oculto`; selector con `~`; primer bloque móvil con `.bandeja::before`; segundo bloque de §6.4; `.hoja-flotante` con `--asb-hoja-velo`; fuera `.tema-lateral*` |
| `app.js` | `posicionDelDocumento()`; `desplegable`: `scrollAlAbrir`, `cerrarSiSeDesplaza()`; docblocks |
| `tests/Feature/NavbarMovilTest.php` | `git mv` desde `MenuMovilTest.php` y reescritura (§8.2) |
| `NavbarTresEstadosTest`, `TemaClaroOscuroTest`, `ObjetivoTactilTest`, `MovimientoTest`, `Panel/ComponentesDelPanelTest` | §8.1 |
| `docs/ingenieria/navbar-tres-estados-diseno.md` | Notas fechadas en §1, §3.1, §3.8, §6.4 y §8 |
| `matriz-de-pruebas.md`, `estado.md`, `encargo.md` §13 | Cifras móviles caducadas; decisiones de Sua |

Sin dependencias ni carpetas nuevas. Un solo commit para vista, CSS, tokens y guardias. Antes de codificar: `grep -rn desplazamiento-panel tests/`, `grep -n duracion-panel resources/css`, `GIT_OPTIONAL_LOCKS=0 git status`, `git log --oneline -5`.

## 8. Pruebas

Cada aserción nueva se ve **roja** rompiendo el código a propósito antes de darla por buena.

### 8.1 Guardias que cambian, con su porqué

| Archivo:línea | Antes | Después | Porqué |
|---|---|---|---|
| `MenuMovilTest.php` entera | panel absoluto, tres salidas de `menuMovil`, recorte sin `aria-expanded` ni «Apariencia», dos `<h2 class="antetitulo">`, seis hrefs | **`git mv` a `NavbarMovilTest.php`** y reescritura; el docblock cuenta la decisión invertida (772 px medidos; hojas a cambio de barra siempre visible) | D-M11 |
| `NavbarTresEstadosTest.php:14-17` | «la barra pública de escritorio» | «... de escritorio y, desde la Parte II, la del móvil» | Historia |
| `:170-172`, `:190` | rotura «quitar el atributo media»; nombre `..._se_precarga_solo_en_escritorio`; precarga con `media` | `..._y_se_precarga_en_los_dos_anchos`; rotura «devolver el `media`»; precarga sin `media` | El móvil cruza al isotipo |
| `:371-372` | dos literales de `menuMovil` | `assertStringNotContainsString('menuMovil', $navbar)`; `:373-374` se conservan | Muere con el panel |
| `:394` | `//nav` = 1 | `//nav` = 2; `//header/nav[2]/@aria-label` = «Navegación principal»; la primera lleva `x-bind:aria-label` | D-M9 y landmarks veraces |
| `:418` (docblock) | «`<div id="menu-movil"`» | «`<nav id="menu-movil"`» | Historia; `:420-448` no cambia |
| `:453-460` | `File::get(barra-tema)`, `tema-lateral fixed`, `lg:hidden` | `assertFileDoesNotExist(barra-tema.blade.php)`; `app.css` sin `tema-lateral`; `tokens.css` sin `--duracion-cromo`; conserva `selector-tema` | `File::get` sobre archivo borrado revienta |
| `:463-485` (docblock) | «banda que el móvil conserva» | igual, citando la Parte II | Historia |
| `:505` | `modulo modulo-cuenta hidden ... lg:flex lg:justify-self-end` | `modulo modulo-cuenta flex min-w-0 items-center gap-2 whitespace-nowrap lg:justify-self-end lg:px-2` | Un solo DOM |
| `:534-540` | siete definiciones | ocho: `cerrarSiSeDesplaza() {`; `this.scrollAlAbrir = posicionDelDocumento();` en `abrir()` | Cierre por scroll |
| `:596-614` | siete cableados | diez | Toque fuera en iOS, scroll, bfcache |
| `:617-636` | docblock «en el panel móvil»; `href="/afiliate"` 2 veces | docblock reescrito; 1 vez; con sesión 0; el pie lo conserva | Un solo DOM |
| `:667-688` | docblock «la hamburguesa se perdía»; `regla($movil, '.bandeja')` con vidrio | `regla($movil, '.bandeja::before')` con velo, `-webkit-backdrop-filter`, `backdrop-filter` y apoyo; `regla($movil, '.bandeja')` sin `backdrop-filter` | Raíz de fondo |
| `TemaClaroOscuroTest.php:14-16`, `:85-95`, `:114-123` | «barra lateral», «dos controles a la vez»; `tema-lateral fixed`, `sm:top-1/2`... | `test_el_control_de_tema_vive_en_la_navbar_en_los_dos_anchos`: `assertDontSee('tema-lateral')`, `id="popover-tema"` una vez, el botón `aria-controls="popover-tema"` sin `hidden` ni `lg:` | D-M6, D-M17 |
| `ObjetivoTactilTest.php:87-89`, `:124-133` | «las filas del menú móvil»; filas hamburguesa y menú móvil | docblock reescrito; filas borradas; entran las de §8.3 solo con cifra medida | Objetos desaparecidos |
| `MovimientoTest.php:28-34`, `:46`, `:98`, `:401-404`, `:406-423` | tres excepciones; `--asb-desplazamiento-panel`; «Eran tres...»; `navbar` en la lista de `transicion-desplegable` | dos excepciones; `--asb-desplazamiento-hoja: 6px`/`0px`; docblock reescrito; `navbar` sale de esa lista (sin panel ni hamburguesa no queda `x-transition`; en `:906-929` se queda: las pestañas son `fila-pulsable`) | Con razón escrita |
| `Panel/ComponentesDelPanelTest.php:70` | `--asb-desplazamiento-panel: 0%` | `--asb-desplazamiento-hoja: 0px` | El panel nunca consumió ese token; sin esto el commit sale rojo |

**No cambian**: `NavegacionAgrupadaTest` entera, `NavbarTresEstadosTest.php:50-81`, `:86-113`, `:296-330`, `:341-368`, `:377`, `:395-415`, `:420-448` salvo docblock, `:500-504`, `:507-522`, `CalendarioDeEventosTest`, `EscenaPublicaTest`, `PortadaEditableTest`, `TransicionesDeVistaTest`, `TipografiaTest`, `FormulariosPublicosTest:410-424`, los patrones prohibidos de `MovimientoTest.php:313-370` y `:807-893`, el barrido de colores de `TemaClaroOscuroTest.php:243-275`, `ObjetivoTactilTest.php:314-348`, y las aseveraciones de rol y sesión (`TemaClaroOscuroTest.php:127-177`).

### 8.2 `NavbarMovilTest` (renombrada desde `MenuMovilTest`)

| Prueba | Qué protege | Rotura |
|---|---|---|
| el cromo ya no es bloque contenedor | `regla(app.css, '.cromo')` sin `transform`, `filter`, `will-change`, `contain`; la clase de ocultación del cromo no aparece en `app.css` (el comentario de `.cromo` la nombra sin pegarla) | devolver el `transform` |
| el módulo inferior vive en el header, es fijo y es el landmark | `//header/nav[@id="menu-movil" and @aria-label="Navegación principal"]` = 1; su clase contiene `lg:hidden` y `modulo-inferior`; `//nav[1]//*[@id="menu-movil"]` = 0; ningún hijo de `//nav[2]` contiene «modulo» ni «gap-1»; `regla('.modulo-inferior')` contiene `position: fixed;` y `bottom: 0;`; la primera `<nav>` lleva `x-bind:aria-label` | quitar `lg:hidden`; `fixed` por `sticky`; quitar el `x-bind` |
| los cinco destinos en orden y las dos hojas | pestañas en orden Directorio, Abre tu negocio, Eventos, Bolsas, El gremio; dos botones con `aria-controls` `menu-bolsas-movil`/`menu-el-gremio-movil`; `//div[@id="menu-bolsas-movil"]/a` y `//div[@id="menu-el-gremio-movil"]/a` empiezan por los textos exactos de `GRUPOS`; `'icono' =>` cinco veces y `'texto' => 'Entrar como afiliado'` exactamente una vez en `navbar.blade.php`; `menu-grupo.blade.php` sin `'El gremio'` | quitar Eventos; redeclarar la fila en el componente |
| las hojas son disclosures que se anuncian | cada botón con `x-bind:aria-expanded`, `x-ref="disparador"` y sin `aria-current="page"`; sin `role="menu"`, `aria-haspopup`, `x-collapse`; cada hoja después de su botón | quitar el `x-bind:aria-expanded` |
| los seis destinos plegados siguen a dos toques como máximo | para cada uno de los seis `href` dentro de `nav#menu-movil`: `count(ancestor::*[@x-data="desplegable"])` = 1 y su `div[@id]` padre es una de las dos hojas | envolver una fila en un segundo `x-data="desplegable"` |
| el activo se anuncia una vez por ancho, y el grupo por su cuenta | en `/empleo`: el botón `aria-controls="menu-bolsas-movil"` lleva `text-acento` y `aria-current="true"`, el de El gremio ninguno; la fila «Empleo» lleva `aria-current="page"`; en `/eventos` la pestaña Eventos lleva `aria-current="page"`, `text-acento` y el `<svg>` sólido (`fill="currentColor"`) | intercambiar los botones; poner `page` en el botón |
| la máquina móvil por dirección | `navbar.blade.php` contiene `compactar() {`, `posicion() {`, `menosMovimiento() {`, `medirTeclado() {`, `campoEnfocado() {`, `esCampo(`, `esEscritorio`, `matchMedia('(min-width: 64rem)')`, `if (! this.esEscritorio) {`, `dentro <= 8`, `dentro >= tope`, `Math.abs(recorrido) > 200`, `(this.compacta && recorrido > 0) \|\| (! this.compacta && recorrido < 0)`, `recorrido > 24`, `recorrido < -12`, `window.innerHeight` dentro de `posicion() {`, `classList.contains('sin-desplazamiento')` dentro de `compactar`, `altoReferencia` dentro de `medirTeclado`, `visualViewport`, `x-on:focusout.window="if (! esCampo($event.relatedTarget)) teclado = false"`, `x-bind:data-teclado`, `x-bind:class="{ 'cromo-apoyado': desplazado }"`; y no contiene `menuMovil`, `x-on:resize.window`, `reposar`, ni `clientHeight` | intercambiar 24 y 12; invertir `recorrido > 24`; borrar la rama de extremo; volver a `clientHeight`; borrar la puerta de movimiento |
| las hojas cierran al desplazar sin robar el foco | `app.js` casa `/cerrarSiSeDesplaza\(\) \{\s*if \(! this\.abierto \|\| this\.\$root\.contains\(document\.activeElement\)\) \{\s*return;\s*\}\s*if \(Math\.abs\(posicionDelDocumento\(\) - this\.scrollAlAbrir\) > 24\) \{\s*this\.cerrar\(\);/` y define `function posicionDelDocumento()` con `window.innerHeight` | quitar el `contains`; borrar el método dejando el cableado |
| los dos módulos son vidrio por token en su pseudoelemento, y ninguno es raíz de fondo | tras el primer `@media (max-width: 63.999rem) {`: `regla('.modulo-inferior::before')` con velo, `-webkit-backdrop-filter`, `backdrop-filter`, `var(--asb-cromo-apoyo-inferior)`; `regla('.modulo-inferior')` sin `backdrop-filter`, `filter:`, `blur(`, `contain:`, `env(` en ninguna propiedad de su `transition`, ni `height`; `app.css` no contiene `view-transition-name`; ninguna regla del bloque móvil contiene `blur(` ni sombra por filtro | mover el vidrio al módulo; poner nombre a `.bandeja`; devolver el `env()` a una altura transicionada |
| el estado cambia dos tokens y nada más | `regla('.cromo[data-estado="scroll"]')` contiene exactamente las dos reasignaciones; `regla('.bandeja', primer bloque)` contiene `height: var(--asb-alto-modulo-superior);`; `regla('.pestanas')` contiene `height: var(--asb-alto-modulo-inferior);`; `[data-estado="scroll"] .pestana__rotulo {` contiene `grid-template-rows: 0fr` | animar con `--duracion-rebote` (`:310`) |
| las dos rayas de apoyo se encienden juntas | `.cromo-apoyado::before {` antes del primer `@media (min-width: 64rem) {` (ya en `:482-484`) y `.cromo-apoyado .modulo-inferior::after {` **después** de él | borrar la del inferior |
| el rótulo no recorta | `.pestana__rotulo > span {` contiene `overflow-wrap: anywhere`; ninguna pestaña lleva `line-clamp`; ninguna cadena de pestaña ni de chip contiene `leading-`; `regla('.pestana__rotulo')` contiene `min-height: var(--asb-alto-rotulo-pestana)` | poner `line-clamp-2`; escribir `leading-tight` |
| los tokens del móvil y su anulación | `tokens.css`: los cinco altos con sus valores, `--asb-desplazamiento-hoja: 6px`, `--asb-retirada-barra: 100%`, `--asb-hoja-velo:` una vez fuera de las dos medias de accesibilidad y con `var(--asb-superficie)` dentro de cada una, `--asb-cromo-apoyo-inferior:` dos veces; tras `prefers-reduced-motion: reduce`: `--asb-desplazamiento-hoja: 0px`, `--asb-retirada-barra: 0%` y ningún `--asb-alto-modulo`; tras `prefers-contrast: more`: `--asb-cromo-desenfoque: none`; `.hoja-flotante` con `var(--asb-hoja-velo)` y sin `color-mix(`; sin `--duracion-cromo` ni `--asb-desplazamiento-panel` en `tokens.css` ni en `tests/` | borrar una anulación; anular un alto; duplicar el velo de la hoja |
| el velo del móvil sostiene el rótulo | lee el porcentaje del `--asb-cromo-velo` del bloque `(max-width: 63.999rem)` de `tokens.css` (claro y oscuro) y los hex de `--asb-fondo` y `--asb-acento` de cada tema, compone sobre negro (claro) y blanco (oscuro) y exige contraste ≥ 4,5:1 para el acento y para `--asb-tenue`; `menu-usuario.blade.php` no contiene `text-2xs text-apagado` | bajar el velo al 72 % |
| el apartado no cuelga del orden de landmarks ni del zoom | `app.css` contiene `.cromo-fijo ~ main > section:first-child:not(.hero-portada) {` y no `.tema-lateral`; el bloque móvil contiene `body {` con `padding-bottom: calc(var(--asb-alto-modulo-inferior) + env(safe-area-inset-bottom, 0px));` y `padding-inline: env(safe-area-inset-left, 0px)`, `html {` con `scroll-padding-top: calc(var(--asb-alto-modulo-superior)` y `scroll-padding-bottom`, `.cromo-fijo {` con `safe-area-inset-left`, y `@media (max-height: 20rem)` con `position: static`; el layout contiene `viewport-fit=cover`, `lg:scroll-pt-24`, `min-h-svh`, y no `barra-tema`, `scroll-pt-24"`, `min-h-screen`; `hero.blade.php` contiene `pb-28` y no `pb-20` | devolver el `+`; borrar un padding; volver a `min-h-screen` |
| la barra se retira ante el teclado | `.cromo[data-teclado="abierto"] .modulo-inferior {` con `visibility: hidden` y `translate: 0 var(--asb-retirada-barra)` | quitar `visibility` |
| la hoja no bloquea el gesto en vertical | `regla('.hoja-inferior')` del bloque móvil no contiene `overflow-y`, `overscroll-behavior` ni `touch-action`; dentro de `@media (orientation: landscape) and (max-height: 30rem)` la regla `.hoja-flotante {` contiene `touch-action: pan-y pinch-zoom` | sacar `overflow-y: auto` fuera de la media |
| la marca cruza sin recortarse | `navbar.blade.php` contiene `'marca-compacta' => auth()->check()` y `min-h-11` en el logo; el bloque móvil contiene `.marca-compacta .logo-doble {` y `[data-estado="scroll"] .logo-doble` **después** de `.logo-doble {`; `/contacto` anónimo sin `marca-compacta`, con sesión con; sin `object-fit`, `clip-path`, `mask`, `filter:` sobre `.logo-doble`; `logo.blade.php` conserva `width="156" height="108"` | poner el cruce antes; quitar la clase |
| el tema y la cuenta en los dos anchos | `/contacto` anónimo: `id="popover-tema"` una vez y `aria-label="Apariencia del sitio"` exactamente dos veces (botón y `role="group"` del único control, `control-tema.blade.php:45` y `:65`); «Entrar como afiliado» y `route('mi-cuenta.entrar')` dentro de `//div[@id="menu-el-gremio-movil"]`; sin `menu-cuenta` ni «Cerrar sesión»; con secretaría: sin «Entrar como afiliado», con `menu-cuenta`, `>Sec.<`, un `<span ... lg:hidden>` con «Secretaría del gremio» en el disparador, y el `sr-only` empieza por `Sec. ` seguido del nombre; `control-idioma.blade.php` contiene `class="relative max-lg:hidden"`; `menu-usuario.blade.php` contiene `max-lg:static`; `navbar.blade.php` no contiene `modulo-cuenta hidden`; el pie contiene `route('mi-cuenta.entrar')` | quitar el `@guest`; duplicar `control-tema` (cuatro); volver a esconder el módulo |
| lo que era del panel y sigue valiendo | ningún `x-collapse` en `navbar` ni `menu-grupo`; `menu-grupo` conserva `origin-top-left`; `tokens.css` comenta `--duracion-panel` nombrando `.revelar` y el video | — |

### 8.3 Cadenas que entran en `ObjetivoTactilTest::cadenasMedidas()` (solo tras medirlas)

Con `document.elementFromPoint` sobre el cuadrado de 44×44 en Chromium a **320 y 390 px**, en `inicial` y `scroll`, dos temas, y una pasada con el bookmarklet de espaciado de texto (1.4.12) a 320 y 360 afirmando `scrollWidth <= clientWidth` de cada rótulo:

| Fila | Archivo | Cadena | Hipótesis (no cifra) |
|---|---|---|---|
| navbar, pestaña directa | `navbar.blade.php` | `pestana fila-pulsable flex min-h-11 flex-1 flex-col items-center justify-center rounded-xl px-0.5` | 64×68 a 320, 78×68 a 390; 48 de alto en scroll |
| navbar, pestaña de grupo | `menu-grupo.blade.php` | `pestana fila-pulsable flex min-h-11 w-full flex-col items-center justify-center rounded-xl px-0.5` | igual |
| navbar, filas de la hoja inferior y «Entrar como afiliado» | `menu-grupo.blade.php` | `fila-pulsable block rounded-lg px-3 py-3 text-sm` (ya existe) | 45,7 a 368 de ancho |
| navbar, logo con mínimo | `navbar.blade.php` | `-my-1.5 flex shrink-0 items-center py-1.5` + `min-h-11` | 44×153,5 y 44×40 |
| menú de usuario, chip a dos renglones | `menu-usuario.blade.php` | `-m-1 flex items-center gap-2 rounded-full p-1` (ya existe) | 47,9 de alto; no roba el toque al tema; la hoja no desborda a 320 |
| navbar, «Afíliate» en móvil | `navbar.blade.php` | `after:absolute after:inset-x-0 after:-inset-y-1 after:content-['']` | 45,7 en la bandeja de 56 y de 48 |
| navbar, control de tema | `control-tema.blade.php` | `flex h-11 w-11 items-center justify-center rounded-full` | 44×44 en los dos estados |
| pie, «Entrar a mi cuenta» y última fila | `footer.blade.php` | `flex min-h-11 items-center` | responden con la página al final y la barra encima |

### 8.4 Verificación en navegador (fuera de PHPUnit)

`playwright-cli` sobre `localhost`, `--device`, toques por CDP, en `360x800`, `390x844`, `430x932`, `768x1024`, `844x390` y `320x180` con `deviceScaleFactor` 4; claro y oscuro; anónimo, asociado y secretaría; confirmando dentro de la página que `matchMedia('(pointer: coarse)').matches` es verdadero:

1. **Vidrio y raíz de fondo, antes que nada**: `getImageData` sobre una franja de texto que pase por detrás de cada módulo **y de cada hoja abierta** (las cuatro): que los dos `::before` desenfocan la página y que una hoja colgada de un módulo también. Si no, `--asb-hoja-velo` sube al 94 %. De paso, si los popovers de escritorio desenfocan en `inicial` (cuelgan de una `.bandeja` con `backdrop-filter`, `app.css:661-670`): hallazgo de la Parte I.
2. **Contraste medido**: `getImageData` del rótulo activo, del rótulo en reposo y del rango del chip sobre el vidrio en `/directorio` con fotos detrás, en los dos temas; acento a 11 px ≥ 4,5:1, icono ≥ 3:1.
3. **Bloque contenedor**: `getBoundingClientRect().bottom === window.innerHeight` de `nav#menu-movil` en `/contacto` y `/` con `scrollY` 0 y 600; a 1280 `getComputedStyle(nav#menu-movil).display === 'none'`.
4. **Geometría**: altos 56/68 en `inicial` y 48/48 tras 30 px; objetivos de §8.3; «Abre tu negocio» en dos líneas sin recorte, las otras cuatro en una; primera línea de `/contacto`, `/directorio`, `/afiliate` bajo el superior; `location.hash` sobre un ancla de `/guia` con `safe-area-inset-top` emulado aterriza bajo el cromo; `getBoundingClientRect().left` del `<h1>` de `/contacto` y del primer campo de `/afiliate` ≥ inset izquierdo en 844×390; el último enlace del pie con la página al final; `/directorio?vista=mapa`.
5. **Dirección e histéresis, desde cualquier parada**: `scrollTo` en pasos de 10 px y lectura de `data-estado`: compacta entre 24 y 32; desde cualquier punto de parada subir 10 no devuelve y subir 13 sí; `scrollTo(0)` devuelve; `scrollTo(600)` de golpe no cambia; `scrollTo(scrollHeight)` seguido de `scrollTo(scrollHeight + 50)` no cambia; `cromo-apoyado` sigue encendido a media página con la barra desplegada; abrir una hoja no cambia el alto.
6. **Hojas**: toque en Bolsas abre `#menu-bolsas-movil`; El gremio cierra la primera; el tema cierra la de abajo; `pointerdown` fuera cierra; **arrastre de 30 px sobre la hoja abierta cierra la hoja**; `scrollBy(0, 30)` cierra; abrir con Enter, Tab a la primera fila, flecha abajo: `document.activeElement` sigue dentro de la hoja; Escape devuelve el foco; con sesión, la hoja de cuenta no desborda a 320 y queda por encima del inferior en apaisado; «Cerrar sesión» a un toque; anónimo: «Entrar como afiliado».
7. **Teclado**: `focus()` en el campo de `/contacto` con `visualViewport` emulado pone `data-teclado="abierto"`; `focus()` del segundo campo desde el primero: `data-teclado` no pasa por `null` (observado con `MutationObserver`); `blur()` lo devuelve; la casilla de habeas data no lo retira.
8. **768×1024, 844×390, 320×180**: pestañas centradas; logotipo completo; girar a 1024 con hoja abierta la cierra; módulos compactos en apaisado; a 320×180 tras 200 px de scroll `nav#menu-movil` no cubre el viewport.
9. **Movimiento reducido**: `data-estado` no cambia tras 100 px abajo y 100 arriba; `--asb-desplazamiento-hoja` y `--asb-retirada-barra` computan `0px`/`0%`; la retirada ante el teclado sigue. **Transparencia reducida y más contraste**: `backdrop-filter: none` computado en los dos `::before` y `--asb-hoja-velo` sólido (D-31).
10. **Portada con el video corriendo**: trazado del compositor a 390×844, presupuesto 4 ms por fotograma, con `blur(20px)`; solo si falla se compara con un valor menor y se baja el token con la cifra.
11. **Dispositivo real (iPhone y Android de gama baja, S7)**: barra inferior de Safari y Chrome, inset de 0 a 34 a mitad de scroll, teclado de iOS, `scrollY` en el rebote, `click.outside` sobre fondo (si `pointerdown.outside` tampoco basta, el velo por `:has([aria-expanded="true"])` es la salida documentada), transparencia reducida, y en Android una vez con `interactive-widget=resizes-content` puesto a mano para confirmar que la barra se retira en los dos modos. Las cifras entran en §8.3, en la matriz RNF-12 y en `estado.md` ese día.

## 9. Fuera de alcance, y dónde queda anotado

- **Las veinte vistas sin `<section>` inicial**: defecto preexistente desde el 4 sep; se mide en `directorio/show`, `mi-cuenta/index` y `artistas/show` y se corrige en su propio commit.
- **Los popovers de escritorio y la raíz de fondo** en `inicial` (§8.4, punto 1) y **el disparador de grupo de escritorio sin `aria-current`**: hallazgos de la Parte I, aparte.
- **Botón Atrás del sistema**: sin historial. `CloseWatcher` como mejora progresiva si en la demo se echa en falta.
- **Chip flotante de WhatsApp**: no existe; si llega, nace encima del módulo por el token.
- **`@alpinejs/collapse`** importado sin consumidor: dependencia, decisión aparte.
- **Idiomas**: el chip sigue siendo el sitio reservado; en móvil, oculto hasta que exista la traducción.

## 10. Riesgos conocidos

- Toda cifra de ancho y alto de este diseño es aritmética sobre dimensiones declaradas y sobre dos lectores de la Poppins servida (fontTools y canvas, que difieren hasta 3 px): 319,5 px para el anónimo a 360, 60 px útiles por pestaña a 320, 63,3 de pestaña en 68 de módulo, 39,9 de chip. Nada entra en ObjetivoTactilTest ni en el expediente sin medirse en Chromium a 320, 360 y 390 el día que se construya; si «Directorio» (55,8) no cabe en los 60 útiles a 320, la pestaña baja a px-0 antes de tocar nada más.
- El contraste sobre el vidrio es aritmética sobre los hex de tokens.css con composición alfa lineal (unos 3,0:1 y 1,9:1 hoy; unos 4,6:1 y 5,4:1 con el velo al 88/85 %): la guardia lo recalcula y §8.4.2 lo mide con getImageData sobre fotos reales del directorio. Si el acento a 11 px no llega, el velo sube antes que cambiar el color; y el velo al 88 % es visiblemente menos vidrio que el 72 % de escritorio, cosa que Sua tiene que ver en el teléfono (D-M18).
- La raíz de fondo (Filter Effects 2) no está medida: el diseño da por hecho que una hoja con backdrop-filter colgada de un módulo cuyo vidrio vive en ::before desenfoca la página, y que isolation: isolate no crea raíz de fondo. Es la PRIMERA comprobación de la verificación (getImageData sobre módulos Y hojas abiertas); si falla, --asb-hoja-velo sube al 94 % bajo 64rem por token. De paso puede aparecer que los popovers de escritorio tampoco desenfocan hoy en inicial: hallazgo de la Parte I.
- Seis trampas no existen en Chromium de escritorio y solo se ven en un iPhone y un Android de gama baja reales con el video corriendo: la barra inferior de Safari y de Chrome Android, el inset de 0 a 34 px a mitad de scroll (ya fuera de toda transición), el teclado de iOS que no encoge el layout, el scrollY del rebote (ya con clamp por innerHeight y extremos muertos), click.outside sobre fondo (reserva: pointerdown.outside; segunda reserva: velo por :has), y la transparencia reducida (D-31 sigue abierta). La demo del 4 al 11 sep es en el teléfono del directivo: hay que medir allí antes.
- Coste de composición: dos franjas de backdrop-filter fijas sobre el video de 100svh a 3x de densidad con blur(20px). Se mide antes de tocar el token; si el compositor pasa de 4 ms por fotograma en el Android de gama baja, o la franja sale negra sobre un video en superposición de hardware, se baja el desenfoque con la cifra medida o la portada lo apaga por token. La cifra no existe todavía.
- La detección del teclado exige las dos señales (campo de texto enfocado y viewport visual 150 px por debajo del mayor alto visto en la orientación): un teclado físico en iPad no la dispara y un pinch-zoom sin campo tampoco, pero un pinch-zoom CON un campo enfocado sí retiraría la barra. Caso raro y reversible; se anota para no venderlo como cubierto. Y el modo que encoge el viewport de layout se prueba una vez en Android con interactive-widget=resizes-content puesto a mano.
- La interpolación de grid-template-rows (1fr a 0fr) la animan Chrome 107+, Firefox 66+ y Safari 16+; en iOS 15 y anteriores el rótulo salta y solo funde la opacidad. Aceptable, pero hay que verlo en un iPhone viejo y anotarlo, no descubrirlo en la demo.
- El apartado de 7rem sigue cubriendo solo a las páginas cuyo main empieza por <section>: veinte vistas ya nacen parcialmente bajo la cabecera fija desde el 4 sep, y un módulo superior de vidrio lo hace más visible. Es corrección y no ampliación de alcance, pero queda fuera de este commit.
- El rótulo del video al pie del hero es una colocación provisional en discusión con Ingrid (D-29), y el módulo inferior es lo primero que compite con él a 390x664. El pb-28 lo aparta a 390x844, pero la píldora de afiliados y el antetítulo que faltan por sembrar añaden unos 54 px que hay que volver a medir.
- Los tres cableados nuevos del desplegable entran también en el escritorio: una hoja abierta por hover se cerrará al mover la rueda (nunca con el foco dentro). Es el comportamiento deseado, pero es un cambio de escritorio que hay que enseñar a Sua y grabar en la verificación de 1440x900 junto a la del móvil.
- El umbral de 200 px que ignora saltos puede comerse un evento de un fling muy rápido en iOS (deltas de hasta 100 px por fotograma): la barra decide en el siguiente evento, un fotograma de retraso; si aletea, el umbral sube a 300 con la cifra medida. Y la histéresis 24/12, ahora medida desde el extremo del recorrido, se eligió por coherencia con los umbrales existentes, no con dedos reales: el pliegue de la barra de direcciones de Chrome Android mueve el layout sin gesto y puede contar como recorrido; se observa en Android y, si aletea, se sube la vuelta a 16.
- Bajo movimiento reducido no hay estado scroll en móvil: quien lo tiene activado ve siempre la barra de 68 px. Es la interpretación estricta de WCAG 2.3.3 y el precedente del proyecto; si Sua considera la compactación funcional y no cosmética, la puerta se quita de compactar() y las alturas vuelven a comportarse como layout puro, con la guardia cambiada y la razón escrita.
- Plegar los rótulos en scroll deja una barra de iconos solos: quien no asocie aún el icono con la sección puede desorientarse durante los 12 px que cuesta volver. Si en la demo se echa en falta, la salida es no plegar y compactar solo el relleno (D-M13), un bloque de CSS que no toca la arquitectura. Y si Sua rechaza los iconos (D-M16), §6.2 se reescribe entero antes de codificar.
- Con viewport-fit=cover todo el documento se mete bajo la muesca en apaisado y la compensación vive en una sola regla de body más las de header e inferior: cualquier vista futura con position: fixed propio tendrá que pagar sus insets, y nadie lo vigila salvo esta spec. Sin cover nada de esto existe, a cambio de que el vidrio termine en la barra del sistema.
- La cuenta de href a /afiliate baja de 2 a 1, el módulo de cuenta deja de estar hidden, el segundo <nav> cambia el conteo de :394, tres pruebas afirman --asb-desplazamiento-panel y una docena de literales cambian en cinco guardias: si el rediseño se parte en varios commits hay un estado intermedio con la suite roja o con ids duplicados. Se hace en uno, con vista, CSS, tokens y guardias juntos, y antes se barre grep -rn desplazamiento-panel tests/ y grep -n duracion-panel resources/css.
- Hay otra sesión trabajando en este directorio: 09e8c17 llegó después de lo que cita el encabezado de estado.md (76b6620). Todas las líneas citadas aquí son las de 09e8c17 (árbol limpio al escribir esto); antes de codificar, GIT_OPTIONAL_LOCKS=0 git status, git log --oneline -5 y el encabezado de material/estado.md.
- Con JavaScript apagado no hay hojas: el anónimo pierde «Entrar como afiliado» en la barra (le queda el pie, que por eso gana el enlace), el asociado conserva solo el <noscript> de cerrar sesión, y los dos <nav> se llaman «Navegación principal». No empeora respecto a hoy, y se anota.
- La precarga del isotipo sin media añade una petición de 156x108 px en escritorio desde el primer pintado; es despreciable, pero si alguien vuelve a poner el media por «optimizar», el primer cruce parpadea otra vez en el teléfono: la guardia :190 actualizada es la única red.
- La hoja de cuenta anclada al módulo con max-lg:static confía en que la utilidad con variante gane al class="relative" literal del mismo elemento (orden de Tailwind 4) y en que .modulo siga siendo position: relative (app.css:485): si alguien saca el relative del módulo, la hoja se anclaría al header entero. Se mide a 320 y la fila entra en ObjetivoTactilTest.
## 11. Lo que la crítica cambió

Cada hallazgo se verificó en el repositorio (`main` en `09e8c17`).

1. **`view-transition-name` en los módulos** (movil-real, bloqueante). Confirmado en CSS View Transitions 1 §2.1.1: un elemento con nombre computado distinto de `none`, «at any time», forma stacking context, se aplana y **forma un backdrop root**. Las cuatro hojas habrían muestreado el interior transparente de su módulo. Resuelto: sin nombres (§3.4, §6.4); guardia «`app.css` no contiene `view-transition-name`» (hoy solo aparece en vistas, grep confirmado); `getImageData` sobre hoja abierta en §8.4.1.
2. **`clientHeight` en el clamp** (movil-real). Confirmado: el navegador acota `scrollY` con el `innerHeight` vigente. Resuelto: `innerHeight` en `posicion()` y en `posicionDelDocumento()` de `app.js`, extremos como zona muerta, `scrollAlAbrir` con el mismo clamp (§4.2, §6.3).
3. **El inset en una altura transicionada** (movil-real). Confirmado en mi propio CSS. Resuelto: el alto va en `.pestanas`; el `<nav>` solo paga `padding-bottom` (§5.1, §6.4); guardia: `regla('.modulo-inferior')` sin `env(` en propiedades transicionadas ni `height`.
4. **`viewport-fit=cover` mete todo el documento bajo la muesca** (movil-real). Confirmado: cero `env()` en `resources/`, `hero.blade.php:40` y `footer.blade.php:6` con `px-4`. Resuelto: `body { padding-inline }` y `.cromo-fijo { padding-inline }` (no en `.bandeja`, cuyo `px-4` es utilidad y ganaría, §3.7); medición del `<h1>` y del primer campo en 844×390.
5. **La hoja inferior era contenedor de scroll siempre** (movil-real). Confirmado en el CSS de §6.4. Resuelto: en vertical no lo es; `overflow`, `overscroll-behavior` y `touch-action: pan-y pinch-zoom` solo en apaisado; prueba «arrastre sobre la hoja cierra».
6. **`min-h-screen` y el scroll fantasma de iOS** (movil-real). Confirmado (`publico.blade.php:189`; cinco vistas con `min-h-[65..80vh]`; sin guardia). Resuelto: `min-h-svh`, con guardia.
7. **Teclado medido contra `innerHeight`** (movil-real). Confirmado el razonamiento. Resuelto: `altoReferencia` (mayor alto visto por orientación), prueba con `resizes-content` en Android.
8. **Contraste de 11 px sobre el velo** (accesibilidad). Confirmado: el cromo va al 72/62 % (`tokens.css:180`, `:334`) y solo el velo del hero está calibrado (`:145-163`); la aritmética da unos 3,0:1 y 1,9:1. Resuelto: velo móvil 88/85 % por reasignación de `--asb-cromo-velo` bajo 64rem (D-M18), rango en `text-tenue`, `prefers-contrast: more` vuelve sólido el material, guardia que recalcula (como `VeloDelHeroTest`) y medición en §8.4.2.
9. **`cerrarSiSeDesplaza()` tiraba el foco al body** (accesibilidad). Confirmado (`app.js:136-139`, `:183-187`). Resuelto: no cierra con el foco dentro; regex de la guardia con el `contains`; prueba de flecha abajo.
10. **Landmark «Navegación principal» vacío bajo 64rem** (accesibilidad). Confirmado (`navbar.blade.php:140`, `:155`). Resuelto: el inferior es «Navegación principal», la primera `<nav>` cambia por `x-bind` (§4.1).
11. **Compactar bajo movimiento reducido** (accesibilidad). Confirmado: es animación por interacción (2.3.3) y el precedente es `app.js:88-91`. Resuelto: puerta en `compactar()` por `html.sin-desplazamiento`; §5.3 reescrita.
12. **`line-clamp` recorta con espaciado de texto** (accesibilidad). Confirmado por aritmética (69 px contra 60-68 útiles). Resuelto: sin `line-clamp`, `overflow-wrap: anywhere`, pasada de 1.4.12 en §8.3.
13. **320×180 al 400 %** (accesibilidad). Resuelto: `@media (max-height: 20rem)` con el inferior en flujo.
14. **Nombre accesible del chip** (accesibilidad). Confirmado el precedente (`NavbarTresEstadosTest.php:246`). Resuelto: texto visible primero, con el prefijo.
15. **El grupo activo no se anunciaba** (accesibilidad). Resuelto con `aria-current="true"` en la pestaña de grupo (precedente en `guia/index.blade.php:31` y `conmutador-eventos.blade.php:70`; la guardia de `NavegacionAgrupadaTest` compara el valor `page`).
16. **`focusout` entre campos parpadeaba la barra** (accesibilidad). Resuelto con `esCampo($event.relatedTarget)` y `MutationObserver` en la verificación.
17. **`ComponentesDelPanelTest.php:70`** (oficio). Confirmado por grep: tres pruebas afirman `--asb-desplazamiento-panel`, no dos. Añadida a §8.1.
18. **`scroll-pt-24` bajo la muesca** (oficio). Confirmado (`publico.blade.php:2`). Resuelto: `scroll-padding-top` móvil por token; `<html>` a `lg:scroll-pt-24` porque la utilidad ganaría al componente (§3.7).
19. **`lg:hidden` sin guardia** (oficio). Resuelto: la fila del landmark exige `lg:hidden`, `position: fixed;` y `bottom: 0;`, y `display: none` a 1280 en §8.4.3.
20. **La histéresis costaba 12 a 36 px** (oficio). Confirmado siguiendo el algoritmo. Resuelto: el ancla sigue al extremo del recorrido en el sentido vigente; pinzado.
21. **Iconos sin decisión** (oficio). Confirmado: el brief dice «botones» y el escritorio no lleva iconos. Resuelto: D-M16, de la que dependen D-M3 y D-M13.
22. **`leading-tight` contra la escala** (oficio). Confirmado (`app.css:62-66`, `:73`). Resuelto: sin `leading-*`; rótulo de 36,3 por `--asb-alto-rotulo-pestana`; módulo inferior a 4.25rem; chip a 39,9 px; §8.3 rehecha; guardia contra `leading-` en las cadenas nuevas.
23. **Tema en popover frente a un toque** (oficio). Confirmado (`barra-tema.blade.php:29-47`). Resuelto: D-M17.
24. **`blur(14px)` preventivo** (oficio). Confirmado (`c650f3a`). Resuelto: se construye con 20 y se mide; entra en D-M18.
25. **`reposar()` al abrir** (oficio). Confirmado el doble resorte. Resuelto: no hay `reposar()`; guardia lo prohíbe.
26. **La guardia «seis destinos» no se ponía roja** (oficio). Confirmado. Resuelto: `count(ancestor::*[@x-data="desplegable"]) = 1`.
27. **`--asb-hoja-velo` duplicado** (oficio). Confirmado (`tokens.css:273`: `.dark` es el `<html>`). Resuelto: una declaración; guardia exige una.
28. **`--duracion-panel` con tres consumidores** (oficio). Confirmado (`app.css:1040-1042`, `:1140`). Resuelto en el comentario y en la guardia.
29. **«Entrar como afiliado» declarado en el componente** (guardias-y-dom). Confirmado (`menu-grupo.blade.php:17` solo recibe `titulo` y `enlaces`; `NavegacionAgrupadaTest.php:199-214` barre solo `navbar`). Resuelto: `'pie'` en `$grupos`, prop `:pie`, sin `$titulo === '...'`; guardia cuenta `'texto' => 'Entrar como afiliado'` una vez.
30. **Comentarios que ponían rojas las guardias** (guardias-y-dom). Confirmado el mecanismo (`assertStringNotContainsString` sobre archivo). Resuelto: §3.8; los comentarios de `.cromo`, de `init()` y del vidrio nombran sin pegar; la raya inferior se afirma **después** del bloque de 64rem, que es donde vive.
31. **«Apariencia del sitio» dos veces por control** (guardias-y-dom). Confirmado (`control-tema.blade.php:45`, `:65`). Resuelto: se cuenta `id="popover-tema"` una vez y la etiqueta dos.
32. **`control-idioma` no fusiona atributos** (guardias-y-dom). Confirmado (`:31` literal; solo `menu-grupo.blade.php:50` usa `$attributes`). Resuelto: el literal del componente cambia; la guardia lo lee ahí.
33. **La hoja de cuenta desborda 4 px a 320** (guardias-y-dom). Confirmado por aritmética sobre `menu-usuario.blade.php:52`, `:88`, `navbar.blade.php:139`, `control-tema.blade.php:46`. Resuelto: `max-lg:static` en la raíz de `menu-usuario` (el `.modulo` es `relative`, `app.css:485`).
34. **Docblocks que contarían una barra que no existe** (guardias-y-dom). Confirmados los cinco (`NavbarTresEstadosTest.php:14-17`, `:170-172`, `:617-627`; `MovimientoTest.php:401-404`; `ObjetivoTactilTest.php:87-89`). Añadidos a §8.1.

## 12. Lo que la crítica dijo y no se acepta

1. **Poner el `view-transition-name` en los pseudoelementos `::before`** (movil-real, como alternativa). No: no hay garantía de que un pseudoelemento con nombre se capture igual en todos los motores, y no hay ganancia visible que lo justifique: la barra es idéntica en los dos documentos y el fundido de 180 ms de `root` (`app.css:1269-1273`) no la mueve. Se prescinde del nombre; si algún día se quiere la barra quieta de verdad, es un experimento con su medición, no una línea en este diseño.
2. **`visualViewport.height + offsetTop` para el tope** (movil-real, como alternativa). No: `innerHeight` es exactamente lo que el navegador usa para acotar `scrollY`, y meter el zoom en la máquina de estados abre otro frente (el pinch-zoom cambia `offsetTop` sin gesto de scroll).
3. **`<x-slot:pie>` para la fila de invitado** (guardias-y-dom, como alternativa). No: un slot se escribe en la vista que pinta y volvería a declarar el destino fuera del arreglo; el prop `:pie` viaja desde `$grupos` y es lo que la guardia puede contar.
4. **Un paso de escala nuevo para rótulos de barra** (oficio, opción 2). No: sería un tamaño de 0.6875rem con un segundo leading, y `TipografiaTest.php:64-69` exige que la escala sea monótona por forma; la reserva de dos líneas sale de la escala existente y el módulo gana 4 px.
5. **`reposar()` al cerrar la hoja en vez de al abrir** (oficio, como alternativa). No: seguiría siendo un cambio de tamaño que el usuario no pidió, ahora justo después de soltar; la dirección del scroll es la única señal que Sua describió y la barra no se mueve por abrir ni por cerrar.
6. **`<span class="sr-only">, sección actual</span>` en el botón de grupo** (accesibilidad, como alternativa a `aria-current="true"`). No: el proyecto ya usa `aria-current="true"` en dos filtros (`guia/index.blade.php:31`, `conmutador-eventos.blade.php:70`), es semántica nativa y no suma texto invisible que un lector recite con la coma; y no se lleva al disparador de escritorio en esta fase porque es un hallazgo de la Parte I y toca un archivo con cadenas pinzadas (`menu-grupo.blade.php:55-75`).
7. **Bajar el desenfoque móvil «si Sua quiere más transparencia» hasta 0,72 y pasar el rótulo activo a `text-tinta` con el rojo solo en el icono** (accesibilidad, variante). No se toma como alternativa de la tabla: dejaría el rótulo activo sin el color que en escritorio marca la sección (`text-acento`, `NavegacionAgrupadaTest.php:325-331`) y dos vocabularios por ancho; el velo al 88/85 % es lo que permite conservar el mismo lenguaje. Si Sua prefiere más transparencia, es una decisión nueva con su propia medición, no una nota al pie.
8. **Cambiar la guardia de `:394` de vuelta a un solo `<nav>` con un `<div role="navigation">`** (implícito en la restricción 1 de la Parte I). Ya rechazado en la primera versión y se mantiene: esquivar la guardia con ARIA en vez del elemento nativo es esconder el cambio.