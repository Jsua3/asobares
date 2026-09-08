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

**«Afíliate» (anónimo).** La pastilla de escritorio dentro de `@guest`, cadena medida `after:absolute after:inset-x-0 after:-inset-y-1.5 after:content-['']` (`ObjetivoTactilTest.php:119-123`) y sus cuatro piezas (`:314-348`); se re-mide en la bandeja de 56 y de 48. El `href="/afiliate"` en el header pasa de **2 a 1** para el anónimo (`NavbarTresEstadosTest.php:632-636`).

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
| navbar, «Afíliate» en móvil | `navbar.blade.php` | `after:absolute after:inset-x-0 after:-inset-y-1.5 after:content-['']` | 45,7 en la bandeja de 56 y de 48 (medido el 6 sep: la escala tipográfica dejaba la pastilla en 33,7 y con `-inset-y-1` el área daba 42) |
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


## 13. Lo que la construcción cambió (6 sep 2026)

La Parte II se escribió antes de tocar código y el código la contradijo en ocho sitios. Lo que sigue es lo que de verdad quedó, para que la próxima sesión lea esto y no la propuesta.

### 13.1 Lo que el navegador corrigió mientras se construía

1. **El apaisado compacta desde `.cromo`, no desde `:root`.** La media de `max-height: 30rem` reasignaba los tokens de alto sobre `:root` dentro de `@layer components`, y `tokens.css` no está en ninguna capa: una regla sin capa gana a cualquier regla en capa, así que la reasignación no llegaba nunca. Medido: 56/68 con los rótulos ya plegados, en vez de 48/48. Los tokens se reasignan sobre `.cromo`, que es de la capa y del componente.
2. **La pastilla «Afíliate» necesita `-inset-y-1.5`.** La escala tipográfica deja la pastilla en 33,7 px de alto, no en 37,7: con 4 px por lado el área pulsable daba 42 y no los 44 exigidos. Con 6 px da 45,7, medidos a 1440 y a 390 y también en la bandeja compacta.
3. **El segundo bloque de CSS móvil va después de `.hoja-flotante`.** Colocado antes, las reglas de la hoja pisaban las del módulo por orden de cascada.
4. **A 320×180 no basta con sacar del fijo al módulo inferior.** `position: static` sobre el módulo no lo devuelve al flujo del documento: sigue siendo hijo del `<header>` fijo, así que solo lo convertía en una segunda fila de una cabecera de 96 px, que es exactamente lo que la media quería evitar (medido: 96 de 180 px, y la barra no se iba con el desplazamiento). Sale del fijo el header entero; el módulo queda `relative` para seguir siendo el bloque contenedor de sus hojas, y se apagan el apartado de la primera sección y el `scroll-padding-top`.
5. **La raya roja del módulo superior necesita `z-index: 3`.** La bandeja es `relative` con `z-index: 2` y aísla, y la raya del header es absoluta sin z-index: se pintaba debajo del vidrio al 88 %, es decir un 4 % de rojo efectivo. Los dos bordes coinciden en 48,0 px, así que no era cuestión de geometría sino de orden de pintado.

### 13.2 Lo que la revisión adversaria encontró (y no había guardia que lo viera)

6. **El velo base había subido de 72 % a 88 %.** D-M18 reasigna el velo del móvil bajo 64rem; el primer WIP cambió además la línea base de `:root`, con lo que la barra de **escritorio** en claro cambió de material sin decisión mientras el oscuro seguía en 62 %. Cuatro lectores independientes lo señalaron. La guardia nueva fija 72 en la base y 62 en `.dark`, y deja el 88 / 85 solo dentro de la media del móvil.
7. **El pie ofrecía la entrada del afiliado con sesión abierta.** La fila «Entrar a mi cuenta» nació en esta rama para que la entrada exista sin JavaScript (D-M4); como la fila de la hoja de El gremio, es del anónimo. A quien ya tiene sesión el formulario de afiliados le reemplazaría la suya.
8. **El módulo inferior declaraba `translate: 0 0`.** Un translate identidad hace al elemento bloque contenedor de sus descendientes fijos: la misma trampa que se le había quitado al header el 4 sep. Se retira junto con los otros dos valores iniciales redundantes.

### 13.3 Lo que queda abierto

- **El foco al cruzar 64rem.** El header despacha el cierre general y cada desplegable llama a `cerrar()`, no a la variante que devuelve el foco: girar un iPad con teclado y una hoja abierta deja el foco en el cuerpo. Devolverlo al disparador tampoco sirve, porque ese disparador se oculta con su módulo. Pide decisión propia.
- **Los dos vidrios sobre el video.** El móvil paga dos `backdrop-filter` permanentes y un tercero con la hoja abierta, cuando el velo al 88 % deja al desenfoque como mucho un 12 % del píxel. En este equipo el desplazamiento guiado dio 6,1 ms de mediana con el video corriendo; la medición que decide es la del teléfono de gama baja, que D-M18 dejó pendiente.
- **La duplicación del degradado de la raya.** `.cromo::before` y `.modulo-inferior::after` repiten el mismo `linear-gradient`. Un token lo unificaría; no se toca hoy para no mover el vocabulario de tokens en la misma rama que estrena la barra.


---

# Parte III · La barra lateral del panel: cristal, luz y resorte — diseño aprobado

**7 de septiembre de 2026** · Persona 1 (Sua) con Claude Code · sobre `main` en `b1b270d` · **aprobada por Sua el 7 sep 2026**, textual: «apruebo todo». Las dieciocho decisiones de más abajo (D-L1 a D-L18) quedan tomadas con la recomendación de cada una. Se escribe **antes** de la primera línea de código, como las dos partes anteriores. Encargo de Sua del 7 sep, textual: «vamos a hacerle rework al menú de la izquierda que ahora mismo está rojo oscuro, y el rework hará que sea muy semejante a la navBar de escritorio pero vertical [...] debe permitir observar animaciones de resorte a la hora de scrollear y en general tenemos que definir todo su funcionamiento, animación y calidad gráfica [...] también quiero que aquí mantenga un cristal con detalles luminiscentes en rojo claro para el modo claro y en rojo oscuro para el modo oscuro».

Cómo se hizo: cinco miradas independientes sobre el mismo encargo (movimiento, material, estados, encaje con Filament y accesibilidad), cada una criticada por un adversario que verificó contra el repositorio, y una síntesis que resolvió las cinco contradicciones entre ellas. Los hechos que sostienen las decisiones están comprobados en el vendor de Filament 4.12.5 y en las guardias vigentes; los tres principales se volvieron a comprobar a mano antes de registrar esto.

**Qué es esta barra.** Es la navegación permanente del panel de administración: una columna de 15,25 rem con 24 destinos repartidos en cinco grupos, que en escritorio vive en el flujo del layout (`.fi-layout` es `flex`, `.fi-main-ctn` es `flex-1`) y se queda pegada con `lg:sticky` bajo el topbar, y que por debajo de 64 rem es un cajón `fixed` que entra sobre el contenido. Tiene que hablar el idioma de la barra pública: un solo DOM, el estado en un atributo, todo el CSS por selector, el vidrio siempre en un pseudoelemento, el vocabulario en tokens y el comportamiento en JavaScript con nombres en español.

**Qué NO es.** No es la navBar de escritorio girada noventa grados. La navBar pública se retrae porque le roba alto a la lectura; esta barra no le roba nada al contenido, que va a su lado, así que no se retrae, no se compacta y no se va. Su cabecera con el logotipo no existe en escritorio (Filament la marca `lg:hidden` cuando hay topbar), así que no hay logotipo que condensar. Y detrás de ella, en escritorio, no pasa nada: solo el color plano de `.fi-body`. No es tampoco un carril de iconos: el plegado de escritorio está apagado y encenderlo es otro encargo. Lo único que de verdad se desplaza aquí es su propia lista, y ahí es donde tiene que estar todo lo que se mueve.

## La revisión adversaria, 8 sep 2026 (tarea 11)

Hecha por ángulos sobre lo construido, verificando cada sospecha contra el repositorio. Dos hallazgos confirmados, los dos arreglados con la guardia vista roja antes del arreglo.

**1. Cinco tokens declarados y sin ningún consumidor, y dos guardias verdes encima de uno de ellos.** `--asb-admin-barra-filo` existía —según D-L9 y D-L17— para volverse línea bajo más contraste, y dos pruebas afirmaban que estaba declarado y que la señal lo reasignaba. Hacía dos días que **nadie lo consumía**: perdió su consumidor cuando Sua rechazó el filo rojo y pidió continuidad. Lo mismo `--asb-admin-barra-sombra`, la otra mitad de aquel límite. Y tres más de la paleta borgoña vieja del panel: `--asb-admin-rojo`, `--asb-admin-borgona`, `--asb-admin-borgona-profundo`. Los cinco fuera.

La guardia que existía vigilaba un token concreto, `--asb-admin-barra-union`. La nueva **generaliza a los cuarenta y dos**: ninguno se declara sin que alguien lo consuma, contando el consumo por `var()` en CSS y por `getPropertyValue` en JavaScript, que es como el campo de puntos lee su color.

**Corrección a D-L9 y D-L17, entonces:** el filo ya no existe como token. Bajo más contraste, lo que se vuelve línea es `--asb-admin-barra-modulo-canto`, que es el canto que de verdad se pinta.

**2. El comando de la maqueta escribía dentro de `public/`, y eso queda servido.** En una máquina de trabajo es lo que se quiere; en producción es publicar una página que nadie pidió con el marcado del panel dentro. El comando se niega ahora en producción, y se niega **antes** de tocar el disco.

**Barrido final:** sin sondas, sin `dd(`, sin `console.log`, sin `FUGA`, y `git status` limpio salvo lo que entra en el commit.

### D-L29. En el teléfono la barra es un riel de iconos

**Pedido de Sua, 8 sep, con una captura del panel en un teléfono:** «la barra del panel en el móvil está terrible. Quiero que se vean los iconos a la izquierda y que al desplegarlo aparezcan los nombres correspondientes. Tiene que ser responsivo a la pantalla de un teléfono correctamente».

**Primero, el defecto que hacía «terrible» la captura, y que no era de diseño.** El tema declaraba `position: relative` en `.fi-sidebar`. Filament la declara `fixed` y solo la vuelve `lg:sticky` en escritorio, y **nuestra regla va después en el archivo compilado** —que no lleva capas: `lightningcss` las aplana y manda el orden—, así que ganaba en todas las anchuras. Consecuencias, las dos comprobadas leyendo el CSS servido:

1. **En el teléfono**, el cajón cerrado dejaba de estar fuera de pantalla y pasaba a ocupar sus 252 px **en el flujo**: la franja rosa vacía de la captura, con el contenido aplastado contra el canto derecho.
2. **En escritorio**, `lg:sticky` también perdía, así que la barra se iba con el desplazamiento de la página en vez de quedarse.

La regla existía para que el resplandor de `::before` tuviera bloque contenedor. Desde que el resplandor se mudó a `.fi-body::before` **no hace falta ninguna**, y se retira. Hay guardia: `.fi-sidebar` no declara `position`, y el mensaje dice por qué.

**El riel.** Por debajo de 64 rem la barra deja de irse: se **estrecha**.

| | Riel (cerrado) | Cajón (abierto) |
|---|---|---|
| Ancho | `--asb-admin-barra-riel`, 3,5 rem | `--sidebar-width`, 15,75 rem |
| Qué se ve | solo los iconos | icono y nombre |
| Dónde está | fijo al canto izquierdo, debajo del topbar | encima del contenido, con el velo de cierre de Filament |
| El contenido | apartado por el ancho del riel | quieto; el cajón se le superpone |

**Cuatro reglas que no se negocian:**

1. **El nombre no se borra, se esconde.** Los rótulos se ocultan con recorte visual (`clip-path: inset(50%)` sobre un cuadro de 1 px), **nunca con `display: none`**: el enlace conserva su nombre accesible y el riel sigue siendo navegable a ciegas. Un riel de iconos sin nombre accesible es una lista de enlaces sin texto.
2. **La fila del riel sigue midiendo 44 px** como mínimo, y el riel 56 px de ancho, que da margen a los cuatro cantos del cuadrado táctil.
3. **La transición es del ancho, declarada a mano.** Filament pone `transition-all` en `.fi-sidebar`, que es justo lo que la Parte III ya prohibió para el cajón: se declara `transition-property: width` con su token de duración, y bajo movimiento reducido no hay transición.
4. **`translate: none`, no `translate: 0 0`.** Deshacer el `-translate-x-full` de Filament con un cero deja un `translate` computado distinto de `none`, y eso convierte a la barra en bloque contenedor de todo `fixed` que cuelgue dentro. Es el mismo pisotón que este proyecto ya pagó dos veces.

**Lo que el riel se lleva por delante:** la cabecera con el logotipo, que en 56 px no cabe y que ya está en el topbar; el chevron de los grupos, que sin rótulo no plega nada legible; y el contador de la insignia, que a 56 px no se lee y se convierte en **punto**, para no perder la señal de que hay pendientes que D-L22 le encargó.

**Y el resplandor se estrecha con la zona:** 13 rem de lavado sobre una pantalla de 375 px cubren más de la mitad. Por debajo de 64 rem el ancho del lavado baja a 6 rem, que es lo que marca un riel de 3,5.

