# Navbar de escritorio en tres estados — plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir en la rama `p1-navbar-alternativa` la barra pública de escritorio con tres estados (inicial, scroll, atención), tres módulos que se separan por CSS sobre un único DOM, popover de tema con «Sistema», chip de idioma con inglés «próximamente», y módulo de cuenta con prefijo de rol; con rebote real por `linear()` y sin tocar el móvil.

**Architecture:** Un solo `<header>` con un solo `<nav>` cuyos tres hijos son los tres módulos; Alpine solo calcula `data-estado` y todo lo visual lo decide CSS con tokens de resorte que `prefers-reduced-motion` anula. Los controles nuevos (tema, idioma, bandera) son componentes Blade propios; el store de tema aprende `system` y expone `resuelto`. Cada tarea añade primero su prueba, la ve roja, y confirma.

**Tech Stack:** Laravel 13 · Blade · Alpine 3 · Tailwind 4 (CSS-first, `@theme`) · blade-heroicons (ya instalado por Filament) · PHPUnit 12 · playwright-cli para medir en Chromium.

**Spec:** `docs/ingenieria/navbar-tres-estados-diseno.md` — el plan argumenta desde ahí; quien ejecute lee los dos.

## Global Constraints

Copiadas de la spec §3. Cada tarea las hereda.

- Un solo `<header>`, un solo `<nav>`; los cinco controles de escritorio en un mismo `div` hijo directo de `<nav>` con clase `gap-1`, en este orden: `Directorio`, `Abre tu negocio`, `Eventos`, `Bolsas`, `El gremio`. `aria-current="page"` exactamente dos veces en el header. Cada etiqueta declarada una sola vez como `'texto' => 'Etiqueta'` en `navbar.blade.php`.
- Nunca una segunda navegación en el DOM: los estados se resuelven por CSS sobre el mismo marcado.
- En vistas Blade están prohibidos: `duration-N` numérico, `duration-[…]`, `delay-[…]`, `ease-[…]`, `ease-in` suelto, `transition-all`, `transition: all`, y cualquier flecha Unicode (U+2190–21FF, U+25B6, U+25C0, U+2B00–2BFF) **incluidos comentarios**. Duraciones solo como `duration-(--duracion-*)` y curvas como utilidades `ease-*`.
- Los portadores `pulsable`, `fila-pulsable`, `enlace-accion`, `tarjeta-pulsable` no comparten elemento ni lista de clases (`class`, `x-bind:class`, `@class`) con utilidades `transition-*`, `duration-*`, `ease-*`, `delay-*`; `fila-pulsable` tampoco con `hover:bg-*`. Excepción: los valores de `x-transition:*`.
- Toda geometría de movimiento nueva es un token `--asb-*` que `@media (prefers-reduced-motion: reduce)` pone a `0`/`1`. Las duraciones no se tocan bajo movimiento reducido.
- Vidrio solo con `var(--asb-cromo-velo)` y `var(--asb-cromo-desenfoque)`; nunca `blur()` literal en reglas nuevas.
- El tema se cambia solo por `$store.tema.elegir(valor)`; nunca alternar la clase `dark` a mano. `localStorage.theme` acepta `light`, `dark`, `system`.
- La marca no se recolorea ni se recorta. Solo existe isotipo rojo (`public/img/monograma-asobares.png`, 156×108).
- Cadenas fijadas por pruebas que leen archivos crudos y que se conservan literalmente: en `navbar.blade.php` `-my-1.5 flex shrink-0 items-center py-1.5`, `-my-1 rounded-lg px-3 py-3 text-sm`, `-my-1 rounded-lg px-3 py-3 text-sm text-tenue`, `after:absolute after:inset-x-0 after:-inset-y-1 after:content-['']`, `-m-0.5 rounded-lg p-2.5`, `rounded-lg px-3 py-3 text-sm text-tinta`, `absolute inset-x-0 top-full`, `bg-fondo`, `overflow-y-auto`, `duration-(--duracion-panel)`, `duration-(--duracion-salida)`, `ease-cajon`, `x-on:keydown.escape.window="menuMovil = false"`, `x-on:click.outside="menuMovil = false"`, `x-on:resize.window`, `transicion-desplegable`, `fila-pulsable`; en `menu-usuario.blade.php` `-m-1 flex items-center gap-2 rounded-full p-1`, `rounded-lg px-3 py-3 text-sm text-suave`, `transicion-desplegable`, `fila-pulsable`. Ninguno de los tres archivos puede contener `hover:bg-superficie-alta` ni `x-collapse`.
- Anónimo no ve `menu-cuenta`, `Cerrar sesión`, `Ir al panel del gremio` ni `Configuración del sitio`. Los popovers nuevos usan `popover-tema` y `popover-idioma` y van **antes** de `<div id="menu-movil"` en el header.
- Sin dependencias nuevas. Sin carpetas nuevas. Sin clases Tailwind de fábrica de color (`bg-white`, `text-gray-*`, `border-white/…`); sin `outline-none` ni `focus:ring-0`.
- Antes de confirmar cualquier PHP: `vendor/bin/pint --dirty --format agent`. Si se tocan vistas o CSS: `php artisan view:clear` y `npm run build`.
- Git: siempre `GIT_OPTIONAL_LOCKS=0`. Mensajes de commit en español, sin `Co-Authored-By`.

---

## Estructura de archivos

| Archivo | Responsabilidad | Tarea |
|---|---|---|
| `resources/css/tokens.css` | Curvas de resorte, `--duracion-rebote`, cinco tokens de geometría, `@supports`, movimiento reducido | 1 |
| `tests/Feature/NavbarTresEstadosTest.php` | **Nuevo.** Todas las guardias de lo nuevo; crece tarea a tarea | 1–10 |
| `tests/Feature/MovimientoTest.php` | Guardia del token de rebote y la excepción al techo de 300 ms | 1 |
| `resources/js/app.js` | Store de tema con `system` y `resuelto` | 2 |
| `resources/views/components/publico/barra-tema.blade.php` | Marca activo por `resuelto`; `lg:hidden` | 2, 10 |
| `resources/views/components/publico/bandera.blade.php` | **Nuevo.** SVG inline `co` / `us` | 3 |
| `resources/views/components/publico/logo.blade.php` | Modo `doble`: logotipo + isotipo superpuestos | 4 |
| `resources/views/components/layouts/publico.blade.php` | Precarga del isotipo | 4 |
| `resources/views/components/publico/control-tema.blade.php` | **Nuevo.** Botón sol/luna + popover de tres opciones | 5 |
| `tests/Feature/TemaClaroOscuroTest.php` | Pasa a exigir «Sistema»; la barra lateral solo en móvil | 5, 10 |
| `resources/views/components/publico/control-idioma.blade.php` | **Nuevo.** Chip `ES` + popover vertical | 6 |
| `resources/views/components/publico/menu-usuario.blade.php` | Prefijo `Sec.` / `Admin` en el disparador | 7 |
| `resources/css/app.css` | Reglas `.bandeja`, `.modulo*`, `.control-plegable`, `.indicador-mas`, `.logo-doble*`; retira `.cromo-bandeja`, `.cromo-compacto`, `.cromo-expandido`, `.cromo-desplegable` | 8 |
| `resources/views/components/publico/menu-grupo.blade.php` | Acepta clase externa en su raíz (para `control-plegable`) | 9 |
| `resources/views/components/publico/navbar.blade.php` | Bloque de escritorio: tres módulos, `data-estado`, máquina de estados | 9 |
| `resources/views/components/publico/selector-tema.blade.php` | **Se borra** (huérfano) | 10 |
| `tests/Feature/ObjetivoTactilTest.php` | Borra la fila de `selector-tema`; añade filas medidas de los controles nuevos | 10, 11 |
| `material/encargo.md` | §13: nota fechada de la opción «Sistema» | 12 |

---

### Task 1: Tokens de resorte

**Files:**
- Modify: `resources/css/tokens.css` (bloque `@theme` líneas 20-31; `:root` líneas 191-196 y 245-248; bloque `@media (prefers-reduced-motion: reduce)` líneas 411-428)
- Modify: `tests/Feature/MovimientoTest.php:28-35`
- Create: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Produces: utilidades Tailwind `ease-rebote-suave`, `ease-rebote-vivo`; tokens `--duracion-rebote`, `--asb-separacion-modulos`, `--asb-caida-modulo`, `--asb-escala-popover`, `--asb-desplazamiento-popover`, `--asb-escala-isotipo`. Los usan las tareas 5, 6, 8 y 9.

- [ ] **Step 1: Crear la clase de prueba con la primera guardia**

Run: `php artisan make:test --phpunit NavbarTresEstadosTest --no-interaction`

Sustituir el contenido de `tests/Feature/NavbarTresEstadosTest.php` por:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * La barra pública de escritorio en tres estados (spec:
 * docs/ingenieria/navbar-tres-estados-diseno.md). Cada prueba nombra en su
 * docblock la rotura que la pone roja; se hizo antes de darla por buena.
 */
class NavbarTresEstadosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Rotura: borrar `--asb-caida-modulo: 0px` del bloque de movimiento reducido.
     */
    public function test_el_rebote_es_un_token_que_el_movimiento_reducido_anula(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertStringContainsString('--duracion-rebote: 520ms', $tokens);
        $this->assertStringContainsString('--asb-separacion-modulos: 0.75rem', $tokens);
        $this->assertStringContainsString('--asb-caida-modulo: 6px', $tokens);
        $this->assertStringContainsString('--asb-escala-popover: 0.92', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-popover: -6px', $tokens);
        $this->assertStringContainsString('--asb-escala-isotipo: 0.9', $tokens);

        $reducido = strstr($tokens, '@media (prefers-reduced-motion: reduce)');
        $this->assertNotFalse($reducido, 'tokens.css ya no tiene el bloque de movimiento reducido');

        $this->assertStringContainsString('--asb-caida-modulo: 0px', $reducido);
        $this->assertStringContainsString('--asb-escala-popover: 1', $reducido);
        $this->assertStringContainsString('--asb-desplazamiento-popover: 0px', $reducido);
        $this->assertStringContainsString('--asb-escala-isotipo: 1', $reducido);
        $this->assertStringContainsString('--ease-rebote-suave: var(--ease-cajon)', $reducido);
        $this->assertStringContainsString('--ease-rebote-vivo: var(--ease-cajon)', $reducido);

        // La separación es layout, no movimiento: sobrevive.
        $this->assertStringNotContainsString('--asb-separacion-modulos: 0', $reducido);
    }

    /**
     * El respaldo para navegadores sin linear() no puede ser una segunda
     * declaración: var() es inválido en tiempo de cómputo y la propiedad
     * caería a `ease`, no a la declaración anterior. Por eso el token nace
     * como cubic-bezier y solo dentro de @supports pasa a linear().
     *
     * Rotura: borrar la declaración con cubic-bezier de fuera del @supports.
     */
    public function test_el_rebote_lleva_respaldo_por_supports(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $soporte = strstr($tokens, '@supports (animation-timing-function: linear(0, 1))');
        $this->assertNotFalse($soporte, 'falta el bloque @supports de linear()');

        $antes = strstr($tokens, '@supports (animation-timing-function: linear(0, 1))', true);

        foreach (['--ease-rebote-suave', '--ease-rebote-vivo'] as $curva) {
            $this->assertStringContainsString("{$curva}: cubic-bezier(0.32, 0.72, 0, 1)", $antes, "{$curva} sin respaldo cubic-bezier");
            $this->assertStringContainsString("{$curva}: linear(0, ", $soporte, "{$curva} sin linear() dentro del @supports");
        }
    }
}
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=NavbarTresEstadosTest`
Expected: 2 FAIL — `--duracion-rebote: 520ms` no está; falta el `@supports`.

- [ ] **Step 3: Añadir las curvas al `@theme` de `tokens.css`**

Tras la línea `--ease-color: ease;` (línea 31), añadir:

```css

    /*
     * Resortes reales para la barra de escritorio. Nacen como la curva del
     * cajón (respaldo) y el @supports del final los sustituye por linear()
     * donde exista: una segunda declaración no serviría de respaldo porque
     * var() es inválido en tiempo de cómputo y la propiedad caería a `ease`.
     */
    --ease-rebote-suave: cubic-bezier(0.32, 0.72, 0, 1);
    --ease-rebote-vivo: cubic-bezier(0.32, 0.72, 0, 1);
```

- [ ] **Step 4: Añadir la duración y la geometría al `:root`**

Tras `--duracion-cromo: 520ms; /* apertura de la barra pública */` (línea 196), añadir:

```css
    --duracion-rebote: 520ms; /* asentamiento de un resorte; el movimiento «llega» hacia los 250 */
```

Tras `--asb-avance-flecha: 3px;` (línea 248), añadir:

```css

    /*
     * Geometría de la barra en tres estados (escritorio). La separación entre
     * módulos es layout y sobrevive al movimiento reducido; caída, escalas y
     * desplazamiento son movimiento y el interruptor del final los anula.
     */
    --asb-separacion-modulos: 0.75rem;
    --asb-caida-modulo: 6px;
    --asb-escala-popover: 0.92;
    --asb-desplazamiento-popover: -6px;
    --asb-escala-isotipo: 0.9;
