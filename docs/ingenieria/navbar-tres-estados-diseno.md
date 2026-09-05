# Navbar de escritorio en tres estados — diseño aprobado

**3 de septiembre de 2026** · Persona 1 (Sua) con Claude Code · rama `p1-navbar-alternativa` · **no se fusiona ni se despliega**: es la opción B que se lleva a la reunión con la dirección junto a la opción A (la barra de la Persona 2, hoy en producción).

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
| D7 | Duración del rebote | Hasta **520 ms** de reloj con token `--duracion-rebote` y excepción anotada en la suite |
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

Los **tres hijos del `<nav>` son los tres módulos**. En `inicial` la píldora exterior lleva el vidrio y los módulos son transparentes; en `scroll` y `atencion` la píldora exterior se vuelve transparente (fondo, borde, sombra) y cada módulo enciende su propio vidrio. El `gap` del `<nav>` crece de `0` a `var(--asb-separacion-modulos)`. Ningún nodo se añade ni se quita al cambiar de estado.

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
| `--duracion-rebote` | `520ms` | Toda transición con curva de rebote. El movimiento «llega» hacia los 250 ms; el resto es asentamiento |
| `--asb-separacion-modulos` | `0.75rem` | `gap` del `<nav>` en scroll/atención. **Es layout, no movimiento: no se anula** bajo movimiento reducido |
| `--asb-caida-modulo` | `6px` | `translate` vertical de los módulos al separarse (bajan y asientan) |
| `--asb-escala-popover` | `0.92` | escala de arranque de los popovers de tema e idioma |
| `--asb-desplazamiento-popover` | `-6px` | `translate` de arranque de los popovers |
| `--asb-escala-isotipo` | `0.9` | escala de arranque de la imagen entrante en el cruce del logo |

`--duracion-cromo` (520 ms, de la barra de la Persona 2) **se conserva**: lo sigue usando `.tema-lateral__cuerpo`, que se queda en móvil. Las reglas de escritorio que se reemplazan pasan a `--duracion-rebote`; los dos tokens valen lo mismo y significan cosas distintas (uno abre la barra lateral, otro asienta un resorte).

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

**Tema.** Un `<button>` de 44×44 con `aria-label="Apariencia del sitio"`, `aria-expanded`, `aria-controls="popover-tema"`. Muestra `heroicon-o-sun` si el tema **resuelto** es claro y `heroicon-o-moon` si es oscuro; **nunca el monitor**. El icono lo decide CSS por la clase `dark` del `<html>` (`dark:hidden` / `hidden dark:block`), no `x-show`: el `<head>` pone esa clase antes del primer pintado y `elegir()` la cambia al instante, así que es el tema resuelto sin destello del icono equivocado antes de que arranque Alpine (corregido el 5 sep tras la revisión de la tarea 5). Con puntero fino se abre en `mouseenter` (280 ms de gracia al salir); con grueso, al tocar; con teclado, al pulsar. El popover `#popover-tema` es una `hoja-flotante` **debajo** de la barra, alineada al botón, con tres filas `fila-pulsable` de 44 px: `☀ Claro`, `🌙 Oscuro`, `🖥 Sistema` (iconos Heroicons `sun`, `moon`, `computer-desktop` con `<span class="sr-only">` y texto visible), cada una con `aria-pressed` ligado a `$store.tema.preferencia` y un punto indicador en la activa. Cierra con clic fuera, Escape (devuelve el foco al botón) y `focusout`.

**Idioma.** Un `<button>` de 44×44 con el texto `ES` (siglas ISO 639-1 del idioma actual) y un galón, `aria-label="Idioma del sitio"`, `aria-controls="popover-idioma"`. El popover `#popover-idioma` es **vertical**: dos filas `fila-pulsable` con bandera SVG inline de 20×14 + nombre del idioma en su propia lengua: `🇨🇴 Español` (activa, `aria-pressed="true"`, punto indicador) y `🇺🇸 English` (`disabled`, `aria-disabled="true"`, con «próximamente» en `text-2xs text-apagado`). Las banderas son un componente `x-publico.bandera` con `pais="co"|"us"`: Colombia en tres franjas (amarillo 50 %, azul 25 %, rojo 25 %); Estados Unidos **simplificada** (trece franjas y cantón azul sin estrellas, que a 14 px no se resuelven). No se instala ningún paquete.

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
| `resources/views/components/publico/barra-tema.blade.php` | `lg:hidden` en el `<aside>` |
| `resources/views/components/layouts/publico.blade.php` | `preload` del isotipo con `media="(min-width: 64rem)"` |
| `resources/css/tokens.css` | Dos curvas `linear()`, `--duracion-rebote`, cinco tokens de geometría, bloque de movimiento reducido ampliado; `--duracion-cromo` se conserva |
| `resources/css/app.css` | Reglas `.bandeja`, `.modulo*`, `.control-plegable`, `.indicador-mas`, brillo y lente; **retira** `.cromo-bandeja`, `.cromo-compacto`, `.cromo-expandido`, `.cromo-desplegable`, sustituidas; conserva `.cromo`, `.cromo-apoyado`, `.cromo-oculto`, `.nav-enlace` (subrayado de la sección actual), `.hoja-flotante`, `.tema-lateral*` |
| `resources/js/app.js` | Store de tema (§7.1); `Alpine.data('escena')` sin cambios, reutilizado |
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
| `MovimientoTest` comentario «nada pasa de 300 ms» | techo implícito | excepción anotada: `--duracion-rebote` es la única duración > 300 ms y se justifica por el asentamiento del resorte; se añade guardia literal `--duracion-rebote: 520ms` | D7 |

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