**Corregido el mismo día: el riel no tiene suelo.** Sua lo vio construido: «deja solo los módulos y quita la barra blanca de fondo que los agrupa para que así se les vea libertad, y a los módulos entrégales un poco de transparencia». Medido en la maqueta, ese blanco no era de la barra —que computa `rgba(0,0,0,0)`— sino de **`.fi-sidebar::before`, que pintaba el velo del CAJÓN al 94 % también cuando la barra está cerrada**. El velo del cajón tiene su razón (D-L18: debajo pasa contenido variable y hay que taparlo) y esa razón **no existe en el riel**, que no tapa nada: solo está a un lado.

Así que el suelo se ata al estado. Abierta, velo y desenfoque como estaban. Cerrada, **nada**: los módulos flotan sobre el campo de puntos y el resplandor, que es exactamente el idioma del escritorio —«la barra no tiene fondo propio»— llevado al teléfono.

Y el cristal de los apartados baja del 76 % al **66 %** por debajo de 64 rem, que es la transparencia que Sua pidió. Recalculado el 8 sep con `MideContraste`: el rótulo de grupo da **11,08:1** en claro y **7,74:1** en oscuro; el del ítem activo, **6,23:1** y **5,23:1**. Contra los 11,18 / 6,29 y 7,74 / 5,23 que daba al 76 %, la cuenta apenas se mueve, porque la superficie y el fondo del panel son casi el mismo color: aquí manda el ojo.

**Lo que la construcción añadió (8 sep).** Tres cosas que no estaban en la decisión y que solo aparecieron al medir:

1. **El ancho del riel se pone moviendo el token de Filament, no la propiedad.** Su regla de ancho cuelga de `.fi-body:not(…):not(…) .fi-sidebar:not(.fi-sidebar-open)` y tiene mucha más especificidad que cualquier `width` declarado aquí: medido, el riel seguía saliendo de 252 px. Como esa regla dice `width: var(--sidebar-width)`, basta con darle otro valor al token dentro de la media.
2. **La reasignación del lavado salió de `@layer components`.** Las REGLAS del riel sí viven dentro de la capa; los tokens no pueden, porque el `:root` sin capa de este mismo archivo les gana. Es la misma trampa de D-L17, ahora en una media de anchura.
3. **La maqueta mintió tres veces y por eso no se veía el defecto.** Declaraba `position: sticky` sobre `.fi-sidebar` —tapando el `fixed` de Filament, que es justo lo que fallaba—, ponía su `<style>` DESPUÉS de la hoja compilada —tapando el `display: none` del chevron— y no reproducía el `opacity: 1` que el blade le pone al contenido con Alpine, así que medía un contenido invisible. Las tres corregidas: la maqueta ya no posiciona nada, su estilo va **antes** de la hoja, y trae `.fi-main-ctn` con el estilo en línea del blade. Tiene también `--cerrada`, que es como se mira el riel.

**Medido después, en la maqueta servida por HTTP.** A 375 px: riel de **56 px**, `fixed`, empezando en `y = 60` bajo el topbar; las 22 filas a **48 px**; ninguna desborda el riel; los iconos centrados; el chevron y la cabecera fuera; el contenido empezando en **56**; y el nombre accesible intacto (`Asociados`, `Eventos y capacitaciones`…). Abierto: **252 px**, `fixed`, `z-index: 30`, velo al 94 % con `blur(18px) saturate(1.3)`, rótulos visibles y ninguno recortado. A 1.280 px: `position: sticky` —**recuperado**, llevaba dos días en `relative`—, 252 px, contenido en 252 y sin relleno de riel.

---

### D-L30. El teléfono, rehecho: cromo, perfil anclado, riel con aire y resorte al gesto

**Pedido de Sua, 8 sep, viendo el panel en un teléfono.** Cuatro cosas, y una quinta que resolvió al preguntarle: **el cambio es solo del teléfono**. En escritorio no se toca nada, porque lo que hay está aprobado y desplegado.

**Qué skill se usó, y qué no.** Sua propuso una de diseño de Apple. **No existe** entre las suyas ni entre las que puede añadir; se dijo en vez de improvisar una. Se usó `ui-ux-pro-max`, que trae su guía pero no su buscador, y de ella salen cuatro exigencias que se aplican aquí: objetivo táctil de 44 px, nombre accesible en botones de solo icono, escala de `z-index` declarada, y `prefers-reduced-motion` respetado. Los principios de la escuela de Apple que Sua quería —deferencia, manipulación directa, muelles con masa— ya son el idioma de este proyecto: están en `--ease-rebote-suave` y `--ease-rebote-vivo` desde la Parte I.

#### 1. El cromo superior

Hamburguesa a la izquierda **donde está**, logotipo **centrado**, control de tema a la derecha. La cuenta se va de ahí.

El centrado es por rejilla `1fr auto 1fr` y no por `justify-content`, que es lo mismo que hace la barra pública de escritorio: con dos costados de anchura distinta, centrar el contenedor deja el logotipo descentrado a ojo, y el ojo lo nota.

#### 2. El perfil, anclado al pie de la barra

Baja a la barra lateral, **abajo a la izquierda y anclado**, visible en cualquier momento. Los iconos que se desplazan **pasan por debajo**, no chocan con él.

Eso obliga a que el perfil **flote sobre la lista**, no que sea su último elemento: si fuera un hermano al pie, la lista terminaría encima y no habría nada que pasara por debajo. Va absoluto sobre el canto inferior, con el cristal de la casa —velo y desenfoque— para que lo que pasa debajo se intuya y no estorbe. La lista gana relleno inferior igual a su alto, o el último destino quedaría inalcanzable.

En el riel se ve **solo el avatar**; con la barra abierta, avatar, nombre y rango, que es el chip que ya existe. **El del cromo y el de la barra son el mismo componente en dos ganchos**, y el que no toca se apaga con `display: none` y no solo se esconde: si no, quedaría un duplicado invisible recibiendo tabulación, que es un incumplimiento que este proyecto ya arregló una vez en el cajón.

#### 3. El riel gana aire, y por eso crece

Los módulos dejan de estar pegados al canto. Pero el aire sale de algún sitio: con el riel en 56 px y 8 px a cada lado, el módulo cae a 40 y **el objetivo táctil se rompe**. Así que el riel **sube a 4 rem (64 px)**: 8 px de aire a cada lado, módulo de 48, fila de 48. Es la cuenta que hace que el aire no se pague con el dedo.

#### 4. El resorte va en los iconos, ligado al gesto

Al desplazar el riel, cada icono **se retrasa respecto al dedo y llega con muelle**, y tanto más cuanto más rápido el gesto. Es manipulación directa: el movimiento responde a lo que hace la mano, no a un reloj.

| Opción | Coste |
|---|---|
| A. Un desfase por ícono, integrado con muelle, escalonado por posición | 22 nodos con `translate` por fotograma. Solo compositor, sin disposición ni pintura. El bucle corre mientras algo se mueve y se para solo, como el campo de puntos |
| B. El desfase por módulo, cinco nodos | Más barato y se lee como cinco bloques rebotando, no como una lista con inercia. No es lo que se pidió |
| C. Una transición CSS por ícono | No puede depender de la velocidad del gesto: es un reloj, no una respuesta |

**Recomendación: A**, con tres condiciones que no se negocian:

1. **`translate` y nada más.** Ni `top`, ni `margin`, ni `height`: cualquiera de esos mide la página en cada fotograma.
2. **El bucle se para solo** cuando todo está en su sitio, y no arranca bajo `prefers-reduced-motion`. Mismo contrato que el campo de puntos.
3. **Nada de lo que se mueve recibe el dedo mientras se mueve** más de 4 px: un destino que huye del pulgar es peor que un destino quieto.

**Lo que NO cambia:** el escritorio entero; el cajón abierto con sus nombres; el campo de puntos; el resplandor; y el objetivo táctil de 48 px, que sigue siendo el suelo.

#### Lo que la construcción cambió (8 sep)

1. **La primera reacción del resorte se pinta en el mismo gesto**, no en el fotograma siguiente. Se escribió así al descubrir que no había forma de verlo —`requestAnimationFrame` no corre con el panel del navegador oculto— y resultó ser además lo correcto: la respuesta sale con la mano y no detrás de ella. El bucle se queda con el regreso.
2. **`ARRASTRE` se calibró midiendo, no a ojo.** Con 0,55 un desplazamiento de 24 px por fotograma —un pase normal del pulgar— ya saturaba el tope y todos los iconos se quedaban en 14: la respuesta al gesto se perdía justo donde importa. Con **0,35** el rango útil cubre de 5 a 40 px por fotograma. Medido: un gesto de 5 px da −1,7 / −2,4 / −3,0 px en la primera fila, la de en medio y la última, que es el escalonado que se buscaba.
3. **El orden del archivo compilado no es el nuestro, y esto costó dos vueltas.** `lightningcss` aplana las capas, agrupa las medias y mueve reglas, así que dos reglas de la misma especificidad **no** se resuelven como están escritas. Pasó dos veces seguidas: el `display: none` de escritorio salió después del de móvil y lo anulaba, y `position: relative` de `.asb-barra-cuenta` le ganaba a `position: absolute` de `.asb-cuenta-al-pie`. Se arregló **sin depender del orden**: el apagado de escritorio vive en su propia media de `min-width`, y las reglas del teléfono llevan `.fi-sidebar` delante para ganar por especificidad. Es una regla general de este archivo desde hoy.
4. **La maqueta mintió tres veces más** y por eso el defecto del `position` llevaba dos días invisible: declaraba `position: sticky` sobre `.fi-sidebar` —tapando justo lo que fallaba—, ponía su `<style>` **después** de la hoja compilada —tapando el `display: none` del chevron— y no reproducía el `opacity: 1` que el blade le da al contenido con Alpine, así que medía un contenido invisible. Las tres corregidas. Pinta ya el cromo entero, la cuenta al pie y carga el módulo del resorte.

**Medido en la maqueta a 375 px:** logotipo a **5 px** del centro de la pantalla; riel de **64**, módulo de **48**, aire de **8** a cada lado; cuenta al pie **absoluta**, `z-index: 10`, 64×64, pegada al canto; lista reservando **68 px**, con el último destino acabando en 722 y la cuenta empezando en 748 —alcanzable—; y la copia de la cuenta del cromo en `display: none`.

**Lo que falta y no puede medirse aquí:** el tacto del resorte en un teléfono de verdad. Dos constantes lo gobiernan, `ARRASTRE` y `AMORTIGUACION`, y se ajustan en una línea cada una.

#### Siete correcciones de Sua, el mismo día

Vio la primera versión en el teléfono y nombró siete cosas. Las siete, y lo que se hizo:

1. **El logotipo se veía pequeño.** Su tope en el teléfono era de 6,5 rem, heredado de cuando compartía fila con la cuenta. En el centro del cromo hay sitio de sobra —a 375 px, 44 de hamburguesa y 44 de tema dejan 287—, así que sube a **10 rem**.
2. **El menú de la cuenta no se podía abrir con la barra cerrada.** No era que no respondiera: la hoja colgaba del chip **hacia abajo y hacia la izquierda**, que es correcto en el cromo y absurdo al pie de una barra de 64 px, así que se abría fuera de la pantalla. Ahora abre **hacia arriba y hacia dentro**.
3. **Había un corte entre el cromo y el cajón.** Dos alturas para lo mismo: una media de 40 rem dejaba el cromo en 3,45 rem mientras la barra empieza en `--asb-admin-topbar-alto` (3,75). Cinco píxeles por los que se veía el contenido colarse. **El alto del cromo pasa a ser uno solo, el del token.**
4. **Los módulos seguían pegados al canto.** Y era cierto para uno: **«Tablero» no tiene grupo**, y Filament pinta los destinos sin grupo sueltos en la lista, fuera de todo `.fi-sidebar-group`. No recibía ni cristal ni aire mientras los demás flotaban. Ahora es un módulo más.
5. **El resorte movía lo de dentro y no los módulos.** Sua lo diagnosticó con precisión: el indicador rojo del apartado activo se quedaba quieto mientras su fila se desplazaba, **porque el indicador lo pinta el módulo y la fila iba por su cuenta**. Lo que se mueve pasa a ser el módulo —grupo o ítem suelto— y no el botón.
6. **El cajón abierto era un cuadrado blanco sin gracia.** Se intentó primero volverlo una lámina de cristal —separada del borde, con radio, canto y el velo bajado de 94 a 84 %— y **Sua lo rechazó otra vez, con razón**: seguía siendo una barra detrás de los módulos. La corrección definitiva es que **el cajón no tiene suelo ninguno**, igual que el riel: `content: none` en su `::before`, y entre los módulos se ve la página atenuada.

   Eso mueve la carga del contraste: sin suelo detrás, lo que sostiene la lectura es el cristal de cada módulo, y ese sí sube **dentro del cajón**, del 66 % del riel al **84 %**. La diferencia tiene una razón física: en el riel, detrás del módulo hay campo de puntos sobre la superficie del panel, que es un color conocido; dentro del cajón hay **página**, y la página puede ser cualquier cosa. Medido el 8 sep sobre los dos extremos, con el velo de cierre de Filament en medio: al 66 %, sobre página negra, el rótulo del ítem activo da **3,03:1** y no pasa; al 84 % da 4,61 sobre negra y 5,49 sobre blanca, y en oscuro 5,30 y 4,78.

7. **El `bg-white` de Filament** —que la barra lleva por debajo de `lg`— se apaga con un selector que gana por especificidad, no por orden.

**Medido después, a 375 px:** riel 64, módulo 48 y **8 px de aire también para el ítem suelto**, con su cristal al 66 % y su radio de 16; el resorte moviendo **seis módulos** −3,5 px con un gesto de 10, y las filas a cero.

---

---