```

- [ ] **Step 5: Añadir el `@supports` justo antes del bloque de movimiento reducido**

Antes de la línea `@media (prefers-reduced-motion: reduce) {` (línea 411), añadir:

```css
/*
 * Oscilador amortiguado muestreado en 25 paradas. Suave: amortiguación 0,70,
 * sobreimpulso 4,6 % en el 54 % del recorrido. Vivo: 0,55, sobreimpulso
 * 12,5 % en el 33 %. Solo donde el navegador entiende linear().
 */
@supports (animation-timing-function: linear(0, 1)) {
    :root {
        --ease-rebote-suave: linear(0, 0.050 4%, 0.168 8%, 0.319 12%, 0.475 17%, 0.620 21%, 0.744 25%, 0.845 29%, 0.922 33%, 0.977 38%, 1.013 42%, 1.034 46%, 1.044 50%, 1.046 54%, 1.043 58%, 1.037 62%, 1.030 67%, 1.023 71%, 1.017 75%, 1.011 79%, 1.006 83%, 1.003 88%, 1.001 92%, 0.999 96%, 1);
        --ease-rebote-vivo: linear(0, 0.086 4%, 0.283 8%, 0.516 12%, 0.735 17%, 0.910 21%, 1.030 25%, 1.099 29%, 1.125 33%, 1.121 38%, 1.099 42%, 1.071 46%, 1.042 50%, 1.018 54%, 1.000 58%, 0.990 62%, 0.985 67%, 0.984 71%, 0.986 75%, 0.990 79%, 0.994 83%, 0.997 88%, 0.999 92%, 1.001 96%, 1);
    }
}

```

- [ ] **Step 6: Ampliar el bloque de movimiento reducido**

Dentro de `@media (prefers-reduced-motion: reduce) { :root { … } }`, tras `--asb-avance-flecha: 0px;`, añadir:

```css

        /* Barra en tres estados: la caída, las escalas y el desplazamiento son
           movimiento; la separación entre módulos es layout y se queda. */
        --asb-caida-modulo: 0px;
        --asb-escala-popover: 1;
        --asb-desplazamiento-popover: 0px;
        --asb-escala-isotipo: 1;

        /* El rebote ES geometría: sin sobreimpulso. */
        --ease-rebote-suave: var(--ease-cajon);
        --ease-rebote-vivo: var(--ease-cajon);
```

- [ ] **Step 7: Verla verde**

Run: `php artisan test --compact --filter=NavbarTresEstadosTest`
Expected: PASS (2).

- [ ] **Step 8: Anotar la excepción al techo de 300 ms en `MovimientoTest`**

En `tests/Feature/MovimientoTest.php`, sustituir las líneas 28-35:

```php
        // Duraciones: la escala codifica que la salida es más rápida que la
        // entrada (160 < 200) y que nada de interfaz pasa de 300 ms.
        $this->assertStringContainsString('--duracion-instante: 100ms', $tokens);
        $this->assertStringContainsString('--duracion-boton: 140ms', $tokens);
        $this->assertStringContainsString('--duracion-salida: 160ms', $tokens);
        $this->assertStringContainsString('--duracion-entrada: 200ms', $tokens);
        $this->assertStringContainsString('--duracion-panel: 240ms', $tokens);
```

por:

```php
        // Duraciones: la escala codifica que la salida es más rápida que la
        // entrada (160 < 200) y que nada de interfaz pasa de 300 ms, con dos
        // excepciones con nombre: la apertura de la barra lateral y el
        // asentamiento del resorte de la barra de escritorio (spec del 3 sep
        // 2026, D7). Un resorte «llega» hacia los 250 ms; el resto es la cola
        // que se asienta, y cortarla es quitarle el rebote.
        $this->assertStringContainsString('--duracion-instante: 100ms', $tokens);
        $this->assertStringContainsString('--duracion-boton: 140ms', $tokens);
        $this->assertStringContainsString('--duracion-salida: 160ms', $tokens);
        $this->assertStringContainsString('--duracion-entrada: 200ms', $tokens);
        $this->assertStringContainsString('--duracion-panel: 240ms', $tokens);
        $this->assertStringContainsString('--duracion-rebote: 520ms', $tokens);
```

- [ ] **Step 9: Comprobar que la suite de movimiento sigue verde y confirmar**

Run: `php artisan test --compact --filter="MovimientoTest|NavbarTresEstadosTest"`
Expected: PASS.

Run: `vendor/bin/pint --dirty --format agent`

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/css/tokens.css tests/Feature/NavbarTresEstadosTest.php tests/Feature/MovimientoTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Anade los tokens de resorte de la barra en tres estados

Dos curvas de oscilador amortiguado como linear() (suave 0,70 y viva
0,55), con respaldo cubic-bezier fuera de un @supports porque var() es
invalido en tiempo de computo y una segunda declaracion no serviria.
Duracion propia de 520 ms anotada como excepcion al techo de 300, y cinco
tokens de geometria que el movimiento reducido anula, salvo la separacion
entre modulos, que es layout.
EOF
```

---

### Task 2: El store de tema aprende «system» y expone «resuelto»

**Files:**
- Modify: `resources/js/app.js:16-58` y `:182-187`
- Modify: `resources/views/components/publico/barra-tema.blade.php:30-38`
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Produces: `$store.tema.preferencia ∈ {'light','dark','system'}`, `$store.tema.resuelto ∈ {'light','dark'}`, `$store.tema.elegir('light'|'dark'|'system')`. Los consumen las tareas 5 y 10.

- [ ] **Step 1: Escribir la prueba**

Añadir a `NavbarTresEstadosTest`:

```php
    /**
     * Rotura: en `leer()` volver a `['light', 'dark'].includes(guardado)`.
     */
    public function test_el_store_de_tema_acepta_sistema_y_distingue_lo_resuelto(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString("resuelto: 'light'", $js);
        $this->assertStringContainsString("['light', 'dark', 'system'].includes(guardado)", $js);
        $this->assertStringContainsString('this.resuelto = this.resolver(', $js);
        $this->assertStringContainsString("matchMedia('(prefers-color-scheme: dark)').addEventListener('change'", $js);
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_el_store_de_tema_acepta_sistema_y_distingue_lo_resuelto`
Expected: FAIL — `resuelto: 'light'` no está.

- [ ] **Step 3: Reescribir el store**

Sustituir en `resources/js/app.js` el bloque `Alpine.store('tema', { … });` (líneas 16-58) por:

```js
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
```

- [ ] **Step 4: Actualizar el listener de `storage`**

Sustituir (líneas 182-187):

```js
    if (evento.key === 'theme') {
        const tema = Alpine.store('tema');
        tema.preferencia = tema.resolver(tema.leer());
    }
```

por:

```js
    if (evento.key === 'theme') {
        const tema = Alpine.store('tema');
        tema.preferencia = tema.leer();
        tema.resuelto = tema.resolver(tema.preferencia);
    }
```

- [ ] **Step 5: La barra lateral de móvil marca activo por lo resuelto**

Antes, `preferencia` colapsaba a lo pintado y la barra lateral siempre tenía un botón activo. Con `system` ya no; para que el móvil siga igual, en `barra-tema.blade.php` sustituir las tres apariciones de `$store.tema.preferencia` (líneas 30, 32 y 35) por `$store.tema.resuelto`.

- [ ] **Step 6: Verla verde, compilar y confirmar**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|TemaClaroOscuroTest"`
Expected: PASS.

Run: `npm run build`

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/js/app.js resources/views/components/publico/barra-tema.blade.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
El store de tema acepta system y distingue la preferencia de lo pintado

preferencia guarda lo que el usuario eligio, incluido system; resuelto
guarda lo que esta pintado y es lo que un icono sol/luna tiene que mirar.
El head ya entendia system y Filament ya lo escribe: solo faltaba que el
store dejara de colapsarlo. La barra lateral del movil marca activo por lo
resuelto, que es lo que hacia de hecho antes.
EOF
```

---

### Task 3: Componente de bandera

**Files:**
- Create: `resources/views/components/publico/bandera.blade.php`
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Produces: `<x-publico.bandera pais="co" />` y `pais="us"`; SVG de 20×14 con `data-pais`. Lo consume la tarea 6.

- [ ] **Step 1: Escribir la prueba**

```php
    /**
     * Rotura: cambiar `data-pais="co"` por `data-pais="es"`.
     */
    public function test_las_banderas_son_colombia_y_estados_unidos(): void
    {
        $bandera = File::get(resource_path('views/components/publico/bandera.blade.php'));

        $this->assertStringContainsString('data-pais="co"', $bandera);
        $this->assertStringContainsString('data-pais="us"', $bandera);
        $this->assertStringNotContainsString('data-pais="es"', $bandera);
        $this->assertStringNotContainsString('data-pais="gb"', $bandera);

        $colombia = \Illuminate\Support\Facades\Blade::render('<x-publico.bandera pais="co" />');
        $this->assertStringContainsString('<svg', $colombia);
        $this->assertStringContainsString('aria-hidden="true"', $colombia);
        $this->assertStringContainsString('#FCD116', $colombia);
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_las_banderas_son_colombia_y_estados_unidos`
Expected: FAIL — el archivo no existe.

- [ ] **Step 3: Crear el componente**

`resources/views/components/publico/bandera.blade.php`:

```blade
@props(['pais'])

{{--
    Banderas dibujadas a mano. No hay activos en el repositorio, el subconjunto
    de Poppins no trae emoji, y un paquete de banderas tocaría composer.json.
    Colombia en sus tres franjas (amarillo 50 %, azul 25 %, rojo 25 %).
    Estados Unidos simplificada: trece franjas y cantón azul sin estrellas,
    que a 14 px de alto no se resuelven.

    Los colores son los de las banderas, no del tema: van como atributos
    `fill` del SVG y no como clases, a propósito.
--}}
@if ($pais === 'co')
    <svg data-pais="co" viewBox="0 0 20 14" width="20" height="14"
         {{ $attributes->merge(['class' => 'h-3.5 w-5 shrink-0 rounded-sm']) }} aria-hidden="true">
        <rect width="20" height="7" fill="#FCD116"/>
        <rect y="7" width="20" height="3.5" fill="#003893"/>
        <rect y="10.5" width="20" height="3.5" fill="#CE1126"/>
    </svg>
@elseif ($pais === 'us')
    <svg data-pais="us" viewBox="0 0 20 14" width="20" height="14"
         {{ $attributes->merge(['class' => 'h-3.5 w-5 shrink-0 rounded-sm']) }} aria-hidden="true">
        <rect width="20" height="14" fill="#FFFFFF"/>
        @for ($franja = 0; $franja < 13; $franja += 2)
            <rect y="{{ round($franja * 14 / 13, 3) }}" width="20" height="{{ round(14 / 13, 3) }}" fill="#B22234"/>
        @endfor
        <rect width="8" height="7.538" fill="#3C3B6E"/>
    </svg>
@endif
```

- [ ] **Step 4: Verla verde y confirmar**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|TemaClaroOscuroTest"`
Expected: PASS (la guardia de clases de tema barre el archivo nuevo y no encuentra nada prohibido: `fill` es atributo, no clase).

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/bandera.blade.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Dibuja las banderas de Colombia y Estados Unidos para el chip de idioma

SVG inline de 20x14 sin paquete nuevo: Poppins no trae emoji y no hay
activos en el repositorio. Colombia para el espanol y Estados Unidos para
el ingles, que es la lectura natural para el publico del gremio (decision
de Sua, 3 sep).
EOF
```

---

### Task 4: Logo doble e isotipo precargado

**Files:**
- Modify: `resources/views/components/publico/logo.blade.php`
- Modify: `resources/views/components/layouts/publico.blade.php:48`
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Produces: `<x-publico.logo doble alto="h-8" />` pinta `<span class="logo-doble …">` con dos `<img>`: `.logo-doble__completo` y `.logo-doble__isotipo`. Lo consume la tarea 9; las clases las estiliza la tarea 8.

- [ ] **Step 1: Escribir la prueba**

```php
    /**
     * Rotura: quitar el atributo `media` de la precarga del isotipo.
     */
    public function test_el_isotipo_existe_se_pinta_doble_y_se_precarga_solo_en_escritorio(): void
    {
        $this->assertFileExists(public_path('img/monograma-asobares.png'));

        $doble = \Illuminate\Support\Facades\Blade::render('<x-publico.logo doble alto="h-8" />');
        $this->assertStringContainsString('logo-doble__completo', $doble);
        $this->assertStringContainsString('logo-doble__isotipo', $doble);
        $this->assertStringContainsString('img/logo-asobares.png', $doble);
        $this->assertStringContainsString('img/monograma-asobares.png', $doble);
        $this->assertSame(1, substr_count($doble, 'alt="ASOBARES Capítulo Quindío"'), 'la marca se anuncia una sola vez');
        $this->assertStringContainsString('alt=""', $doble);
        $this->assertStringContainsString('width="156" height="108"', $doble);

        $simple = \Illuminate\Support\Facades\Blade::render('<x-publico.logo alto="h-8" />');
        $this->assertStringNotContainsString('logo-doble', $simple, 'sin `doble` el componente rinde lo de siempre');

        $this->get('/contacto')
            ->assertOk()
            ->assertSee('rel="preload" as="image" href="http://localhost:8000/img/monograma-asobares.png" media="(min-width: 64rem)"', false);
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_el_isotipo_existe_se_pinta_doble`
Expected: FAIL — `logo-doble__completo` no está.

- [ ] **Step 3: Ampliar `logo.blade.php`**

Sustituir el archivo entero por:

```blade
@props(['variante' => 'color', 'alto' => 'h-10', 'doble' => false])

{{--
    Logo oficial de ASOBARES Capítulo Quindío, tal cual viene del kit de marca.

    El manual prohíbe reorganizar, deformar o recolorear la marca, así que el
    archivo se usa completo y sin filtros. `blanco` es la única alternativa
    permitida, para fondos rojos o fotografías.

    `doble` pinta el logotipo completo y el isotipo «ab» superpuestos en la
    misma caja: el CSS de la barra de escritorio los cruza según `data-estado`
    (logotipo en reposo, isotipo al hacer scroll). El isotipo es el archivo
    del kit sin recortar ni recolorear; en oscuro va rojo sobre negro, como el
    favicon, porque no existe versión blanca del isotipo.
--}}
{{--
    El archivo de color es PNG y no SVG a propósito, y no es una degradación de
    la marca: `logo-asobares.svg` nunca fue un vector. Era un <svg><image> que
    envolvía este mismo PNG en base64, así que costaba un 34 % más de bytes por
    la codificación, más un análisis de XML y una decodificación de base64
    extra antes de poder pintar. Los píxeles son exactamente los mismos: el PNG
    se extrajo de dentro de aquel archivo, sin recodificar nada.

    Importa porque el logo tiene que estar en el PRIMER pintado. Medido antes
    del cambio, en los dos temas, el <img> llegaba a `pagereveal` y al primer
    rAF con `naturalWidth` 0 y no terminaba hasta `load`: en cada navegación el
    logo se veía desaparecer y volver.
--}}
@php
    $archivo = $variante === 'blanco' ? 'img/logo-asobares-blanco.png' : 'img/logo-asobares.png';
@endphp

@if ($doble)
    <span {{ $attributes->merge(['class' => "logo-doble relative block {$alto}"]) }}>
        <img src="{{ asset($archivo) }}"
             alt="ASOBARES Capítulo Quindío"
             width="592" height="108"
             fetchpriority="high"
             class="logo-doble__completo h-full w-auto">
        {{-- alt vacío: la marca ya la anuncia el logotipo de al lado. --}}
        <img src="{{ asset('img/monograma-asobares.png') }}"
             alt=""
             width="156" height="108"
             class="logo-doble__isotipo absolute inset-y-0 left-0 h-full w-auto">
    </span>
@else
    <img src="{{ asset($archivo) }}"
         alt="ASOBARES Capítulo Quindío"
         width="592" height="108"
         fetchpriority="high"
         {{ $attributes->merge(['class' => "{$alto} w-auto"]) }}>
@endif
```

- [ ] **Step 4: Precargar el isotipo en el `<head>`**

En `resources/views/components/layouts/publico.blade.php`, tras la línea 48 (`<link rel="preload" as="image" href="{{ asset('img/logo-asobares.png') }}" fetchpriority="high">`), añadir:

```blade
    {{-- El isotipo solo lo pinta la barra de escritorio al hacer scroll; se
         precarga solo ahí, o el cruce de logo parpadea la primera vez. --}}
    <link rel="preload" as="image" href="{{ asset('img/monograma-asobares.png') }}" media="(min-width: 64rem)">
```

- [ ] **Step 5: Verla verde y confirmar**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|EscenaPublicaTest|ConfiguracionDeDespliegueTest"`
Expected: PASS.

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/logo.blade.php resources/views/components/layouts/publico.blade.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
El logo aprende a pintarse doble: logotipo e isotipo superpuestos

Para el modulo de marca de la barra en tres estados. El isotipo ab del kit
(156x108) existia en public/img y nadie lo pintaba. Se precarga solo en
escritorio, que es donde se ve; sin precarga el cruce parpadea la primera
vez, defecto ya medido con el logotipo.
EOF
```

---

### Task 5: Control de tema con popover de tres opciones

**Files:**
- Create: `resources/views/components/publico/control-tema.blade.php`
- Modify: `tests/Feature/TemaClaroOscuroTest.php:84-96`
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Consumes: `$store.tema.preferencia`, `$store.tema.resuelto`, `$store.tema.elegir()` (tarea 2); tokens `--asb-escala-popover`, `--asb-desplazamiento-popover`, `--duracion-rebote`, utilidad `ease-rebote-vivo` (tarea 1).
- Produces: `<x-publico.control-tema />` con botón `aria-controls="popover-tema"` y `<div id="popover-tema">`. Lo monta la tarea 9.

- [ ] **Step 1: Escribir las pruebas**

```php
    /**
     * Rotura: poner `computer-desktop` en el botón, o quitar la fila Sistema.
     */
    public function test_el_control_de_tema_muestra_sol_o_luna_y_ofrece_sistema_en_el_popover(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render('<x-publico.control-tema />');

        [$boton, $popover] = explode('id="popover-tema"', $html, 2);

        $this->assertStringContainsString('aria-label="Apariencia del sitio"', $boton);
        $this->assertStringContainsString('aria-controls="popover-tema"', $boton);
        $this->assertStringContainsString("x-show=\"\$store.tema.resuelto === 'light'\"", $boton);
        $this->assertStringContainsString("x-show=\"\$store.tema.resuelto === 'dark'\"", $boton);
        $this->assertStringNotContainsString('computer-desktop', $boton, 'el botón nunca muestra el monitor');
        $this->assertStringNotContainsString('M9 17.25v1.007', $boton, 'ni el path del monitor');

        $this->assertStringContainsString('>Claro<', $popover);
        $this->assertStringContainsString('>Oscuro<', $popover);
        $this->assertStringContainsString('>Sistema<', $popover);
        $this->assertStringContainsString("\$store.tema.elegir('light')", $popover);
        $this->assertStringContainsString("\$store.tema.elegir('dark')", $popover);
        $this->assertStringContainsString("\$store.tema.elegir('system')", $popover);
        $this->assertSame(3, substr_count($popover, 'x-bind:aria-pressed='), 'las tres filas marcan la activa');

        // Lo que las guardias globales exigen a todo desplegable de la barra.
        $this->assertStringContainsString('transicion-desplegable', $html);
        $this->assertStringContainsString('fila-pulsable', $html);
        $this->assertStringContainsString('ease-rebote-vivo duration-(--duracion-rebote)', $html);
        $this->assertStringContainsString('scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)', $html);
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_el_control_de_tema_muestra_sol_o_luna`
Expected: FAIL — el componente no existe.

- [ ] **Step 3: Crear el componente**

`resources/views/components/publico/control-tema.blade.php`:

```blade
{{--
    Control de tema de la barra de escritorio.

    El botón muestra el tema RESUELTO —sol o luna— y nunca el monitor: lo que
    el visitante ve pintado es lo que el icono tiene que decir. El popover de
    debajo ofrece las tres preferencias, Sistema incluida (decisión de Sua del
    3 sep 2026; antes la prueba lo prohibía a propósito).

    Es un «disclosure» como el resto de desplegables de la barra: botón con
    aria-expanded y el panel que controla. Con ratón se asoma al pasar y se
    retira con 280 ms de gracia; con dedo y con teclado, al pulsar.

    `fila-pulsable` y no `pulsable` en las filas, sin ningún hover:bg-*: el
    fondo lo trae el portador detrás de la puerta táctil. `MovimientoTest`
    lee este archivo crudo.
--}}
@php
    $opciones = [
        ['valor' => 'light', 'etiqueta' => 'Claro', 'icono' => 'heroicon-o-sun'],
        ['valor' => 'dark', 'etiqueta' => 'Oscuro', 'icono' => 'heroicon-o-moon'],
        ['valor' => 'system', 'etiqueta' => 'Sistema', 'icono' => 'heroicon-o-computer-desktop'],
    ];
@endphp

<div x-data="{
        abierto: false,
        cierre: null,
        punteroFino() {
            return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        },
        asomar() {
            if (! this.punteroFino()) {
                return;
            }

            clearTimeout(this.cierre);
            this.abierto = true;
        },
        retirar() {
            if (! this.punteroFino()) {
                return;
            }

            clearTimeout(this.cierre);
            this.cierre = setTimeout(() => { this.abierto = false; }, 280);
        },
        cerrarYVolverAlFoco() {
            if (! this.abierto) {
                return;
            }

            this.abierto = false;
            this.$refs.disparador.focus();
        },
     }"
     x-on:mouseenter="asomar()"
     x-on:mouseleave="retirar()"
     x-on:click.outside="abierto = false"
     x-on:keydown.escape.window="cerrarYVolverAlFoco()"
     x-on:focusout="if (! $el.contains($event.relatedTarget)) abierto = false"
     class="relative">

    <button type="button"
            x-ref="disparador"
            x-on:click="abierto = ! abierto"
            x-bind:aria-expanded="abierto ? 'true' : 'false'"
            aria-controls="popover-tema"
            aria-label="Apariencia del sitio"
            class="pulsable flex h-11 w-11 items-center justify-center rounded-full text-suave hover:text-fuerte">
        <x-heroicon-o-sun x-show="$store.tema.resuelto === 'light'" class="h-5 w-5" aria-hidden="true" />
        <x-heroicon-o-moon x-show="$store.tema.resuelto === 'dark'" x-cloak class="h-5 w-5" aria-hidden="true" />
    </button>

    <div id="popover-tema"
         x-show="abierto"
         x-cloak
         x-transition:enter="transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)"
         x-transition:enter-start="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         role="group"
         aria-label="Apariencia del sitio"
         class="hoja-flotante absolute right-0 z-50 mt-2 w-44 origin-top-right rounded-2xl p-2">
        @foreach ($opciones as $opcion)
            <button type="button"
                    x-on:click="$store.tema.elegir('{{ $opcion['valor'] }}'); abierto = false"
                    x-bind:aria-pressed="$store.tema.preferencia === '{{ $opcion['valor'] }}' ? 'true' : 'false'"
                    x-bind:class="$store.tema.preferencia === '{{ $opcion['valor'] }}' ? 'text-acento' : 'text-suave hover:text-fuerte'"
                    class="fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm">
                <x-dynamic-component :component="$opcion['icono']" class="h-4 w-4 shrink-0" aria-hidden="true" />
                <span>{{ $opcion['etiqueta'] }}</span>
                <span x-show="$store.tema.preferencia === '{{ $opcion['valor'] }}'"
                      x-cloak
                      class="ml-auto h-1.5 w-1.5 rounded-full bg-marca-500"
                      aria-hidden="true"></span>
            </button>
        @endforeach
    </div>
</div>
```

- [ ] **Step 4: Verla verde**

Run: `php artisan test --compact --filter=test_el_control_de_tema_muestra_sol_o_luna`
Expected: PASS.

- [ ] **Step 5: Reescribir la prueba que prohibía «Sistema»**

En `tests/Feature/TemaClaroOscuroTest.php`, sustituir el método `test_el_selector_ofrece_solo_claro_y_oscuro` (líneas 84-96) por:

```php
    /**
     * Hasta el 3 sep 2026 esta prueba prohibía «Sistema» a propósito (OBS3-03:
     * el sitio arranca en el tema del dispositivo, y el selector solo ofrecía
     * forzar uno). Sua decidió ese día que el popover de la barra de
     * escritorio ofrezca las tres: el arranque sigue siendo el del
     * dispositivo; lo que cambia es que se puede VOLVER a él tras forzar uno.
     * Anotado en encargo.md §13.
     *
     * Las cadenas '>Claro<' y '>Oscuro<' las emiten dos controles a la vez:
     * la barra lateral (móvil) y el popover (escritorio). '>Sistema<' solo el
     * popover.
     */
    public function test_el_selector_ofrece_claro_oscuro_y_sistema(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('Apariencia del sitio', false)
            ->assertSee('>Claro<', false)
            ->assertSee('>Oscuro<', false)
            ->assertSee('>Sistema<', false)
            ->assertSee("\$store.tema.elegir('light')", false)
            ->assertSee("\$store.tema.elegir('dark')", false)
            ->assertSee("\$store.tema.elegir('system')", false);
    }
```

Esta prueba quedará roja hasta que la tarea 9 monte el control en la barra: es lo esperado y se anota en el commit.

- [ ] **Step 6: Confirmar**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest"`
Expected: PASS. (`TemaClaroOscuroTest::test_el_selector_ofrece_claro_oscuro_y_sistema` está roja a propósito hasta la tarea 9.)

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/control-tema.blade.php tests/Feature/TemaClaroOscuroTest.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Crea el control de tema de escritorio: sol o luna, y popover con Sistema

El boton muestra lo resuelto y nunca el monitor; el popover de debajo
ofrece Claro, Oscuro y Sistema con la activa marcada. La prueba que
prohibia Sistema pasa a exigirlo: decision de Sua del 3 sep, anotada en
su docblock. Queda roja hasta que la barra monte el control (tarea 9).
EOF
```

---

### Task 6: Chip de idioma con popover vertical

**Files:**
- Create: `resources/views/components/publico/control-idioma.blade.php`
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Consumes: `<x-publico.bandera pais="…" />` (tarea 3); tokens y utilidad `ease-rebote-vivo` (tarea 1).
- Produces: `<x-publico.control-idioma />` con botón `aria-controls="popover-idioma"` y `<div id="popover-idioma">`. Lo monta la tarea 9.

- [ ] **Step 1: Escribir la prueba**

```php
    /**
     * Rotura: quitar `disabled` de la fila de English.
     */
    public function test_el_chip_de_idioma_se_ve_y_el_ingles_no_funciona_a_proposito(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render('<x-publico.control-idioma />');

        [$boton, $popover] = explode('id="popover-idioma"', $html, 2);

        $this->assertStringContainsString('>ES<', $boton);
        $this->assertStringContainsString('aria-label="Idioma del sitio"', $boton);
        $this->assertStringContainsString('aria-controls="popover-idioma"', $boton);

        $this->assertStringContainsString('>Español<', $popover);
        $this->assertStringContainsString('>English<', $popover);
        $this->assertStringContainsString('próximamente', $popover);
        $this->assertStringContainsString('data-pais="co"', $popover);
        $this->assertStringContainsString('data-pais="us"', $popover);

        [, $filaIngles] = explode('lang="en"', $popover, 2);
        $filaIngles = strstr($filaIngles, '</button>', true);
        $this->assertStringContainsString('disabled', $popover);
        $this->assertStringContainsString('aria-disabled="true"', $popover);
        $this->assertStringContainsString('>English<', $filaIngles);

        $this->assertStringContainsString('aria-pressed="true"', $popover);
        $this->assertStringContainsString('transicion-desplegable', $html);
        $this->assertStringContainsString('fila-pulsable', $html);
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_el_chip_de_idioma_se_ve`
Expected: FAIL — el componente no existe.

- [ ] **Step 3: Crear el componente**

`resources/views/components/publico/control-idioma.blade.php`:

```blade
{{--
    Chip de idioma de la barra de escritorio.

    Se ve y NO funciona a propósito: el sitio no tiene traducción (no existe
    lang/, cero __() en las vistas, y la tabla de ajustes es monolingüe).
    Traducirlo es otro subsistema con su propia spec y su acta. Este chip es
    su sitio reservado en la interfaz: Español activo, English deshabilitado
    con «próximamente». Cuando exista la traducción, `$idiomas` sale de la
    configuración y `$actual` del locale de la petición.

    Popover VERTICAL, con bandera y nombre del idioma en su propia lengua.
    Mismo disclosure que el control de tema.
--}}
@php
    $idiomas = [
        ['codigo' => 'es', 'siglas' => 'ES', 'nombre' => 'Español', 'pais' => 'co', 'disponible' => true],
        ['codigo' => 'en', 'siglas' => 'EN', 'nombre' => 'English', 'pais' => 'us', 'disponible' => false],
    ];

    $actual = $idiomas[0];
@endphp

<div x-data="{
        abierto: false,
        cierre: null,
        punteroFino() {
            return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        },
        asomar() {
            if (! this.punteroFino()) {
                return;
            }

            clearTimeout(this.cierre);
            this.abierto = true;
        },
        retirar() {
            if (! this.punteroFino()) {
                return;
            }

            clearTimeout(this.cierre);
            this.cierre = setTimeout(() => { this.abierto = false; }, 280);
        },
        cerrarYVolverAlFoco() {
            if (! this.abierto) {
                return;
            }

            this.abierto = false;
            this.$refs.disparador.focus();
        },
     }"
     x-on:mouseenter="asomar()"
     x-on:mouseleave="retirar()"
     x-on:click.outside="abierto = false"
     x-on:keydown.escape.window="cerrarYVolverAlFoco()"
     x-on:focusout="if (! $el.contains($event.relatedTarget)) abierto = false"
     class="relative">

    <button type="button"
            x-ref="disparador"
            x-on:click="abierto = ! abierto"
            x-bind:aria-expanded="abierto ? 'true' : 'false'"
            aria-controls="popover-idioma"
            aria-label="Idioma del sitio"
            class="pulsable flex h-11 min-w-11 items-center justify-center gap-1 rounded-full px-2 text-sm font-medium text-suave hover:text-fuerte">
        <span>{{ $actual['siglas'] }}</span>
        {{-- Galón SVG y no carácter: Poppins subconjuntada no trae el glifo. --}}
        <svg class="transicion-desplegable h-3.5 w-3.5 duration-(--duracion-salida) ease-out"
             x-bind:class="abierto ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
        </svg>
    </button>

    <div id="popover-idioma"
         x-show="abierto"
         x-cloak
         x-transition:enter="transicion-desplegable ease-rebote-vivo duration-(--duracion-rebote)"
         x-transition:enter-start="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transicion-desplegable ease-cajon duration-(--duracion-salida)"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-(--asb-escala-popover) translate-y-(--asb-desplazamiento-popover)"
         role="group"
         aria-label="Idioma del sitio"
         class="hoja-flotante absolute right-0 z-50 mt-2 w-52 origin-top-right rounded-2xl p-2">
        @foreach ($idiomas as $idioma)
            @php($esActual = $idioma['codigo'] === $actual['codigo'])
            <button type="button"
                    lang="{{ $idioma['codigo'] }}"
                    aria-pressed="{{ $esActual ? 'true' : 'false' }}"
                    @if (! $idioma['disponible']) disabled aria-disabled="true" @endif
                    @class([
                        'fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm',
                        'text-acento' => $esActual,
                        'text-suave hover:text-fuerte' => ! $esActual && $idioma['disponible'],
                        'text-apagado' => ! $idioma['disponible'],
                    ])>
                <x-publico.bandera :pais="$idioma['pais']" />
                <span>{{ $idioma['nombre'] }}</span>
                @if ($esActual)
                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-marca-500" aria-hidden="true"></span>
                @elseif (! $idioma['disponible'])
                    <span class="ml-auto text-2xs text-apagado">próximamente</span>
                @endif
            </button>
        @endforeach
    </div>
</div>
```

- [ ] **Step 4: Verla verde y confirmar**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|MovimientoTest|FocoVisibleTest|TemaClaroOscuroTest"`
Expected: PASS salvo `test_el_selector_ofrece_claro_oscuro_y_sistema` (roja a propósito hasta la tarea 9).

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/control-idioma.blade.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Crea el chip de idioma: se ve, y el ingles no funciona a proposito

Popover vertical con bandera y nombre del idioma en su lengua. Espanol
activo; English deshabilitado con proximamente. El sitio no tiene
traduccion y traducirlo es otro subsistema con su acta: este chip es su
sitio reservado, no una promesa.
EOF
```

---

### Task 7: Prefijo de rol en el disparador de cuenta

**Files:**
- Modify: `resources/views/components/publico/menu-usuario.blade.php:8-29` y `:52-66`
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Produces: el disparador de `menu-usuario` muestra avatar + nombre para el asociado, y `Sec. {name}` / `Admin {name}` para el equipo. Conserva `id="menu-cuenta"` y todo lo que las pruebas de rol exigen dentro del panel.

- [ ] **Step 1: Escribir la prueba**

Añadir a `NavbarTresEstadosTest` el `setUp` y un helper de usuarios, más la prueba:

```php
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /**
     * @param  list<string>  $roles
     */
    private function usuarioCon(array $roles, ?\App\Models\Asociado $asociado = null): \App\Models\User
    {
        foreach ($roles as $rol) {
            \Spatie\Permission\Models\Role::findOrCreate($rol, 'web');
        }

        $usuario = \App\Models\User::factory()->create([
            'name' => 'Lola Pantoja',
            'asociado_id' => $asociado?->id,
        ]);
        $usuario->syncRoles($roles);

        return $usuario->fresh();
    }

    /**
     * Rotura: invertir el orden del `match` que resuelve `$prefijoRol`.
     */
    public function test_el_disparador_de_cuenta_lleva_el_prefijo_del_rol(): void
    {
        $asociado = \App\Models\Asociado::query()->firstOrFail();

        $this->actingAs($this->usuarioCon([\App\Models\User::ROL_ASOCIADO], $asociado))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('Lola Pantoja')
            ->assertDontSee('>Sec.<', false)
            ->assertDontSee('>Admin<', false);

        $this->actingAs($this->usuarioCon([\App\Models\User::ROL_SUBADMIN]))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('>Sec.<', false)
            ->assertSee('Secretaría del gremio')
            ->assertDontSee('>Admin<', false);

        $this->actingAs($this->usuarioCon([\App\Models\User::ROL_SUPER_ADMIN]))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('>Admin<', false)
            ->assertSee('Dirección del gremio')
            ->assertDontSee('>Sec.<', false);

        // Con los dos roles gana Admin.
        $this->actingAs($this->usuarioCon([\App\Models\User::ROL_SUPER_ADMIN, \App\Models\User::ROL_SUBADMIN]))
            ->get('/contacto')
            ->assertOk()
            ->assertSee('>Admin<', false)
            ->assertDontSee('>Sec.<', false);
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_el_disparador_de_cuenta_lleva_el_prefijo_del_rol`
Expected: FAIL — `>Sec.<` no está.

- [ ] **Step 3: Añadir el prefijo al `@php` y al disparador**

En `menu-usuario.blade.php`, tras el `$rol = match (true) { … };` (línea 28), añadir:

```php

    /* Lo que va en la barra, corto: el rol largo sigue dentro del panel. */
    $prefijoRol = match (true) {
        (bool) $usuario?->esSuperAdmin() => 'Admin',
        (bool) $usuario?->esSubadmin() => 'Sec.',
        default => null,
    };
```

Sustituir el `<button>` disparador (líneas 52-66) por:

```blade
    <button type="button"
            x-ref="disparador"
            x-on:click="abierto = ! abierto"
            x-bind:aria-expanded="abierto ? 'true' : 'false'"
            aria-controls="menu-cuenta"
            {{-- Padding negativo óptico: `p-1` lleva el botón a 44x44 y `-m-1`
                 devuelve al flujo los 36x36 del avatar, que es marca y no se
                 puede agrandar. El nombre al lado es escritorio: en móvil el
                 panel ya lo escribe. --}}
            class="pulsable -m-1 flex items-center gap-2 rounded-full p-1 text-tenue hover:text-tinta">
        <span class="sr-only">Configuración y sesión de {{ $usuario->name }}</span>
        <span aria-hidden="true"
              class="flex h-9 w-9 items-center justify-center rounded-full bg-marca-500 text-xs font-bold tracking-wide text-white">
            {{ $iniciales }}
        </span>
        <span aria-hidden="true" class="hidden max-w-40 truncate pr-1 text-sm font-medium lg:block">
            @if ($prefijoRol)<span class="text-apagado">{{ $prefijoRol }}</span> @endif{{ $usuario->name }}
        </span>
    </button>
```

- [ ] **Step 4: Verla verde y confirmar**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|TemaClaroOscuroTest|FormulariosPublicosTest|ObjetivoTactilTest"`
Expected: PASS salvo `test_el_selector_ofrece_claro_oscuro_y_sistema` (roja a propósito hasta la tarea 9).

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/menu-usuario.blade.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
El disparador de cuenta lleva el nombre y el prefijo del rol

Sec. para la secretaria, Admin para la direccion, nada para el asociado;
con los dos roles gana Admin. Las etiquetas largas siguen dentro del
panel, que es donde las pruebas las exigen.
EOF
```

---

### Task 8: CSS de los tres estados

**Files:**
- Modify: `resources/css/app.css` (sustituir desde `.cromo-bandeja {` en la línea 421 hasta el cierre del `@media (min-width: 64rem)` en la línea 562, conservando `.cromo`, `.cromo::before`, `.cromo-apoyado`, `.cromo-oculto` y `.nav-enlace`)
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Consumes: tokens de la tarea 1; `--puntero-x`/`--puntero-y` que escribe `Alpine.data('escena')` (ya existe en `app.js`).
- Produces: clases `bandeja`, `modulo`, `modulo-logo`, `modulo-principal`, `modulo-cuenta`, `control-plegable`, `indicador-mas`, `logo-doble`, `logo-doble__completo`, `logo-doble__isotipo`, y el selector de estado `[data-estado="…"]` sobre el header. Las monta la tarea 9.

- [ ] **Step 1: Escribir las pruebas**

```php
    /**
     * Rotura: escribir `blur(20px)` literal en `.modulo`, o sacar
     * `.modulo::before` de la puerta táctil.
     */
    public function test_el_vidrio_usa_tokens_y_el_brillo_tiene_puerta_tactil(): void
    {
        $css = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('.bandeja {', $css);
        $this->assertStringContainsString('.modulo {', $css);
        $this->assertStringContainsString('.control-plegable {', $css);
        $this->assertStringContainsString('.indicador-mas {', $css);
        $this->assertStringContainsString('.logo-doble__isotipo {', $css);

        // Retiradas: sustituidas por las de arriba.
        $this->assertStringNotContainsString('.cromo-bandeja', $css);
        $this->assertStringNotContainsString('.cromo-compacto', $css);
        $this->assertStringNotContainsString('.cromo-desplegable', $css);

        $modulo = $this->regla($css, '.modulo');
        $this->assertStringContainsString('var(--asb-cromo-desenfoque)', $this->regla($css, '[data-estado="scroll"] .modulo,'));
        $this->assertStringNotContainsString('blur(', $modulo);
        $this->assertStringContainsString('var(--ease-rebote-suave)', $modulo);
        $this->assertStringContainsString('translate var(--duracion-rebote)', $modulo);

        $this->assertMatchesRegularExpression(
            '/@media \(hover: hover\) and \(pointer: fine\) \{\s*\.modulo::before \{[^}]*--puntero-x/',
            $css,
            'el brillo que sigue al puntero va dentro de la puerta táctil'
        );
        $this->assertSame(
            1,
            preg_match_all('/\.modulo::before \{[^}]*--puntero-x/', $css),
            'solo la regla con puerta usa --puntero-x'
        );

        $this->assertMatchesRegularExpression(
            '/@media \(hover: none\) \{\s*\[data-estado="scroll"\] \.indicador-mas \{/',
            $css,
            'el indicador solo aparece con puntero grueso y en scroll'
        );

        $this->assertStringContainsString('[data-estado="scroll"]:not(:focus-within) .control-plegable {', $css);
    }

    /** El cuerpo de la primera regla cuyo selector empieza así. */
    private function regla(string $css, string $selector): string
    {
        $inicio = strpos($css, $selector.' {');
        $this->assertNotFalse($inicio, "no existe la regla {$selector}");
        $fin = strpos($css, '}', $inicio);

        return substr($css, $inicio, $fin - $inicio);
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_el_vidrio_usa_tokens_y_el_brillo_tiene_puerta_tactil`
Expected: FAIL — `.bandeja {` no está.

- [ ] **Step 3: Sustituir las reglas del cromo en `app.css`**

Borrar desde la línea 421 (`.cromo-bandeja {`) hasta la 456 (cierre de `.cromo-bandeja > * { … }`), **conservando** lo que sigue (`.cromo::before`, `.cromo-apoyado`, `.cromo-oculto`, `.nav-enlace*`), y borrar entero el bloque `@media (min-width: 64rem) { … }` de las líneas 509-562. En el sitio del bloque borrado de la 421, pegar:

```css
    /*
     * La barra pública de escritorio en tres estados: inicial, scroll y
     * atención. El header lleva `data-estado` y de ahí sale todo; el DOM es
     * uno solo. La píldora exterior (.bandeja) es vidrio en inicial; en
     * scroll y atención se apaga y cada módulo (.modulo) enciende el suyo.
     * Spec: docs/ingenieria/navbar-tres-estados-diseno.md.
     *
     * Rebote de verdad por `--ease-rebote-*`, que ya traen su respaldo. Todo
     * `translate`/`scale` va con las propiedades individuales: Tailwind 4
     * compila a ellas y una transición que nombre `transform` no las anima.
     */
    .bandeja {
        position: relative;
        isolation: isolate;
        gap: 1rem;
        border: 1px solid transparent;
        background-color: transparent;
        box-shadow: none;
        transition:
            gap var(--duracion-rebote) var(--ease-rebote-suave),
            background-color var(--duracion-entrada) var(--ease-color),
            border-color var(--duracion-entrada) var(--ease-color),
            box-shadow var(--duracion-entrada) var(--ease-color);
    }

    .modulo {
        position: relative;
        isolation: isolate;
        border: 1px solid transparent;
        border-radius: 999px;
        background-color: transparent;
        box-shadow: none;
        translate: 0 0;
        transition:
            background-color var(--duracion-entrada) var(--ease-color),
            border-color var(--duracion-entrada) var(--ease-color),
            box-shadow var(--duracion-entrada) var(--ease-color),
            translate var(--duracion-rebote) var(--ease-rebote-suave);
    }

    /* Brillo especular (::before) y canto de cristal (::after). Apagados en
       inicial, donde el vidrio es de la píldora exterior. */
    .modulo::before,
    .modulo::after {
        content: '';
        pointer-events: none;
        position: absolute;
        inset: 0;
        z-index: 0;
        border-radius: inherit;
        opacity: 0;
        transition: opacity var(--duracion-entrada) var(--ease-color);
    }

    .modulo::before {
        background: radial-gradient(18rem circle at 50% 0%, rgb(255 255 255 / 0.14), transparent 60%);
    }

    .modulo::after {
        box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.18);
    }

    .modulo > * {
        position: relative;
        z-index: 1;
    }

    /* Los dos controles que se pliegan en scroll. */
    .control-plegable {
        max-width: 12rem;
        opacity: 1;
        overflow: hidden;
        white-space: nowrap;
        transition:
            max-width var(--duracion-rebote) var(--ease-rebote-suave),
            margin var(--duracion-rebote) var(--ease-rebote-suave),
            opacity var(--duracion-entrada) var(--ease-color),
            visibility 0s linear 0s;
    }

    /* Tres puntos para el dedo: solo hay puntero grueso, solo en scroll. */
    .indicador-mas {
        visibility: hidden;
        opacity: 0;
        scale: 0.6;
        transition:
            opacity var(--duracion-entrada) var(--ease-color),
            scale var(--duracion-rebote) var(--ease-rebote-vivo),
            visibility 0s linear var(--duracion-entrada);
    }

    /* Logotipo e isotipo superpuestos; el cruce lo decide el estado. */
    .logo-doble {
        max-width: 11rem;
        overflow: hidden;
        transition: max-width var(--duracion-rebote) var(--ease-rebote-suave);
    }

    .logo-doble__completo,
    .logo-doble__isotipo {
        transition:
            opacity var(--duracion-entrada) var(--ease-color),
            scale var(--duracion-rebote) var(--ease-rebote-vivo);
    }

    .logo-doble__isotipo {
        opacity: 0;
        scale: var(--asb-escala-isotipo);
    }
```

En el sitio del bloque `@media (min-width: 64rem)` borrado (tras `.nav-enlace[aria-current='page']::after { … }`), pegar:

```css
    @media (min-width: 64rem) {
        .cromo {
            min-height: 4.35rem;
            padding-block: 0.4rem 0;
        }

        .bandeja {
            width: min(calc(100% - 2rem), 80rem);
            gap: 0;
            border-radius: 999px;
        }

        /* --- inicial: una sola píldora de vidrio --- */
        [data-estado="inicial"] .bandeja {
            border-color: var(--asb-linea);
            background-color: var(--asb-cromo-velo);
            -webkit-backdrop-filter: var(--asb-cromo-desenfoque);
            backdrop-filter: var(--asb-cromo-desenfoque);
            box-shadow:
                var(--asb-cromo-apoyo),
                inset 0 1px 0 rgb(255 255 255 / 0.26),
                0 20px 70px rgb(238 65 55 / 0.08);
        }

        /* --- scroll y atención: tres módulos, cada uno con su vidrio --- */
        [data-estado="scroll"] .bandeja,
        [data-estado="atencion"] .bandeja {
            gap: var(--asb-separacion-modulos);
        }

        [data-estado="scroll"] .modulo,
        [data-estado="atencion"] .modulo {
            border-color: var(--asb-linea);
            background-color: var(--asb-cromo-velo);
            -webkit-backdrop-filter: var(--asb-cromo-desenfoque);
            backdrop-filter: var(--asb-cromo-desenfoque);
            box-shadow:
                var(--asb-cromo-apoyo),
                inset 0 1px 0 rgb(255 255 255 / 0.26);
            translate: 0 var(--asb-caida-modulo);
        }

        [data-estado="scroll"] .modulo::before,
        [data-estado="atencion"] .modulo::before,
        [data-estado="scroll"] .modulo::after,
        [data-estado="atencion"] .modulo::after {
            opacity: 1;
        }

        /* Los dos controles plegados, salvo si el teclado está dentro. El
           margen negativo compensa el px-3 del enlace, que es utilidad y gana
           a esta capa: sin él quedarían 24 px de relleno vacío. */
        [data-estado="scroll"]:not(:focus-within) .control-plegable {
            max-width: 0;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition:
                max-width var(--duracion-rebote) var(--ease-rebote-suave),
                margin var(--duracion-rebote) var(--ease-rebote-suave),
                opacity var(--duracion-salida) var(--ease-color),
                visibility 0s linear var(--duracion-rebote);
        }

        [data-estado="scroll"]:not(:focus-within) a.control-plegable {
            margin-inline: -0.75rem;
        }

        /* --- el cruce del logo --- */
        [data-estado="scroll"] .logo-doble,
        [data-estado="atencion"] .logo-doble {
            max-width: 2.9rem;
        }

        [data-estado="scroll"] .logo-doble__completo,
        [data-estado="atencion"] .logo-doble__completo {
            opacity: 0;
            scale: var(--asb-escala-isotipo);
        }

        [data-estado="scroll"] .logo-doble__isotipo,
        [data-estado="atencion"] .logo-doble__isotipo {
            opacity: 1;
            scale: 1;
        }
    }

    @media (hover: none) {
        [data-estado="scroll"] .indicador-mas {
            visibility: visible;
            opacity: 1;
            scale: 1;
            transition:
                opacity var(--duracion-entrada) var(--ease-color),
                scale var(--duracion-rebote) var(--ease-rebote-vivo),
                visibility 0s linear 0s;
        }
    }

    @media (hover: hover) and (pointer: fine) {
        .modulo::before {
            background: radial-gradient(
                18rem circle at calc(var(--puntero-x, 50) * 1%) calc(var(--puntero-y, 0) * 1%),
                rgb(255 255 255 / 0.14),
                transparent 60%
            );
        }
    }
```

- [ ] **Step 4: Verla verde**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|MovimientoTest|EscenaPublicaTest"`
Expected: PASS.

- [ ] **Step 5: Compilar y confirmar**

Run: `npm run build`

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/css/app.css tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Escribe el CSS de la barra en tres estados y retira el de la pildora unica

.bandeja es vidrio solo en inicial; en scroll y atencion cada .modulo
enciende el suyo, se separan por gap y bajan con caida tokenizada. Los
dos controles plegables se cierran por max-width con margen que compensa
el relleno utilitario; el teclado dentro los reabre. Brillo que sigue al
puntero solo tras la puerta tactil; indicador de tres puntos solo con
dedo y en scroll. Se van .cromo-bandeja, .cromo-compacto,
.cromo-expandido y .cromo-desplegable, que eran el mecanismo anterior.
EOF
```

La barra queda sin estilo hasta la tarea siguiente: es una rama y nadie la ve; las pruebas no miran píxeles.

---

### Task 9: La barra: tres módulos y máquina de estados

**Files:**
- Modify: `resources/views/components/publico/menu-grupo.blade.php:35-56` (raíz acepta clase externa)
- Modify: `resources/views/components/publico/navbar.blade.php:52-158` (header y bloque de escritorio; el panel móvil, la hamburguesa y el `<noscript>` no se tocan)
- Test: `tests/Feature/NavbarTresEstadosTest.php`

**Interfaces:**
- Consumes: `<x-publico.logo doble />` (4), `<x-publico.control-tema />` (5), `<x-publico.control-idioma />` (6), `<x-publico.menu-usuario />` (7), clases CSS (8), `Alpine.data('escena')` (existe en `app.js`).
- Produces: `<header data-estado="…">` con `x-data` que expone `estado`, `atendiendo`, `sincronizar()`, `punteroFino()`, `atender()`, `soltar()`, `alternarAtencion()`.

- [ ] **Step 1: Escribir las pruebas**

```php
    /**
     * Rotura: quitar `alternarAtencion` del x-data del header.
     */
    public function test_el_header_declara_los_tres_estados(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        $this->assertStringContainsString('x-bind:data-estado="estado"', $navbar);
        foreach (["'inicial'", "'scroll'", "'atencion'"] as $estado) {
            $this->assertStringContainsString($estado, $navbar);
        }
        foreach (['sincronizar()', 'atender()', 'soltar()', 'alternarAtencion()', 'punteroFino()'] as $metodo) {
            $this->assertStringContainsString($metodo, $navbar);
        }

        // Lo que el panel móvil sigue exigiendo, literal.
        $this->assertStringContainsString('x-on:keydown.escape.window="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:click.outside="menuMovil = false"', $navbar);
        $this->assertStringNotContainsString('cromo-compacto', $navbar);
        $this->assertStringNotContainsString('cromo-expandido', $navbar);

        $html = $this->get('/contacto')->assertOk()->getContent();
        $this->assertStringContainsString('data-estado="inicial"', $html, 'el servidor pinta el estado inicial antes de que Alpine arranque');
    }

    /**
     * Rotura: añadir `control-plegable` al enlace de Eventos.
     */
    public function test_los_cinco_controles_siguen_en_un_solo_bloque_y_solo_dos_se_pliegan(): void
    {
        $html = $this->get('/contacto')->assertOk()->getContent();
        preg_match('/<header\b.*?<\/header>/s', $html, $header);

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$header[0]);
        libxml_clear_errors();
        $xpath = new \DOMXPath($dom);

        $this->assertSame(1, $xpath->query('//nav')->length, 'una sola <nav>');
        $this->assertSame(3, $xpath->query('//nav/*[contains(@class, "modulo")]')->length, 'tres módulos, hijos directos de <nav>');

        $principal = $xpath->query('//nav/div[contains(@class, "gap-1")]')->item(0);
        $this->assertNotNull($principal);
        $this->assertStringContainsString('modulo-principal', $principal->getAttribute('class'));

        $plegables = $xpath->query('.//*[contains(@class, "control-plegable")]', $principal);
        $this->assertSame(2, $plegables->length);
        $textos = [];
        foreach ($plegables as $nodo) {
            $textos[] = trim(preg_replace('/\s+/', ' ', $nodo->textContent));
        }
        $this->assertSame(['Abre tu negocio', 'El gremio'], $textos);

        $this->assertSame(1, $xpath->query('//nav/div[contains(@class, "gap-1")]/span[contains(@class, "indicador-mas")]')->length);
        $this->assertSame(1, $xpath->query('//nav/a[contains(@class, "modulo-logo")]//span[contains(@class, "logo-doble")]')->length);
    }

    /**
     * Rotura: mover `<x-publico.control-tema />` debajo de `<div id="menu-movil"`.
     */
    public function test_los_popovers_van_antes_del_panel_movil_y_el_anonimo_ve_lo_suyo(): void
    {
        $html = $this->get('/contacto')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'id="menu-movil"'), strpos($html, 'id="popover-tema"'));
        $this->assertLessThan(strpos($html, 'id="menu-movil"'), strpos($html, 'id="popover-idioma"'));

        preg_match('/<div class="modulo modulo-cuenta.*?<\/div>\s*<\/nav>/s', $html, $cuenta);
        $this->assertNotEmpty($cuenta, 'el módulo de cuenta cierra la <nav>');
        $this->assertStringContainsString('>Mi cuenta<', $cuenta[0]);
        $this->assertStringContainsString('Afíliate', $cuenta[0]);
        $this->assertStringContainsString('id="popover-tema"', $cuenta[0]);
        $this->assertStringContainsString('id="popover-idioma"', $cuenta[0]);
        $this->assertStringNotContainsString('menu-cuenta', $html);
        $this->assertStringNotContainsString('Cerrar sesión', $html);
    }
```

- [ ] **Step 2: Verlas rojas**

Run: `php artisan test --compact --filter="test_el_header_declara_los_tres_estados|test_los_cinco_controles_siguen|test_los_popovers_van_antes"`
Expected: 3 FAIL.

- [ ] **Step 3: `menu-grupo` acepta una clase en su raíz**

En `menu-grupo.blade.php`, sustituir la línea 56 `     class="relative">` por:

```blade
     {{ $attributes->merge(['class' => 'relative']) }}>
```

- [ ] **Step 4: Reescribir el header y el bloque de escritorio de `navbar.blade.php`**

Sustituir desde la línea 47 (`{{-- \`menuMovil\` y no \`abierto\`: …`) hasta la línea 158 (`        @endauth` que cierra el bloque de cuenta de escritorio, justo antes de `        {{-- Móvil --}}`) por:

```blade
{{-- `menuMovil` y no `abierto`: el desplegable de configuración anida su propio
     x-data y dos propiedades con el mismo nombre se pisarían. --}}
{{-- Las tres salidas van en el <header> y no en el panel: el botón que
     alterna vive dentro del header, y si `click.outside` estuviera en el
     panel el clic del botón lo cerraría y lo abriría en el mismo gesto. --}}
{{-- `desplazado` gobierna la separación con el contenido: la barra solo se
     apoya —sombra en claro, filo de luz en oscuro— cuando hay algo pasando por
     debajo. El umbral de 8 px evita que el rebote elástico del scroll en iOS
     la encienda y apague sola en el tope. --}}
{{-- Tres estados de escritorio, resueltos aquí y pintados por CSS desde
     `data-estado`: `inicial` en el tope, `scroll` al bajar, `atencion` cuando
     el usuario pide la barra entera (ratón encima, toque en el módulo
     principal, o teclado dentro, que lo resuelve CSS con :focus-within).
     Spec: docs/ingenieria/navbar-tres-estados-diseno.md. --}}
<header x-data="{
            menuMovil: false,
            desplazado: false,
            atendiendo: false,
            cierre: null,
            scrollAlAtender: 0,
            get estado() {
                if (! this.desplazado) {
                    return 'inicial';
                }

                return this.atendiendo ? 'atencion' : 'scroll';
            },
            punteroFino() {
                return window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            },
            sincronizar() {
                const actual = Math.max(window.scrollY, 0);

                this.desplazado = actual > 8;

                // Con dedo, desplazarse es soltar: 24 px desde que se abrió.
                if (this.atendiendo && ! this.punteroFino() && Math.abs(actual - this.scrollAlAtender) > 24) {
                    this.atendiendo = false;
                }
            },
            atender() {
                if (! this.punteroFino()) {
                    return;
                }

                clearTimeout(this.cierre);
                this.atendiendo = true;
            },
            soltar() {
                if (! this.punteroFino()) {
                    return;
                }

                clearTimeout(this.cierre);
                this.cierre = setTimeout(() => {
                    this.atendiendo = false;
                }, 280);
            },
            alternarAtencion() {
                if (this.punteroFino()) {
                    return;
                }

                this.atendiendo = ! this.atendiendo;
                this.scrollAlAtender = Math.max(window.scrollY, 0);
            },
        }"
        x-init="sincronizar()"
        x-on:mouseenter="atender()"
        x-on:mouseleave="soltar()"
        x-on:scroll.window.passive="sincronizar()"
        x-on:keydown.escape.window="menuMovil = false"
        x-on:click.outside="menuMovil = false"
        x-on:resize.window="if (window.innerWidth >= 1024) menuMovil = false"
        x-bind:data-estado="estado"
        x-bind:class="{ 'cromo-apoyado': desplazado || menuMovil }"
        data-estado="inicial"
        class="cromo sticky top-0 z-40">
    {{-- La <nav> es la píldora exterior y la escena del brillo: `escena` ya
         existe en app.js y escribe --puntero-x/y; con dedo o con movimiento
         reducido no hace nada, que es lo que se quiere. --}}
    <nav x-data="escena"
         x-on:pointermove="seguir($event)"
         x-on:pointerleave="salir()"
         x-on:keydown.escape.window="atendiendo = false"
         x-bind:style="`--puntero-x: ${px}; --puntero-y: ${py}`"
         class="bandeja mx-auto flex max-w-7xl items-center justify-between px-4 py-2 sm:px-6 lg:px-3"
         aria-label="Navegación principal">

        {{-- Módulo 1: la marca. `-my-1.5 py-1.5` es padding negativo óptico: el
             logo mide 32 px de alto en móvil y el relleno lo lleva a 44,
             mientras el margen negativo devuelve al flujo esos mismos 32. --}}
        <a href="{{ route('inicio') }}"
           class="modulo modulo-logo pulsable -my-1.5 flex shrink-0 items-center py-1.5 lg:px-3"
           aria-label="Inicio — ASOBARES Capítulo Quindío">
            <x-publico.logo doble alto="h-7 sm:h-8" />
        </a>

        {{-- Módulo 2: los cinco controles, siempre los cinco y en este orden.
             Dos de ellos se pliegan en scroll por CSS; nada sale del DOM. Con
             dedo, tocar el módulo alterna el estado de atención. --}}
        <div class="modulo modulo-principal hidden items-center gap-1 px-2 lg:flex"
             x-on:click="alternarAtencion()"
             x-on:click.outside="if (! punteroFino()) atendiendo = false">
            @foreach ($enlacesDirectos as $enlace)
                @php($actual = request()->routeIs($patron($enlace['ruta'])))
                <a href="{{ route($enlace['ruta']) }}"
                   @if ($actual) aria-current="page" @endif
                   @class([
                       'nav-enlace enlace-accion -my-1 rounded-lg px-3 py-3 text-sm',
                       'control-plegable' => $enlace['ruta'] === 'guia.index',
                       'text-acento' => $actual,
                       'text-suave hover:text-fuerte' => ! $actual,
                   ])>
                    {{ $enlace['texto'] }}
                </a>
            @endforeach

            @foreach ($grupos as $grupo)
                <x-publico.menu-grupo :titulo="$grupo['titulo']"
                                      :enlaces="$grupo['enlaces']"
                                      :class="$grupo['titulo'] === 'El gremio' ? 'control-plegable' : ''" />
            @endforeach

            {{-- Solo con dedo y solo en scroll: la señal de que hay más. --}}
            <span class="indicador-mas -my-1 flex items-center rounded-lg px-2 py-3 text-apagado" aria-hidden="true">
                <x-heroicon-o-ellipsis-horizontal class="h-4 w-4" />
            </span>
        </div>

        {{-- Módulo 3: la cuenta, el tema y el idioma. Con sesión abierta el
             atajo vive dentro del desplegable, para no repetir el mismo enlace
             dos veces en la misma barra. --}}
        <div class="modulo modulo-cuenta hidden items-center gap-2 px-2 lg:flex">
            @guest
                <a href="{{ route('mi-cuenta.index') }}"
                   class="nav-enlace enlace-accion -my-1 rounded-lg px-3 py-3 text-sm text-tenue hover:text-fuerte">
                    Mi cuenta
                </a>
            @endguest
            <a href="{{ route('afiliate') }}"
               {{-- ::after y no padding: es la única pastilla pintada de la barra
                    y agrandarla se vería. `-inset-y-1` da 37,7 + 8 = 45,7 px de
                    área pulsable sin tocar el dibujo ni el alto del header.

                    `.pulsable` convive con ese pseudoelemento: el `scale(0.97)`
                    del `:active` encoge también el `::after`. Medido con el
                    ratón abajo: el área efectiva pasa de 46,4 a 45,2 px, o sea
                    que sigue por encima del mínimo, y para entonces el
                    navegador ya fijó el destino del clic en el `pointerdown`.
                    A cambio, la utilidad de fundido de color se fue: pisaba al
                    portador y con ella moría la duración cero de su `:active`.
                    Se nombra y no se pega porque la guardia lee este archivo
                    crudo, comentarios incluidos. --}}
               class="pulsable cta-vivo relative rounded-lg bg-marca-500 px-4 py-1.5 text-sm font-semibold text-white after:absolute after:inset-x-0 after:-inset-y-1 after:content-[''] hover:bg-marca-600">
                Afíliate
            </a>
            @auth
                <x-publico.menu-usuario />
            @endauth
            <x-publico.control-tema />
            <x-publico.control-idioma />
        </div>
```

El `{{-- Móvil --}}` y todo lo que sigue (hamburguesa, `<noscript>`, `<div id="menu-movil">`) se queda exactamente como está. Comprobar que la hamburguesa sigue siendo hija directa de `<nav>` tras el módulo de cuenta.

- [ ] **Step 5: Verlas verdes con todo lo que vigila la barra**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|NavegacionAgrupadaTest|MenuMovilTest|TemaClaroOscuroTest|ObjetivoTactilTest|MovimientoTest|FocoVisibleTest|CalendarioDeEventosTest|FormulariosPublicosTest|EscenaPublicaTest|TransicionesDeVistaTest"`
Expected: PASS, incluida `test_el_selector_ofrece_claro_oscuro_y_sistema`, que llevaba roja desde la tarea 5. Si `ObjetivoTactilTest` falla por una cadena de `navbar.blade.php`, la cadena se ha perdido en la reescritura: restaurarla, no ajustar la prueba.

- [ ] **Step 6: Compilar, mirar y confirmar**

Run: `php artisan view:clear` y `npm run build`.

Abrir el servidor de desarrollo con la herramienta de vista previa (nunca con Bash) y comprobar por JS en la página, a 1440×900: `document.querySelector('header').dataset.estado === 'inicial'`; tras `window.scrollTo(0, 400)` y 700 ms, `'scroll'`; el ancho de `.control-plegable` de «Abre tu negocio» computa `max-width: 0px`; tras `dispatchEvent(new Event('mouseenter'))` en el header, `'atencion'` y `max-width` vuelve a `192px`. Si el panel del navegador no compone fotogramas, medir con transiciones desactivadas (`*{transition:none!important}` inyectado) el valor final, como se hizo el 3 sep.

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/navbar.blade.php resources/views/components/publico/menu-grupo.blade.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Monta la barra de escritorio en tres modulos con maquina de estados

El header calcula inicial, scroll o atencion y lo escribe en data-estado;
la nav es la pildora exterior y la escena del brillo; los tres hijos son
los modulos. Los cinco controles siguen en su div y en su orden: Abre tu
negocio y El gremio llevan control-plegable y el CSS los pliega. Con raton
la atencion es el hover del header; con dedo, tocar el modulo principal;
con teclado, el foco dentro. El panel movil no cambia una letra.
EOF
```

---

### Task 10: La barra lateral solo en móvil; adiós al selector huérfano

**Files:**
- Modify: `resources/views/components/publico/barra-tema.blade.php:21`
- Delete: `resources/views/components/publico/selector-tema.blade.php`
- Modify: `tests/Feature/ObjetivoTactilTest.php:157-161` (borrar la fila `'selector de tema'`)
- Modify: `tests/Feature/TemaClaroOscuroTest.php:98-105`
- Test: `tests/Feature/NavbarTresEstadosTest.php`

- [ ] **Step 1: Escribir la prueba**

```php
    /**
     * Rotura: quitar `lg:hidden` del <aside> de la barra lateral.
     */
    public function test_la_barra_lateral_de_tema_se_queda_solo_en_movil(): void
    {
        $barra = File::get(resource_path('views/components/publico/barra-tema.blade.php'));
        $this->assertStringContainsString('tema-lateral fixed', $barra);
        $this->assertStringContainsString('lg:hidden', $barra);

        $this->assertFileDoesNotExist(resource_path('views/components/publico/selector-tema.blade.php'), 'el selector huérfano se borró');
    }
```

- [ ] **Step 2: Verla roja**

Run: `php artisan test --compact --filter=test_la_barra_lateral_de_tema_se_queda_solo_en_movil`
Expected: FAIL — `lg:hidden` no está.

- [ ] **Step 3: `lg:hidden` en el `<aside>`**

En `barra-tema.blade.php` línea 21, sustituir:

```blade
       class="tema-lateral fixed bottom-4 right-3 z-30 sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2"
```

por:

```blade
       {{-- Solo por debajo de 1024 px: en escritorio el tema vive en la barra
            de navegación (control-tema). Así el móvil no cambia. --}}
       class="tema-lateral fixed bottom-4 right-3 z-30 sm:bottom-auto sm:top-1/2 sm:-translate-y-1/2 lg:hidden"
```

- [ ] **Step 4: Borrar el selector huérfano y su fila**

```bash
GIT_OPTIONAL_LOCKS=0 git rm resources/views/components/publico/selector-tema.blade.php
```

En `tests/Feature/ObjetivoTactilTest.php`, borrar las líneas 157-161:

```php
            'selector de tema' => [
                'components/publico/selector-tema.blade.php',
                'flex min-h-11 flex-1 items-center justify-center rounded-lg px-3',
                'el icono mide 18 px: con py-2 el botón quedaba en 34',
            ],
```

- [ ] **Step 5: Reescribir la prueba de la barra lateral**

En `TemaClaroOscuroTest.php`, sustituir `test_el_control_de_tema_vive_en_una_barra_lateral_fija` (líneas 98-105) por:

```php
    /**
     * Desde el 3 sep 2026 el tema de escritorio vive en la barra de
     * navegación (popover-tema) y la barra lateral se queda solo en móvil.
     */
    public function test_el_control_de_tema_vive_en_la_barra_lateral_en_movil_y_en_la_navbar_en_escritorio(): void
    {
        $respuesta = $this->get('/contacto');

        $respuesta->assertOk()
            ->assertSee('tema-lateral fixed', false)
            ->assertSee('sm:top-1/2', false)
            ->assertSee('lg:hidden', false)
            ->assertSee('id="popover-tema"', false);
    }
```

- [ ] **Step 6: Verla verde y confirmar**

Run: `php artisan test --compact --filter="NavbarTresEstadosTest|TemaClaroOscuroTest|ObjetivoTactilTest"`
Expected: PASS.

Run: `vendor/bin/pint --dirty --format agent`

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/views/components/publico/barra-tema.blade.php tests/Feature/ObjetivoTactilTest.php tests/Feature/TemaClaroOscuroTest.php tests/Feature/NavbarTresEstadosTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
La barra lateral de tema se queda solo en movil y se va el selector huerfano

En escritorio el tema vive en la barra de navegacion desde la tarea
anterior; el aside lateral recibe lg:hidden y el movil sigue identico.
selector-tema.blade.php no lo montaba nadie desde e82edc8 y solo lo
mantenia viva una fila de ObjetivoTactilTest, que se borra con el.
EOF
```

---

### Task 11: Medir los objetivos táctiles nuevos en Chromium

**Files:**
- Modify: `tests/Feature/ObjetivoTactilTest.php` (DataProvider `cadenasMedidas`, tras la fila `'menú de usuario, filas'`)

- [ ] **Step 1: Medir con playwright-cli**

Con el servidor de desarrollo abierto por la herramienta de vista previa en `http://localhost:8123`:

```bash
playwright-cli -s=medir open --browser=chrome http://localhost:8123/contacto
playwright-cli -s=medir resize 1440 900
playwright-cli -s=medir --raw eval "JSON.stringify([...document.querySelectorAll('[aria-controls=popover-tema], [aria-controls=popover-idioma], #popover-tema button, #popover-idioma button, .indicador-mas')].map(e => { const r = e.getBoundingClientRect(); return { que: e.getAttribute('aria-label') || e.textContent.trim().slice(0, 12), ancho: Math.round(r.width * 10) / 10, alto: Math.round(r.height * 10) / 10 }; }))"
playwright-cli -s=medir close
```

Expected: el botón de tema 44×44; el chip de idioma ≥ 44 de alto y ≥ 44 de ancho; cada fila de popover ≥ 44 de alto. Si alguna mide menos, subir `h-11`/`py-3` hasta que llegue **antes** de escribir la fila. Anotar las medidas reales.

- [ ] **Step 2: Añadir las filas medidas**

Tras la fila `'menú de usuario, filas' => [ … ],` añadir:

```php
            // Los controles nuevos de la barra de escritorio (3 sep 2026).
            'navbar, control de tema' => [
                'components/publico/control-tema.blade.php',
                'flex h-11 w-11 items-center justify-center rounded-full',
                'el icono mide 20 px: h-11 w-11 dan 44x44 medidos',
            ],
            'navbar, chip de idioma' => [
                'components/publico/control-idioma.blade.php',
                'flex h-11 min-w-11 items-center justify-center gap-1 rounded-full px-2',
                'dos letras y un galón: h-11 da 44 de alto y min-w-11 asegura 44 de ancho',
            ],
            'navbar, filas de los popovers' => [
                'components/publico/control-tema.blade.php',
                'fila-pulsable flex w-full items-center gap-2.5 rounded-lg px-3 py-3 text-left text-sm',
                'misma geometría que las filas del menú de usuario: 45,7 px medidos',
            ],
```

- [ ] **Step 3: Romper a propósito y ver rojo**

Cambiar `h-11 w-11` por `h-10 w-10` en `control-tema.blade.php`, correr `php artisan test --compact --filter=ObjetivoTactilTest`, ver la fila roja, y restaurar.

- [ ] **Step 4: Verde y confirmar**

Run: `php artisan test --compact --filter=ObjetivoTactilTest`
Expected: PASS.

```bash
GIT_OPTIONAL_LOCKS=0 git add tests/Feature/ObjetivoTactilTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Vigila la geometria de los controles nuevos de la barra, medida en Chromium

Boton de tema, chip de idioma y filas de los popovers: 44 px o mas,
medidos con playwright-cli a 1440x900 antes de escribir cada fila.
EOF
```

---

### Task 12: Encargo, verificación en navegador y cierre de la rama

**Files:**
- Modify: `material/encargo.md` §13 (tabla «Decisiones que rigen», al final)
- No se toca `material/estado.md`: la rama no está en `main` y el estado describe `main`. La sesión que fusione (si se fusiona) lo reescribe.

- [ ] **Step 1: Anotar la decisión en el encargo**

Al final de la tabla del §13 de `material/encargo.md` (tras la fila del 3 sep sobre el video), añadir:

```markdown
| 3 sep 2026 | **El popover de tema de la barra de escritorio ofrece Claro, Oscuro y Sistema.** Matiza OBS3-03 sin revertirlo: el sitio sigue arrancando en el tema del dispositivo; lo que cambia es que el usuario puede volver a él tras forzar uno. Vive en la rama `p1-navbar-alternativa` (opción B para la dirección) y entra en `main` solo si la dirección la elige. `TemaClaroOscuroTest` pasó de prohibir «Sistema» a exigirlo | spec `docs/ingenieria/navbar-tres-estados-diseno.md` §7.2 |
```

- [ ] **Step 2: Suite entera, formato y compilación**

Run: `vendor/bin/pint --dirty --format agent`
Run: `php artisan view:clear` y `npm run build`
Run: `php artisan test --compact` — **sin editar nada mientras corre**.
Expected: 0 fallos. Anotar la cifra medida (casos, pasan, omitidas, aserciones) para el mensaje de cierre; no citarla de memoria.

- [ ] **Step 3: Verificación en navegador con playwright-cli**

Guion en el scratchpad (fuera del repositorio), ejecutado con `playwright-cli run-code --filename=…`, contra el servidor de desarrollo abierto por la herramienta de vista previa. Comprobaciones, cada una con su medida:

1. **Escritorio 1440×900, ratón.** `dataset.estado` es `inicial`; ancho de `.bandeja` 1280 (`min(calc(100% - 2rem), 80rem)` a 1440 px de ancho) y `.modulo` sin fondo (`background-color` computado `rgba(0, 0, 0, 0)`). Tras `scrollTo(0, 400)` + 700 ms: `scroll`; `gap` computado de `.bandeja` = `12px`; `max-width` de `a.control-plegable` = `0px`; `opacity` de `.logo-doble__isotipo` = `1`. Tras `mouseenter` en el header + 700 ms: `atencion`; `max-width` = `192px`. Tras `mouseleave` + 400 ms: `scroll`. Tras `scrollTo(0, 0)` + 700 ms: `inicial`.
2. **Teclado.** Desde `scroll`, `Tab` hasta el logo: `max-width` de `a.control-plegable` = `192px` (por `:focus-within`, sin cambiar `dataset.estado`).
3. **iPad Pro 11 horizontal** (`--device="iPad Pro 11 landscape"`): verificar **dentro de la página** `matchMedia('(pointer: coarse)').matches === true`. En `scroll`, `.indicador-mas` con `visibility: visible`; `touchscreen.tap` sobre el módulo principal → `atencion` y `max-width` = `192px`; tap fuera → `scroll`.
4. **Movimiento reducido** (`run-code` con `page.emulateMedia({ reducedMotion: 'reduce' })`): verificar dentro de la página `matchMedia('(prefers-reduced-motion: reduce)').matches === true`; `getComputedStyle(document.documentElement).getPropertyValue('--asb-caida-modulo').trim() === '0px'` y `--ease-rebote-suave` computa a `cubic-bezier(0.32, 0.72, 0, 1)`.
5. **Transparencia reducida** (`emulateMedia({ reducedTransparency: 'reduce' })` — Playwright acepta la señal y a veces no la aplica: verificar dentro de la página `matchMedia('(prefers-reduced-transparency: reduce)').matches`; si es `false`, anotarlo como no verificable aquí y no darlo por probado): en `scroll`, `backdrop-filter` de `.modulo` computa `none`.
6. **Popovers.** `mouseenter` sobre el botón de tema → `#popover-tema` visible con tres botones y uno con `aria-pressed="true"`; clic en «Sistema» → `localStorage.theme === 'system'` y el botón de la barra sigue mostrando sol o luna según `documentElement.classList.contains('dark')`. `mouseenter` sobre el chip → `#popover-idioma` visible y el botón de English `disabled`.

Grabar además tres vídeos con `page.screencast` (escritorio, iPad horizontal, teclado) en el scratchpad y convertirlos a `.mp4` con ffmpeg (`-c:v libx264 -pix_fmt yuv420p -movflags +faststart -an`) para ponerlos al lado de los de la opción A.

- [ ] **Step 4: Confirmar y publicar la rama**

```bash
GIT_OPTIONAL_LOCKS=0 git add material/encargo.md
GIT_OPTIONAL_LOCKS=0 git commit -F - <<'EOF'
Anota en el encargo que el popover de tema ofrece Sistema

Matiza OBS3-03 sin revertirlo: el arranque sigue siendo el del
dispositivo; lo que cambia es que se puede volver a el tras forzar uno.
Solo rige en la rama p1-navbar-alternativa hasta que la direccion elija.
EOF
GIT_OPTIONAL_LOCKS=0 git push -u origin p1-navbar-alternativa
```

No se fusiona a `main`. No se despliega.

---

## Self-review

**Cobertura de la spec.** §1 qué es y qué no (móvil intacto: T10 `lg:hidden` y T9 no toca el panel) ✓ · §2 D1 (T6) D2 (T9 `alternarAtencion`, T8 `.indicador-mas`) D3 (T9/T10) D4 (T9 `get estado()`) D5 (T1 `linear()`) D6 (T4/T8/T9) D7 (T1) D8 (T3) ✓ · §3 restricciones en Global Constraints ✓ · §4.1 DOM (T9) y §4.2 Alpine (T9) ✓ · §5.1 curvas + `@supports` (T1) · §5.2 tokens (T1) · §5.3 reducido (T1) · §5.4 qué transiciona (T8) · §5.5 brillo y lente (T8) ✓ · §6.1 logo (T4/T8/T9) · §6.2 principal (T8/T9) · §6.3 cuenta, tema, idioma, barra lateral (T5/T6/T7/T9/T10) ✓ · §7 store (T2) y decisión (T5/T12) ✓ · §8 archivos: todos aparecen en alguna tarea ✓ · §9.1 quince pruebas → repartidas: rebote token (T1), respaldo (T1), vidrio/brillo/indicador/plegable (T8), store (T2), sol-luna-sistema (T5), idioma (T6), banderas (T3), rol (T7), popovers antes del móvil + anónimo (T9), isotipo y precarga (T4), tres estados y cinco controles (T9), barra lateral (T10) ✓ · §9.2 cuatro actualizaciones (T1 Movimiento, T5 y T10 TemaClaroOscuro, T10/T11 ObjetivoTactil) ✓ · §9.3 navegador (T12) ✓ · §10 fuera de alcance: nada del plan lo toca ✓.

**Marcadores.** Ninguno: cada paso trae el código.

**Consistencia de nombres.** `resuelto` (T2) se lee en T5 y T2/barra-tema ✓ · `popover-tema`/`popover-idioma` (T5/T6) se buscan en T9 y T10 ✓ · `control-plegable` (T8 CSS) se aplica en T9 a `guia.index` y a `El gremio` ✓ · `modulo-principal` con `gap-1` (T9) es lo que busca la prueba de T9 y NavegacionAgrupadaTest ✓ · `logo-doble__completo`/`__isotipo` (T4) se estilizan en T8 y se buscan en T9 ✓ · `alternarAtencion`, `atender`, `soltar`, `sincronizar`, `punteroFino` (T9) coinciden con la prueba de T9 ✓ · `ease-rebote-vivo`, `--duracion-rebote`, `--asb-escala-popover`, `--asb-desplazamiento-popover` (T1) coinciden con las cadenas que exigen las pruebas de T5 y T6 ✓ · el `<x-publico.menu-grupo :class="…">` de T9 requiere el `$attributes->merge` de T9 paso 3 ✓.

---

# Parte II · Navbar 2.1: el móvil en dos módulos — plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Construir en la rama `p1-navbar-movil` la barra pública por debajo de 64rem con dos módulos de vidrio: el superior (marca, tema y cuenta, el mismo DOM que el módulo de cuenta de escritorio) y el inferior (fijo abajo, cinco pestañas con icono y rótulo, dos hojas que suben al tocar), con los estados `inicial` y `scroll` decididos por la dirección del desplazamiento, sin hamburguesa, sin panel en plano y sin barra lateral de tema.

**Architecture:** Un solo `<header>` sin `transform` (el que lo hacía bloque contenedor era de una clase muerta); la `<nav class="bandeja">` de escritorio pasa a ser el módulo superior y gana un `::before` con el vidrio; un segundo `<nav id="menu-movil" class="modulo-inferior lg:hidden">` fijo al viewport pinta las cinco secciones desde los mismos arreglos y usa `menu-grupo` con `variante="pestana"`; Alpine solo calcula `data-estado` (por dirección, con ancla e histéresis) y `data-teclado`, y todo lo visual sale de dos tokens de alto que el estado reasigna. Cada tarea añade primero su guardia, la ve roja, y confirma en un commit de trabajo; al final los commits de código se funden en uno, porque partidos dejan ids duplicados o la suite roja.

**Tech Stack:** Laravel 13 · Blade · Alpine 3 · Tailwind 4 (CSS-first) · blade-heroicons (viene con Filament) · PHPUnit 12 · playwright-cli sobre Chromium real (toques por CDP).

**Spec:** `docs/ingenieria/navbar-tres-estados-diseno.md`, **Parte II** (aprobada por Sua el 6 sep 2026, D-M1 a D-M18 con la recomendación en cada una). El plan argumenta desde ahí; quien ejecute lee los dos. Los bloques de código que la spec ya trae **tal cual** (el `x-data` de §4.2, el marcado del inferior de §6.2, `menu-grupo` y `app.js` de §6.3, el CSS entero de §6.4 y los tokens de §5.2) no se repiten aquí: cada tarea dice qué bloque copiar y qué cambia respecto a él.

## Global Constraints

Copiadas de la Parte II §3 y de la Parte I §3. Cada tarea las hereda.

- Todo lo que se cuenta se cuenta dentro del primer `<header>`: nueve `href`, dos `aria-current="page"`, el `href` de afiliación, el enlace encendido del calendario. El módulo inferior es descendiente del `<header>`.
- Los tres módulos de escritorio son los tres hijos con «modulo» en la clase de la primera `<nav>`; el primer `//nav/div[gap-1]` es el bloque de escritorio. Ningún hijo del segundo `<nav>` lleva «modulo» ni «gap-1» en su clase.
- `.cromo` no lleva `transform`, `filter`, `will-change` ni `contain`. Ningún ancestro de una hoja de vidrio lleva `backdrop-filter`, `filter`, `opacity` menor que 1 ni `view-transition-name`: el vidrio de cada módulo vive en su `::before`.
- Toda duración y curva sale de tokens; toda geometría nueva es `--asb-*` con anulación bajo movimiento reducido; el vidrio es `var(--asb-cromo-velo)` + `var(--asb-cromo-desenfoque)`, nunca `blur()` literal; el tema se cambia solo por `$store.tema.elegir()`; la marca no se recolorea ni se recorta; anónimo no ve `menu-cuenta` ni «Cerrar sesión»; cerrar sesión es `POST` con `@csrf` y `<noscript>`; sin dependencias ni carpetas nuevas.
- Todo el CSS móvil nuevo va en un **segundo** bloque `@media (max-width: 63.999rem)` colocado **después** del bloque `@media (hover: hover) and (pointer: fine)`, dentro de `@layer components`: `regla()` de las guardias toma la primera aparición de cada `selector {`.
- Las utilidades de `@layer utilities` ganan a `@layer components`: los insets laterales van en `.cromo-fijo`, `<html>` pasa a `lg:scroll-pt-24`, y el reancle de la hoja de cuenta es `max-lg:static`.
- Las guardias que leen archivos crudos leen también los comentarios: todo comentario nuevo **nombra y no pega** la cadena prohibida (`.cromo-oculto`, `menuMovil`, `innerWidth`, `clientHeight`, `reposar`, `blur(`, `filter: drop-shadow`, `view-transition-name`, `--duracion-cromo`, `--asb-desplazamiento-panel`).
- La escala tipográfica gobierna: `--text-2xs` es 0.6875rem con `line-height: 1.65`; ninguna clase nueva lleva `leading-*`.
- Cadenas fijadas por pruebas que se conservan letra a letra: en `navbar.blade.php` la `<nav>` (`bandeja mx-auto flex max-w-7xl items-center justify-between px-4 py-2 sm:px-6 lg:grid lg:grid-cols-[1fr_auto_1fr] lg:px-3`), el logo (`modulo modulo-logo pulsable -my-1.5 flex shrink-0 items-center py-1.5 lg:justify-self-start lg:px-3`), el principal (`modulo modulo-principal hidden min-h-11 items-center gap-1 px-2 lg:flex lg:justify-self-center`), «Mi cuenta» (`-my-1 rounded-lg px-3 py-3 text-sm text-tenue`), «Afíliate» (`after:absolute after:inset-x-0 after:-inset-y-1 after:content-['']`), los literales del `x-data` (`get estado() {`, `punteroFino() {`, `sincronizar() {`, `atender() {`, `soltar() {`, `alternarAtencion() {`, `return this.atendiendo ? 'atencion' : 'scroll';`, `this.desplazado = actual > 8;`, `Math.abs(actual - this.scrollAlAtender) > 24`, `}, 280);`, `if (! $event.target.closest('a, button')) alternarAtencion()`), los cableados `x-on:scroll.window.passive="sincronizar()"`, `x-on:mouseenter="atender()"`, `x-on:mouseleave="soltar()"` y `data-estado="inicial"` servido; en `menu-grupo.blade.php` `origin-top-left`, `enter-start="opacity-0 scale-95"`, `leave-end="opacity-0 scale-95"`, `duration-(--duracion-entrada)`, `duration-(--duracion-salida)`, `fila-pulsable block rounded-lg px-3 py-3 text-sm`, `-my-1 inline-flex items-center gap-1 rounded-lg px-3 py-3 text-sm`; en `menu-usuario.blade.php` `-m-1 flex items-center gap-2 rounded-full p-1`, `rounded-lg px-3 py-3 text-sm text-suave`; en `control-tema.blade.php` `flex h-11 w-11 items-center justify-center rounded-full`; los siete cableados del desplegable en las cuatro vistas; y en `app.css` la primera regla `.bandeja {` con `gap var(--duracion-estado)`, `.logo-doble {` con `max-width var(--duracion-estado)`, `.cromo::before {` sin `content: none` fuera del bloque de 64rem, ningún `var(--duracion-rebote)`.
- En vistas Blade están prohibidos `duration-N`, `duration-[…]`, `ease-[…]`, `ease-in` suelto, `transition-all` y cualquier flecha Unicode, también en comentarios; los portadores `pulsable`, `fila-pulsable`, `enlace-accion`, `tarjeta-pulsable` no comparten elemento con utilidades de reloj, y `fila-pulsable` tampoco con `hover:bg-*`.
- Antes de confirmar PHP: `vendor/bin/pint --dirty --format agent`. Si se tocan vistas o CSS: `php artisan view:clear && npm run build`. Git: siempre `GIT_OPTIONAL_LOCKS=0`; mensajes en español; sin `Co-Authored-By`.
- Cada guardia nueva se ve **roja** rompiendo el código a propósito antes de darla por buena (regla 3 del prompt maestro).

---

## Estructura de archivos

| Archivo | Responsabilidad | Tarea |
|---|---|---|
| `resources/css/tokens.css` | Nueve tokens del móvil, el velo calibrado, las anulaciones; fuera `--duracion-cromo` y `--asb-desplazamiento-panel` | 1 |
| `tests/Feature/NavbarMovilTest.php` | **`git mv` desde `MenuMovilTest.php`** y reescritura: todas las guardias de lo nuevo; crece tarea a tarea | 1–7 |
| `tests/Feature/MovimientoTest.php`, `tests/Feature/Panel/ComponentesDelPanelTest.php` | El token de la hoja en vez del del panel; docblocks | 1, 5 |
| `resources/js/app.js` | `posicionDelDocumento()`; `desplegable` cierra al desplazar sin robar el foco | 2 |
| `tests/Feature/NavbarTresEstadosTest.php` | Definiciones y cableados nuevos del desplegable; precarga sin `media`; dos `<nav>`; `menuMovil` fuera; cuenta visible; vidrio en `::before` | 2, 3, 5, 6 |
| `menu-usuario`, `control-tema`, `control-idioma` (`resources/views/components/publico/`) | Tres cableados nuevos; el chip con nombre y rango; el idioma oculto bajo 64rem | 3 |
| `resources/views/components/publico/menu-grupo.blade.php` | Variante `pestana` con icono, hoja hacia arriba y filas de pie; tres cableados | 4 |
| `resources/views/components/publico/navbar.blade.php` | Arreglos con `icono` y `pie`; máquina de estados por dirección; módulo de cuenta visible; fuera hamburguesa y panel; entra el `<nav>` inferior | 5 |
| `layouts/publico.blade.php`, `hero.blade.php`, `footer.blade.php`, `barra-tema.blade.php` | `viewport-fit=cover`, `lg:scroll-pt-24`, `min-h-svh`, precarga sin `media`; `pb-28` en la portada; «Entrar a mi cuenta» en el pie; la barra lateral se borra | 5 |
| `tests/Feature/TemaClaroOscuroTest.php`, `ObjetivoTactilTest.php` | El tema en la navbar en los dos anchos; fuera las filas de la hamburguesa y del panel | 5 |
| `resources/css/app.css` | `.cromo` sin `transform`; apartado con `~`; vidrio de la bandeja en `::before`; el bloque móvil entero; `.hoja-flotante` por token; fuera `.cromo-oculto` y `.tema-lateral*` | 6 |
| `database/seeders/UsuarioSeeder.php` | El usuario demo de secretaría es una persona (D-M15) | 7 |
| `docs/ingenieria/navbar-tres-estados-diseno.md`, `material/encargo.md` §13, `material/estado.md`, `material/bitacora.md`, `docs/ingenieria/matriz-de-pruebas.md` | Notas fechadas de la Parte I; la decisión de producto; cifras medidas; cierre | 11 |

---

### Task 1: Tokens del móvil y la guardia que nace renombrada

**Files:**
- Modify: `resources/css/tokens.css:205` (fuera `--duracion-cromo`), `:215` (fuera `--asb-desplazamiento-panel`), `:270` (tras `--asb-escala-isotipo: 0.9;`), `:350` (`.dark`, tras `--asb-luz-ambiente`), `:360` (antes del bloque de transparencia reducida), `:360-371` y `:373-388` (dentro de los dos bloques de accesibilidad), `:448` (fuera la anulación del panel), `:467` (tras `--asb-escala-isotipo: 1;`), y el comentario de `--duracion-panel` (`:204`)
- Rename: `tests/Feature/MenuMovilTest.php` a `tests/Feature/NavbarMovilTest.php` (con `git mv`)
- Modify: `tests/Feature/MovimientoTest.php:28-34` (comentario), `:46`, `:98`; `tests/Feature/Panel/ComponentesDelPanelTest.php:70`

**Interfaces:**
- Produces: `--asb-alto-modulo-superior`, `--asb-alto-modulo-superior-compacto`, `--asb-alto-modulo-inferior`, `--asb-alto-modulo-inferior-compacto`, `--asb-alto-rotulo-pestana`, `--asb-desplazamiento-hoja`, `--asb-retirada-barra`, `--asb-hoja-velo`, `--asb-cromo-apoyo-inferior` (los consume el CSS de la tarea 6 y las vistas de la 4); el velo móvil de `--asb-cromo-velo` bajo 64rem; la clase `NavbarMovilTest` con sus ayudantes `regla()`, `bloque()`, `cabecera()`, `arbol()`, `usuarioCon()` (los usan las tareas 2 a 7).

- [ ] **Step 1: Renombrar la guardia conservando la historia y dejarle solo el esqueleto**

```bash
GIT_OPTIONAL_LOCKS=0 git mv tests/Feature/MenuMovilTest.php tests/Feature/NavbarMovilTest.php
```

Contenido nuevo del archivo (las tres pruebas viejas se retiran con la decisión D-M11; el docblock cuenta la inversión):

```php
<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * La barra pública por debajo de 64rem: dos módulos de vidrio, el superior
 * con marca, tema y cuenta, y el inferior fijo abajo con las cinco secciones
 * y dos hojas que suben al tocar (Parte II de la spec, aprobada por Sua el
 * 6 sep 2026).
 *
 * Esta clase se llamó `MenuMovilTest` y protegía LO CONTRARIO: un panel en
 * plano bajo la cabecera, sin desplegables anidados, porque «sobra vertical
 * y lo escaso es el número de toques» (772 px de panel medidos en 390x844).
 * Sua decidió el 6 sep (D-M11) invertirlo: los seis destinos plegados pasan
 * de un toque a dos a cambio de una barra siempre visible, sin hamburguesa,
 * con los tres directos y el tema a un toque. El `git mv` conserva la
 * historia de aquella decisión; este docblock conserva su razón para que
 * nadie la deshaga sin saberlo.
 *
 * Cada prueba nombra en su docblock la rotura que la pone roja.
 */
class NavbarMovilTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * @param  list<string>  $roles
     */
    private function usuarioCon(array $roles, ?Asociado $asociado = null): User
    {
        foreach ($roles as $rol) {
            Role::findOrCreate($rol, 'web');
        }

        $usuario = User::factory()->create([
            'name' => 'Lola Pantoja',
            'asociado_id' => $asociado?->id,
        ]);
        $usuario->syncRoles($roles);

        return $usuario->fresh();
    }

    /** El primer `<header>` de la página: donde se cuenta todo. */
    private function cabecera(string $ruta): string
    {
        $html = $this->get($ruta)->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<header\b.*?<\/header>/s', $html, $trozos), "{$ruta} no tiene <header>");

        return $trozos[0];
    }

    private function arbol(string $html): DOMXPath
    {
        $documento = new DOMDocument;
        $anteriores = libxml_use_internal_errors(true);
        $documento->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($anteriores);

        return new DOMXPath($documento);
    }

    /** El cuerpo de la PRIMERA regla cuyo selector empieza así. */
    private function regla(string $css, string $selector): string
    {
        $inicio = strpos($css, $selector.' {');
        $this->assertNotFalse($inicio, "no existe la regla {$selector}");
        $fin = strpos($css, '}', $inicio);

        return substr($css, $inicio, $fin - $inicio);
    }

    /**
     * El bloque n-ésimo que empieza por esa marca, entero y con sus llaves
     * contadas: `strstr` daría desde la primera marca hasta el final del
     * archivo, que para el segundo bloque móvil es justo lo que no sirve.
     */
    private function bloque(string $css, string $marca, int $ordinal = 1): string
    {
        $desde = 0;

        for ($n = 0; $n < $ordinal; $n++) {
            $inicio = strpos($css, $marca, $desde);
            $this->assertNotFalse($inicio, "no existe el bloque {$ordinal} de {$marca}");
            $desde = $inicio + strlen($marca);
        }

        $llave = strpos($css, '{', $inicio);
        $nivel = 0;

        for ($i = $llave, $largo = strlen($css); $i < $largo; $i++) {
            if ($css[$i] === '{') {
                $nivel++;
            } elseif ($css[$i] === '}') {
                $nivel--;

                if ($nivel === 0) {
                    return substr($css, $inicio, $i - $inicio + 1);
                }
            }
        }

        $this->fail("el bloque {$marca} no cierra");
    }
}
```

- [ ] **Step 2: Escribir las dos guardias de tokens**

Dentro de la clase, antes de los ayudantes:

```php
    /**
     * Roturas: borrar `--asb-retirada-barra: 0%` del bloque de movimiento
     * reducido; anular `--asb-alto-modulo-inferior` ahí (es layout, no se
     * anula); devolver `--duracion-cromo`; duplicar `--asb-hoja-velo` en `.dark`.
     */
    public function test_los_tokens_del_movil_y_su_anulacion(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        foreach ([
            '--asb-alto-modulo-superior: 3.5rem;',
            '--asb-alto-modulo-superior-compacto: 3rem;',
            '--asb-alto-modulo-inferior: 4.25rem;',
            '--asb-alto-modulo-inferior-compacto: 3rem;',
            '--asb-alto-rotulo-pestana: calc(2 * 1.65 * 0.6875rem);',
            '--asb-desplazamiento-hoja: 6px;',
            '--asb-retirada-barra: 100%;',
            '--asb-hoja-velo: color-mix(in oklab, var(--asb-superficie) 84%, transparent);',
        ] as $declaracion) {
            $this->assertStringContainsString($declaracion, $tokens);
        }

        // Una sola receta del velo de la hoja fuera de las medias de
        // accesibilidad: `.dark` es el propio <html> y el var() ya resuelve.
        $this->assertSame(1, substr_count($tokens, '--asb-hoja-velo: color-mix('), 'el velo de la hoja se declara una vez');
        $this->assertSame(2, substr_count($tokens, '--asb-cromo-apoyo-inferior:'), 'el apoyo inferior tiene sus dos recetas');

        $reducido = strstr($tokens, '@media (prefers-reduced-motion: reduce)');
        $this->assertNotFalse($reducido);
        $this->assertStringContainsString('--asb-desplazamiento-hoja: 0px;', $reducido);
        $this->assertStringContainsString('--asb-retirada-barra: 0%;', $reducido);
        $this->assertStringNotContainsString('--asb-alto-modulo', $reducido, 'los altos son layout y no se anulan');

        $transparencia = $this->bloque($tokens, '@media (prefers-reduced-transparency: reduce)');
        $this->assertStringContainsString('--asb-hoja-velo: var(--asb-superficie);', $transparencia);

        $contraste = $this->bloque($tokens, '@media (prefers-contrast: more)');
        $this->assertStringContainsString('--asb-cromo-velo: var(--asb-fondo);', $contraste);
        $this->assertStringContainsString('--asb-cromo-desenfoque: none;', $contraste);
        $this->assertStringContainsString('--asb-hoja-velo: var(--asb-superficie);', $contraste);

        // Los dos tokens que se retiran con su único consumidor. Se
        // concatenan para que esta prueba no se delate a sí misma.
        foreach (['--duracion-'.'cromo', '--asb-desplazamiento-'.'panel'] as $muerto) {
            $this->assertStringNotContainsString($muerto, $tokens, "{$muerto} sigue en tokens.css sin consumidor");

            foreach (File::allFiles(base_path('tests')) as $archivo) {
                $this->assertStringNotContainsString($muerto, str_replace("'.'", '', $archivo->getContents()), "{$archivo->getFilename()} sigue afirmando {$muerto}");
            }
        }
    }

    /**
     * Los rótulos de 11 px y el rango del chip van sobre el vidrio, y el
     * único velo del sitio calibrado para texto era el del hero: el cromo al
     * 72 / 62 % da unos 3:1 con una foto detrás. Como `VeloDelHeroTest`, se
     * recalcula desde el archivo contra la imagen más hostil (negro bajo el
     * tema claro, blanco bajo el oscuro), con la composición alfa que pinta
     * el navegador (D-M18).
     *
     * Roturas: bajar el velo móvil claro al 72 %; devolver `text-apagado` al
     * rango del chip.
     */
    public function test_el_velo_del_movil_sostiene_el_rotulo(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));
        $movil = $this->bloque($tokens, '@media (max-width: 63.999rem)');
        $raiz = $this->bloque($tokens, ':root {');
        $oscuro = $this->bloque($tokens, '.dark {');

        foreach ([
            'claro' => [$raiz, $this->bloque($movil, ':root {'), '#000000'],
            'oscuro' => [$oscuro, $this->bloque($movil, ':root.dark {'), '#ffffff'],
        ] as $tema => [$paleta, $velo, $peorImagen]) {
            $this->assertSame(1, preg_match('/--asb-cromo-velo: color-mix\(in oklab, var\(--asb-fondo\) (\d+)%, transparent\);/', $velo, $porcentaje), "el velo móvil {$tema} no reasigna --asb-cromo-velo");
            $alfa = ((int) $porcentaje[1]) / 100;
            $fondo = $this->hex($paleta, '--asb-fondo');
            $compuesto = $this->componer($fondo, $alfa, $peorImagen);

            foreach (['--asb-acento', '--asb-tenue'] as $texto) {
                $contraste = $this->contraste($this->hex($paleta, $texto), $compuesto);
                $this->assertGreaterThanOrEqual(4.5, $contraste, sprintf('%s a 11 px sobre el velo %s al %d %% da %.2f:1 contra %s', $texto, $tema, $porcentaje[1], $contraste, $peorImagen));
            }
        }
    }

    private function hex(string $bloque, string $propiedad): string
    {
        $this->assertSame(1, preg_match('/'.preg_quote($propiedad, '/').': (#[0-9a-f]{6});/', $bloque, $valor), "{$propiedad} no es un hexadecimal en ese bloque");

        return $valor[1];
    }

    /** Composición alfa normal en sRGB, que es como el navegador pinta el velo. */
    private function componer(string $velo, float $alfa, string $imagen): string
    {
        $v = $this->canales($velo);
        $i = $this->canales($imagen);

        return sprintf('#%02x%02x%02x', (int) round($alfa * $v[0] + (1 - $alfa) * $i[0]), (int) round($alfa * $v[1] + (1 - $alfa) * $i[1]), (int) round($alfa * $v[2] + (1 - $alfa) * $i[2]));
    }

    private function contraste(string $a, string $b): float
    {
        $luminancias = [$this->luminancia($a), $this->luminancia($b)];
        rsort($luminancias);

        return ($luminancias[0] + 0.05) / ($luminancias[1] + 0.05);
    }

    private function luminancia(string $hex): float
    {
        $lineal = array_map(static function (int $canal): float {
            $proporcion = $canal / 255;

            return $proporcion <= 0.03928 ? $proporcion / 12.92 : (($proporcion + 0.055) / 1.055) ** 2.4;
        }, $this->canales($hex));

        return 0.2126 * $lineal[0] + 0.7152 * $lineal[1] + 0.0722 * $lineal[2];
    }

    /** @return array{int, int, int} */
    private function canales(string $hex): array
    {
        $limpio = ltrim($hex, '#');

        return [(int) hexdec(substr($limpio, 0, 2)), (int) hexdec(substr($limpio, 2, 2)), (int) hexdec(substr($limpio, 4, 2))];
    }
```

- [ ] **Step 3: Ver las dos rojas**

Run: `php artisan test --compact --filter=NavbarMovilTest`
Expected: las dos fallan («--asb-alto-modulo-superior» no está; «no existe el bloque 1 de @media (max-width: 63.999rem)»).

- [ ] **Step 4: Los tokens**

En `tokens.css`, tras `--asb-escala-isotipo: 0.9;` (línea 270), el bloque de la spec §5.2 con estos valores exactos:

```css
    /*
     * Barra móvil en dos módulos (Parte II de la spec). Los cuatro altos son
     * LAYOUT, como --asb-separacion-modulos: no se anulan bajo movimiento
     * reducido (allí simplemente no existe el estado scroll). El estado
     * `scroll` los cambia reasignando los dos primeros sobre el header.
     */
    --asb-alto-modulo-superior: 3.5rem; /* 56 px: lo que la cabecera mide hoy */
    --asb-alto-modulo-superior-compacto: 3rem; /* 48 px: un control de 44 con 2 px por lado */
    --asb-alto-modulo-inferior: 4.25rem; /* 68 px: icono de 24, 3 de aire y rótulo a dos líneas de la escala */
    --asb-alto-modulo-inferior-compacto: 3rem; /* 48 px: icono solo, pestaña de 44 en flujo */
    --asb-alto-rotulo-pestana: calc(2 * 1.65 * 0.6875rem); /* dos líneas de --text-2xs, sin leading suelto */

    /* Movimiento: la hoja sube desde la barra (el gemelo positivo de
       --asb-desplazamiento-popover) y la barra se retira ante el teclado.
       Los dos se anulan abajo. */
    --asb-desplazamiento-hoja: 6px;
    --asb-retirada-barra: 100%;

    /* Velo de las hojas, una sola receta: `.dark` es el propio <html>, así
       que el var() de --asb-superficie ya resuelve al tema. Hasta hoy
       .hoja-flotante lo cableaba y la transparencia reducida no lo alcanzaba. */
    --asb-hoja-velo: color-mix(in oklab, var(--asb-superficie) 84%, transparent);

    /* Apoyo de un módulo que cuelga del canto INFERIOR: la receta del cromo,
       invertida. Dos recetas, como --asb-cromo-apoyo. */
    --asb-cromo-apoyo-inferior:
        0 -1px 0 rgb(11 9 10 / 0.07),
        0 -8px 24px rgb(11 9 10 / 0.06);
```

En `.dark`, tras `--asb-luz-ambiente: rgb(238 65 55 / 0.18);` (línea 350):

```css

    --asb-cromo-apoyo-inferior:
        0 -1px 0 rgb(255 255 255 / 0.1),
        0 -8px 28px rgb(0 0 0 / 0.55);
```

Antes de `@media (prefers-reduced-transparency: reduce)` (línea 360). El porcentaje se fija con **dos puntos de margen sobre el mínimo que calcula la guardia** (la guardia imprime el contraste; si 88 / 85 no llega a 4,5:1 en alguno, se sube de dos en dos y se anota el valor final en la spec §5.2 y en el cierre):

```css
/*
 * El velo del móvil sostiene texto de 11 px sobre fotos (los rótulos de las
 * pestañas y el rango del chip): mínimo calculado contra la imagen más
 * hostil, no gusto, como --asb-velo-hero. `NavbarMovilTest` lo recalcula
 * leyendo este archivo. El desenfoque no cambia hasta medirlo con el video
 * de la portada corriendo (D-M18).
 */
@media (max-width: 63.999rem) {
    :root {
        --asb-cromo-velo: color-mix(in oklab, var(--asb-fondo) 88%, transparent);
    }

    :root.dark {
        --asb-cromo-velo: color-mix(in oklab, var(--asb-fondo) 85%, transparent);
    }
}

```

Dentro de `@media (prefers-reduced-transparency: reduce)`, tras `--asb-vidrio-desenfoque: none;`:

```css
        --asb-hoja-velo: var(--asb-superficie);
```

Dentro de `@media (prefers-contrast: more)`, en `:root` y en `:root.dark`, al final de cada uno:

```css
        /* Quien pide más contraste no lee 11 px a través de una foto: el
           material se vuelve sólido, como bajo transparencia reducida. */
        --asb-cromo-velo: var(--asb-fondo);
        --asb-cromo-desenfoque: none;
        --asb-hoja-velo: var(--asb-superficie);
```

Dentro de `@media (prefers-reduced-motion: reduce)`, tras `--asb-escala-isotipo: 1;` (línea 467):

```css

        /* Barra móvil: sube la hoja y se retira la barra, movimiento; los
           altos son layout y se quedan. */
        --asb-desplazamiento-hoja: 0px;
        --asb-retirada-barra: 0%;
```

Se borran la línea 205 (`--duracion-cromo`), la 215 (`--asb-desplazamiento-panel: -4%;`) y la 448 (su anulación). El comentario de `--duracion-panel` (línea 204) pasa a `/* capas que aparecen o se retiran: el revelado de la portada, la capa del video y el módulo inferior ante el teclado */`.

- [ ] **Step 5: Las tres guardias que afirmaban el token del panel**

`MovimientoTest.php:46`: `'--asb-desplazamiento-panel: -4%'` pasa a `'--asb-desplazamiento-hoja: 6px'`. `:98`: `'--asb-desplazamiento-panel: 0%'` pasa a `'--asb-desplazamiento-hoja: 0px'`. `ComponentesDelPanelTest.php:70`: igual que `:98`. El comentario de `MovimientoTest.php:28-34` pasa de «tres excepciones con nombre: la apertura de la barra lateral, el asentamiento...» a «dos excepciones con nombre: el asentamiento del resorte de los popovers (spec del 3 sep 2026, D7) y el cambio de estado de la barra, un punto más lento a petición de Sua (5 sep); la barra lateral de tema y su reloj se retiraron el 6 sep con la Parte II».

- [ ] **Step 6: Verde, y rojo a propósito**

Run: `php artisan test --compact tests/Feature/NavbarMovilTest.php tests/Feature/MovimientoTest.php tests/Feature/Panel/ComponentesDelPanelTest.php tests/Feature/NavbarTresEstadosTest.php`
Expected: PASS. Después, tres roturas y sus rojos: borrar `--asb-retirada-barra: 0%;` del bloque reducido (rojo en `test_los_tokens_del_movil_y_su_anulacion`); cambiar `88%` por `72%` en el velo claro (rojo en `test_el_velo_del_movil_sostiene_el_rotulo` con la cifra); devolver `--duracion-cromo: 520ms;` (rojo). Deshacer cada una.

- [ ] **Step 7: Commit de trabajo**

```bash
GIT_OPTIONAL_LOCKS=0 git add resources/css/tokens.css tests/Feature/NavbarMovilTest.php tests/Feature/MovimientoTest.php tests/Feature/Panel/ComponentesDelPanelTest.php
GIT_OPTIONAL_LOCKS=0 git commit -F <mensaje> # "WIP 1: tokens del móvil y NavbarMovilTest nace del git mv"
```

---

### Task 2: El desplegable cierra al desplazar sin robar el foco

**Files:**
- Modify: `resources/js/app.js:76-77` (junto a `punteroFino`), `:106-133` (docblock y `abrir()` del desplegable), `:9-11` (comentario del store)
- Test: `tests/Feature/NavbarTresEstadosTest.php:534-546`, `tests/Feature/NavbarMovilTest.php`

**Interfaces:**
- Produces: `posicionDelDocumento()` (función de módulo), `desplegable.scrollAlAbrir`, `desplegable.cerrarSiSeDesplaza()` (lo cablean las cuatro vistas en la tarea 3).

- [ ] **Step 1: Las guardias**

En `NavbarTresEstadosTest::test_los_desplegables_comparten_componente_y_se_excluyen`, la lista de definiciones (`:537`) gana `'cerrarSiSeDesplaza() {'`, y tras la aserción del `$dispatch` (`:546`):

```php
        // Abrir recuerda dónde estaba el documento, con el mismo clamp que el
        // header: sin él, una hoja abierta con la página al final se cerraba
        // con un tirón de 25 px que no desplazaba nada. Rotura: quitar la línea.
        $this->assertStringContainsString('this.scrollAlAbrir = posicionDelDocumento();', $js);
```

En `NavbarMovilTest`:

```php
    /**
     * Desplazarse ES cerrar (24 px, el umbral con el que el header suelta la
     * atención con dedo), salvo con el foco DENTRO: quien baja con una flecha
     * o AvPág mientras recorre la hoja está usando el teclado, y cerrarle el
     * panel bajo el foco lo tira al body, el defecto que el 5 sep se corrigió
     * para Escape.
     *
     * Roturas: quitar el `contains`; borrar el método dejando el cableado;
     * volver a `window.scrollY` sin clamp en `posicionDelDocumento`.
     */
    public function test_las_hojas_cierran_al_desplazar_sin_robar_el_foco(): void
    {
        $js = File::get(resource_path('js/app.js'));

        $this->assertMatchesRegularExpression(
            '/cerrarSiSeDesplaza\(\) \{\s*if \(! this\.abierto \|\| this\.\$root\.contains\(document\.activeElement\)\) \{\s*return;\s*\}\s*if \(Math\.abs\(posicionDelDocumento\(\) - this\.scrollAlAbrir\) > 24\) \{\s*this\.cerrar\(\);/',
            $js,
            'la hoja cierra a los 24 px de desplazamiento salvo con el foco dentro'
        );
        $this->assertMatchesRegularExpression(
            '/const posicionDelDocumento = \(\) => Math\.min\(Math\.max\(window\.scrollY, 0\), Math\.max\(document\.documentElement\.scrollHeight - window\.innerHeight, 0\)\);/',
            $js,
            'la posición se acota al documento con el alto vigente del viewport, que es con el que el navegador acota scrollY'
        );
    }
```

- [ ] **Step 2: Rojo**

Run: `php artisan test --compact --filter="test_las_hojas_cierran_al_desplazar_sin_robar_el_foco|test_los_desplegables_comparten_componente_y_se_excluyen"`
Expected: las dos rojas.

- [ ] **Step 3: El código**

Tras `const punteroFino = ...` (línea 77):

```js
// Dentro del documento: ni el rebote elástico de iOS por debajo de 0 ni el
// de más allá del final cuentan. El navegador acota scrollY con el alto
// VIGENTE del viewport, que en iOS crece al plegarse la barra de direcciones.
const posicionDelDocumento = () => Math.min(Math.max(window.scrollY, 0), Math.max(document.documentElement.scrollHeight - window.innerHeight, 0));
```

En el desplegable: propiedad `scrollAlAbrir: 0,` tras `cierre: null,`; en `abrir()`, antes del `$dispatch`: `this.scrollAlAbrir = posicionDelDocumento();`; y tras `cerrar()`, el método `cerrarSiSeDesplaza()` de la spec §6.3 con su comentario. El docblock del componente (`:106-117`) deja de decir «de la barra de escritorio» («Desplegable de la barra, en los dos anchos: los dos grupos, la cuenta, el tema y el idioma en escritorio; las dos hojas del módulo inferior, la cuenta y el tema en móvil...») y el comentario del store (`:9-11`) deja de nombrar «el del menú móvil» («los controles de la barra, en escritorio y en móvil»).

- [ ] **Step 4: Verde y roturas**

Run: `php artisan test --compact tests/Feature/NavbarTresEstadosTest.php tests/Feature/NavbarMovilTest.php tests/Feature/NavegacionAgrupadaTest.php`
Expected: PASS. Roturas: quitar `|| this.$root.contains(document.activeElement)` (rojo); borrar `this.scrollAlAbrir = ...` (rojo).

- [ ] **Step 5: Commit de trabajo** (`WIP 2: el desplegable cierra al desplazar sin robar el foco`)

---

### Task 3: Las cuatro vistas del desplegable: tres cableados, el idioma oculto y el chip con nombre y rango

**Files:**
- Modify: `resources/views/components/publico/menu-grupo.blade.php:43-50`, `menu-usuario.blade.php:1-8` (docblock), `:43-72` (raíz y disparador), `control-tema.blade.php:1-20` (docblock) y `:31-38`, `control-idioma.blade.php:1-13` (docblock) y `:24-31`
- Test: `tests/Feature/NavbarTresEstadosTest.php:596-614`, `tests/Feature/NavbarMovilTest.php`

**Interfaces:**
- Consumes: `cerrarSiSeDesplaza()` (tarea 2).
- Produces: el chip de `menu-usuario` con `$rangoCorto` y el renglón de rango `lg:hidden`; la raíz de `control-idioma` con `max-lg:hidden`; la raíz de `menu-usuario` con `max-lg:static`.

- [ ] **Step 1: Las guardias**

`NavbarTresEstadosTest.php:601-609`: la lista de cableados gana tres cadenas:

```php
                'x-on:pointerdown.outside="cerrar()"',
                'x-on:scroll.window.passive="cerrarSiSeDesplaza()"',
                'x-on:pageshow.window="if ($event.persisted) cerrar()"',
```

y el docblock de la prueba (`:526-533`) gana: «Desde el 6 sep (Parte II) los cuatro llevan además el toque fuera por `pointerdown` (Safari no despacha `click` a `document` sobre fondo sin oyente), el cierre a los 24 px de scroll y el cierre al volver del bfcache. Rotura: quitar `x-on:pageshow.window` de control-tema.»

En `NavbarMovilTest`:

```php
    /**
     * Con sesión, «aparecerá su nombre y su rango» (Sua): el chip es el
     * disparador de siempre, con el nombre visible en los dos anchos y un
     * renglón de rango solo en móvil, en `text-tenue` porque 11 px en
     * `text-apagado` sobre el vidrio no llegan a 4,5:1. El nombre accesible
     * empieza por el texto visible (WCAG 2.5.3). Y la hoja se ancla al módulo
     * y no al chip: anclada al chip, a 320 px desborda 4 px por la izquierda.
     *
     * Roturas: devolver `hidden ... lg:block` al nombre; `text-apagado` en el
     * rango; quitar `max-lg:static` de la raíz; poner el rol antes del nombre
     * en el `sr-only`.
     */
    public function test_el_chip_de_cuenta_lleva_nombre_y_rango_en_los_dos_anchos(): void
    {
        $vista = File::get(resource_path('views/components/publico/menu-usuario.blade.php'));

        $this->assertStringContainsString('class="relative min-w-0 max-lg:static"', $vista);
        $this->assertStringContainsString('-m-1 flex items-center gap-2 rounded-full p-1 min-w-0', $vista);
        $this->assertStringNotContainsString('text-2xs text-apagado', $vista);
        $this->assertStringNotContainsString('lg:block', $vista, 'el nombre ya no es solo de escritorio');
        $this->assertStringNotContainsString('leading-', $vista, 'la escala tipográfica gobierna el chip');
        $this->assertStringContainsString("\$rangoCorto = \$rol === 'Establecimiento afiliado' ? 'Afiliado' : \$rol;", $vista);

        $html = $this->actingAs($this->usuarioCon([User::ROL_SUBADMIN]))->get('/contacto')->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<button[^>]*aria-controls="menu-cuenta"[^>]*>(.*?)<\/button>/s', $html, $chip), 'existe el disparador de cuenta');
        $this->assertMatchesRegularExpression('/<span class="sr-only">Sec\. Lola Pantoja, Secretaría del gremio: configuración y sesión<\/span>/', $chip[1]);
        $this->assertMatchesRegularExpression('/<span class="block truncate text-2xs text-tenue lg:hidden">\s*Secretaría del gremio\s*<\/span>/', $chip[1]);
        $this->assertStringContainsString('>Sec.<', $chip[1]);
    }

    /**
     * El chip de idioma se ve y no funciona a propósito (Parte I, D1); en 360
     * px no sobra un solo control. Se esconde bajo 64rem y sigue en el DOM
     * para las guardias. La raíz no fusiona atributos, así que va en el literal.
     *
     * Rotura: quitar `max-lg:hidden`.
     */
    public function test_el_chip_de_idioma_se_esconde_en_movil(): void
    {
        $this->assertStringContainsString('class="relative max-lg:hidden"', File::get(resource_path('views/components/publico/control-idioma.blade.php')));
    }
```

- [ ] **Step 2: Rojo**

Run: `php artisan test --compact --filter="test_el_chip_de_cuenta_lleva_nombre_y_rango_en_los_dos_anchos|test_el_chip_de_idioma_se_esconde_en_movil|test_los_desplegables_comparten_componente_y_se_excluyen"`
Expected: las tres rojas.

- [ ] **Step 3: El código**

En las cuatro vistas, tras `x-on:focusout="if (! $el.contains($event.relatedTarget)) cerrar()"`:

```blade
     x-on:pointerdown.outside="cerrar()"
     x-on:scroll.window.passive="cerrarSiSeDesplaza()"
     x-on:pageshow.window="if ($event.persisted) cerrar()"
```

`control-idioma.blade.php:31`: `class="relative"` pasa a `class="relative max-lg:hidden"`, con un comentario Blade encima: «Oculto bajo 64rem (Parte II, D-M5): no funciona todavía y en 360 px no sobra un control; sigue en el DOM.»

`menu-usuario.blade.php`: en el `@php`, tras `$prefijoRol`: `$rangoCorto = $rol === 'Establecimiento afiliado' ? 'Afiliado' : $rol;` (con el comentario «lo que cabe en el chip: el largo sigue en la hoja»). La raíz pasa a `class="relative min-w-0 max-lg:static"` con el comentario de la spec §6.1 (anclar la hoja al módulo). El botón y su interior, exactamente el bloque de §6.1 de la spec («Nombre y rango con sesión: el chip»). El docblock (`:1-8`) deja de decir «vive en la barra lateral (móvil)» («El tema vive en el control de tema de la barra, en los dos anchos; este menú queda solo para sesión...»). Los docblocks de `control-tema` y `control-idioma` («de la barra de escritorio») pasan a «de la barra, en los dos anchos».

- [ ] **Step 4: Verde y roturas**

Run: `php artisan test --compact tests/Feature/NavbarMovilTest.php tests/Feature/NavbarTresEstadosTest.php tests/Feature/TemaClaroOscuroTest.php tests/Feature/ObjetivoTactilTest.php tests/Feature/MovimientoTest.php`
Expected: PASS (la barra lateral sigue en pie hasta la tarea 5). Roturas: quitar `x-on:pageshow.window` de control-tema (rojo en NavbarTresEstadosTest); `text-apagado` en el rango (rojo).

- [ ] **Step 5: Commit de trabajo** (`WIP 3: tres cableados, el idioma oculto y el chip con rango`)

---

### Task 4: `menu-grupo` con variante `pestana`

**Files:**
- Modify: `resources/views/components/publico/menu-grupo.blade.php` entero (props, `@php`, raíz, botón por variante, hoja por variante, filas de pie)
- Test: `tests/Feature/NavbarMovilTest.php`

**Interfaces:**
- Consumes: `desplegable` (tarea 2), `--asb-desplazamiento-hoja` (tarea 1).
- Produces: `<x-publico.menu-grupo variante="pestana" :titulo :enlaces :icono :pie />` que pinta un `<button class="pestana ...">` con `aria-controls="menu-{slug}-movil"` y una hoja `#menu-{slug}-movil` con clase `hoja-flotante hoja-inferior`; la variante por defecto (`barra`) rinde letra a letra lo de hoy.

- [ ] **Step 1: La guardia**

```php
    /**
     * Un componente, dos pinturas: en escritorio el botón con galón y la hoja
     * hacia abajo; en la pestaña el icono sobre el rótulo y la hoja hacia
     * ARRIBA, con id propio para no pisar `menu-bolsas`. La fila de invitado
     * viaja como prop desde los arreglos y solo se pinta sin sesión.
     *
     * Roturas: quitar `-movil` del id; quitar `aria-current="true"` del botón
     * activo; pintar la fila de pie con sesión; quitar `origin-bottom` de la hoja.
     */
    public function test_la_pestana_de_grupo_abre_una_hoja_hacia_arriba(): void
    {
        $enlaces = "[['ruta' => 'empleo.index', 'texto' => 'Empleo'], ['ruta' => 'artistas.index', 'texto' => 'Artistas']]";
        $pie = "[['ruta' => 'mi-cuenta.entrar', 'texto' => 'Entrar como afiliado', 'solo' => 'guest']]";

        $pestana = Blade::render('<x-publico.menu-grupo variante="pestana" titulo="Bolsas" icono="briefcase" :enlaces="'.$enlaces.'" :pie="'.$pie.'" />');

        $this->assertStringContainsString('aria-controls="menu-bolsas-movil"', $pestana);
        $this->assertStringContainsString('id="menu-bolsas-movil"', $pestana);
        $this->assertStringContainsString('x-ref="disparador"', $pestana);
        $this->assertStringContainsString('x-bind:aria-expanded="abierto ? \'true\' : \'false\'"', $pestana);
        $this->assertStringContainsString('pestana fila-pulsable flex min-h-11 w-full flex-col items-center justify-center rounded-xl px-0.5 text-center text-2xs font-medium', $pestana);
        $this->assertStringContainsString('<span class="pestana__rotulo"><span class="text-balance">Bolsas</span></span>', $pestana);
        $this->assertStringContainsString('hoja-flotante hoja-inferior absolute inset-x-2 bottom-full z-50 mx-auto mb-2 max-w-sm origin-bottom rounded-2xl p-2', $pestana);
        $this->assertStringContainsString('translate-y-(--asb-desplazamiento-hoja)', $pestana);
        $this->assertStringContainsString('ease-rebote-vivo duration-(--duracion-rebote)', $pestana);
        $this->assertStringContainsString('role="group"', $pestana);
        $this->assertStringContainsString('aria-label="Bolsas"', $pestana);
        $this->assertStringContainsString('Entrar como afiliado', $pestana);
        $this->assertStringContainsString(route('mi-cuenta.entrar'), $pestana);
        $this->assertStringNotContainsString('aria-current', $pestana, 'ninguna sección activa: ni el botón ni las filas lo llevan');
        $this->assertStringNotContainsString('origin-top-left', $pestana);
        // El icono: contorno en reposo, y del vendor, no un path a mano.
        $this->assertMatchesRegularExpression('/<button[^>]*aria-controls="menu-bolsas-movil"[^>]*>\s*<svg[^>]*class="h-6 w-6 shrink-0"/s', $pestana);
        foreach (['role="menu"', 'aria-haspopup', 'x-collapse', 'line-clamp', 'leading-'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $pestana);
        }

        // Con sesión la fila de invitado no se pinta.
        $this->actingAs($this->usuarioCon([User::ROL_ASOCIADO], Asociado::query()->firstOrFail()));
        $this->assertStringNotContainsString('Entrar como afiliado', Blade::render('<x-publico.menu-grupo variante="pestana" titulo="Bolsas" icono="briefcase" :enlaces="'.$enlaces.'" :pie="'.$pie.'" />'));

        // Y la variante por defecto rinde lo de siempre.
        $barra = Blade::render('<x-publico.menu-grupo titulo="Bolsas" :enlaces="'.$enlaces.'" />');
        $this->assertStringContainsString('aria-controls="menu-bolsas"', $barra);
        $this->assertStringContainsString('origin-top-left', $barra);
        $this->assertStringNotContainsString('pestana', $barra);
        $this->assertStringNotContainsString('Entrar como afiliado', $barra);
    }
```

- [ ] **Step 2: Rojo** — Run con `--filter=test_la_pestana_de_grupo_abre_una_hoja_hacia_arriba`. Expected: rojo (la variante no existe).

- [ ] **Step 3: El código** — el componente entero de la spec §6.3 («`menu-grupo` con variante `pestana`»), conservando letra a letra el botón de escritorio (`menu-grupo.blade.php:55-75` de hoy), la hoja de escritorio (`:77-90`) y las filas (`:91-107`). Dos cosas que la spec deja implícitas y aquí se fijan: el `aria-current="true"` va en el botón de la pestaña solo si `$grupoActivo`; y el `@class` de la pestaña no lleva `hover:` de ningún tipo. El docblock del componente gana un párrafo: «Desde el 6 sep pinta también la pestaña del módulo inferior (Parte II §6.3): icono sobre rótulo, hoja hacia arriba con id propio, y las filas de pie que llegan desde los arreglos de `navbar.blade.php` (la navegación se declara una vez).»

- [ ] **Step 4: Verde y roturas** — Run: `php artisan test --compact tests/Feature/NavbarMovilTest.php tests/Feature/NavegacionAgrupadaTest.php tests/Feature/MovimientoTest.php tests/Feature/ObjetivoTactilTest.php`. Expected: PASS. Roturas: quitar `-movil` del id (rojo); pintar el pie sin mirar `solo` (rojo con sesión).

- [ ] **Step 5: Commit de trabajo** (`WIP 4: menu-grupo con variante pestana`)

---

### Task 5: La barra: módulo superior compartido, máquina por dirección y módulo inferior

**Files:**
- Modify: `resources/views/components/publico/navbar.blade.php` entero
- Modify: `resources/views/components/layouts/publico.blade.php:2`, `:5`, `:49-51`, `:189`, `:196`
- Modify: `resources/views/components/publico/hero.blade.php:41`, `footer.blade.php:26-34`
- Delete: `resources/views/components/publico/barra-tema.blade.php`
- Test: `tests/Feature/NavbarTresEstadosTest.php:14-17`, `:172-192`, `:371-374`, `:394`, `:418`, `:453-460`, `:505`, `:617-636`; `tests/Feature/TemaClaroOscuroTest.php:14-16`, `:85-123`; `tests/Feature/ObjetivoTactilTest.php:87-89`, `:124-133`; `tests/Feature/MovimientoTest.php:401-423`; `tests/Feature/NavbarMovilTest.php`

**Interfaces:**
- Consumes: `menu-grupo variante="pestana"` (4), el chip (3), `posicionDelDocumento` (solo por nombre: el header lleva su propia `posicion()`).
- Produces: `<header>` con `data-estado` por dirección bajo 64rem y `data-teclado`; `<nav id="menu-movil" class="modulo-inferior lg:hidden" aria-label="Navegación principal">` (el CSS de la tarea 6 lo fija abajo).

- [ ] **Step 1: Las guardias que cambian**

`NavbarTresEstadosTest`:
- `:14-17`: «La barra pública de escritorio en tres estados» pasa a «La barra pública: de escritorio en tres estados (Parte I) y, desde el 6 sep, la del móvil en dos módulos (Parte II)».
- `:170-172`: el nombre pasa a `test_el_isotipo_existe_se_pinta_doble_y_se_precarga_en_los_dos_anchos` y la rotura a «devolver el `media` a la precarga»; `:190-192` pasa a:

```php
        $html = $this->get('/contacto')->assertOk()->getContent();
        $this->assertStringContainsString('rel="preload" as="image" href="http://localhost:8000/img/monograma-asobares.png">', $html, 'el móvil cruza al isotipo en scroll y con sesión: se precarga en los dos anchos');
        $this->assertStringNotContainsString('monograma-asobares.png" media=', $html);
```

- `:371-372`: las dos aserciones de `menuMovil = false` pasan a `$this->assertStringNotContainsString('menuMovil', $navbar, 'el panel murió y con él su nombre');` y el comentario «Lo que el panel móvil sigue exigiendo, literal.» a «El panel móvil se retiró el 6 sep: su nombre no vuelve.»
- `:394`: `$this->assertSame(1, $xpath->query('//nav')->length, 'una sola <nav>');` pasa a:

```php
        // Dos <nav> desde el 6 sep (Parte II, D-M9): la primera es la bandeja,
        // que bajo 64rem solo expone logo y cuenta; la segunda es la navegación
        // real del teléfono. Los nombres dicen la verdad por ancho.
        $this->assertSame(2, $xpath->query('//nav')->length, 'la bandeja y el módulo inferior');
        $this->assertSame('menu-movil', $xpath->query('//header/nav[2]/@id')->item(0)?->nodeValue);
        $this->assertSame('Navegación principal', $xpath->query('//header/nav[2]/@aria-label')->item(0)?->nodeValue);
```

- `:418` (docblock): `<div id="menu-movil"` pasa a `<nav id="menu-movil"`.
- `:453-460`: pasa a:

```php
    /**
     * La barra lateral se retiró el 6 sep (Parte II, D-M6): el tema vive en
     * el módulo superior por control-tema. Rotura: devolver el <aside>.
     */
    public function test_la_barra_lateral_de_tema_se_retiro_con_la_parte_ii(): void
    {
        $this->assertFileDoesNotExist(resource_path('views/components/publico/barra-tema.blade.php'));
        $this->assertStringNotContainsString('barra-tema', File::get(resource_path('views/components/layouts/publico.blade.php')));
        $this->assertFileDoesNotExist(resource_path('views/components/publico/selector-tema.blade.php'), 'el selector huérfano se borró');
    }
```

- `:505`: la cadena de la cuenta pasa a `modulo modulo-cuenta flex min-w-0 items-center gap-2 whitespace-nowrap lg:justify-self-end lg:px-2`.
- `:617-627` (docblock): «Se esconde en la barra de escritorio y en el panel móvil» pasa a «Se esconde en el módulo de cuenta, que desde el 6 sep es el mismo en los dos anchos», y la rotura a «sacar el enlace del `@guest`»; `:632-636`: el `2` pasa a `1` con el mensaje «sin sesión, una vez: el módulo de cuenta es el mismo DOM en los dos anchos».

`TemaClaroOscuroTest`: `:14-16` pasa a «...y el control vive en la barra de navegación, en los dos anchos (desde el 6 sep)»; `:85-95` quita «Las cadenas '>Claro<' y '>Oscuro<' las emiten dos controles a la vez...» y dice «Un solo control las emite: el popover de la barra»; `:110-123` pasa a:

```php
    /**
     * Desde el 6 sep 2026 (Parte II, D-M6 y D-M17) el tema vive en la barra en
     * los dos anchos: la barra lateral de móvil se retiró. Rotura: esconder
     * el botón de tema bajo 64rem con `hidden lg:flex`.
     */
    public function test_el_control_de_tema_vive_en_la_navbar_en_los_dos_anchos(): void
    {
        $html = $this->get('/contacto')->assertOk()->getContent();

        $this->assertStringNotContainsString('tema-lateral', $html);
        $this->assertSame(1, substr_count($html, 'id="popover-tema"'), 'un solo popover de tema en la página');
        $this->assertSame(1, preg_match('/<button[^>]*aria-controls="popover-tema"[^>]*class="([^"]*)"/', $html, $clases));
        $this->assertStringNotContainsString('hidden', $clases[1]);
        $this->assertStringNotContainsString('lg:', $clases[1]);
    }
```

`ObjetivoTactilTest`: se borran las filas `'navbar, hamburguesa'` (`:124-128`) y `'navbar, filas del menú móvil'` (`:129-133`); el docblock `:87-89` pasa a «...y las filas de los desplegables su `hover:bg-superficie-alta`...».

`MovimientoTest`: `:401-404` pasa a «Eran tres (menú móvil, hamburguesa y menú de usuario); con la reagrupación fueron cinco, y desde el 6 sep son el menú de usuario y el componente de grupo, que pinta los dos grupos de escritorio y las dos hojas del móvil; la barra ya no lleva ningún `x-transition` propio»; en `:409-413` sale `'components/publico/navbar.blade.php'`.

`NavbarMovilTest`, seis pruebas nuevas:

```php
    /**
     * Roturas: mover el <nav> a `publico.blade.php` tras <main>; quitar
     * `lg:hidden`; poner `gap-1` en `.pestanas`; quitar el `x-bind:aria-label`
     * de la bandeja.
     */
    public function test_el_modulo_inferior_vive_en_el_header_y_es_el_landmark(): void
    {
        $xpath = $this->arbol($this->cabecera('/contacto'));

        $this->assertSame(1, $xpath->query('//header/nav[@id="menu-movil" and @aria-label="Navegación principal"]')->length);
        $this->assertSame(0, $xpath->query('//nav[1]//*[@id="menu-movil"]')->length, 'el inferior no cuelga de la bandeja');
        $clase = $xpath->query('//header/nav[2]/@class')->item(0)?->nodeValue ?? '';
        $this->assertStringContainsString('lg:hidden', $clase);
        $this->assertStringContainsString('modulo-inferior', $clase);
        $this->assertSame(0, $xpath->query('//header/nav[2]/*[contains(@class, "modulo") or contains(@class, "gap-1")]')->length, 'ningún hijo del inferior se confunde con un módulo de escritorio');

        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));
        $this->assertStringContainsString('x-bind:aria-label="esEscritorio ? \'Navegación principal\' : \'Marca y cuenta\'"', $navbar);
    }

    /**
     * Roturas: quitar Eventos; meter Eventos en la hoja; redeclarar la fila de
     * invitado en el componente; quitar un `'icono'`.
     */
    public function test_los_cinco_destinos_en_orden_y_las_dos_hojas(): void
    {
        $xpath = $this->arbol($this->cabecera('/contacto'));
        $inferior = $xpath->query('//header/nav[@id="menu-movil"]')->item(0);
        $this->assertNotNull($inferior);

        $etiquetas = [];
        foreach ($xpath->query('.//a[contains(@class, "pestana")] | .//button[contains(@class, "pestana")]', $inferior) as $control) {
            $etiquetas[] = trim(preg_replace('/\s+/u', ' ', $control->textContent));
        }
        $this->assertSame(['Directorio', 'Abre tu negocio', 'Eventos', 'Bolsas', 'El gremio'], $etiquetas);

        foreach (['menu-bolsas-movil' => ['Empleo', 'Artistas', 'Proveedores'], 'menu-el-gremio-movil' => ['Quiénes somos', 'Boletín', 'Contacto']] as $hoja => $esperadas) {
            $this->assertSame(1, $xpath->query('.//button[@type="button" and @aria-controls="'.$hoja.'"]', $inferior)->length);
            $filas = [];
            foreach ($xpath->query('//div[@id="'.$hoja.'"]/a') as $fila) {
                $filas[] = trim(preg_replace('/\s+/u', ' ', $fila->textContent));
            }
            $this->assertSame($esperadas, array_slice($filas, 0, 3), "la hoja {$hoja} cambió de contenido");
        }
        $this->assertSame(1, $xpath->query('//div[@id="menu-el-gremio-movil"]/a[@href="'.route('mi-cuenta.entrar').'"]')->length, 'la entrada del afiliado es la última fila de El gremio');

        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));
        $this->assertSame(5, substr_count($navbar, "'icono' => '"), 'los cinco controles llevan icono');
        $this->assertSame(1, substr_count($navbar, "'texto' => 'Entrar como afiliado'"), 'la fila de invitado se declara una vez, con el resto de la navegación');
        $this->assertStringNotContainsString("'El gremio'", File::get(resource_path('views/components/publico/menu-grupo.blade.php')), 'el componente no sabe qué grupo lleva pie');
    }

    /**
     * Roturas: envolver una fila de hoja en un segundo `x-data="desplegable"`;
     * mover una fila fuera de la hoja.
     */
    public function test_los_seis_destinos_plegados_siguen_a_dos_toques_como_maximo(): void
    {
        $xpath = $this->arbol($this->cabecera('/contacto'));

        foreach (['empleo.index', 'artistas.index', 'proveedores.index', 'quienes-somos', 'boletin.index', 'contacto'] as $ruta) {
            $fila = $xpath->query('//header/nav[@id="menu-movil"]//a[@href="'.route($ruta).'"]')->item(0);
            $this->assertNotNull($fila, "el inferior dejó de enlazar a {$ruta}");
            $this->assertSame(1, $xpath->query('count(ancestor::*[@x-data="desplegable"])', $fila), "{$ruta} está anidado en más de una hoja");
            $this->assertMatchesRegularExpression('/^menu-(bolsas|el-gremio)-movil$/', $fila->parentNode->getAttribute('id'), "{$ruta} no es hija directa de una hoja");
        }
    }

    /**
     * Tres capas y sin tocar la regla de conteo: la pestaña directa de la
     * página lleva `aria-current="page"`; la pestaña de grupo cuya sección
     * está activa lleva `aria-current="true"` (el valor genérico que el
     * proyecto ya usa en dos filtros) y el icono sólido; la fila de la hoja
     * lleva el `page`. En /empleo el conjunto anunciado sigue siendo
     * ['Empleo'] y dos, que es lo que NavegacionAgrupadaTest cuenta.
     *
     * Roturas: poner `page` en el botón de grupo; quitar el `true`; pintar el
     * icono de contorno con la sección activa.
     */
    public function test_el_activo_se_anuncia_una_vez_por_ancho_y_el_grupo_por_su_cuenta(): void
    {
        $xpath = $this->arbol($this->cabecera('/empleo'));
        $bolsas = $xpath->query('//header/nav[@id="menu-movil"]//button[@aria-controls="menu-bolsas-movil"]')->item(0);
        $gremio = $xpath->query('//header/nav[@id="menu-movil"]//button[@aria-controls="menu-el-gremio-movil"]')->item(0);
        $this->assertSame('true', $bolsas->getAttribute('aria-current'));
        $this->assertStringContainsString('text-acento', $bolsas->getAttribute('class'));
        $this->assertSame('', $gremio->getAttribute('aria-current'));
        $this->assertStringNotContainsString('text-acento', $gremio->getAttribute('class'));
        $this->assertSame(1, $xpath->query('//div[@id="menu-bolsas-movil"]/a[@aria-current="page"]')->length);

        $xpath = $this->arbol($this->cabecera('/eventos'));
        $eventos = $xpath->query('//header/nav[@id="menu-movil"]//a[@aria-current="page"]')->item(0);
        $this->assertNotNull($eventos);
        $this->assertSame(route('eventos.index'), $eventos->getAttribute('href'));
        $this->assertStringContainsString('text-acento', $eventos->getAttribute('class'));
        $this->assertSame(1, $xpath->query('.//*[local-name()="svg"][@fill="currentColor"]', $eventos)->length, 'la sección activa lleva el icono sólido');
    }

    /**
     * La máquina móvil: por DIRECCIÓN y no por posición, con ancla en el
     * extremo del recorrido (24 px para irse, 12 para volver), extremos del
     * documento como zona muerta, saltos de más de 200 px ignorados, nada
     * bajo movimiento reducido, y la frontera del CSS (64rem) como única
     * frontera. Sin `reposar`: abrir una hoja no cambia el tamaño.
     *
     * Roturas: intercambiar 24 y 12; invertir `recorrido > 24`; borrar la rama
     * de extremo; volver al alto del bloque contenedor inicial en el clamp;
     * borrar la puerta de movimiento; devolver el ancho en píxeles.
     */
    public function test_la_maquina_movil_decide_por_direccion(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        foreach ([
            'compactar() {', 'posicion() {', 'menosMovimiento() {', 'medirTeclado() {', 'campoEnfocado() {', 'esCampo(',
            'esEscritorio', "matchMedia('(min-width: 64rem)')", 'if (! this.esEscritorio) {',
            'dentro <= 8', 'dentro >= tope', 'Math.abs(recorrido) > 200',
            '(this.compacta && recorrido > 0) || (! this.compacta && recorrido < 0)',
            'recorrido > 24', 'recorrido < -12',
            'x-on:focusin.window="$nextTick(() => medirTeclado())"',
            'x-on:focusout.window="if (! esCampo($event.relatedTarget)) teclado = false"',
            'x-bind:data-teclado="teclado ? \'abierto\' : null"',
            'x-bind:class="{ \'cromo-apoyado\': desplazado }"',
            "this.\$dispatch('desplegable-abierto', null);",
        ] as $literal) {
            $this->assertStringContainsString($literal, $navbar, "el x-data del header perdió {$literal}");
        }

        $this->assertMatchesRegularExpression('/posicion\(\) \{[^}]*window\.innerHeight/', $navbar, 'el clamp usa el alto vigente del viewport');
        $this->assertMatchesRegularExpression('/compactar\(\) \{\s*if \(this\.menosMovimiento\(\)\) \{\s*this\.compacta = false;\s*return;\s*\}/', $navbar, 'bajo movimiento reducido no hay estado scroll');
        $this->assertMatchesRegularExpression('/menosMovimiento\(\) \{\s*return document\.documentElement\.classList\.contains\(\'sin-desplazamiento\'\);/', $navbar);
        $this->assertMatchesRegularExpression('/medirTeclado\(\) \{[^}]*altoReferencia/', $navbar, 'el teclado se mide contra el mayor alto visto, no contra el del layout');

        foreach (['menuMovil', 'x-on:resize.window', 'reposar', 'clientHeight', 'innerWidth'] as $muerto) {
            $this->assertStringNotContainsString($muerto, $navbar, "{$muerto} volvió al header");
        }
    }

    /**
     * Roturas: volver a esconder el módulo de cuenta con `hidden lg:flex`;
     * quitar el `@guest` de la fila de invitado; duplicar `control-tema`.
     */
    public function test_el_tema_y_la_cuenta_en_los_dos_anchos(): void
    {
        $html = $this->get('/contacto')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'id="popover-tema"'));
        $this->assertSame(2, substr_count($html, 'aria-label="Apariencia del sitio"'), 'el botón y el role=group del único control');
        $this->assertStringContainsString('Entrar como afiliado', $html);
        $this->assertStringNotContainsString('menu-cuenta', $html);
        $this->assertStringNotContainsString('Cerrar sesión', $html);
        $this->assertStringNotContainsString('modulo-cuenta hidden', File::get(resource_path('views/components/publico/navbar.blade.php')));
        $this->assertStringContainsString(route('mi-cuenta.entrar'), File::get(resource_path('views/components/publico/footer.blade.php')) !== '' ? $html : '', 'el pie enlaza la entrada para todos los anchos');

        $conSesion = $this->actingAs($this->usuarioCon([User::ROL_SUBADMIN]))->get('/contacto')->assertOk()->getContent();
        $this->assertStringNotContainsString('Entrar como afiliado', $conSesion);
        $this->assertStringContainsString('menu-cuenta', $conSesion);
        $this->assertStringContainsString('Cerrar sesión', $conSesion);
    }

    /**
     * Roturas: quitar `viewport-fit=cover`; volver a `scroll-pt-24` sin
     * variante; volver a `min-h-screen`; devolver `pb-20` al hero.
     */
    public function test_el_layout_reserva_la_zona_segura_y_el_aire_del_movil(): void
    {
        $layout = File::get(resource_path('views/components/layouts/publico.blade.php'));
        $this->assertStringContainsString('content="width=device-width, initial-scale=1, viewport-fit=cover"', $layout);
        $this->assertStringContainsString('<html lang="es" class="lg:scroll-pt-24">', $layout);
        $this->assertStringContainsString('<body class="min-h-svh bg-fondo text-tinta antialiased">', $layout);
        $this->assertStringNotContainsString('min-h-screen', $layout);
        $this->assertStringNotContainsString('barra-tema', $layout);

        $hero = File::get(resource_path('views/components/publico/hero.blade.php'));
        $this->assertStringContainsString("'flex min-h-[calc(100svh-1rem)] items-center pb-28 pt-28 sm:pt-32 lg:pb-24 lg:pt-36' => \$portada", $hero);
        $this->assertStringNotContainsString('pb-20', $hero);
    }
```

En la aserción del pie de `test_el_tema_y_la_cuenta_en_los_dos_anchos`, escribirla así de simple (lo de arriba tiene un ternario que sobra): `$this->assertStringContainsString('href="'.route('mi-cuenta.entrar').'"', File::get(resource_path('views/components/publico/footer.blade.php')) === '' ? '' : $html);` no; **la versión correcta** es una sola línea:

```php
        $this->assertMatchesRegularExpression('/<footer.*href="'.preg_quote(route('mi-cuenta.entrar'), '/').'"[^>]*>\s*Entrar a mi cuenta/s', $html, 'el pie enlaza la entrada en todos los anchos y sin JavaScript');
```

- [ ] **Step 2: Rojo** — Run: `php artisan test --compact tests/Feature/NavbarMovilTest.php tests/Feature/NavbarTresEstadosTest.php tests/Feature/TemaClaroOscuroTest.php`. Expected: rojas las seis nuevas y las actualizadas.

- [ ] **Step 3: `navbar.blade.php`**

1. Los arreglos, con `'icono'` en los cinco y `'pie'` en El gremio, exactamente como la spec §6.2 (`building-storefront`, `clipboard-document-check`, `calendar-days`, `briefcase`, `user-group`; `'pie' => [['ruta' => 'mi-cuenta.entrar', 'texto' => 'Entrar como afiliado', 'solo' => 'guest']]`). Fuera `$usuario` y `$esDelEquipo` del `@php` (solo los usaba el panel).
2. Los comentarios Blade de arriba del header (`:46-59`): fuera los de `menuMovil` y las tres salidas; el de los tres estados gana «Bajo 64rem no hay atención: `scroll` lo decide la DIRECCIÓN del desplazamiento con ancla e histéresis (Parte II §4.2), y `data-teclado` retira el módulo inferior ante el teclado virtual».
3. El `<header>` con el `x-data` **de la spec §4.2 entero** (con `atender()`, `soltar()` y `alternarAtencion()` copiados letra a letra de hoy) y estos atributos, en este orden:

```blade
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

El comentario de `posicion()` no puede pegar la palabra que nombra al alto del bloque contenedor inicial (la guardia la prohíbe): «con el alto del bloque contenedor inicial el tope quedaba 80-110 px por encima del real».

4. La `<nav>` gana `x-bind:aria-label="esEscritorio ? 'Navegación principal' : 'Marca y cuenta'"` antes de `aria-label="Navegación principal"` (la cadena de clases no cambia).
5. El logo, con `@class([... , 'min-h-11', 'marca-compacta' => auth()->check()])` (spec §6.1).
6. El módulo de cuenta: `class="modulo modulo-cuenta flex min-w-0 items-center gap-2 whitespace-nowrap lg:justify-self-end lg:px-2"`; «Mi cuenta» de invitado gana `max-lg:hidden` al FINAL de su lista (`... text-tenue hover:text-fuerte max-lg:hidden`).
7. Fuera la hamburguesa (`:219-242`). Fuera el panel entero (`:259-363`). El `<noscript>` (`:248-257`) se queda.
8. Tras el `<noscript>`, el `<nav id="menu-movil">` de la spec §6.2 (tres pestañas directas y los dos `menu-grupo variante="pestana"` con `:pie="$grupo['pie'] ?? []"`).

`publico.blade.php`: `:2` pasa a `<html lang="es" class="lg:scroll-pt-24">`; `:5` a `content="width=device-width, initial-scale=1, viewport-fit=cover"`; `:49-51` la precarga del isotipo pierde el `media` y su comentario pasa a «El isotipo lo pinta la barra al hacer scroll en los dos anchos y con sesión en móvil: se precarga siempre, o el primer cruce parpadea»; `:189` a `<body class="min-h-svh bg-fondo text-tinta antialiased">` con un comentario encima «`svh` y no `screen`: en iOS `100vh` es el viewport grande y las páginas de un párrafo se desplazaban 80-110 px sin contenido, que la máquina por dirección leería como gesto»; fuera `:196` (`<x-publico.barra-tema />`). `git rm resources/views/components/publico/barra-tema.blade.php`. `hero.blade.php:41`: `'flex min-h-[calc(100svh-1rem)] items-center pb-28 pt-28 sm:pt-32 lg:pb-24 lg:pt-36' => $portada,` con un comentario «`pb-28`: 112 px cubren el módulo inferior de 68 más la zona segura de 34 (Parte II §6.4)». `footer.blade.php`, en la columna «El gremio», tras «Afíliate»: `<li><a href="{{ route('mi-cuenta.entrar') }}" class="enlace-accion flex min-h-11 items-center text-suave hover:text-acento">Entrar a mi cuenta</a></li>`.

- [ ] **Step 4: Verde**

Run: `php artisan test --compact tests/Feature/NavbarMovilTest.php tests/Feature/NavbarTresEstadosTest.php tests/Feature/NavegacionAgrupadaTest.php tests/Feature/TemaClaroOscuroTest.php tests/Feature/ObjetivoTactilTest.php tests/Feature/MovimientoTest.php tests/Feature/CalendarioDeEventosTest.php tests/Feature/EscenaPublicaTest.php tests/Feature/TransicionesDeVistaTest.php tests/Feature/TipografiaTest.php tests/Feature/FormulariosPublicosTest.php`
Expected: PASS salvo las guardias de CSS de la tarea 6, que todavía no existen. Si `TipografiaTest` u `ObjetivoTactilTest` cazan algo (un `leading-` o una cadena recortada), se arregla aquí y se anota.

- [ ] **Step 5: Roturas** — quitar Eventos del arreglo (rojo en cinco destinos y en NavegacionAgrupadaTest); poner `aria-current="page"` en el botón de grupo (rojo en el activo y en NavegacionAgrupadaTest); intercambiar 24 y 12 (rojo en la máquina); volver `hidden lg:flex` en la cuenta (rojo en :505 y en el tema); devolver el `media` a la precarga (rojo).

- [ ] **Step 6: Commit de trabajo** (`WIP 5: la barra en dos módulos, sin hamburguesa ni panel ni barra lateral`)

---

### Task 6: El CSS del móvil

**Files:**
- Modify: `resources/css/app.css:409-419` (`.cromo`), `:430-432` (apartado), `:467-482` (primer bloque móvil), `:575-597` (`.cromo::before` y fuera `.cromo-oculto`), `:759-775` (`.hoja-flotante`), `:777-859` (fuera `.tema-lateral*`), tras `:757` (el bloque móvil nuevo), `:1251-1254` (fuera la anulación de la clase de ocultación)
- Test: `tests/Feature/NavbarTresEstadosTest.php:667-688`, `tests/Feature/NavbarMovilTest.php`

**Interfaces:**
- Consumes: los tokens (1), `data-estado`/`data-teclado`/`.marca-compacta`/`.modulo-inferior`/`.pestanas`/`.pestana__rotulo`/`.hoja-inferior` (4, 5).

- [ ] **Step 1: Las guardias**

`NavbarTresEstadosTest::test_el_movil_conserva_el_vidrio_de_la_barra` (`:667-688`): el docblock pasa a «El vidrio del móvil vive en el `::before` de la bandeja desde el 6 sep: un vidrio no es ancestro de otro vidrio (raíz de fondo), y las hojas que cuelgan de la bandeja tienen que desenfocar la página, no el interior de la bandeja. Rotura: devolver el `backdrop-filter` a `.bandeja`.», y el cuerpo a:

```php
        $css = File::get(resource_path('css/app.css'));

        $movil = strstr($css, '@media (max-width: 63.999rem) {');
        $this->assertNotFalse($movil, 'app.css ya no tiene el bloque de vidrio del móvil');
        $vidrio = $this->regla($movil, '.bandeja::before');

        $this->assertStringContainsString('background-color: var(--asb-cromo-velo);', $vidrio);
        $this->assertStringContainsString('-webkit-backdrop-filter: var(--asb-cromo-desenfoque);', $vidrio);
        $this->assertStringContainsString('backdrop-filter: var(--asb-cromo-desenfoque);', $vidrio);
        $this->assertStringContainsString('var(--asb-cromo-apoyo)', $vidrio);

        $bandeja = $this->regla($movil, '.bandeja');
        $this->assertStringNotContainsString('backdrop-filter', $bandeja, 'la bandeja no es raíz de fondo');
        $this->assertStringContainsString('height: var(--asb-alto-modulo-superior);', $bandeja);
```

`NavbarMovilTest`, nueve pruebas:

```php
    /**
     * Roturas: devolver `transform: translateY(0)` a `.cromo`; devolver la
     * clase de ocultación del cromo.
     */
    public function test_el_cromo_ya_no_es_bloque_contenedor(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $cromo = $this->regla($css, '.cromo');

        foreach (['transform', 'filter', 'will-change', 'contain', 'opacity'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $cromo, ".cromo con {$prohibido} es bloque contenedor de todo fixed descendiente");
        }
        $this->assertStringContainsString('isolation: isolate;', $cromo);
        $this->assertStringNotContainsString('.cromo-'.'oculto', $css, 'la clase de ocultación del cromo, que nadie usaba, se retiró con su transform');
        $this->assertStringNotContainsString('.tema-lateral', $css);
        $this->assertStringNotContainsString('view-transition-name', $css, 'un elemento con nombre de transición de vista es raíz de fondo');
    }

    /** Rotura: mover el vidrio del pseudoelemento al módulo; escribir `blur(14px)`. */
    public function test_los_dos_modulos_son_vidrio_por_token_en_su_pseudoelemento(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $vidrio = $this->regla($movil, '.modulo-inferior::before');
        $this->assertStringContainsString('background-color: var(--asb-cromo-velo);', $vidrio);
        $this->assertStringContainsString('-webkit-backdrop-filter: var(--asb-cromo-desenfoque);', $vidrio);
        $this->assertStringContainsString('backdrop-filter: var(--asb-cromo-desenfoque);', $vidrio);
        $this->assertStringContainsString('var(--asb-cromo-apoyo-inferior)', $vidrio);

        $modulo = $this->regla($movil, '.modulo-inferior');
        foreach (['backdrop-filter', 'filter:', 'blur(', 'contain:', 'height'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $modulo);
        }
        $this->assertStringContainsString('position: fixed;', $modulo);
        $this->assertStringContainsString('bottom: 0;', $modulo);
        $this->assertStringContainsString('padding-bottom: env(safe-area-inset-bottom, 0px);', $modulo);
        // El inset de la zona segura no entra en ninguna propiedad transicionada.
        $this->assertSame(1, preg_match('/transition:([^;]*);/s', $modulo, $transicion));
        $this->assertStringNotContainsString('env(', $transicion[1]);
        $this->assertStringNotContainsString('height', $transicion[1]);

        $this->assertStringNotContainsString('blur(', $movil, 'el vidrio del móvil no lleva blur literal');
        $this->assertStringNotContainsString('drop-'.'shadow', $movil, 'las sombras van en box-shadow: una sombra por filtro anula el vidrio de dentro');
    }

    /** Rotura: animar la altura con otro reloj; reasignar un tercer token en el estado. */
    public function test_el_estado_cambia_dos_tokens_y_nada_mas(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $estado = $this->regla($movil, '.cromo[data-estado="scroll"]');
        $this->assertStringContainsString('--asb-alto-modulo-superior: var(--asb-alto-modulo-superior-compacto);', $estado);
        $this->assertStringContainsString('--asb-alto-modulo-inferior: var(--asb-alto-modulo-inferior-compacto);', $estado);
        $this->assertSame(2, substr_count($estado, ';'), 'el estado no hace nada más');

        $this->assertStringContainsString('height: var(--asb-alto-modulo-inferior);', $this->regla($movil, '.pestanas'));
        $this->assertStringContainsString('height var(--duracion-estado) var(--ease-rebote-suave)', $this->regla($movil, '.pestanas'));
        $this->assertStringContainsString('grid-template-rows: 0fr;', $this->regla($movil, '[data-estado="scroll"] .pestana__rotulo'));
        $this->assertSame(0, preg_match('/height var\(--duracion-(?!estado\))/', $movil), 'toda altura del móvil se anima con el reloj de estado');
    }

    /** Rotura: borrar la raya del inferior. */
    public function test_las_dos_rayas_de_apoyo_se_encienden_juntas(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $antes = strstr($css, '@media (min-width: 64rem) {', true);
        $despues = strstr($css, '@media (min-width: 64rem) {');

        $this->assertStringContainsString('.cromo-apoyado::before {', $antes);
        $this->assertStringContainsString('.cromo-apoyado .modulo-inferior::after {', $despues);
        $this->assertStringContainsString('opacity: 1;', $this->regla($despues, '.cromo-apoyado .modulo-inferior::after'));
    }

    /** Rotura: poner `line-clamp`; quitar `overflow-wrap: anywhere`. */
    public function test_el_rotulo_no_recorta(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $this->assertStringContainsString('overflow-wrap: anywhere;', $this->regla($movil, '.pestana__rotulo > span'));
        $this->assertStringContainsString('min-height: var(--asb-alto-rotulo-pestana);', $this->regla($movil, '.pestana__rotulo'));
        foreach (['navbar', 'menu-grupo'] as $vista) {
            $this->assertStringNotContainsString('line-clamp', File::get(resource_path("views/components/publico/{$vista}.blade.php")));
        }
    }

    /** Rotura: sacar `overflow-y: auto` de la media de apaisado. */
    public function test_la_hoja_no_bloquea_el_gesto_en_vertical(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);
        $apaisado = $this->bloque($movil, '@media (orientation: landscape) and (max-height: 30rem)');

        $hoja = $this->regla($movil, '.hoja-inferior');
        foreach (['overflow-y', 'overscroll-behavior', 'touch-action'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $hoja, 'en vertical desplazarse es cerrar');
        }
        $this->assertStringContainsString('touch-action: pan-y pinch-zoom;', $this->regla($apaisado, '.hoja-flotante'));
        $this->assertStringContainsString('overscroll-behavior: contain;', $this->regla($apaisado, '.hoja-flotante'));

        $primero = $this->bloque($css, '@media (max-width: 63.999rem)', 1);
        $this->assertLessThan(strpos($css, '.hoja-inferior {'), strpos($css, '.hoja-flotante {'), 'la hoja del móvil se apoya en el material de siempre');
        $this->assertStringContainsString('var(--asb-hoja-velo)', $this->regla($css, '.hoja-flotante'));
        $this->assertStringNotContainsString('color-mix(', $this->regla($css, '.hoja-flotante'));
        $this->assertStringNotContainsString('.modulo-inferior', $primero, 'el módulo inferior vive en el segundo bloque, tras el de escritorio');
    }

    /** Rotura: poner el cruce antes de `.logo-doble {`; quitar `marca-compacta`. */
    public function test_la_marca_cruza_sin_recortarse(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        $this->assertStringContainsString("'marca-compacta' => auth()->check()", $navbar);
        $this->assertStringContainsString("'min-h-11',", $navbar);
        $this->assertGreaterThan(strpos($css, '.logo-doble {'), strpos($css, '.marca-compacta .logo-doble {'), 'el cruce va DESPUÉS de la primera regla del logo, que es la que la guardia de escritorio lee');
        $this->assertGreaterThan(strpos($css, '.logo-doble {'), strpos($css, '[data-estado="scroll"] .logo-doble,'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);
        foreach (['object-fit', 'clip-path', 'mask', 'filter:'] as $prohibido) {
            $this->assertStringNotContainsString($prohibido, $movil, 'la marca no se recorta ni se recolorea');
        }

        $this->assertStringNotContainsString('marca-compacta', $this->cabecera('/contacto'));
        $this->assertStringContainsString('marca-compacta', $this->actingAs($this->usuarioCon([User::ROL_SUBADMIN]))->get('/contacto')->getContent());
    }

    /** Rotura: quitar `visibility: hidden` de la retirada. */
    public function test_la_barra_se_retira_ante_el_teclado(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $retirada = $this->regla($this->bloque($css, '@media (max-width: 63.999rem)', 2), '.cromo[data-teclado="abierto"] .modulo-inferior');

        $this->assertStringContainsString('visibility: hidden;', $retirada);
        $this->assertStringContainsString('translate: 0 var(--asb-retirada-barra);', $retirada);
    }

    /** Rotura: devolver el `+` al selector; borrar el padding del body; quitar la media de 20rem. */
    public function test_el_apartado_no_cuelga_del_orden_de_landmarks_ni_del_zoom(): void
    {
        $css = File::get(resource_path('css/app.css'));
        $movil = $this->bloque($css, '@media (max-width: 63.999rem)', 2);

        $this->assertStringContainsString('.cromo-fijo ~ main > section:first-child:not(.hero-portada) {', $css);
        $this->assertStringNotContainsString('.cromo-fijo + ', $css);
        $this->assertStringContainsString('padding-bottom: calc(var(--asb-alto-modulo-inferior) + env(safe-area-inset-bottom, 0px));', $this->regla($movil, 'body'));
        $this->assertStringContainsString('padding-inline: env(safe-area-inset-left, 0px) env(safe-area-inset-right, 0px);', $this->regla($movil, 'body'));
        $this->assertStringContainsString('scroll-padding-top: calc(var(--asb-alto-modulo-superior)', $this->regla($movil, 'html'));
        $this->assertStringContainsString('scroll-padding-bottom', $this->regla($movil, 'html'));
        $this->assertStringContainsString('safe-area-inset-left', $this->regla($movil, '.cromo-fijo'));
        $this->assertStringContainsString('position: static;', $this->regla($this->bloque($movil, '@media (max-height: 20rem)'), '.modulo-inferior'));
    }
```

- [ ] **Step 2: Rojo** — Run: `php artisan test --compact tests/Feature/NavbarMovilTest.php tests/Feature/NavbarTresEstadosTest.php`. Expected: rojas las nueve y la del vidrio.

- [ ] **Step 3: El CSS** — exactamente el de la spec §6.4 («El CSS del móvil, entero»), con estas precisiones: (1) el comentario de `.cromo` no pega la clase de ocultación; (2) el primer bloque móvil sustituye al de hoy (`:467-482`) y su comentario pasa a «El vidrio del móvil vive en el pseudoelemento de la bandeja: un vidrio no es ancestro de otro vidrio (raíz de fondo, Filter Effects 2), y las hojas que cuelgan de ella desenfocan la página y no el interior de la bandeja. El header es fijo desde la portada a pantalla completa»; (3) se borran `.cromo-oculto` (`:594-597`) y su regla en el bloque reducido (`:1251-1254`), y `.tema-lateral*` entero (`:777-859`); (4) `.hoja-flotante` pasa a `background: radial-gradient(16rem circle at 18% 0%, rgb(238 65 55 / 0.09), transparent 54%), var(--asb-hoja-velo);` con el comentario «el velo por token: sólido bajo transparencia reducida y bajo más contraste»; (5) el segundo bloque móvil va inmediatamente después del cierre de `@media (hover: hover) and (pointer: fine) { ... }` (`:757`), dentro de `@layer components`.

- [ ] **Step 4: Compilar y ver verde**

Run: `php artisan view:clear && npm run build` y después `php artisan test --compact tests/Feature/NavbarMovilTest.php tests/Feature/NavbarTresEstadosTest.php tests/Feature/MovimientoTest.php tests/Feature/EscenaPublicaTest.php`. Expected: build sin errores (Lightning CSS acepta las medias anidadas), PASS.

- [ ] **Step 5: Roturas** — devolver `transform: translateY(0)` a `.cromo` (rojo); mover `backdrop-filter` de `.modulo-inferior::before` a `.modulo-inferior` (rojo); poner `height: calc(var(--asb-alto-modulo-inferior) + env(safe-area-inset-bottom, 0px))` en `.modulo-inferior` con su transición (rojo); mover el cruce del logo antes de `.logo-doble {` (rojo); sacar `overflow-y: auto` de la media de apaisado (rojo).

- [ ] **Step 6: Commit de trabajo** (`WIP 6: el CSS del móvil`)

---

### Task 7: El usuario demo de secretaría es una persona (D-M15)

**Files:**
- Modify: `database/seeders/UsuarioSeeder.php:44`
- Test: `tests/Feature/NavbarMovilTest.php`

- [ ] **Step 1: La guardia**

```php
    /**
     * El chip diría «Sec. Secretaría del c…» a 360 px con el usuario demo de
     * la oficina (D-M15). Es dato de semilla, no de producción.
     * Rotura: devolver «Secretaría del capítulo».
     */
    public function test_el_usuario_demo_de_secretaria_es_una_persona(): void
    {
        $sembrador = File::get(database_path('seeders/UsuarioSeeder.php'));

        $this->assertStringNotContainsString('Secretaría del capítulo', $sembrador);
        $this->assertStringContainsString("'name' => 'Mariana Restrepo',", $sembrador);
    }
```

- [ ] **Step 2: Rojo, código, verde** — `'name' => 'Secretaría del capítulo',` pasa a `'name' => 'Mariana Restrepo',` con un comentario encima («Una persona y no el cargo: el chip de la barra móvil muestra nombre y rango, y «Sec. Secretaría del c…» decía dos veces lo mismo (D-M15, 6 sep)»). Run: `php artisan test --compact --filter=test_el_usuario_demo_de_secretaria_es_una_persona` y `grep -rn "Secretaría del capítulo" tests/ app/ resources/ docs/ingenieria/*.md` (nada más que cambiar; `manual-de-usuario.md` no la nombra).

- [ ] **Step 3: Commit de trabajo** (`WIP 7: el usuario demo de secretaría es una persona`)

---

### Task 8: Pint, la suite entera y la pasada de mutaciones

- [ ] **Step 1:** `vendor/bin/pint --dirty --format agent`.
- [ ] **Step 2:** `php artisan view:clear && npm run build && php artisan test --compact`. Expected: 0 fallos. Anotar casos, aserciones y segundos para el cierre (cifra medida ese día).
- [ ] **Step 3: Mutaciones**, una por guardia nueva y en este orden, cada una vista roja y deshecha antes de la siguiente: (1) `--asb-retirada-barra: 0%` fuera del bloque reducido; (2) velo claro al 72 %; (3) `contains` fuera de `cerrarSiSeDesplaza`; (4) `x-on:pageshow.window` fuera de control-tema; (5) `text-apagado` en el rango; (6) `-movil` fuera del id de la pestaña; (7) Eventos fuera del arreglo; (8) `aria-current="page"` en el botón de grupo; (9) 24 y 12 intercambiados; (10) `hidden lg:flex` de vuelta en la cuenta; (11) `media` de vuelta en la precarga; (12) `transform: translateY(0)` de vuelta en `.cromo`; (13) el vidrio de `::before` al módulo; (14) `env()` en la altura transicionada; (15) el cruce del logo antes de `.logo-doble {`; (16) `overflow-y: auto` fuera de la media de apaisado; (17) `line-clamp-2` en el rótulo; (18) `Secretaría del capítulo` de vuelta. Un guion `f5/mutar.pl` con `mutar N` / `restaurar N` y `git diff --stat` limpio al final.

---

### Task 9: Chromium: raíz de fondo, geometría, dirección, hojas, teclado y las cifras de `ObjetivoTactilTest`

**Files:**
- Create (scratchpad, no repositorio): `f5/verificar-movil.js` y sucesivos, con `playwright-cli open` / `run-code --filename` / `close` contra `http://localhost:8123` (el servidor de `preview` de la sesión; si no está, `preview_start`).
- Modify: `tests/Feature/ObjetivoTactilTest.php` (las filas de la spec §8.3, con la cifra medida).

- [ ] **Step 1:** Los once puntos de la spec §8.4 en este orden, cada uno con su cifra en `f5/medidas.md`: (1) `getImageData` sobre una franja de texto tras cada módulo y tras cada hoja abierta (las cuatro), en los dos temas; (2) contraste medido del rótulo activo, del de reposo y del rango sobre `/directorio` con fotos; (3) `getBoundingClientRect().bottom === window.innerHeight` del inferior a 390 y a 768 con `scrollY` 0 y 600, y `display: none` a 1280; (4) altos 56/68 y 48/48; objetivos de §8.3 con `elementFromPoint` sobre el cuadrado de 44 en `inicial` y `scroll`, a 320 y 390; «Abre tu negocio» en dos líneas sin recorte y las otras cuatro en una (`scrollWidth <= clientWidth`), también con el espaciado de texto de WCAG 1.4.12; el `<h1>` de /contacto y el primer campo de /afiliate bajo el superior; el último enlace del pie con la página al final; `/directorio?vista=mapa`; (5) dirección e histéresis desde cualquier parada: pasos de 10 px, cambia entre 24 y 32, subir 10 no devuelve y 13 sí, `scrollTo(0)` devuelve, salto de 600 no cambia, `scrollTo(scrollHeight + 50)` no cambia, `cromo-apoyado` sigue a media página, abrir una hoja no cambia el alto; (6) hojas: Bolsas abre, El gremio cierra la primera, el tema cierra la de abajo, `pointerdown` fuera cierra, arrastre de 30 px sobre la hoja cierra, Enter + Tab + flecha abajo deja el foco dentro, Escape devuelve el foco; con sesión la hoja de cuenta no desborda a 320 y «Cerrar sesión» a un toque; anónimo ve «Entrar como afiliado»; (7) teclado con `visualViewport` emulado por CDP: `data-teclado="abierto"` y `visibility: hidden`, sin parpadeo entre campos (`MutationObserver`), la casilla de habeas data no lo retira; (8) 768×1024, 844×390 y 320×180; girar de 820 a 1180 con una hoja abierta la cierra; (9) movimiento reducido (`sin-desplazamiento` puesto por `addInitScript`): `data-estado` no cambia; transparencia reducida y más contraste por CDP: `backdrop-filter: none` computado en los dos `::before`; (10) portada con el video corriendo: trazado del compositor a 390×844 (`page.tracing` o `Performance` por CDP), presupuesto 4 ms por fotograma; (11) el punto 11 (dispositivo real) queda para la S7 y se anota en el estado.
- [ ] **Step 2:** Capturas para Sua en el scratchpad (`f5-*.png`): portada y /contacto a 390 en `inicial` y `scroll`, claro y oscuro, anónimo y con sesión; Bolsas abierta; la cuenta abierta; 768 y 844×390.
- [ ] **Step 3:** Las filas de §8.3 entran en `ObjetivoTactilTest::cadenasMedidas()` **con la cifra medida** en su tercera columna; `php artisan test --compact tests/Feature/ObjetivoTactilTest.php`.
- [ ] **Step 4:** Lo que no cuadre se arregla con su guardia y su rotura, y se apunta en `f5/medidas.md` para el cierre. Commit de trabajo (`WIP 9: cifras medidas en Chromium`).

---

### Task 10: Revisión adversaria y arreglos

- [ ] **Step 1:** Con la skill de revisión de código de la sesión (`/code-review` a nivel alto sobre la rama, o `superpowers:requesting-code-review`): tres lentes como el 5 sep (Alpine, CSS, guardias) más la de iOS y Android reales. Cada hallazgo se verifica antes de tocar nada.
- [ ] **Step 2:** Cada arreglo con su guardia vista roja. Commits de trabajo.

---

### Task 11: Documentación, un solo commit de código y el cierre

- [ ] **Step 1: La Parte I gana sus notas fechadas** (spec Parte II §7): §1 («Un rediseño del móvil... no se tocan» gana «**6 sep:** la Parte II lo rediseña»), §3.1 («un solo `<nav>`» gana «**6 sep:** dos, D-M9»), §3.8 (los popovers antes de `<nav id="menu-movil">`), §6.4 (la barra lateral «se conserva solo por debajo de 1024 px» gana «**6 sep:** retirada, D-M6») y §8 («conserva `.cromo-oculto`, `.tema-lateral*`» gana «**6 sep:** retirados»). En la Parte II: §5.2 con el porcentaje final del velo si cambió, §8.3 con las cifras medidas, y una sección «§13. Lo que la construcción cambió» si algo de §4-§6 se apartó de lo escrito.
- [ ] **Step 2: `material/encargo.md` §13**, una fila fechada 6 sep 2026: «**La barra móvil 2.1: dos módulos, el inferior fijo con las cinco secciones y dos hojas, sin hamburguesa ni panel en plano ni barra lateral de tema.** Los seis destinos plegados pasan de un toque a dos a cambio de una barra siempre visible; el tema ofrece Sistema también en móvil; el chip de idioma se oculta bajo 64rem hasta que exista la traducción. Dieciocho decisiones de Sua (spec Parte II §2)».
- [ ] **Step 3: `docs/ingenieria/matriz-de-pruebas.md`**: las tres cifras móviles caducadas (cabecera 56, panel 772, 594 objetivos) se sustituyen por las medidas o se marcan como re-medidas ese día.
- [ ] **Step 4: Un solo commit de código.** `GIT_OPTIONAL_LOCKS=0 git reset --soft <commit de la Parte II aprobada>` y un commit con vista, CSS, tokens, JS, sembrador y guardias juntos (mensaje en español con el porqué: partido, ids duplicados o suite roja); después un commit de documentación (spec, encargo, matriz) y el commit de cierre (`estado.md` con el commit nuevo, `bitacora.md` §41).
- [ ] **Step 5: Cierre del estado:** D-34 sale de pendientes (resuelta por Sua el 6 sep) y entra en «Decisiones que rigen»; el estado dice que la rama existe, qué se midió y qué falta (dispositivo real, S7); las cifras del §5 se re-miden ese día. `estado.md` con el commit nuevo en el encabezado; `bitacora.md` §41 con lo que se hizo, lo que se midió, lo que se aprendió.
- [ ] **Step 6:** No se fusiona ni se empuja sin que Sua lo pida: se le presentan las capturas, las cifras y las decisiones que la verificación haya dejado abiertas.

## Self-review

- **Cobertura de la spec:** §3.3 (tarea 6), §3.4 (6), §3.7 (5 y 6), §4.1 (5), §4.2 (5), §5.1-5.3 (1 y 6), §6.1 (3 y 5), §6.2 (5), §6.3 (2, 3 y 4), §6.4 (5 y 6), §7 (todas), §8.1 (1, 2, 3, 5, 6), §8.2 (1-7), §8.3 (9), §8.4 (9), D-M15 (7), notas de la Parte I y encargo (11). Sin huecos.
- **Placeholders:** ninguno; los bloques de código que no se repiten remiten a secciones de la spec que los traen tal cual.
- **Consistencia de nombres:** `posicionDelDocumento` (2, 5), `cerrarSiSeDesplaza` (2, 3), `scrollAlAbrir` (2), `menu-{slug}-movil` (4, 5), `pestana`/`pestanas`/`pestana__rotulo`/`hoja-inferior`/`modulo-inferior`/`marca-compacta` (4, 5, 6), `--asb-alto-*`/`--asb-desplazamiento-hoja`/`--asb-retirada-barra`/`--asb-hoja-velo`/`--asb-cromo-apoyo-inferior` (1, 4, 6), `data-teclado` (5, 6), `esEscritorio`/`compacta`/`altoReferencia` (5).
