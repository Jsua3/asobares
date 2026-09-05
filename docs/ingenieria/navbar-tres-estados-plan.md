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

1. **Escritorio 1440×900, ratón.** `dataset.estado` es `inicial`; ancho de `.bandeja` ≈ 1248 y `.modulo` sin fondo (`background-color` computado `rgba(0, 0, 0, 0)`). Tras `scrollTo(0, 400)` + 700 ms: `scroll`; `gap` computado de `.bandeja` = `12px`; `max-width` de `a.control-plegable` = `0px`; `opacity` de `.logo-doble__isotipo` = `1`. Tras `mouseenter` en el header + 700 ms: `atencion`; `max-width` = `192px`. Tras `mouseleave` + 400 ms: `scroll`. Tras `scrollTo(0, 0)` + 700 ms: `inicial`.
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