## La medición de la barra ya construida, 8 sep 2026 (tarea 10)

Tomada en Chromium sobre la maqueta que genera `php artisan maqueta:barra`, servida por HTTP. **Ninguna cifra sale de una suma.** Antes de medir nada se confirmó dentro de la página lo que la propia spec exige: la barra lleva `fi-sidebar-open`, empieza en `x = 0` y mide lo que dice su token.

| Qué | Cuánto | Cómo |
|---|---|---|
| Ancho de la barra | **252 px**, `x = 0`, `fi-sidebar-open` puesta | `getBoundingClientRect` |
| Alto de fila, el mínimo de las 22 | **48 px** en escritorio y a 375 px de ancho | `getBoundingClientRect` sobre las 22 |
| Objetivo táctil | **Las 22 filas pasan las cuatro esquinas** del cuadrado de 44 px | `elementFromPoint` en las cuatro esquinas, desplazando la lista para que cada fila entre en el viewport |
| Rótulos recortados | **Ninguno**, ni en escritorio ni a 375 px | `scrollWidth` contra `clientWidth` |
| Desborde de la lista | **750 px** más de contenido que de hueco | `scrollHeight - clientHeight` |
| Sombra del módulo en `scroll` | `rgba(11, 9, 10, 0.06) 0 12px 28px` — **se aplica** | Computada con la transición apagada |
| Canto de cristal | `rgba(11, 9, 10, 0.1) 0 0 0 1px inset`, en el pseudoelemento | `getComputedStyle(g, '::after')` |
| Brote del indicador | `barra-brota`, **520 ms** | `getAnimations()` sobre el ítem activo |
| Cajón a 375 px | 252 px de ancho, velo al **94 %**, `blur(18px) saturate(1.3)` | Computadas |
| Rótulo de grupo | `rgb(61, 57, 59)` | Computada |
| Rótulo del ítem activo | `rgb(151, 29, 24)` | Computada |
| Las cuatro señales | Las cuatro condiciones existen en el CSSOM compilado y **ninguna cae dentro de una capa**; las de Filament sí caen dentro, así que las nuestras ganan | Recorrido de `document.styleSheets` |

**Dos cosas que la medición enseñó y que no eran defectos.** `elementFromPoint` devuelve `null` para todo lo que cae fuera del viewport, así que medir el objetivo táctil sin desplazar la lista da catorce filas «rotas» que están perfectamente bien. Y leer `box-shadow` justo después de cambiar el estado devuelve `rgba(0, 0, 0, 0) 0 0 0 0`: es el valor interpolado en t = 0 de la transición, no una sombra anulada. La primera vez pareció el mismo defecto que `lg:shadow-none`; no lo era.

**Un defecto de fidelidad de la maqueta, arreglado.** A 375 px la barra medía 246 px y no 252, porque el `aside` era un ítem flex que encogía. En el panel el cajón es fijo y no lo encoge nadie: la maqueta lleva ya `flex-shrink: 0`.

**Lo que esta medición NO puede dar, y por qué:**

- **La costura con el topbar.** La maqueta no lo pinta y el panel exige segundo factor. Lo que sí se sabe, de la consola del panel real: `.fi-topbar-ctn` computa `position: sticky` con `z-index: 20`.
- **Las cuatro señales aplicadas de verdad.** El navegador de la sesión no emula movimiento reducido, transparencia reducida ni más contraste. Lo verificado es que los cuatro bloques llegan al CSSOM fuera de capa y que `matchMedia` soporta las cuatro condiciones; que **apliquen** hay que verlo en un equipo con la preferencia puesta (D-31 ya lo pide).
- **El coste del campo de puntos en marcha.** Con la ventana detrás el navegador no pinta fotogramas y `requestAnimationFrame` no corre. Lo mide Sua.

---

## Cifras de partida, medidas el 7 sep 2026

Las midió Sua en su propio navegador, con el panel abierto y la sesión iniciada, porque el segundo factor impide que una sesión automatizada llegue a `/admin`. Se tomaron **dos veces**: a **201 x 987**, por debajo de 64 rem, que es el **cajón**; y a **1.084 x 1.083**, por encima, que es la **barra de escritorio**. Las dos coinciden en todo salvo en la posición y en cuánto se corta la lista.

| Qué | Medido | Lectura |
|---|---|---|
| Ancho de la barra | 244 px (15,25 rem) al empezar; **252 px (15,75 rem)** desde el 8 sep, para devolverle aire simétrico al módulo | Coincide con `--asb-admin-sidebar-ancho`. Vale en los dos anchos |
| Posición | `fixed` en el cajón, **`sticky` a 1.084 px** | **Medido, no deducido: en escritorio la barra va en flujo y pegada, así que detrás de ella no pasa contenido.** Es el hecho que sostiene D-L1 y el que convierte el `blur(14px)` de hoy en coste sin imagen |
| Desenfoque | `blur(14px)` | Aquí SÍ desenfoca, porque bajo el cajón pasa contenido. En escritorio es el que no se ve |
| Fondo computado | `rgba(0, 0, 0, 0)` | El degradado va en `background-image`, así que el color computa transparente: la franja burdeos que se ve la pinta el degradado, no el color |
| Ítems | 24, todos de **43,5 px** | **Ninguno llega a 44**: faltan 0,5 px, y la comprobación de las cuatro esquinas del cuadrado da `false` en los 24. Es el defecto de partida que D-L12 y la retícula corrigen |
| Lista | 1.651 px de contenido en 913 de hueco (cajón) y en **1.019 (escritorio)** | **Se corta el 45 % y el 38 %**. El recorte no depende del ancho sino del alto de la ventana, así que la lista está cortada siempre: es lo que justifica el aviso de borde de D-L15 y que el estado lo mande el scroll interno (D-L3). Los 24 ítems suman 1.044 px; el resto hasta 1.651 son los rótulos de grupo y los huecos |
| Rótulo de grupo | 11,52 px, `rgb(191, 165, 166)` | Es `#bfa5a6`. Sobre el burdeos de hoy da 7,77:1, así que hoy sobra contraste; el cristal es lo que lo pone en juego y por eso D-L11 recalcula el velo |
| Ítem activo | 43,5 px de alto, blanco sobre `rgba(238, 65, 55, 0.16)`, con filo rojo de 3 px hacia dentro | El filo interior de 3 px ya existe y es el germen del indicador de D-L7 |

Las dos mediciones están tomadas, así que no queda nada por medir antes de construir. Lo que sigue sin medirse es lo de después: el cristal nuevo, que se juzga contra estas mismas cifras.


## Contradicciones entre miradas, resueltas

Cinco puntos donde dos análisis decían cosas incompatibles. Se resuelven aquí, con la evidencia delante, para que no se vuelvan a abrir dentro de tres decisiones.

1. **¿La barra es `fixed` de alto completo o está en flujo?** Gana "en flujo". `vendor/filament/filament/resources/css/components/sidebar.css:62-115`: `.fi-sidebar` nace `fixed`, pero en la rama de este panel (sin navegación superior y sin plegado de escritorio) recibe `lg:sticky` y `lg:translate-x-0`. Consecuencia dura: **detrás de la barra no pasa el contenido**, y todo el análisis que daba por hecho un `backdrop-filter` útil en escritorio se cae.
2. **¿Cristal real o cristal pintado?** Gana pintado en escritorio, real solo en el cajón. Además del punto anterior, el campo ambiental rojo que se proponía pintar detrás difiere de un color plano en uno o dos niveles de 255 tras el velo: desenfocar eso devuelve el mismo color. Y el sitio propuesto para ese campo, `.fi-body::before`, está **prohibido por una guardia verde** (`tests/Feature/Panel/TemaDelPanelTest.php:134`).
3. **¿El resorte al hacer scroll va en la cabecera de la barra?** No: esa cabecera es `lg:hidden` con topbar (`sidebar.css:130`), y las reglas que hoy le dedicamos (`theme.css:250-260`) son CSS muerto en escritorio. El resorte se muda a los bordes de la lista, a la llegada del indicador, a la flecha del grupo y al cajón.
4. **¿El estado lo dispara el scroll del documento o el de la lista?** Lo dispara la lista. En escritorio la barra no se mueve con el documento, así que compactar por el documento es efecto sin causa; y la lista desborda casi siempre (23 entradas agrupadas más dos sueltas contra unos 900 px útiles).
5. **¿El rojo luminiscente es claro sobre claro, o invertido?** Se parte en dos oficios. El rojo que **alumbra** (filo y halo) sigue la palabra de Sua: claro en claro, oscuro en oscuro. El rojo que **informa** (rótulo del ítem activo) va en dirección contraria porque lo manda la aritmética: `#ee4137` no llega a 4,5:1 en ningún tema.

---

## D-L1. ¿Qué puede ser el cristal en escritorio, si detrás de la barra no pasa nada?

**Hoy.** `theme.css:227-238` pinta un degradado opaco `#3b1113 → #291012 → #171012` y encima `backdrop-filter: blur(14px)` sobre el propio elemento. El desenfoque no se ve (el fondo que lleva encima es opaco), cuesta una pasada de compositor sobre 244 px por 100 dvh, y al ir en el elemento convierte la barra en raíz de fondo para todo lo que anide dentro.

| Opción | Coste |
|---|---|
| A. Cristal **pintado** en escritorio (velo translúcido, filo, halo, canto interior de luz y sombra) y cristal **real** solo por debajo de 64 rem, donde sí pasa contenido bajo el cajón | Dos recetas que documentar. En escritorio no hay difusión: el material se lee como panel translúcido, no como lente |
| B. Cristal real en los dos anchos, creando un campo ambiental detrás | Descartada con evidencia: el sitio natural del campo está vetado por guardia, y el campo que se proponía es indistinguible de un color plano tras el velo. Se paga el compositor para no ver nada |
| C. Dejar el `blur(14px)` de hoy | Cero trabajo y una mentira medible |

**Recomendación: A.** Porque un desenfoque solo vale lo que vale su fondo, y en escritorio el fondo es un color plano: allí el cristal lo hacen el velo, la luz y la sombra, y solo el cajón tiene página que refractar.

**Consecuencia que hay que escribir, no descubrir:** Filament da a `.fi-sidebar` un fondo opaco propio (`bg-white`, `dark:bg-gray-900`) y solo lo vuelve transparente en `lg`. Hay que declarar `background: transparent` explícitamente sobre el elemento, no limitarse a borrar nuestra declaración, o el cajón queda opaco bajo el velo.

## D-L2. ¿Dónde vive el material y qué portador se anima?

**Hoy.** Fondo, borde, sombra y desenfoque cuelgan del elemento `.fi-sidebar`.

| Opción | Coste |
|---|---|
| A. `::before` lleva el velo y el desenfoque del cajón; `::after` lleva el material que responde al estado (filo y sombra, por opacidad); el elemento no lleva ningún filtro | Dos pseudoelementos gastados: si más adelante hace falta un tercero, hay que un envoltorio real |
| B. Todo en el elemento, como hoy | Deja a cualquier descendiente sin página que desenfocar y ata el filo al `transition-property` que Filament pone en el elemento |

**Recomendación: A.** Porque la regla del proyecto no es teoría aquí: el ítem activo va a querer su propia luz, y con el filtro en el elemento ninguna funcionaría.

**Dato que corrige un miedo heredado:** `.fi-sidebar` **ya** es bloque contenedor de sus descendientes `fixed`, porque Filament le aplica `lg:translate-x-0` fuera de toda media. No es algo que vayamos a provocar ni algo que desaparezca al quitar el `backdrop-filter`. Esta especificación no añade ninguna propiedad nueva de las que crean bloque contenedor, y esa abstención es deliberada.

## D-L3. ¿Qué scroll manda el estado, con qué vocabulario, y hay estado de atención?

**Hoy.** La barra no reacciona a nada. El topbar sí, por posición (`scrollY > 8`), desde el conmutador de tema.

| Opción | Coste |
|---|---|
| A. Manda el scroll interno de `.fi-sidebar-nav`, con el vocabulario ya registrado `data-estado="inicial \| scroll"` y **sin** `atencion`; aparte, `data-borde="ninguno \| arriba \| abajo \| ambos"` para el aviso funcional de lista cortada | Dos atributos y un oyente. Hay que justificar por escrito que aquí no hay `atencion` |
| B. Manda el scroll del documento, con la histéresis de la barra pública | Efecto sin causa: en escritorio la barra no se ha movido y encoge; y en páginas cortas del panel el estado no se alcanza nunca |
| C. Los dos, un ancla por scroller | Dos anclas y dos guardas para gobernar un material que solo cambia de opacidad. Coste sin ganancia una vez que la cabecera sale del cuadro |

**Recomendación: A.** Porque la única superficie que se mueve aquí es la lista, y porque `atencion` en la barra pública significa "el usuario quiere más barra": aquí la barra entera está siempre a la vista y no hay nada que pedir.

**Por qué son dos atributos y no tres valores de uno:** el aviso de borde es funcional y tiene que sobrevivir mientras el material cambia; meterlos en un solo enum obliga a apagar el aviso justo cuando el usuario está recorriendo la lista.

## D-L4. ¿Quién escribe el estado y sobre qué elemento?

**Hoy.** El único escritor de estado del panel es `syncTopbar()` dentro del conmutador de tema, protegido por la guardia `test_la_topbar_reacciona_al_scroll_sin_un_script_adicional` (`TemaDelPanelTest.php:137-147`).

| Opción | Coste |
|---|---|
| A. Módulo propio `resources/js/panel-barra-lateral.js`, registrado en `assetsDelPanel()` con `->module()`, que escribe los dos atributos **sobre `<body>`** y los reescribe en `livewire:navigated` | Un archivo y una entrada más. Los selectores del CSS empiezan por `body[data-barra-estado]` |
| B. Lo mismo, pero escribiendo sobre `#fi-main-sidebar` | Ese nodo está dentro del componente Livewire `Sidebar`, que se re-renderiza por evento propio: el morph borra los atributos que no vienen del servidor y el estado desaparece sin error y sin navegación |
| C. Un `x-data` inyectado por `SIDEBAR_START` | Muere y renace en cada navegación, lo que está bien para reanclar y mal para recordar; y añade un segundo dueño de la señal |

**Recomendación: A.** Porque `<body>` es el único ancla que ni el morph del componente ni la navegación SPA tocan, y `->module()` es lo que de verdad garantiza que el módulo no se reevalúe (`data-navigate-once` en modo SPA), no la analogía con `panel-graficas.js`.

**Lo que NO se hace en esta entrega:** unificar el dueño de la señal de scroll con el topbar. Es un defecto real (el topbar pierde su clase al volver atrás) pero borrar `syncTopbar()` revierte una decisión registrada y pone roja una guardia por tres afirmaciones. Va a constancia, no a este commit. Y no hay esquina que desincronizar mientras la barra no responda al scroll del documento.

## D-L5. ¿Dónde vive el resorte que pidió Sua, si nada de la barra se retrae?

**Hoy.** Ninguna transición de la barra usa los resortes; el único movimiento es `transform 160ms` en la fila, neutralizado por un `transform: none` en el bloque de hover.

| Opción | Coste |
|---|---|
| A. El resorte se muda a cuatro sitios: el filete del borde de lista entra con `--ease-rebote-vivo`, el indicador del ítem activo llega con resorte, la flecha del grupo rota con resorte, y el cajón entra con `--ease-rebote-suave` | Ninguno de los cuatro está bajo el puntero mientras se desplaza. Hay que explicar a Sua que el resorte no está en la barra entera |
| B. Las filas se compactan por dirección de scroll, calcando el módulo de la barra pública | Descartada: en vertical el scroller y el objetivo del puntero son la misma superficie, así que encoger las filas mueve lo que el usuario está a punto de pulsar, y con 520 ms el objetivo sigue asentándose medio segundo después de soltar la rueda |
| C. Rebote elástico de la lista en sus extremos | Exige interceptar rueda y toque y pelear con el scroll nativo. No es una tarde y en híbridos sale mal |

**Recomendación: A.** Porque el resorte tiene que ir donde algo empieza y termina por voluntad del usuario, y recorrer una lista no es ni lo uno ni lo otro.

## D-L6. El indicador del ítem activo: ¿llega, o viaja de una fila a otra?

**Hoy.** El indicador es `inset 3px 0 0 #ee4137` y nace en su sitio final, porque el panel es SPA (`->spa()`), la barra es un componente Livewire sin `x-persist` y su DOM se sustituye entero en cada navegación.

| Opción | Coste |
|---|---|
| A. **Llega**: el indicador brota con `scaleY` desde `--asb-admin-barra-brote` con `--ease-rebote-vivo` en `--duracion-rebote`. Puro compositor, sin medir nada, igual en navegación SPA y en carga completa | La barra se lee como cinco listas que se repintan, no como un objeto continuo |
| B. **Viaja** por FLIP: se guarda la posición de la fila saliente en ámbito de módulo y el indicador nuevo nace desplazado y se suelta a cero | Quince líneas y tres guardas, más una trampa demostrada: `offsetTop` no mide contra el `<nav>` (su `offsetParent` es `.fi-sidebar`), así que hay que medir con `getBoundingClientRect` contra el nav más su `scrollTop`, y el respaldo tiene que expresarse en esa misma unidad. Es la pieza más frágil de todo el encargo |
| C. `x-persist` sobre la barra, o transiciones de vista | Descartadas. Con `x-persist` sobrevive el DOM viejo y `aria-current` lo pinta el servidor: la barra marcaría la página anterior para siempre, y además congela las dos insignias vivas. `view-transition-name` crea raíz de fondo y contexto de apilamiento |

**Recomendación: A, y B como decisión aparte que Sua puede pedir después.** Porque un viaje mal medido se lee como un error y no como un resorte, y porque el alcance está congelado: la llegada ya cumple la petición de resorte con una décima parte del riesgo.

**Dato que quita una precondición falsa:** no hace falta añadir `wire:scroll` a la lista. El store de Filament ya guarda el `scrollTop` del nav en `livewire:navigate` y lo restaura dentro de un `requestAnimationFrame` en `livewire:navigated` (`vendor/filament/filament/resources/js/stores/sidebar.js:10-38`). Cualquier cosa nuestra que toque el scroll tiene que encolarse después de ese rAF.

## D-L7. ¿Con qué se dibuja el indicador?

**Hoy.** `inset 3px 0 0 #ee4137` dentro de un `box-shadow` de tres capas, sobre una caja de 43,5 px con radio 0,9 rem.

| Opción | Coste |
|---|---|
| A. Pseudoelemento absoluto sobre `.fi-sidebar-item-btn` (que ya es `position: relative`): barra de 3 px por 20 px con radio completo, centrada en vertical | Una regla más y hay que reservar el aire para que no lo recorte el radio de la fila |
| B. Conservar el `inset box-shadow` | En modo de contraste forzado de Windows el navegador descarta `box-shadow` y el indicador desaparece; contra el radio de 14,4 px se lee como una coma y no como un filo; y no se puede trasladar sin arrastrar el texto |

**Recomendación: A.** Porque el segundo canal del ítem activo no puede evaporarse justo en el modo que existe para que la gente vea mejor.

## D-L8. ¿La barra invierte con el tema, o sigue siendo oscura siempre?

**Hoy.** El mismo degradado en `:root` y en `.dark`, y una paleta privada en hexadecimal cableado repartida en dos bloques: `theme.css:227-320` (`#f3e9e9`, `#d8babb`, `#bfa5a6`, `#ff7168`, `#ee4137`, `rgb(255 255 255)` en la fila activa) y `theme.css:673-696`, dentro del bloque de puntero fino (`rgb(255 255 255)`, `rgb(255 255 255 / 0.06)`, `#ff8a82`, `#ff7168`).

| Opción | Coste |
|---|---|
| A. El material invierte y los colores de texto se leen de los tokens del sitio (`--asb-tinta`, `--asb-suave`, `--asb-tenue`, `--asb-acento`) a través de los tokens de la barra | Cambio visual grande, y hay que reescribir los **dos** bloques: el de hover no es opcional, es donde vive la mitad de la paleta privada |
| B. Vidrio oscuro siempre, tokenizado | Conserva la queja de origen: en modo claro sigue habiendo un rail nocturno pegado a una página blanca |
| C. Vidrio claro siempre | El defecto inverso, y peor: en oscuro deslumbra |

**Recomendación: A.** Porque el mayor arreglo de correctitud de todo el encargo no es un color, es que la barra deje de tener paleta privada: el día que la paleta se mueva, se mueve con ella, y bajo `prefers-contrast: more` ya sube sola.

## D-L9. Los dos oficios del rojo: ¿qué rojo alumbra y qué rojo informa?

**Hoy.** El mismo `#ee4137` hace de filo, de fondo y de señal, y `#ff7168` de rótulo activo en los dos temas.

| Opción | Coste |
|---|---|
| A. **Luz** con la palabra de Sua: rojo claro en el tema claro, rojo oscuro en el tema oscuro, alimentando solo filo, halo y resplandor. **Tinta** con la aritmética: `--asb-acento-fuerte` en claro y `--asb-acento` en oscuro para el rótulo del ítem activo | Hay que explicar por qué el rojo del texto va en dirección contraria al rojo de la luz. Es una conversación, no un problema |
| B. Un solo rojo para todo | `#ee4137` da 3,49:1 sobre el cristal claro y 4,27:1 sobre el oscuro. No llega a 4,5 en ninguno de los dos: no puede ser el color de ningún rótulo |
| C. Aclarar el rojo oscuro hasta que pase el umbral | Deja de ser rojo oscuro, que es exactamente lo que Sua pidió |

**Recomendación: A.** Porque la luz no lleva información y por eso puede ir en la dirección que Sua eligió, mientras que el rótulo sí la lleva y no tiene margen.

**Nota que hay que dejar escrita en el CSS, con el número:** en claro, `--asb-acento` (`#b71f18`) sobre el fondo activo con el halo compuesto encima cae por debajo del umbral; solo `--asb-acento-fuerte` pasa. La asimetría entre temas parece un descuido y cualquier limpieza razonable la "arreglaría".

## D-L10. ¿Qué separa la barra del contenido?

**Hoy.** Un burdeos opaco contra una página `#f7f6f5`: el límite es enorme y gratis. Con cristal desaparece.

| Opción | Coste |
|---|---|
| A. El límite lo hacen una **línea** (`--asb-admin-barra-borde`, derivada de `--asb-linea-fuerte`) y la sombra proyectada; el filo luminiscente va encima como segunda capa, no como único canto | Hay que resistir la tentación futura de quitar la línea "porque el vidrio ya separa" |
| B. Solo el filo luminiscente | Medido: el rojo claro contra la página clara da 2,60:1 y el rojo oscuro contra la oscura 1,93:1, y el velo aporta 1,05:1. Con eso ningún canal llega a 3:1 y la barra deja de ser una región |
| C. Doble canto: línea neutra fuera y luz roja dentro, separadas un píxel | A escalas de pantalla no enteras (125 %, 150 %, que es lo normal en Windows) se funden en una raya sucia |

**Recomendación: A.** Porque el borde de una región se juzga contra 3:1 y eso solo lo sostiene la luminancia, no el color; la luz dice que el material es cristal, la línea dice dónde termina.

## D-L11. ¿Cuánto velo lleva el cristal, y es el mismo en el cajón?

**Hoy.** Opaco, así que el contraste sobra y nadie ha tenido que calcularlo.

| Opción | Coste |
|---|---|
| A. Un velo alto calibrado para el rótulo de grupo (11,5 px, texto normal, 4,5:1 sin excepción) con el halo compuesto encima, y un **segundo velo más alto para el cajón**, donde debajo pasa contenido variable tras el velo de cierre de Filament | Dos valores y una guardia que los recalcula. Queda poco cristal a la vista: es lo que cuesta sostener texto de 11,5 px sobre material translúcido |
| B. Reutilizar `--asb-cromo-velo` del sitio público | Está calibrado para texto de 14 px contra fotos y vídeo, y por debajo de 64 rem sube por una razón que no es esta. Compartirlo ata dos calibraciones distintas |
| C. Un solo velo para los dos anchos | Con el velo de escritorio, en el cajón claro el rótulo de grupo y el rojo activo se caen por debajo de 4,5:1. Es el fallo que este proyecto ya pagó dos veces |

**Recomendación: A.** Porque el cajón es el único sitio donde el fondo no se conoce de antemano, y un velo se calibra contra el peor fondo posible, no contra el habitual.

**Regla de procedimiento:** los porcentajes concretos se fijan el día que se escriba el CSS, recalculando con el trait `MideContraste` (`$this->componer(...)`, `$this->contraste(...)`, que son métodos de instancia, no estáticos) sobre los hexadecimales del archivo, y con el halo compuesto encima en su punto más intenso. Ningún número de este documento entra en el CSS sin recalcularse ese día.

## D-L12. ¿Qué canal marca el ítem activo además del color?

**Hoy.** Color, fondo teñido y filo. El fondo teñido da 1,21:1 contra la barra: no es un canal, es decoración.

| Opción | Coste |
|---|---|
| A. Indicador de forma fija (D-L7) más peso tipográfico, sobre el `aria-current="page"` que Filament ya pinta | Cero archivos PHP tocados. El peso solo es un canal débil, así que el trabajo lo hace el indicador |
| B. A, más el icono en variante sólida (`$activeNavigationIcon` en 24 clases) | Es el canal más legible de los tres y el que la barra pública ya eligió, pero son 24 archivos de `app/Filament`: ampliación de alcance, superficie de conflicto con otras sesiones y constancia previa |
| C. Subir el tinte del fondo hasta que se distinga solo | Para llegar a 3:1 hay que teñir tanto que deja de ser cristal. Y sigue siendo color: no es un segundo canal, es más del primero |

**Recomendación: A**, con B anotada como la primera ampliación que se pide si Sua la quiere. Porque el indicador de forma sobrevive al daltonismo, al contraste forzado y a una pantalla mal calibrada sin tocar un solo archivo PHP.

## D-L13. El plegado de grupo: ¿quién manda en su curva?

**Hoy.** `x-collapse.duration.200ms` en el `<ul>`, y el plugin escribe `transition-property`, `transition-duration` y `cubic-bezier(0.4, 0, 0.2, 1)` **en el elemento**, que gana a cualquier hoja sin `!important`. `Configuración` nace plegado, así que ese movimiento se ve en cada carga.

| Opción | Coste |
|---|---|
| A. Pisar solo el reloj y la curva del alto con un `!important` acotado (`--ease-cajon` a `--duracion-panel`), y poner el resorte en la **flecha**, que hoy rota sin ninguna transición | Un `!important` que hay que justificar en la línea de al lado. Ninguna vista publicada |
| B. Publicar `components/sidebar/group.blade.php` y retimar desde el modificador | Una vista del vendor bifurcada con mantenimiento perpetuo en cada actualización de Filament, y ampliación de alcance con constancia |
| C. Resorte también en el alto | Descartada con mecanismo: un sobreimpulso en `height` dentro de un scroller hace oscilar `scrollHeight` medio segundo, la barra de desplazamiento tiembla y el navegador puede recortar `scrollTop` en mitad del rebote |

**Recomendación: A.** Porque el reparto es lo que hace que esto parezca diseñado: la flecha rebota, el alto no; la flecha es una rotación que no arrastra layout, el alto arrastra media lista y un scroller detrás.

**Aviso para la guardia:** `theme.css` ya tiene un `!important` hoy, en el bloque de impresión. Una guardia que exija "exactamente uno" nace roja. Lo que hay que exigir es que todo `!important` fuera de `@media print` esté en una lista de selectores permitidos y lleve su comentario justificándolo.

## D-L14. ¿Puede quedar plegado el grupo que contiene la página actual?

**Hoy.** Sí, y pasa: `Configuración` nace plegado y sus cinco destinos quedan en `display: none`. Quien entre a Ajustes del sitio no ve ningún ítem activo en toda la barra.

| Opción | Coste |
|---|---|
| A. Al arrancar y en cada `livewire:navigated`, si el grupo con `fi-active` está plegado, se saca de `collapsedGroups` | Ocho líneas. Efecto declarado: tras la primera visita a Ajustes, Configuración deja de nacer plegada, contradiciendo el `->collapsed()` del provider |
| B. Abrirlo solo en memoria, sin escribir en el almacenamiento | Pelea con `x-show`, que escribe estilo en línea, y con el `<script>` en línea que Filament ejecuta antes de Alpine y en cada navegación SPA |
| C. Dejarlo | La barra miente: estás en una página y ningún ítem está marcado. La barra nueva lo hace más visible, no menos |

**Recomendación: A.** Porque un grupo no puede quedar cerrado con la página que estás mirando dentro, y el efecto secundario es defendible como producto.

## D-L15. El aviso de "hay más lista": ¿máscara de borde o rótulo de grupo pegajoso?

**Hoy.** Nada. Con cinco grupos y 24 destinos, la lista desborda y no hay ninguna pista de por dónde vas.

| Opción | Coste |
|---|---|
| A. Máscara de desvanecido más filete luminiscente en el canto que oculta lista, gobernados por `data-borde` | Media hora. Es el único sitio de una barra vertical donde el resorte no mueve lo que el usuario apunta |
| B. Rótulo de grupo pegajoso a `top: 0` dentro del nav | Aterriza exactamente donde vive la máscara y se desvanece justo cuando se pega, que es el único momento en el que sirve. Si se quiere, tiene que pegarse **por debajo** de la máscara |
| C. Las dos, sin decidir | Es lo que hicieron dos miradas por separado y es lo que no puede pasar |

**Recomendación: A.** Porque resuelve el hecho que de verdad tiene esta barra (la lista se corta) y porque el rótulo pegajoso, para funcionar, obliga a apagar el aviso o a desplazarlo, que es pagar dos veces por lo mismo.

## D-L16. Hover, foco y toque: dónde hay gesto y dónde hay lectura

**Hoy.** Dos defectos vivos y uno heredado. El único `:focus-visible` de la barra está **dentro** de `@media (hover: hover) and (pointer: fine)` (`theme.css:673-677`), así que en un portátil táctil o una tableta con teclado la barra se navega sin ningún indicador de foco, y Filament ya quitó el contorno nativo. La misma regla neutraliza el movimiento con `transform: none`. Y la fila mide `2.72rem`, o sea 43,52 px.

| Opción | Coste |
|---|---|
| A. **Hover**: la fila se desliza 2 px hacia el contenido con `translate`, dentro de la puerta de puntero fino y atado a `pointerenter`. **Foco**: `:focus-visible` **fuera** de toda media de puntero, anillo de dos colores (tinta más halo, invertidos por tema), sin mover nada y con `scroll-behavior: auto`. **Toque**: `:active` con el encogimiento de forma ancha, nunca el de control de 44 px. **Fila a 2.75rem** | Un token de empuje, un par de tokens de foco, y ampliar la guardia de hover táctil para que también vigile `translate` y no solo `transform` |
| B. Un solo anillo de foco en el color de tinta del tema | Impecable en escritorio, donde el fondo es conocido; en el cajón el fondo efectivo varía y el anillo puede desaparecer sobre él |
| C. Conservar el contorno rojo que el panel usa en `.asb-operativo` | Da 2,06:1 sobre el cristal claro contra un umbral de 3, y además compite con el color del ítem activo |

**Recomendación: A.** Porque el foco es lectura y no gesto (quien tabula necesita saber dónde está antes que ver cómo llegó), y porque el anillo de dos colores es el único que se puede demostrar sobre un fondo que no se conoce de antemano.

**Dos avisos.** El encogimiento del toque no puede reutilizar el token de control de 44 px: su razón escrita dice que sobre cajas anchas ese porcentaje se lee como una arruga, y la fila mide más de 200 px. Y los 44 px no pueden entrar en `ObjetivoTactilTest`: ese proveedor empareja vistas Blade con cadenas de clases y no abre `theme.css`; hace falta una guardia propia del panel más la medición en Chromium.

## D-L17. Las señales del sistema: qué muere y qué sobrevive

**Hoy.** Ninguna llega a la barra. El bloque `@media (prefers-reduced-motion: reduce)` de `theme.css:1182` solo nombra el topbar, el logo y el conmutador; el desenfoque es un `blur()` literal que ninguna media alcanza; `prefers-contrast: more` y `forced-colors` no aparecen.

| Opción | Coste |
|---|---|
| A. Cuatro señales con conducta escrita. **Movimiento reducido**: no hay estado `scroll`, no hay brote ni empuje ni desplazamiento del cajón; sobreviven los fundidos de color y opacidad y el plegado del grupo (que es layout); la flecha deja de rebotar sola porque los dos resortes ya se reasignan a `--ease-cajon` en `tokens.css`. **Transparencia reducida**: velo opaco y desenfoque `none`; las dos luces intactas, y por eso tienen que pintarse **encima** del velo desde el primer día. **Más contraste**: velo opaco también, tinta más fuerte, y el filo deja de ser luz y se vuelve la línea. **Contraste forzado**: las luces se apagan y el indicador se repinta con `outline` y `border`, porque el navegador descarta `box-shadow` | Cuatro bloques y una regla de orden de pintado que hay que escribir |
| B. Solo movimiento reducido | Deja la barra con un desenfoque que ninguna media apaga, que es el defecto de hoy con más líneas |
| C. Apagar el reloj entero bajo movimiento reducido | Mata también los fundidos, que son lo que hay que conservar |

**Recomendación: A.** Porque la señal pide que el material deje de ser translúcido, no que deje de ser un material: se pierde la profundidad y se conserva la jerarquía.

**Dónde viven los tokens, y por qué no en `tokens.css`.** Van en `theme.css`, que ya tiene su `:root` propio fuera de capa y su bloque de movimiento reducido fuera de capa: la media alcanza al token igual, y `tokens.css` es el archivo compartido con el sitio público, que no consume nada de esto. Y hay un precedente roto que sirve de aviso: `theme.css:48` y `:79` redeclaran `--asb-vidrio-desenfoque` después del `@import`, con lo que la anulación por transparencia reducida no llega al panel en modo claro. Un token declarado dos veces deja una media sin efecto y nadie se entera.

## D-L18. El cajón por debajo de 64 rem

**Hoy.** El cajón cerrado no es `display: none`, solo está desplazado: sus enlaces siguen recibiendo tabulación fuera de pantalla, nadie pone `inert`, no hay Escape y el foco no vuelve al disparador. La transición es el `transition-all` por defecto de Tailwind, que ningún token toca.

| Opción | Coste |
|---|---|
| A. Mismo idioma con más velo (D-L11), entrada con `--ease-rebote-suave` y salida con `--ease-cajon`, `transition-property` declarado explícitamente para no envenenar el cajón con el `transition-all` de Filament, más las tres correcciones de foco (`inert`, Escape, devolución) y la fila a 44 px | Las tres correcciones son defectos presentes, no funciones nuevas. Son lo último que puede quedarse fuera si hay que recortar |
| B. Cajón opaco | Cero riesgo de contraste y de compositor, y rompe el mismo idioma visual. Reserva si la medición en un teléfono de gama baja sale mal |
| C. Trampa de foco completa | Filament no lo declara como diálogo, y media trampa es peor que ninguna |

**Recomendación: A.** Porque el cajón es donde el cristal se gana el sueldo y donde el dedo mide de verdad, y porque un cajón cerrado que sigue recibiendo tabulación es un incumplimiento vivo que la barra nueva heredaría.

---

## Tokens nuevos

Todos en `resources/css/filament/admin/theme.css`, declarados una sola vez en su `:root` y su `.dark`, y reasignados en los bloques de media del mismo archivo. Prefijo `--asb-admin-barra-` para que nunca se confundan con las dos barras públicas.

| Token | Claro | Oscuro | Porqué |
|---|---|---|---|
| `--asb-admin-barra-velo` | Velo alto derivado de `--asb-superficie` | Velo alto derivado de `--asb-superficie` | El cristal se levanta de la página en los dos temas. No reutiliza `--asb-cromo-velo` porque aquel está calibrado para 14 px contra fotos. Pasa a `var(--asb-superficie)` a secas bajo transparencia reducida y bajo más contraste |
| `--asb-admin-barra-velo-cajon` | Más alto que el de escritorio | El mismo que el de escritorio | Debajo del cajón pasa contenido variable tras el velo de cierre de Filament; el de escritorio se compone sobre un color conocido. En oscuro no hace falta subirlo porque el velo de cierre ya es negro al 75 % |
| `--asb-admin-barra-desenfoque` | `blur(18px) saturate(130%)` | `blur(18px) saturate(130%)` | Solo se consume en la regla del cajón, donde hay página que refractar. Nunca un `blur()` literal, para que las medias lo puedan apagar. Vale `none` bajo transparencia reducida y bajo más contraste |
| `--asb-admin-barra-luz` | Rojo claro derivado de `#ee4137` | Rojo oscuro derivado de `#ee4137` | El rojo luminiscente que pidió Sua, en la dirección que pidió. No es nunca valor de `color`: solo alimenta `box-shadow`, `border-*-color` y `background-image` |
| `--asb-admin-barra-filo` | `color-mix` de la luz | `color-mix` de la luz | El canto encendido del cristal. Existe aparte de la luz por una sola razón: bajo más contraste se reasigna a `var(--asb-linea-fuerte)` y deja de ser luz para volverse línea |
| `--asb-admin-barra-halo` | `color-mix` de la luz | `color-mix` de la luz | El resplandor del ítem activo, dibujado por el mismo pseudoelemento que su indicador para que no puedan separarse. Sus porcentajes son los que se usaron para calibrar el velo: subirlos sin recalcular rompe el umbral del rótulo activo |
| `--asb-admin-barra-borde` | Derivado de `--asb-linea-fuerte` | Derivado de `--asb-linea-fuerte` | El límite real de la región. El velo aporta 1,05:1 y el filo no llega a 3:1: la separación la hacen esta línea y la sombra, en luminancia |
| `--asb-admin-barra-tinta` | Tinta oscura del sitio | `#f3e9e9` heredado a token | Rótulo de ítem, 14 px, 4,5:1 |
| `--asb-admin-barra-tenue` | Gris que llegue a 4,5:1 | Gris que llegue a 4,5:1 | Rótulo de grupo e icono, unificados. Hoy son dos hexadecimales distintos para el mismo papel y nadie recuerda por qué. El rótulo de grupo mide 11,52 px: no es texto grande bajo ninguna lectura, así que manda el umbral más exigente de los dos |
| `--asb-admin-barra-activo` | `var(--asb-acento-fuerte)` | `var(--asb-acento)` | Rótulo del ítem activo. La asimetría entre temas es la respuesta a una asimetría real: el halo claro aclara el fondo y empuja al texto hacia el fallo, el oscuro lo empuja hacia el aprobado |
| `--asb-admin-barra-activo-fondo` | Tinte bajo | Tinte algo más alto | Sobre cristal claro la misma opacidad se lee más fuerte que sobre cristal oscuro: no puede ser un valor único |
| `--asb-admin-barra-hover-fondo` | Tinte de la marca | Blanco muy bajo | El `rgb(255 255 255 / 0.06)` de hoy sobre un cristal claro es indistinguible del fondo: el hover deja de existir |
| `--asb-admin-barra-fila-alto` | `2.75rem` | `2.75rem` | Geometría, no movimiento: no se anula bajo movimiento reducido. Hoy la fila mide `2.72rem`, es decir 43,52 px, medio píxel por debajo del mínimo táctil |
| `--asb-admin-barra-fila-radio` | `0.625rem` | `0.625rem` | Concentricidad, y para que el indicador de 3 px no quede recortado por el radio como le pasa hoy al `inset box-shadow` |
| `--asb-admin-barra-aviso-alto` | `1.5rem` | `1.5rem` | Alto de la máscara de desvanecido del canto que oculta lista, gobernada por `data-borde` |
| `--asb-admin-barra-brote` | `0.35` | `0.35` | Escala de arranque del indicador al llegar. A `1` bajo movimiento reducido |
| `--asb-admin-barra-empuje` | `2px` | `2px` | Deslizamiento horizontal de la fila al recibir puntero fino. No reutiliza `--asb-levante` porque es de otro eje y de otro componente: un levantamiento vertical sobre una fila dentro de una lista que se desplaza se lee como que la fila se despega. A `0` bajo movimiento reducido |
| `--asb-admin-foco-anillo` | `#0b090a` | `#ffffff` | Mitad del anillo de dos colores. Un solo anillo desaparece sobre el fondo variable del cajón |
| `--asb-admin-foco-halo` | `#ffffff` | `#0b090a` | La otra mitad. Bajo contraste forzado el halo no puede ir en `box-shadow`: se pinta con `border` o con un segundo `outline` |

## Riesgos conocidos

1. **`.fi-sidebar` ya es bloque contenedor de sus descendientes `fixed`**, por el `lg:translate-x-0` que Filament le aplica fuera de toda media. No lo provocamos nosotros y no desaparece al quitar el `backdrop-filter`. Una guardia que solo lea nuestra hoja buscando `translate` da seguridad falsa.
2. **Por debajo de 64 rem, `.fi-sidebar` trae `transition-all` de Filament** (por encima es `transition-none`). Cualquier propiedad que toquemos transicionará ahí con el reloj por defecto de Tailwind, y el cristal entraría fundiéndose mientras el cajón se desliza. Hay que declarar `transition-property` explícito.
3. **`.fi-sidebar` trae fondo opaco propio bajo `lg`** (`bg-white`, `dark:bg-gray-900`). Borrar nuestra declaración de `background` no basta: hay que poner `transparent` explícito o el cajón queda opaco bajo el velo.
4. **La costura con el topbar puede tener 4 px de hueco**: Filament ancla la barra en `lg:top-[4rem]` y el panel pinta el topbar con `min-height: 3.75rem`. Es aritmética sobre dos `min-height`, no una medida: el alto real del topbar puede superar el suyo. Hay que medirlo en Chromium antes de decidir si el topbar sube a 4 rem o la barra se reancla al token, y es decisión de Sua porque cambia el alto del cromo.
5. **`x-collapse` escribe la transición en el elemento** y Filament re-ejecuta el `<script>` en línea que oculta los grupos plegados en cada navegación SPA. Cualquier animación de entrada de un grupo arranca desde `display: none` en cada cambio de página.
6. **Los atributos escritos por JavaScript sobre el nodo de la barra no sobreviven a un re-render del componente Livewire** (el morph borra los que no vienen del servidor), y eso no lo ve ninguna de las capas de verificación previstas. Por eso el estado vive en `<body>`.
7. **El panel no tiene la clase `sin-desplazamiento`**: esa la pone el `<head>` del layout público. Copiar `menosMovimiento()` de la barra pública deja la guarda siempre en falso, y sería un falso verde silencioso porque la clase nunca está. Aquí se consulta `matchMedia` en vivo y se escucha su `change`.
8. **`prefers-reduced-transparency` no llega hoy al panel en modo claro**, porque `theme.css:48` y `:79` redeclaran `--asb-vidrio-desenfoque` después del `@import` con la misma especificidad. Afecta a ModerarFotos y al widget de Pendientes. Arreglarlo toca esas dos piezas: hay que decirlo, no dejarlo como efecto colateral.
9. **Playwright acepta la emulación de transparencia reducida y no la aplica.** Cualquier medición de esa señal que no confirme `matchMedia(...).matches` dentro de la página es nula y no cuenta como verde.
10. **Quedan dos `blur()` literales más en el mismo archivo** (`.fi-topbar` y el conmutador de tema). Arreglar solo la barra deja el panel atendiendo la señal en la mitad de su cromo. O se amplía o se dice por escrito que se deja, con su ticket.
11. **La lista desborda casi siempre**, y con las filas a 44 px desborda un poco más. Con `Configuración` plegado el contenido supera con holgura la altura útil a 1080 px. Hay que comprobar que ese grupo, al final del recorrido, no quede sin ninguna pista de que existe.
12. **El objetivo táctil de la fila de grupo no es un control accesible**: el rótulo vive en un `<div>` con `x-on:click`, sin `role`, sin nombre accesible y sin `tabindex`; el único elemento alcanzable por teclado es la flecha. Agrandarlo a 44 px agranda un área que el teclado no puede usar.
13. **El fuera-de-uno de 1024 px es real y está tapado.** El ítem cierra el cajón con `matchMedia('(max-width: 1024px)')` y el store decide escritorio con `innerWidth >= 1024`, que incluye la barra de desplazamiento clásica mientras la media query no: la banda ambigua es más ancha que un píxel. Hoy no se ve porque el ancho está forzado; se vería el día que se encienda el plegado.
14. **Toda cifra de este documento es aritmética sobre valores declarados.** Los contrastes salen del método sancionado y son fiables como orientación; las geometrías (los 4 px de costura, el alto de la lista, los 44 px reales) no lo son hasta que playwright las mida el día que se escriban.
15. **Puede haber otra sesión en este mismo directorio.** Antes de codificar: `GIT_OPTIONAL_LOCKS=0 git status`, `git log --oneline -5` y el commit del encabezado de `material/estado.md`.

## Fuera de alcance

- **El carril de iconos de escritorio** (`sidebarCollapsibleOnDesktop()`). Hoy está apagado, y encenderlo obliga a dar icono a los cinco grupos y quitárselo a los 24 destinos, porque Filament no admite las dos cosas a la vez; además saca a la luz el fuera-de-uno de 1024 px y cuelga `opacity-0` con `transition-all` al contenedor principal. Es un rediseño de la navegación, no un añadido: constancia previa.
- **El viaje del indicador por FLIP** (D-L6, opción B). Decisión propia, con su medición, si Sua la pide.
- **El icono en variante sólida para el ítem activo** (D-L12, opción B). 24 archivos de `app/Filament`.
- **Unificar el dueño de la señal de scroll con el topbar.** Revierte una decisión registrada y reescribe una guardia verde. Constancia.
- **Publicar cualquier vista del vendor.** Todo lo que pide el encargo cabe entre CSS del tema, un módulo registrado en `assetsDelPanel()` y, si hiciera falta un nodo, los ganchos con constante.
- **Compactar las filas por dirección de scroll**, rebote elástico de la lista, entrada escalonada de los ítems al navegar, parallax del degradado, y que el carril se despliegue al pasar el puntero.
- **`view-transition-name`** en cualquier parte de la barra o de sus ancestros.
- **Navegación por flechas dentro de la lista** y trampa de foco completa en el cajón. Se rechazan por concepto: es una lista de enlaces dentro de un landmark, no un menú, y Filament no declara el cajón como diálogo.
- **Cambiar el ancho de 15,25 rem.** Solo se abre si la medición de los 29 rótulos en la Poppins servida demuestra que alguno se recorta.
- **Renombrar o reagrupar los grupos y destinos**, y recolorear el logotipo. Son contenido y marca, no diseño.
- **El contorno de foco de `.asb-operativo`** (2,06:1 sobre cristal claro). Mismo defecto de fondo, otro sitio: ticket propio y guardia propia.
- **`forced-colors` en el resto del panel.** Aquí solo se atiende en la barra.

## Cómo se comprueba, sabiendo que nadie puede entrar al panel

El segundo factor es obligatorio, así que ninguna sesión automatizada abre `/admin` por la puerta. La verificación se reparte en cuatro capas y **ninguna cifra sale de una sola**.

**Capa 1. PHPUnit sobre el HTML servido.** No necesita navegador y sí necesita sesión, pero la sesión de PHPUnit sí existe: `actingAs($usuario)->get('/admin')->assertOk()` ya funciona en la suite. Cubre estructura y ARIA: exactamente un `aria-current="page"` por ruta probada, cada `aria-controls` apuntando a un `id` presente una sola vez, el enlace de salto como primer elemento enfocable, y el `fi-active` del grupo que contiene la página. Cubre también los roles: la secretaria ve menos entradas, así que la lista puede no desbordar y `data-borde` tiene que dar `ninguno`; probar solo con super_admin deja ese caso sin ejercer.

**Capa 2. Guardias de archivo, que son las que se ponen rojas solas.** Leen el CSS y el JS crudos y afirman: que ningún selector que empiece por `.fi-sidebar` lleva un color literal (barriendo también el bloque de hover de `theme.css:673-696`, que es donde vive la mitad de la paleta privada); que `backdrop-filter` no aparece dentro del bloque del elemento y sí dentro del pseudoelemento; que los tokens de la barra se declaran una sola vez fuera de las medias y que las cuatro medias los reasignan; que el `:focus-visible` de la barra **no** está dentro del bloque de puntero fino; que la regla del ítem activo cambia al menos una propiedad que no sea color ni fondo; que todo `!important` fuera de `@media print` está en la lista de permitidos con su comentario; y que el módulo se registra con `->module()` dentro del `try/catch (ViteException)` que hoy protege a artisan en un clon sin manifiesto. **Aritmética de contraste en la misma capa**: se extraen los porcentajes del archivo con expresión regular, no se repiten a mano, y se recomponen con el trait `MideContraste` sobre el peor fondo posible y con el halo encima, exigiendo 4,5:1 al rótulo de grupo, al rótulo de ítem y al ítem activo, y 3:1 al indicador y al límite de región.

**Capa 3. Guardia de contrato sobre el vendor.** Es lo único que se rompe solo, en una actualización de Filament, sin que nadie toque una línea nuestra. Afirma una por una, con el porqué en el mensaje, las cadenas de las que depende el tema: `x-collapse.duration.200ms`, `fi-sidebar-open`, `lg:hidden` en la cabecera con topbar, `scrollbar-gutter: stable` en el nav, la ausencia de `x-persist` en la barra, la restauración de `scrollTop` en el store, y que `isSidebarCollapsibleOnDesktop()` sigue siendo falso en el panel real. Se ve roja copiando el archivo del vendor a un temporal, borrándole una cadena y apuntando la guardia a la copia: tres mutaciones distintas, no una.

**Capa 4. Maqueta medida en Chromium, sin sesión.** Un comando local genera `public/_medicion/barra-lateral.html` renderizando los mismos componentes de Filament que pinta el panel, y playwright-cli lo abre por `file://`. La maqueta no se guarda en el repositorio y se regenera en cada verificación. Para que mida algo tiene que llevar cuatro piezas, y eso es parte de su coste: el bloque `<style>` de `x-cloak` que Filament emite en el layout base (sin él la barra es `display: none` por debajo de 1024 px), Alpine, un `$store.sidebar` de mentira con `isOpen`, `groupIsCollapsed` y `toggleCollapsedGroup` (sin él la barra nunca recibe `fi-sidebar-open` y se queda fuera de pantalla), y la hoja compilada resuelta leyendo `public/build/manifest.json`, nunca por nombre con hash. Antes de medir nada se confirma dentro de la página que `.fi-sidebar` tiene 244 px de ancho y `x = 0`; y antes de medir bajo una señal emulada se confirma `matchMedia(...).matches` dentro de la página. Ahí se miden: las cuatro esquinas del cuadrado de 44 px con `elementFromPoint`, incluidas las de los controles vecinos; la costura entre topbar y barra; el contraste real por `getImageData` con una franja blanca y una negra bajo el cajón; la curva y la duración reales con `getAnimations()`; el estado tras un scroll programado del nav; y que ningún `scrollWidth` de rótulo supera su `clientWidth`. La propia maqueta se comprueba: una guardia ejecuta el comando y exige que la salida traiga `fi-sidebar-nav`, al menos cinco `fi-sidebar-group` y la barra visible a 390 px.

**Capa 5. Sua, a mano, una vez.** Lo que ninguna capa puede decir: si el cristal parece cristal sobre el tablero real, si el resorte se siente como lo pidió, si el viaje entre dos páginas del panel no salta, y si el botón atrás deja la barra donde debe. Con una lista corta de observaciones escritas, en claro y en oscuro, y en el teléfono antes de la demo.

**Regla que gobierna las cinco:** cada guardia se ve roja por rotura deliberada antes de escribir el código que la pone verde, y se muta **por comportamiento**: cada cableado, cada `aria-*` y cada constante por separado, cada uno poniendo roja su propia afirmación y solo la suya. Afirmar que una función está definida no vale; hay que afirmar también que se llama.


---

## Ampliación del 7 sep: los módulos y la parte superior

**Por qué existe esta sección.** Con las tareas 1 a 5 construidas, Sua miró el panel y dijo dos cosas: que **no se aprecian los módulos** que presenta la barra de escritorio, y que **lo único que cambió fue el color**. Tiene razón, y la Parte III lo explica sin querer: sus dieciocho decisiones son todas de material, de movimiento y de señal, y ninguna toca la ESTRUCTURA. La barra sigue siendo una superficie plana con una lista encima.

En el mismo mensaje pidió rehacer la parte superior del panel: el control de tema, la campana de notificaciones («su funcionalidad es muy poca») y la cuenta del usuario, «asemejándola a la que hay actualmente en la navBar de escritorio».

**Esto es ampliación de alcance sobre la Parte III aprobada**, así que fue por escrito antes de codificarse. Cinco decisiones, D-L19 a D-L23, **aprobadas por Sua el 7 sep 2026** («apruebo») con la recomendación de cada una.

**Lo que la barra de escritorio hace y esta no.** La bandeja es una píldora exterior que contiene tres módulos: logo, principal y cuenta. En `inicial` el vidrio lo pone la píldora y los módulos están apagados; en `scroll` y en `atención` la píldora se apaga y **cada módulo enciende el suyo**, con brillo especular en un pseudoelemento y canto de cristal en el otro. Eso es lo que se lee como «módulos», y es lo que aquí no existe.

### D-L19. ¿Qué es un módulo en una barra vertical?

**Hoy.** Un solo plano: cristal, y encima cinco grupos que solo se distinguen por su rótulo en mayúsculas y por el aire entre ellos.

| Opción | Coste |
|---|---|
| A. **Cada grupo de navegación es un módulo**: cinco cristales apilados con su canto y su brillo, el rótulo dentro como título del módulo | Cinco módulos es mucho módulo para 244 px de ancho, y el aire entre ellos come alto en una lista que ya se corta el 38 % |
| B. **Tres módulos por función, como en escritorio**: marca arriba, navegación en medio (con los cinco grupos dentro, como están), cuenta abajo | Es la traducción literal de la barra de escritorio girada. Obliga a decidir D-L21 (la cuenta baja de la parte superior) para que el tercer módulo exista |
| C. **Dos módulos**: navegación y cuenta, sin módulo de marca, porque con topbar Filament esconde la cabecera de la barra en escritorio | Menos fiel al original, pero es lo que de verdad se ve en escritorio |

**Recomendación: B**, con el módulo de marca visible solo en el cajón, que es donde Filament pinta la cabecera. Porque los módulos de la barra de escritorio son **funcionales y no decorativos**: agrupan por papel, no por sección, y eso es lo que hace que se lean como piezas y no como cajas.

### D-L20. ¿Cuándo enciende cada módulo su vidrio, si aquí no hay estado de atención?

**Hoy.** Nada enciende nada.

| Opción | Coste |
|---|---|
| A. El módulo de navegación enciende su canto **siempre**, y el de cuenta también; el brillo especular solo aparece al recibir puntero o foco dentro | Se pierde el contraste entre «apagado» y «encendido» que en escritorio marca el cambio de estado |
| B. Los módulos nacen apagados y encienden en `data-barra-estado="scroll"`, es decir cuando la lista se ha desplazado, calcando el reparto de la barra de escritorio | El encendido depende de que el usuario desplace la lista, y quien no la desplace nunca ve los módulos |
| C. El módulo de **navegación** enciende con el desplazamiento (es el que se desplaza) y el de **cuenta** está encendido siempre (es el ancla que no se mueve) | Dos conductas distintas que hay que justificar, y es justo lo que las hace legibles |

**Recomendación: C.** Porque en escritorio el encendido significa «esta pieza se separó de la página», y aquí la única que se separa es la lista. La cuenta no se desplaza nunca: encenderla siempre la convierte en el suelo de la barra.

### D-L21. ¿Dónde vive la cuenta del usuario?

**Hoy.** En la parte superior, a la derecha: un círculo con las iniciales que abre un menú con Perfil, tres iconos de tema y Salir. El nombre no se ve por ninguna parte.

| Opción | Coste |
|---|---|
| A. **Baja al pie de la barra lateral** como tercer módulo, con avatar, nombre y rango, igual que el chip de escritorio, y abre su hoja hacia arriba | Es un gancho (`SIDEBAR_FOOTER`) y una vista propia. Deja la parte superior con muy poco dentro, lo que obliga a decidir qué queda arriba. En el cajón hay que comprobar que la hoja cabe |
| B. **Se queda arriba** pero se rehace como el chip de escritorio: avatar, nombre y rango | No añade módulo ninguno a la barra, así que la queja de Sua queda a medias |
| C. En los dos sitios | Dos disparadores para la misma sesión: se contradicen en cuanto uno cambie |

**Recomendación: A.** Porque resuelve las dos quejas con un solo movimiento: la barra gana el módulo que le faltaba y la cuenta gana el nombre y el rango que hoy no muestra. Y porque el pie de la barra es donde el ojo ya busca la sesión en un panel.

### D-L22. La campana de notificaciones

**Hoy.** Filament la pinta con `databaseNotifications()` y sondeo cada 30 s. Sua dice que su funcionalidad es muy poca, y es cierto: el tablero ya tiene la banda «Te está esperando» con lo que hay que aprobar, que es la misma información mejor contada.

| Opción | Coste |
|---|---|
| A. **Se retira del cromo** y la información queda donde ya está, en la banda del tablero | Hay que comprobar que ninguna parte del panel dependa de ella para avisar de algo que no salga en la banda |
| B. Se queda y se le da contenido real | Es un frente propio: decidir qué notifica, quién lo emite y cuándo se marca leído. No es una tarde |
| C. Se queda como está | Ocupa el sitio del cromo que estamos rehaciendo y no dice nada |

**Recomendación: A**, con B anotada como frente aparte si el gremio pide avisos de verdad. Porque un adorno que no informa compite por la atención con lo que sí informa.

### D-L23. ¿Qué queda en la parte superior, y con qué aspecto?

**Hoy.** Campana, círculo de iniciales y un segmentado de dos botones para claro y oscuro, que no existe en el sitio público.

| Opción | Coste |
|---|---|
| A. Queda el **control de tema con la misma forma que en el sitio**: un botón redondo de 44 px con sol o luna que abre un popover con las tres preferencias (claro, oscuro y sistema), con `aria-expanded` y `aria-controls`, sin `role="menu"` | Hay que reescribir el conmutador que entró con el panel de Ingrid. Gana coherencia con el sitio y pierde la comodidad de un clic |
| B. Se conserva el segmentado de dos botones | El sistema deja de ser elegible desde el cromo, y hoy lo es desde el menú de usuario, que en la opción A de D-L21 se va abajo |
| C. El control de tema también baja al pie de la barra | La parte superior se queda vacía y el tema deja de estar donde el ojo lo busca |

**Recomendación: A.** Porque el encargo es que el panel se parezca al sitio, y el control de tema del sitio es un popover de tres opciones, no un interruptor de dos. La parte superior queda con el título de la página a la izquierda y el control de tema a la derecha, que es lo que un panel necesita arriba.

**Lo que no cambia en esta ampliación:** el ancho de la barra, el orden de los grupos, los destinos, el idioma de los rótulos y el logotipo. Y sigue fuera de alcance el carril de iconos plegable.


---

## Corrección del 7 sep: la barra se aplana

**Qué pasó.** Con D-L19 a D-L23 construidas, Sua abrió el panel y dijo dos cosas: que todo está **muy apeñuscado**, y que el perfil del usuario al pie **no le gusta**. Trajo además una referencia, la barra lateral de Roblox, y pidió opinión.

**Lo que la referencia hace, mecánicamente.** No tiene módulos: ni cantos, ni cristal, ni cajas. Es el fondo de la página, más oscuro, y encima una lista. La única caja es la tarjeta de suscripción del final, y es caja precisamente porque no es navegación. No tiene rótulos de grupo: trece destinos con la misma forma. Las filas miden unos 48 px con mucho aire lateral y la activa es una pastilla llena de ancho completo. Los contadores viven dentro de las filas, así que no hay campana compitiendo. Y el perfil está **arriba, como primera fila**.

**Por qué la nuestra se ve apretada, con números.** La fila quedó con 210 px útiles de los 244 de la barra: los 34 que faltan se los comieron el margen del módulo, su relleno y su canto. Y los cantos meten una caja dentro de otra dentro de otra: barra, módulo y fila. Eso es lo que se lee como amontonado, y viene directamente de D-L19.

**Lo que se revierte, y por qué.** Las tres eran recomendación de esta sesión, Sua las aprobó sobre el papel y en pantalla no funcionaron. Se dice aquí para que no se vuelvan a proponer sin leer esto:

- **D-L19 queda sin efecto.** Los módulos dejan de ser cajas con vidrio propio. El ritmo lo hace el aire, no el canto. Los tokens `--asb-admin-barra-modulo-*` no se borran: pasan a alimentar solo la hoja de la cuenta y el popover del tema, que son capas flotantes y sí deben tener canto.
- **D-L20 queda sin objeto.** Si no hay módulo que encender, el estado del desplazamiento no enciende nada: pasa a alimentar **solo** el aviso de lista cortada de D-L15.
- **D-L21 cambia de sitio, no de contenido.** La cuenta sube a la primera fila, con la misma composición (avatar, nombre y rango) y la misma hoja, que ahora abre hacia abajo.

**Lo que entra, con sus valores.** La barra es **una sola superficie de cristal**: velo, línea de límite, filo luminiscente y resplandor de esquina, y nada más. El aire entre grupos sube de 18 a 28 px por encima del rótulo, el rótulo gana 8 px por debajo, y entre filas entran 2 px. La fila sube de 2,75rem a **3rem**, con el icono a 20 px y 12 px de separación con el texto.

**El coste, medido antes de escribirlo.** Con la fila a 48 px la lista pasa de 1.216 a 1.312 px y, en una ventana de 1.019 de hueco, se corta el 22 % en vez del 16 %: de un vistazo caben 20 filas de 24 en vez de 22. Por eso el aviso de lista cortada (D-L15) deja de ser conveniente y pasa a ser necesario.

**Los contadores entran en las filas.** El mecanismo ya existe y lo usan cuatro sitios del panel, entre ellos las fotos por aprobar y la cartera en mora. Aquí se les da estilo propio, alineados a la derecha de la fila, en rojo solo cuando urgen. Cada insignia es una consulta por carga de página: si se extienden a más destinos, hay que medirlo antes.

**Lo que NO se toca.** El cristal y su velo calibrado, las dos luces, la línea del límite, el indicador del ítem activo con su brote, el anillo de foco de dos colores, los rótulos de grupo (más ligeros, pero siguen, porque veinticuatro destinos en plano no tienen dónde agarrarse), el control de tema con sus tres preferencias, y todas las guardias y tokens ya escritos.

### D-L24. El fondo de la barra: campo de puntos que huyen del cursor

**Pedido de Sua, 7 sep, textual:** «sigo viendo la barra para scrollear y el fondo que unifica los módulos, elimínalos y quiero que el fondo sea conformado por el blanco y unos puntos grises, puntos los cuales serán repulsivos al cursor».

**Hoy.** La barra lleva velo propio en todos los estados; solo se apaga al desplazar la lista. Y el scroller enseña su barra, porque Filament reserva el canal con `scrollbar-gutter: stable`.

| Opción | Coste |
|---|---|
| A. El velo desaparece **siempre**. El fondo de la barra pasa a ser un campo de puntos dibujado en un `<canvas>` detrás del contenido, y los puntos se apartan del puntero con caída suave. Se apaga la barra de desplazamiento y el aviso de lista cortada queda como única pista | Un lienzo y un bucle de animación nuevos. Hay que acotar el coste: el bucle solo corre con el puntero dentro y mientras los puntos vuelven a su sitio |
| B. Puntos en CSS con `radial-gradient` repetido, sin repulsión | Cero coste y cero repulsión: es la mitad del pedido |
| C. Un punto por elemento del DOM | Con 18 px de paso son más de mil nodos en una columna de 244: inaceptable |

**Recomendación: A**, con tres condiciones que no son negociables y van escritas aquí:

1. **El fondo invierte con el tema.** «Blanco con puntos grises» es la receta del tema claro; en oscuro es la superficie oscura con puntos claros. La barra dejó de tener paleta privada el 7 sep y no la recupera: los dos colores salen de tokens.
2. **La repulsión se apaga bajo `prefers-reduced-motion`.** Queda el campo de puntos quieto. Es animación ligada al gesto, y el proyecto ya decidió que esas se apagan.
3. **El lienzo no recibe puntero** y no lleva texto: es decoración pura, así que no entra en ninguna cuenta de contraste. Los puntos van por debajo de los módulos.

**Lo que se pierde al esconder la barra de desplazamiento**, dicho claro: la única pista de que la lista sigue pasa a ser la máscara de desvanecido de D-L15. Por eso ese aviso deja de ser un adorno y se vuelve obligatorio.

### D-L25. El límite deja de ser una línea y pasa a ser una unión

**Pedido de Sua, 7 sep:** «cambia la línea roja que está limitando la barra con el resto de la interfaz y hazlo tipo una sombra que va uniendo la barra con el resto».

**Qué decía D-L10 y por qué cambia.** Aquella decisión puso una línea de luminancia más una sombra, con este argumento: el filo rojo da 2,60:1 en claro y 1,93:1 en oscuro, y un borde de región se juzga contra 3:1. El argumento era correcto **cuando la barra no tenía fondo propio**. Desde D-L24 lo tiene: el campo de puntos distingue la región por textura y por superficie, no por su canto. Con eso, la línea dura deja de ser lo que sostiene el límite y pasa a ser solo un corte.

| Opción | Coste |
|---|---|
| A. Fuera la línea y fuera el filo rojo. El límite lo hace una **unión**: un degradado ancho que sale del canto de la barra hacia el contenido y se apaga, más una sombra proyectada suave | Se pierde el corte nítido: en pantallas de brillo bajo la frontera queda insinuada. A cambio es lo que Sua pidió y lo que el campo de puntos ya permite |
| B. Conservar la línea y añadir la sombra | Es lo de hoy más maquillaje: la línea roja seguiría ahí |
| C. Solo sombra proyectada, sin degradado | Una sombra sola contra un fondo casi del mismo tono no une nada: se ve como suciedad en el canto |

**Recomendación: A.** Porque la región ya se distingue por su fondo, y el encargo pide que la barra se una al contenido en vez de cortarlo.

**Lo que se conserva:** el rojo sigue vivo en la unión, pero como resplandor tenue dentro del degradado y no como filo de un píxel. Y el estado `scroll` deja de apagar nada del canto: no queda canto que apagar.

**Corregido el mismo día: la unión también se va.** Sua la vio construida y la rechazó con la misma razón que a la línea: «elimina esa línea roja que no permite la continuidad de la interfaz». Y tenía razón. El límite pasó por tres formas —línea de un píxel, filo rojo y franja difusa de 40 px— y las tres eran la misma cosa: un corte vertical, más o menos borroso. Con el campo de puntos de D-L26 gobernando el fondo entero, **nada separa la barra del contenido**: la zona la marca su resplandor y el orden lo ponen los cristales de cada apartado. Los tokens de la unión se retiran con su consumidor.

### D-L26. El campo de puntos gobierna toda la interfaz, y cada apartado gana su cristal

**Pedido de Sua, 7 sep:** «quiero que el fondo responsivo sea para toda la interfaz, no solo para la barra de navegación lateral, y para cada apartado de la barra de navegación lateral asígnale el módulo de cristal respectivo a cada uno».

**Lo que cambia, y por qué encaja ahora.** Hasta D-L24 los módulos encendían su vidrio solo al desplazar la lista, y la razón era buena: sin nada detrás, un cristal permanente se lee como caja. Con el campo de puntos **sí hay algo detrás**, así que el cristal por fin tiene qué refractar y deja de ser una caja para ser una lámina.

| Opción | Coste |
|---|---|
| A. Un solo lienzo fijo detrás de TODA la interfaz, y cada grupo de la barra con su cristal permanente | El fondo del contenido tiene que dejar de taparlo: hoy `.fi-main-ctn` pinta cuatro degradados opacos. Y el cristal permanente en cinco módulos sobre un lienzo que se repinta es la combinación cara: hay que medirla antes de dejarla |
| B. Un lienzo por zona (barra, contenido, cromo) | Tres bucles y tres pinceles para un solo efecto continuo, y las juntas se notan al mover el puntero entre zonas |
| C. Dejar el campo solo en la barra | Es lo de hoy y no es lo que se pide |

**Recomendación: A**, con dos condiciones medidas y no supuestas:

1. **El desenfoque de los módulos se mide antes de quedarse.** Cinco láminas con `backdrop-filter` sobre un lienzo que se repinta cada fotograma es justo lo que hace tartamudear a una GPU integrada. Si la medición sale mal, el cristal se queda en velo y canto, sin desenfoque, y se dice por escrito.
2. **El lienzo es uno y va detrás de todo**, con `position: fixed`, sin puntero y fuera del árbol de accesibilidad. La repulsión escucha en el documento, no en la barra.

**Lo que se pierde:** los degradados del fondo del contenido, que hoy tapan cualquier cosa que se pinte debajo. El fondo pasa a ser la superficie plana más los puntos.

**Cómo quedó, y qué falta medir (7 sep).** El cristal de los apartados se construyó **sin `backdrop-filter`**: es velo, canto y brillo especular sobre el campo de puntos, que ya da la refracción a la vista. Así la combinación cara de la condición 1 no llega a existir, y por eso el desenfoque no se midió: no hay ninguno que medir. Lo que sí queda pendiente es el coste del propio campo con el puntero en movimiento, medido en una máquina real: el panel de la sesión no pinta fotogramas cuando la ventana no está delante, así que la medición de `requestAnimationFrame` no se pudo tomar aquí y la toma Sua.

---

### D-L27. El cristal de los apartados deja ver el campo

**Pedido de Sua, 8 sep, con el panel ya desplegado:** «me gustaría que los módulos sean un poquito transparentes».

**De dónde viene.** El velo se calibró al 88 % cuando la barra tenía fondo propio y detrás del módulo no había nada que mirar. Desde D-L26 sí lo hay: el campo de puntos. Al 88 % la lámina lo tapa casi entero y se lee como una tarjeta opaca sobre un fondo con textura, no como cristal.

**Lo que cambia.** `--asb-admin-barra-velo` pasa del 88 % al **76 %** en los dos temas. `--asb-admin-barra-velo-cajon` **no se toca**: el cajón móvil y los popovers se apoyan sobre contenido que sí hay que tapar, y ese velo se calibró aparte.

**Lo que NO cambia, y hay que decirlo:** no entra `backdrop-filter`. La condición 1 de D-L26 sigue en pie —cinco láminas desenfocando sobre un lienzo que se repinta es la combinación cara—, y la refracción la sigue dando el campo a la vista.

**El contraste, recalculado el 8 sep** (no estimado: la cuenta la hace `MideContraste` sobre los colores del archivo). El velo apenas mueve la cuenta, porque la superficie y el fondo del panel son casi el mismo color:

| Velo | Claro, rótulo de grupo | Claro, rótulo activo | Oscuro, rótulo de grupo | Oscuro, rótulo activo |
|---|---|---|---|---|
| 88 % (antes) | 11,27:1 | 6,35:1 | 7,68:1 | 5,20:1 |
| **76 % (ahora)** | **11,18:1** | **6,29:1** | **7,74:1** | **5,23:1** |
| 60 % (por saber dónde está el suelo) | 11,01:1 | 6,18:1 | 7,79:1 | 5,30:1 |

Todos por encima de 4,5:1 con holgura, así que aquí manda el ojo y no la cuenta. La guardia existente sigue vigilando el rótulo activo, que es el del margen justo.

### D-L28. El resplandor de la zona cubre todo el lado, no solo la esquina

**Pedido de Sua, 8 sep:** «que el rojo que se ve en la esquina izquierda abarque todo el lado hasta la parte inferior».

**De dónde viene.** El resplandor que marca la zona del panel es `radial-gradient(120% 55% at 0% 0%, …)`: nace en la esquina superior izquierda y se apaga a poco más de media altura. En una pantalla de 1.080 px se acaba sobre los 594, así que la mitad de abajo de la barra se queda sin la marca de su zona.

**Lo que cambia.** El resplandor pasa a dos capas sobre `.fi-sidebar::before`:

1. un **lavado horizontal** anclado al canto izquierdo, `linear-gradient(90deg, …, transparent 82%)`, que da la marca a **toda la altura**;
2. el **radial de la esquina**, que se conserva porque es de donde nace la luz y sigue haciendo el punto más brillante.

Las dos capas suman alfa, así que la esquina queda al doble de intensidad y el resto del lado mantiene un lavado constante hasta abajo.

**Lo que NO puede pasar, y por eso hay guardia.** Sua rechazó tres veces un límite vertical: línea, filo y franja difusa de 40 px. El lavado es lo contrario de esos tres —es más fuerte en el canto izquierdo y se apaga hacia dentro, sin ningún canto en el límite con el contenido—, pero la diferencia es de dirección y una dirección se invierte con un carácter. La guardia de continuidad se amplía a `::before` y exige que el resplandor **empiece opaco en el canto izquierdo**, nunca transparente, que es como se volvería a dibujar la franja del límite.

**Corregido el mismo día: el resplandor deja de ser de la barra y pasa a ser de la página.** Sua vio un corte horizontal justo debajo del logotipo —«noto un corte arriba con lo rojo que colocamos ahí, complétalo hasta arriba»— y la causa es estructural, escrita en el blade de Filament: **el topbar no vive dentro de `.fi-layout`**, sino como hermano anterior, hijo directo de `.fi-body`. Cruza el ancho entero por encima de la barra. El resplandor vivía en `.fi-sidebar::before`, que empieza justo debajo, así que el rojo nacía en el canto inferior del topbar: eso es exactamente un corte.

Pintar el mismo lavado también en el topbar habría sido emparejar dos capas distintas y confiar en que no se separen nunca. Lo que se hace es **una sola capa**: el resplandor se muda a `.fi-body::before`, fija, de alto completo y anclada al canto izquierdo, detrás de todo. Cubre topbar y barra **por construcción, no por coincidencia**, y el topbar —blanco al 78 % con desenfoque— lo deja pasar suavizado, que es justo la transición que faltaba.

El ancho del lavado pasa a token, `--asb-admin-barra-resplandor-ancho`: la capa nueva mide lo que mide la página, no lo que mide la barra, y un porcentaje sobre el ancho de la página no es el mismo lavado.

---

## Lo que la construcción cambió (Parte III, 7 y 8 sep 2026)

Las dieciocho decisiones se escribieron antes de tocar código, y el código las contradijo o las amplió en siete sitios. Lo que sigue es lo que de verdad quedó.

1. **El velo no es lo que sostiene el contraste, y el rótulo de grupo tampoco es el que manda.** La spec calibró el velo contra el rótulo de grupo. Recalculado el 7 sep: sobre el cristal del panel da 11,27:1 en claro y 7,68:1 en oscuro, y ni bajando el velo al 40 % baja de 10:1, porque la superficie y el fondo del panel son casi el mismo color. El que tiene el margen justo es el **rótulo del ítem activo** sobre el tinte con el halo encima: 6,35:1 y 5,20:1. La guardia se reescribió para vigilar ese, que es el que puede romperse.
2. **La aritmética corrigió a la spec en el rojo del rótulo activo.** El documento decía que `--asb-acento` no llegaba a 4,5:1 en claro. Da 4,93:1 y sí llega. Se usa `--asb-acento-fuerte` igual, por margen (6,35:1), no por obligación.
3. **Los módulos con vidrio propio y permanente no funcionan sobre un fondo liso.** D-L19 los pintaba siempre y se leían como cajas dentro de cajas: la fila se quedaba en 210 px útiles de 244. Se aplanó todo (corrección del 7 sep) y luego volvieron, primero apagados y encendidos por estado, y por fin **permanentes pero sobre el campo de puntos** (D-L26), que es lo que les da algo que refractar.
4. **La cuenta pasó por tres sitios el mismo día**: pie de la barra (D-L21), primera fila de la lista, y por fin el cromo superior junto al control de tema, que es donde Sua la quiso.
5. **El límite pasó por tres formas y ninguna sobrevivió**: línea de un píxel (D-L10), filo rojo, y franja difusa de 40 px (D-L25). Las tres se leían como un corte vertical. Con el campo de puntos gobernando el fondo, **nada separa la barra del contenido**.
6. **Filament anula la sombra del elemento en escritorio.** `.fi-sidebar` recibe `lg:shadow-none` desde una capa que gana, así que una sombra declarada en el elemento computa `rgba(0,0,0,0) 0 0 0 0`. Lo cazó la maqueta, no una guardia: la guardia afirmaba que la sombra estaba escrita, y estarlo no es aplicarse.
7. **El lienzo del campo se coló en el flujo** porque `.fi-sidebar > *` empata en especificidad con su regla y va después: le quitaba el `position: absolute`, empujaba la lista fuera de la vista y rompía la barra entera. Hay `:not()` y guardia.
8. **El aviso de lista cortada recortaba el módulo.** La máscara de desvanecido (D-L15) tenía 1,5 rem y la lista solo 0,5 rem de aire vertical, así que en reposo el canto de la primera y la última lámina nacía dentro del desvanecido. Con vidrio suelto no se notaba; con el módulo bordeado de D-L26, un canto a medio pintar se lee como una caja cortada, y Sua lo vio el 8 sep. El aviso baja a 0,9 rem y el relleno pasa a `calc(aviso + 0,35 rem)`: lo que se desvanece es lista, nunca el borde de un módulo quieto. Medido en la maqueta: relleno 20 px contra 14,4 de máscara, primera lámina a 24 px del canto. La guardia hace la cuenta y se vio roja con las dos mutaciones (relleno por debajo del aviso, y relleno exactamente igual).
9. **Y el canto derecho no estaba cortado: estaba estrecho.** El relleno de la lista era asimétrico (0,75 rem a la izquierda, 0,3 a la derecha) desde que Sua pidió «agrandar los módulos un poco a la derecha». Con 16 px de radio y 4,8 px de aire, la curva del canto derecho no tenía fondo contra el que leerse y se veía como un corte, mientras el izquierdo con 12 px se veía entero. Lo que compensa el ancho del módulo es el ancho de la barra, no el aire de un canto: `--asb-admin-sidebar-ancho` sube de 15,25 a 15,75 rem y el relleno vuelve a ser simétrico. Medido: barra 252 px, módulo 228 (antes 227,2, así que no se pierde nada), 12 px de aire a cada lado, fila todavía de 48 px. Y la maqueta reprodujo el defecto solo cuando se le puso el marcado real del grupo, con su botón de plegado.
10. **El desenfoque literal que D-L17 anotó para la barra estaba vivo en el cromo superior.** La guardia de la tarea 8 exige que ningún `blur()` quede escrito en una regla, porque una media no puede apagar lo que no es token; al ponerla se puso roja señalando `.fi-topbar` y `.fi-topbar-ctn.asb-topbar--scrolled .fi-topbar`, que lo llevaban a mano en cuatro declaraciones. Salen dos tokens, `--asb-admin-topbar-desenfoque` y su gemelo `-firme` (se distinguen solo en la saturación, 145 % contra 130 %, pero son dos estados), y las dos señales que apagan transparencia los apagan también. No estaba en el encargo de la barra: lo destapó su guardia.

**Lo que la maqueta enseñó, y por qué existe.** El panel exige segundo factor, así que ninguna sesión automatizada lo abre. Se construyó una maqueta que reproduce el marcado de la barra con el tema compilado y se sirve por HTTP: sin ella se entregaron dos regresiones visuales seguidas. La primera vez que se abrió reprodujo el aviso que la propia spec anotaba: sin `fi-sidebar-open`, Filament deja la barra fuera de pantalla.

**Lo que queda pendiente de las doce tareas del plan:** las cuatro señales del sistema (tarea 8), la guardia de contrato sobre el vendor (tarea 9), la medición completa en Chromium con la maqueta (tarea 10) y la revisión adversaria (tarea 11). Y una medición que solo puede hacer Sua: el coste del campo de puntos en marcha, porque el navegador de la sesión no pinta fotogramas con la ventana detrás.
