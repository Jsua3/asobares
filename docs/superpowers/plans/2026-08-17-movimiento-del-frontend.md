# Movimiento del frontend — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dar al frontend de ASOBARES un sistema de movimiento con fuente única, portadores con nombre y guardia automática, y aplicarlo en doce piezas quirúrgicas.

**Architecture:** Las curvas y duraciones se declaran una sola vez en `resources/css/tokens.css`, que ya importan el sitio público y el panel. El movimiento vive en portadores con nombre (`.pulsable`, `.tarjeta-hover`, `x-publico.boton`, `x-publico.alerta`) y nunca en utilidades sueltas dentro de las vistas. Una prueba de guardia en PHP impide que vuelva a dispersarse, siguiendo el mismo patrón que la guardia de colores cableados que el repositorio ya tiene.

**Tech Stack:** Laravel 13, Blade, Livewire 3, Alpine 3 (+ `@alpinejs/collapse`), Tailwind CSS 4, Filament 4, PHPUnit 12, Vite 8. Verificación con `playwright-cli` (global) sobre Chromium real.

**Especificación de origen:** `docs/superpowers/specs/2026-08-17-movimiento-del-frontend-design.md`

## Global Constraints

Todo lo de aquí aplica a **todas** las tareas.

- **No se toca `package.json`.** Cero dependencias nuevas, cero desinstalaciones. Prohibido `npm install`.
- **Cero `@keyframes` propios.** Todo movimiento es `transition` o `@starting-style`. Es lo que las mordazas de cambio de tema ya cubren.
- **Solo se anima `transform` y `opacity`** (y `border-color`/`color` en fundidos). Nunca `height`, `width`, márgenes ni `all`.
- **Nunca `ease-in`** en interfaz. Entradas y salidas → `ease-out`. Movimiento en pantalla → `ease-in-out`. Hover y color → `ease-color`. Menú móvil → `ease-cajon`.
- **Ninguna vista escribe duraciones ni curvas crudas.** Solo `duration-(--duracion-*)` y las utilidades `ease-*` de los tokens.
- **La sintaxis de variable en Tailwind 4 es el PARÉNTESIS, no el corchete.** Verificado empíricamente contra `tailwindcss@4.3.3`: `duration-(--duracion-boton)` compila a `transition-duration: var(--duracion-boton)`, mientras que `duration-[--duracion-boton]` compila a `transition-duration: --duracion-boton` — una declaración inválida que el navegador **descarta en silencio** y que hace caer la animación al default de 150 ms sin que nada falle a la vista. Vale igual para `translate-y-(--…)`. Si dudas, la forma larga `duration-[var(--duracion-boton)]` también es correcta.
- **Ninguna duración de interfaz pasa de 300 ms**, y la salida siempre es más rápida que la entrada.
- **Identificadores, nombres de archivo, comentarios y mensajes en español.** Los nombres de método de prueba van en snake_case **sin tildes ni eñes**; los comentarios y docblocks sí las llevan.
- **Binario de PHP:** `"C:/Users/Predator/.config/php85/php.exe"`. El `php` del PATH resuelve al mismo, pero usar la ruta absoluta entre comillas es lo seguro.
- **Al terminar de tocar PHP:** `vendor/bin/pint --dirty --format agent`.
- **Pruebas que solo leen archivos de `resources/` no llevan `RefreshDatabase` ni `setUp`.** Importan solo `Illuminate\Support\Facades\File` y `Tests\TestCase`.
- **Docblock de clase obligatorio** en toda clase de prueba: en español, explicando *por qué* existe el archivo, no qué hace.

## Correcciones a la especificación descubiertas en el inventario

La especificación decía «34 clones del botón primario en 23 archivos». El inventario exhaustivo (86 elementos leídos y verificados uno a uno) precisa:

1. **Son 86 elementos de acción en 30 archivos**, no 34 en 23. El reparto real es **34 primarias, 26 contorno y 26 fantasma**. El «34» de la especificación era correcto, pero solo para las primarias.
2. **`transition-colors` está en 18 de las 34 primarias y falta en 16** (la especificación decía 20/14). En el total de 86: 37 la tienen, 49 no.
3. **La variante «fantasma» no tiene padding en ninguno de sus 26 casos.** Son enlaces de texto con cambio de color, no botones. Meterlos en `x-publico.boton` con padding sería un cambio visual en 26 sitios que nadie pidió.

**Decisión de alcance que toma este plan, en consecuencia:** `x-publico.boton` cubre **primaria (34) y contorno-CTA (9) = 43 elementos**, que son botones de verdad. Los 26 fantasma reciben una utilidad de clase sin padding (`.enlace-accion`). Quedan **fuera y documentados**: los 7 chips de filtro (piden componente propio con prop `:activo`), los 2 controles segmentados (mezclan fantasma y primaria en el mismo elemento; no son botones), los 10 contorno-utilitarios y el `<input type="file">` de `artistas/inscripcion:46` (su botón lo pinta el navegador vía pseudo-elemento `file:`, no se puede migrar).

Y un hallazgo de accesibilidad encontrado de paso, que este plan corrige porque está en el camino: **`alerta.blade.php:16` tiene `role="status"` fijo** para los tres tipos. Para `tipo="error"` lo correcto es `role="alert"` — una región assertiva. Hoy el tipo cambia colores e icono pero no la semántica ARIA.

---

# FASE 0 — Cimentación

## Task 1: Tokens de movimiento en `tokens.css`

**Files:**
- Modify: `resources/css/tokens.css:22` (dentro del `@theme`) y `:128` (final del `:root`)
- Test: `tests/Feature/MovimientoTest.php` (crear)

**Interfaces:**
- Produces: los tokens `--ease-out`, `--ease-in-out`, `--ease-cajon`, `--ease-color`, `--duracion-instante`, `--duracion-boton`, `--duracion-salida`, `--duracion-entrada`, `--duracion-panel`, `--asb-levante`, `--asb-desplazamiento-panel`, `--asb-desplazamiento-alerta`. Todas las tareas posteriores los consumen.

- [ ] **Step 1: Escribir la prueba que falla**

Crear `tests/Feature/MovimientoTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El frontend llegó a tener 160 `hover:` sin puerta táctil, cero `:active` en
 * todo el repositorio y una sola curva escrita a mano que además era la
 * prohibida. Nada de eso fue descuido: fue que ningún sitio decía cómo se
 * escribe el movimiento. Esta guardia lo dice, y falla cuando se improvisa.
 */
class MovimientoTest extends TestCase
{
    public function test_los_tokens_de_movimiento_existen(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        // Curvas: van en @theme para que Tailwind genere las utilidades y para
        // que redefinir --ease-out pise la nativa en todo el proyecto.
        $this->assertStringContainsString('--ease-out: cubic-bezier(0.23, 1, 0.32, 1)', $tokens);
        $this->assertStringContainsString('--ease-in-out: cubic-bezier(0.77, 0, 0.175, 1)', $tokens);
        $this->assertStringContainsString('--ease-cajon: cubic-bezier(0.32, 0.72, 0, 1)', $tokens);
        $this->assertStringContainsString('--ease-color: ease', $tokens);

        // Duraciones: la escala codifica que la salida es más rápida que la
        // entrada (160 < 200) y que nada de interfaz pasa de 300 ms.
        $this->assertStringContainsString('--duracion-instante: 100ms', $tokens);
        $this->assertStringContainsString('--duracion-boton: 140ms', $tokens);
        $this->assertStringContainsString('--duracion-salida: 160ms', $tokens);
        $this->assertStringContainsString('--duracion-entrada: 200ms', $tokens);
        $this->assertStringContainsString('--duracion-panel: 240ms', $tokens);

        // Desplazamientos: son tokens y no literales porque el interruptor de
        // `prefers-reduced-motion` los anula sin tocar las duraciones.
        $this->assertStringContainsString('--asb-levante: -2px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: -4%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: -25%', $tokens);
    }
}
```

- [ ] **Step 2: Correr la prueba y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: FAIL — `Failed asserting that '...' contains "--ease-out: cubic-bezier(0.23, 1, 0.32, 1)"`.

- [ ] **Step 3: Añadir las curvas al `@theme`**

En `resources/css/tokens.css`, justo después de la línea 22 (`--font-display: ...`), insertar:

```css

    /* --- Movimiento ---
       Van en @theme y no en :root porque `--ease-*` es namespace de Tailwind 4:
       genera las utilidades `ease-*` y, al redefinir `--ease-out`, pisa la
       nativa (cubic-bezier(0,0,0.2,1)) en todo el proyecto. */
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
    --ease-in-out: cubic-bezier(0.77, 0, 0.175, 1);
    --ease-cajon: cubic-bezier(0.32, 0.72, 0, 1);
    --ease-color: ease;
```

- [ ] **Step 4: Añadir duraciones y desplazamientos al `:root`**

En el mismo archivo, después de la declaración de `--asb-sombra-tarjeta` (que termina en la línea 128 con `);`) y antes del `}` que cierra `:root`, insertar:

```css

    /*
     * Duraciones. No hay namespace de Tailwind para duración, así que viven
     * aquí y se alcanzan desde Blade con `duration-(--duracion-boton)`.
     * Los nombres codifican la regla: la salida es más rápida que la entrada.
     */
    --duracion-instante: 100ms; /* :active */
    --duracion-boton: 140ms; /* hover, color */
    --duracion-salida: 160ms; /* leave de desplegables */
    --duracion-entrada: 200ms; /* enter de desplegables, alertas */
    --duracion-panel: 240ms; /* menú móvil */

    /*
     * Desplazamientos. Son tokens y no literales porque bajo
     * `prefers-reduced-motion` se anulan estos y NO las duraciones: así el
     * borde sigue fundiendo y lo único que desaparece es el movimiento.
     */
    --asb-levante: -2px;
    --asb-desplazamiento-panel: -4%;
    --asb-desplazamiento-alerta: -25%;
```

- [ ] **Step 5: Correr la prueba y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: PASS, 1 prueba, 12 aserciones.

- [ ] **Step 6: Commit**

```bash
git add resources/css/tokens.css tests/Feature/MovimientoTest.php
git commit -m "Le da al movimiento la fuente unica que el color ya tenia"
```

---

## Task 2: El interruptor de accesibilidad

**Files:**
- Modify: `resources/css/tokens.css` (final del archivo)
- Modify: `resources/css/app.css:9-12` (el `scroll-behavior`) y final del archivo
- Modify: `tests/Feature/MovimientoTest.php` (añadir método)
- Modify: `tests/Feature/Panel/ComponentesDelPanelTest.php:55-60` (endurecer)

**Interfaces:**
- Consumes: los tokens de la Task 1.
- Produces: el bloque `@media (prefers-reduced-motion: reduce)` de `tokens.css` que anula los tres desplazamientos.

- [ ] **Step 1: Escribir la prueba que falla**

Añadir a `tests/Feature/MovimientoTest.php`, dentro de la clase:

```php
    /**
     * La regla no dice «quitar toda animación»: dice quitar el movimiento y
     * conservar los fundidos de opacidad y color, que ayudan a comprender.
     * Por eso se anulan los desplazamientos y NO las duraciones — si se
     * pusieran las duraciones a cero moriría también el fundido del borde.
     */
    public function test_el_movimiento_reducido_anula_el_desplazamiento_y_no_el_reloj(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $tokens);
        $this->assertStringContainsString('--asb-levante: 0px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: 0%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: 0%', $tokens);

        // Las duraciones NO se anulan: si alguien las pone a cero aquí, está
        // deshaciendo la precisión de arriba.
        $this->assertStringNotContainsString('--duracion-boton: 0ms', $tokens);
        $this->assertStringNotContainsString('--duracion-entrada: 0ms', $tokens);
    }

    /**
     * Los tokens solo cubren lo que los usa. Filament y Leaflet traen su propio
     * movimiento, y el scroll suave global lo dispara el enlace «Saltar al
     * contenido», que es navegación de teclado.
     */
    public function test_hay_red_de_seguridad_para_lo_que_no_usa_los_tokens(): void
    {
        $app = File::get(resource_path('css/app.css'));

        // Barrido estrecho: solo `animation` y `scroll-behavior`. NO toca
        // `transition`, para no deshacer la precisión del interruptor.
        $this->assertStringContainsString('animation-duration: 1ms !important', $app);
        $this->assertStringContainsString('animation-iteration-count: 1 !important', $app);
        $this->assertStringContainsString('scroll-behavior: auto !important', $app);

        /*
         * Lo que se prohíbe es que el BARRIDO anule `transition`, no que nadie
         * escriba nunca una duración de cero: `.pulsable:active` usa
         * `transition-duration: 0ms` a propósito para que el botón baje sin
         * retardo. Por eso se buscan las formas con `!important`, que son las
         * que solo puede escribir un barrido.
         */
        $this->assertStringNotContainsString('transition-duration: .01ms', $app);
        $this->assertStringNotContainsString('transition-duration: 0ms !important', $app);
        $this->assertStringNotContainsString('transition: none !important', $app);

        // El scroll suave deja de ser incondicional.
        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $app);
    }
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: FAIL en `test_el_movimiento_reducido_anula_el_desplazamiento_y_no_el_reloj` (el bloque `@media` no existe en `tokens.css`).

- [ ] **Step 3: Añadir el interruptor al final de `tokens.css`**

Al final del archivo, después del bloque `@theme inline` que cierra en la línea 209:

```css

/*
 * Movimiento reducido: se anula el DESPLAZAMIENTO, no el reloj.
 *
 * La regla de craft pide quitar el movimiento y conservar las transiciones de
 * opacidad y color, que ayudan a comprender. Poner las duraciones a cero
 * mataría también los fundidos. Con esto, la tarjeta del directorio sigue
 * fundiendo su borde a rojo en 140 ms y lo único que pierde es la elevación.
 *
 * Va después de `.dark` para ganar por orden de aparición: misma
 * especificidad, gana la última.
 */
@media (prefers-reduced-motion: reduce) {
    :root {
        --asb-levante: 0px;
        --asb-desplazamiento-panel: 0%;
        --asb-desplazamiento-alerta: 0%;
    }
}
```

- [ ] **Step 4: Condicionar el scroll suave en `app.css`**

Reemplazar las líneas 9-12 de `resources/css/app.css`:

```css
@layer base {
    html {
        scroll-behavior: smooth;
    }
```

por:

```css
@layer base {
    /* El scroll suave lo dispara el enlace «Saltar al contenido», que es
       navegación de teclado. Incondicional era una barrera. */
    html {
        scroll-behavior: auto;
    }

    @media (prefers-reduced-motion: no-preference) {
        html {
            scroll-behavior: smooth;
        }
    }
```

- [ ] **Step 5: Añadir la red de seguridad al final de `app.css`**

Al final del archivo, después del `}` que cierra `@layer components`:

```css

/*
 * Red de seguridad, deliberadamente estrecha: solo `animation` y
 * `scroll-behavior`.
 *
 * NO toca `transition` a propósito. El interruptor de `tokens.css` ya anula
 * el desplazamiento con precisión, y un barrido de `transition-duration`
 * aquí mataría los fundidos de color que la regla manda conservar.
 *
 * Esto cubre lo que no usa nuestros tokens: los @keyframes de Filament y
 * cualquier cosa futura. Leaflet no se trata aquí sino en su propia API
 * (ver `components/publico/mapa.blade.php`), que es donde el control existe.
 */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 1ms !important;
        animation-iteration-count: 1 !important;
    }

    html {
        scroll-behavior: auto !important;
    }
}
```

- [ ] **Step 6: Endurecer la prueba que hoy no guarda nada**

En `tests/Feature/Panel/ComponentesDelPanelTest.php`, reemplazar el método de las líneas 55-60:

```php
    public function test_el_movimiento_respeta_prefers_reduced_motion(): void
    {
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        $this->assertStringContainsString('prefers-reduced-motion: reduce', $tema);
    }
```

por:

```php
    /**
     * Antes esta prueba solo miraba que la cadena `prefers-reduced-motion`
     * apareciera en el tema del panel: pasaba igual aunque se vaciara la
     * guarda entera. Y además vigilaba el sitio equivocado — la guarda se
     * mudó a `tokens.css`, que importan las dos superficies.
     */
    public function test_el_movimiento_respeta_prefers_reduced_motion(): void
    {
        $tokens = File::get(resource_path('css/tokens.css'));

        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $tokens);

        // Los valores, no la presencia: es lo único que prueba que la guarda
        // hace algo.
        $this->assertStringContainsString('--asb-levante: 0px', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-panel: 0%', $tokens);
        $this->assertStringContainsString('--asb-desplazamiento-alerta: 0%', $tokens);
    }
```

- [ ] **Step 7: Correr las dos clases y verificar que pasan**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php tests/Feature/Panel/ComponentesDelPanelTest.php
```

Esperado: PASS en ambas.

- [ ] **Step 8: Commit**

```bash
git add resources/css/tokens.css resources/css/app.css tests/Feature/MovimientoTest.php tests/Feature/Panel/ComponentesDelPanelTest.php
git commit -m "Da al sitio publico la guarda de movimiento que solo tenia el panel"
```

---

## Task 3: Borrar `welcome.blade.php`

**Files:**
- Delete: `resources/views/welcome.blade.php`

- [ ] **Step 1: Verificar que de verdad no lo usa nadie**

```bash
grep -rn "welcome" app/ routes/ tests/ resources/views/ config/ --include="*.php" --include="*.blade.php"
```

Esperado: sin resultados. Si aparece alguna referencia, **detenerse y reportarlo** — el borrado deja de ser seguro.

- [ ] **Step 2: Correr la suite completa antes de borrar, para tener línea base**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact
```

Anotar el número de pruebas y fallos.

- [ ] **Step 3: Borrar**

```bash
git rm resources/views/welcome.blade.php
```

- [ ] **Step 4: Correr la suite y verificar que el resultado es idéntico**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact
```

Esperado: el mismo número de pruebas pasando que en el Step 2.

- [ ] **Step 5: Commit**

```bash
git commit -m "Borra la portada de fabrica que nadie renderizaba"
```

---

# FASE 1 — Portadores y piezas sin JavaScript

## Task 4: `.pulsable`, `.tarjeta-hover` y `.vidrio-hover` sobre tokens

**Files:**
- Modify: `resources/css/app.css:55-64` (`.tarjeta-hover`) y `@layer components` (añadir `.pulsable` y `.enlace-accion`)
- Modify: `resources/css/filament/admin/theme.css:67-86`
- Modify: `tests/Feature/MovimientoTest.php`

**Interfaces:**
- Consumes: `--duracion-instante`, `--duracion-boton`, `--ease-out`, `--ease-color`, `--asb-levante` de la Task 1.
- Produces: las clases `.pulsable`, `.enlace-accion` y `.tarjeta-hover`, que consumen las Tasks 10, 11 y 12.

**Arreglo obligatorio de una prueba de la Task 2, que esta tarea rompe.**

`.pulsable:active` escribe `transition-duration: 0ms`, y la prueba `test_hay_red_de_seguridad_para_lo_que_no_usa_los_tokens` que la Task 2 ya commiteó afirma `assertStringNotContainsString('transition-duration: 0', $app)`. Las dos no pueden ser ciertas a la vez.

La aserción de la Task 2 estaba mal escrita: su intención era prohibir que el **barrido** anule `transition`, no que nadie escriba nunca una duración de cero. Antes de escribir `.pulsable`, reemplaza esa línea en `tests/Feature/MovimientoTest.php` por:

```php
        $this->assertStringNotContainsString('transition-duration: .01ms', $app);
        $this->assertStringNotContainsString('transition-duration: 0ms !important', $app);
        $this->assertStringNotContainsString('transition: none !important', $app);
```

Conserva la intención original —las tres formas con `!important` son las únicas que puede escribir un barrido— y deja de colisionar con un portador legítimo. **Esta edición es parte de la Task 4 y entra en su commit.**

- [ ] **Step 1: Escribir la prueba que falla**

Añadir a `tests/Feature/MovimientoTest.php`:

```php
    /**
     * Quita del CSS los bloques `@media (hover: hover) and (pointer: fine)`
     * completos, contando llaves para respetar el anidamiento.
     *
     * Se hace así y no con una expresión regular porque lo que hay que probar
     * no es que ambas cosas existan en el archivo —eso pasaría aunque
     * estuvieran en extremos opuestos— sino que el `:hover` está DENTRO de la
     * puerta. Lo que sobrevive a esta poda es exactamente lo que queda fuera.
     */
    private function sinBloquesDeHoverFino(string $css): string
    {
        $marca = '@media (hover: hover) and (pointer: fine)';

        while (($inicio = strpos($css, $marca)) !== false) {
            $llave = strpos($css, '{', $inicio);

            if ($llave === false) {
                break;
            }

            $nivel = 0;
            $fin = null;

            for ($i = $llave, $largo = strlen($css); $i < $largo; $i++) {
                if ($css[$i] === '{') {
                    $nivel++;
                } elseif ($css[$i] === '}') {
                    $nivel--;

                    if ($nivel === 0) {
                        $fin = $i;
                        break;
                    }
                }
            }

            if ($fin === null) {
                break;
            }

            $css = substr($css, 0, $inicio).substr($css, $fin + 1);
        }

        return $css;
    }

    /**
     * En táctil un `:hover` con `transform` se queda pegado tras el toque: la
     * tarjeta del directorio se quedaba elevada y con borde rojo, como si
     * estuviera seleccionada. La puerta va alrededor del bloque `:hover`, no
     * de la declaración `transition`.
     */
    public function test_todo_hover_con_transform_tiene_puerta_tactil(): void
    {
        $hojas = [
            resource_path('css/app.css'),
            resource_path('css/filament/admin/theme.css'),
        ];

        foreach ($hojas as $hoja) {
            $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $hoja);
            $fuera = $this->sinBloquesDeHoverFino(File::get($hoja));

            $this->assertDoesNotMatchRegularExpression(
                '/:hover\s*\{[^}]*transform:/',
                $fuera,
                "{$ruta} eleva en :hover fuera de la puerta táctil."
            );
        }
    }

    /** Cero `:active` en todo el repositorio era la mayor pérdida por línea. */
    public function test_existe_el_portador_del_acuse_de_pulsacion(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('.pulsable:active', $app);
        $this->assertStringContainsString('transform: scale(0.97)', $app);

        // Bajar instantáneo, subir en 100 ms: el retardo al presionar se nota.
        $this->assertStringContainsString('transition-duration: 0ms', $app);
    }

    /** El `translateY(-2px)` y el `200ms ease` estaban duplicados literales. */
    public function test_los_portadores_no_repiten_valores_de_movimiento(): void
    {
        $app = File::get(resource_path('css/app.css'));
        $tema = File::get(resource_path('css/filament/admin/theme.css'));

        foreach (['app.css' => $app, 'theme.css' => $tema] as $nombre => $contenido) {
            $this->assertStringNotContainsString('translateY(-2px)', $contenido, "{$nombre} cablea el levante.");
            $this->assertStringNotContainsString('200ms ease', $contenido, "{$nombre} cablea la duración.");
            $this->assertStringContainsString('var(--asb-levante)', $contenido);
        }
    }
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: FAIL en las tres pruebas nuevas.

- [ ] **Step 3: Reescribir `.tarjeta-hover` en `app.css`**

Reemplazar las líneas 55-64 de `resources/css/app.css`:

```css
    .tarjeta-hover {
        transition:
            border-color 200ms ease,
            transform 200ms ease;
    }

    .tarjeta-hover:hover {
        border-color: rgb(238 65 55 / 0.45);
        transform: translateY(-2px);
    }
```

por:

```css
    .tarjeta-hover {
        transition:
            border-color var(--duracion-boton) var(--ease-color),
            transform var(--duracion-boton) var(--ease-out);
    }

    /* La puerta envuelve solo el `:hover`, nunca la `transition`: en táctil
       el estado se quedaba pegado tras el toque. */
    @media (hover: hover) and (pointer: fine) {
        .tarjeta-hover:hover {
            border-color: rgb(238 65 55 / 0.45);
            transform: translateY(var(--asb-levante));
        }
    }
```

- [ ] **Step 4: Añadir `.pulsable` y `.enlace-accion` a `app.css`**

Dentro de `@layer components`, después del bloque `.tarjeta-hover` recién escrito:

```css

    /*
     * Acuse de pulsación. Sin puerta de puntero fino a propósito: en táctil
     * el `:active` es el único acuse que existe, porque no hay hover.
     * Bajar es instantáneo y subir tarda 100 ms — al revés se siente lento.
     */
    .pulsable {
        transition: transform var(--duracion-instante) var(--ease-out);
    }

    .pulsable:active {
        transform: scale(0.97);
        transition-duration: 0ms;
    }

    /*
     * Enlace de acción en prosa: los 26 «Ver detalle →» del sitio. No lleva
     * padding a propósito — ninguno lo tenía, y dárselo ahora movería texto
     * en 26 sitios. Solo unifica el fundido de color, que hoy está en 2 de 26.
     */
    .enlace-accion {
        transition: color var(--duracion-boton) var(--ease-color);
    }
```

- [ ] **Step 5: Reescribir el bloque de `theme.css`**

Reemplazar las líneas 67-86 de `resources/css/filament/admin/theme.css`:

```css
    .vidrio-hover {
        transition:
            border-color 200ms ease,
            transform 200ms ease;
    }

    .vidrio-hover:hover {
        border-color: rgb(238 65 55 / 0.45);
        transform: translateY(-2px);
    }
}

/* Sin esto el movimiento es una barrera de accesibilidad, no un detalle. */
@media (prefers-reduced-motion: reduce) {
    .vidrio-hover,
    .vidrio-hover:hover {
        transition: none;
        transform: none;
    }
}
```

por:

```css
    .vidrio-hover {
        transition:
            border-color var(--duracion-boton) var(--ease-color),
            transform var(--duracion-boton) var(--ease-out);
    }

    @media (hover: hover) and (pointer: fine) {
        .vidrio-hover:hover {
            border-color: rgb(238 65 55 / 0.45);
            transform: translateY(var(--asb-levante));
        }
    }
}

/*
 * El parche por clase que vivía aquí se borró: `tokens.css` anula
 * `--asb-levante` bajo `prefers-reduced-motion` y eso cubre esta clase, su
 * gemela pública y todo lo que se escriba después. Aquel bloque ya había
 * divergido de `.tarjeta-hover`, que corría sin guarda ninguna.
 */
```

- [ ] **Step 6: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php tests/Feature/Panel/ComponentesDelPanelTest.php
```

Esperado: PASS en ambas.

- [ ] **Step 7: Compilar y verificar en el navegador real**

```bash
npm run build
```

Luego, con el servidor arriba (configuración `asobares` de `.claude/launch.json`, puerto 8123):

```bash
playwright-cli open http://localhost:8123/directorio
```

```bash
playwright-cli eval "getComputedStyle(document.querySelector('.tarjeta-hover')).transitionDuration" --raw
```

Esperado: `0.14s, 0.14s`.

```bash
playwright-cli eval "getComputedStyle(document.querySelector('.tarjeta-hover')).transitionTimingFunction" --raw
```

Esperado: `ease, cubic-bezier(0.23, 1, 0.32, 1)` — **dos curvas distintas, una por propiedad, y así debe ser**: `border-color` funde con `--ease-color` (que vale `ease`) y `transform` se eleva con `--ease-out`. Si las dos salieran iguales, alguien habría colapsado la declaración en una sola.

- [ ] **Step 8: Commit**

```bash
git add resources/css/app.css resources/css/filament/admin/theme.css tests/Feature/MovimientoTest.php
git commit -m "Pone los portadores sobre tokens y les da la puerta tactil"
```

---

## Task 5: La guardia que impide reincidir

**Files:**
- Modify: `tests/Feature/MovimientoTest.php`

**Interfaces:**
- Produces: `MovimientoTest::patronesProhibidos()`, `public static function ... : array` que devuelve `array<string, string>` de patrón => motivo. Sigue el patrón de guardia compartida del repositorio (`TemaClaroOscuroTest::clasesProhibidas()`).

- [ ] **Step 1: Escribir la guardia**

Añadir a `tests/Feature/MovimientoTest.php`:

```php
    /**
     * Patrones que ninguna vista debe contener, con su motivo.
     *
     * Es `public static` y no `private` siguiendo el patrón de guardia
     * compartida del repositorio: `TemaClaroOscuroTest::clasesProhibidas()`
     * la reutiliza `ComponentesDelPanelTest`. Si mañana el panel quiere la
     * misma vigilancia, la importa en vez de copiarla.
     *
     * @return array<string, string> patrón => por qué
     */
    public static function patronesProhibidos(): array
    {
        return [
            '/\bease-in\b(?!-out)/' => 'ease-in arranca lento justo cuando más se mira; en interfaz nunca',
            '/\btransition-all\b/' => 'transition: all arrastra propiedades caras que nadie quiso animar',
            '/transition:\s*all\b/' => 'transition: all arrastra propiedades caras que nadie quiso animar',
            '/\bduration-\d+\b/' => 'la duración se toma de los tokens: duration-(--duracion-*)',

            /*
             * En Tailwind 4 el corchete NO envuelve la variable en var(): esto
             * compila a `transition-duration: --duracion-boton`, que el
             * navegador descarta en silencio y deja la animación en el default
             * de 150 ms. Nada falla a la vista, y por eso hace falta vigilarlo.
             */
            '/(?:duration|translate-[xy]|delay)-\[--/' => 'usa el paréntesis: duration-(--var), no el corchete, o el valor no se envuelve en var()',
        ];
    }

    /**
     * La dispersión no fue descuido de nadie: fue que 39 archivos decidían por
     * su cuenta. Esta prueba es la que hace que el sistema sobreviva a la
     * siguiente persona que edite una vista con prisa.
     */
    public function test_ninguna_vista_improvisa_movimiento(): void
    {
        $directorios = array_filter([
            resource_path('views/publico'),
            resource_path('views/components/publico'),
            resource_path('views/components/panel'),
            resource_path('views/filament'),
            resource_path('views/errors'),
        ], File::isDirectory(...));

        $this->assertNotEmpty($directorios, 'No hay vistas que vigilar.');

        $hallazgos = [];

        foreach ($directorios as $directorio) {
            foreach (File::allFiles($directorio) as $archivo) {
                if ($archivo->getExtension() !== 'php') {
                    continue;
                }

                $contenido = $archivo->getContents();
                $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

                foreach (self::patronesProhibidos() as $patron => $motivo) {
                    if (preg_match_all($patron, $contenido, $coincidencias) > 0) {
                        $hallazgos[] = sprintf(
                            '%s → %s (%s)',
                            $ruta,
                            implode(', ', array_unique($coincidencias[0])),
                            $motivo
                        );
                    }
                }
            }
        }

        $this->assertSame([], $hallazgos, "Movimiento improvisado en vistas:\n".implode("\n", $hallazgos));
    }
```

- [ ] **Step 2: Correr y ver exactamente qué incumple hoy**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_ninguna_vista_improvisa_movimiento
```

Esperado: FAIL, con una lista de hallazgos. **Anotar la lista completa**: es el trabajo de las Tasks 6 y 7. Debe incluir al menos:

- `resources/views/components/publico/menu-usuario.blade.php → ease-in, duration-150, duration-100`
- `resources/views/components/publico/tarjeta-asociado.blade.php → duration-500`

- [ ] **Step 3: Confirmar la lista con grep, por si el regex se dejó algo**

```bash
grep -rnE "\bease-in\b|\bduration-[0-9]+\b|transition-all|transition:\s*all" resources/views/
```

Los resultados deben coincidir con los hallazgos del Step 2. Si aparece algún archivo que no salió en la prueba, el regex tiene un hueco: arreglarlo antes de seguir.

- [ ] **Step 4: NO commitear todavía**

La guardia queda escrita y en rojo en el árbol de trabajo, **sin commitear**. Verla fallar es el valor de este paso —dice exactamente qué archivos hay que arreglar— pero un commit rojo deja el árbol en un estado que nadie que corra la suite entre esta tarea y la 7 sabría interpretar.

Las Tasks 6 y 7 saldan las violaciones que acabas de listar, y **la Task 7 commitea la guardia en verde junto con su propio arreglo.**

Dejar el archivo tal cual y pasar a la Task 6. No ejecutar `git add` ni `git commit` en esta tarea.

---

## Task 6: El desplegable de sesión

**Files:**
- Modify: `resources/views/components/publico/menu-usuario.blade.php:80,83`

- [ ] **Step 1: Corregir la curva prohibida y las duraciones**

En `resources/views/components/publico/menu-usuario.blade.php`, reemplazar la línea 80:

```blade
         x-transition:enter="transition ease-out duration-150"
```

por:

```blade
         x-transition:enter="transition-[opacity,transform] ease-out duration-(--duracion-entrada)"
```

Y la línea 83:

```blade
         x-transition:leave="transition ease-in duration-100"
```

por:

```blade
         x-transition:leave="transition-[opacity,transform] ease-out duration-(--duracion-salida)"
```

Tres arreglos en dos líneas: `ease-in` era la única curva prohibida escrita a mano del proyecto; las duraciones pasan a tokens (200 ms entrada, 160 ms salida); y `transition` a secas arrastraba la lista completa de propiedades de Tailwind —incluidas `box-shadow`, `filter` y `backdrop-filter`— sobre un panel con `shadow-xl`.

**No tocar** `origin-top-right` (línea 86) ni el par `opacity-0 scale-95` de las líneas 81-82 y 84-85: ya son correctos.

- [ ] **Step 2: Verificar que la guardia deja de señalar este archivo**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_ninguna_vista_improvisa_movimiento
```

Esperado: sigue en FAIL, pero `menu-usuario.blade.php` ya **no** aparece en la lista de hallazgos.

- [ ] **Step 3: Verificar en el navegador que ya no anima la sombra**

```bash
npm run build
```

```bash
playwright-cli goto http://localhost:8123/
```

**No sirve forzar `style.display='block'` y leer el estilo computado.** Eso puentea el mecanismo de `x-transition` de Alpine —que aplica las clases solo durante la transición real— y devuelve `all` tanto antes como después del arreglo: verificaría nada. Hay que abrir el desplegable de verdad y leer durante la transición:

```bash
playwright-cli click "Configuración del sitio"
```

```bash
playwright-cli eval "await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r))); return getComputedStyle(document.getElementById('menu-configuracion')).transitionProperty" --raw
```

Esperado: `opacity, transform`. **No** debe aparecer `box-shadow` ni `backdrop-filter`.

- [ ] **Step 4: Commit**

```bash
git add resources/views/components/publico/menu-usuario.blade.php
git commit -m "Quita el ease-in del unico desplegable del sitio"
```

---

## Task 7: La tarjeta del directorio

**Files:**
- Modify: `resources/views/components/publico/tarjeta-asociado.blade.php:10`

- [ ] **Step 1: Sincronizar los dos relojes**

Reemplazar la línea 10 de `resources/views/components/publico/tarjeta-asociado.blade.php`:

```blade
                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
```

por:

```blade
                     class="h-full w-full object-cover transition-transform duration-(--duracion-boton) ease-out group-hover:scale-105">
```

Un solo gesto de hover disparaba dos relojes: el borde y la elevación de la tarjeta a 200 ms, y la foto a 500 ms. La tarjeta terminaba de moverse en dos tiempos. `scale-105` se conserva: la propiedad ya era la correcta.

- [ ] **Step 2: Verificar que la guardia pasa entera**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: **PASS en todas**, incluida `test_ninguna_vista_improvisa_movimiento`. Si sigue en rojo, la lista de hallazgos dice qué archivo falta: arreglarlo con el mismo criterio antes de continuar.

- [ ] **Step 3: Commit — la guardia entra aquí, en verde**

Este commit lleva **tres** cosas: la guardia que la Task 5 escribió y dejó sin commitear, el arreglo de la Task 6 si tampoco se commiteó, y este. La guardia nunca entra al historial en rojo.

```bash
git add tests/Feature/MovimientoTest.php resources/views/components/publico/tarjeta-asociado.blade.php
git commit -m "Sincroniza el zoom de portada y cierra la guardia de movimiento"
```

Verificar que no queda nada suelto:

```bash
git status --short
```

Esperado: limpio.

---

## Task 8: Borrar el contador del KPI

**Files:**
- Modify: `resources/views/components/panel/kpi.blade.php:33-62`
- Modify: `tests/Feature/Panel/ComponentesDelPanelTest.php` (añadir método)

- [ ] **Step 1: Escribir la prueba que falla**

Añadir a `tests/Feature/Panel/ComponentesDelPanelTest.php`:

```php
    /**
     * El conteo animado gastaba 600 ms en retrasar la lectura de las cuatro
     * cifras que son el motivo entero del observatorio, y encima mentía: la
     * cifra correcta se pintaba, saltaba a cero y volvía a subir. No cumplía
     * ninguno de los cinco propósitos válidos del movimiento.
     *
     * Nota: era el único `x-intersect` del repositorio, y funcionaba solo
     * porque este componente vive dentro de /admin, donde el Alpine de
     * Livewire lo trae. `@alpinejs/intersect` nunca estuvo en package.json.
     */
    public function test_el_kpi_pinta_la_cifra_de_una_sin_contarla(): void
    {
        $fuente = File::get(resource_path('views/components/panel/kpi.blade.php'));

        $this->assertStringNotContainsString('x-intersect', $fuente);
        $this->assertStringNotContainsString('requestAnimationFrame', $fuente);
        $this->assertStringNotContainsString('x-text="mostrado"', $fuente);

        $html = \Illuminate\Support\Facades\Blade::render(
            '<x-panel.kpi etiqueta="Recaudado" valor="$1.250.000" />'
        );

        $this->assertStringContainsString('$1.250.000', $html);
    }
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_el_kpi_pinta_la_cifra_de_una_sin_contarla
```

Esperado: FAIL — `Failed asserting that '...' does not contain "x-intersect"`.

- [ ] **Step 3: Borrar el bloque del contador**

En `resources/views/components/panel/kpi.blade.php`, reemplazar el bloque completo de las líneas 33-62 (desde el comentario Blade `{{--` hasta `>{{ $valor }}</p>`):

```blade
    {{--
        El conteo animado sube desde cero al entrar en pantalla. `x-intersect`
        evita animar tarjetas que nadie está mirando, y quien pidió menos
        movimiento ve el valor final de una.
    --}}
    <p
        class="mt-3 font-display text-3xl font-bold tracking-tight text-fuerte"
        x-data="{
            mostrado: @js((string) $valor),
            animar() {
                const crudo = @js((string) $valor);
                const destino = Number(crudo.replace(/[^0-9,-]/g, '').replace(',', '.'));

                if (! Number.isFinite(destino) || destino === 0) return;
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                const inicio = performance.now();
                const paso = (ahora) => {
                    const avance = Math.min((ahora - inicio) / 600, 1);
                    const valor = Math.round(destino * (1 - Math.pow(1 - avance, 3)));
                    this.mostrado = crudo.replace(/[0-9.,]+/, valor.toLocaleString('es-CO'));
                    if (avance < 1) requestAnimationFrame(paso);
                    else this.mostrado = crudo;
                };
                requestAnimationFrame(paso);
            },
        }"
        x-intersect.once="animar()"
        x-text="mostrado"
    >{{ $valor }}</p>
```

por:

```blade
    {{--
        La cifra se pinta de una. El conteo animado que vivía aquí retrasaba
        600 ms la lectura de lo único que la página existe para enseñar, y
        además solo contaba en las tarjetas cuyo ajuste era parseable: en la
        misma fila, unas contaban y otras no.
    --}}
    <p class="mt-3 font-display text-3xl font-bold tracking-tight text-fuerte">{{ $valor }}</p>
```

- [ ] **Step 4: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/Panel/ComponentesDelPanelTest.php
```

Esperado: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/panel/kpi.blade.php tests/Feature/Panel/ComponentesDelPanelTest.php
git commit -m "Deja de retrasar 600ms las cifras que son el motivo de la pagina"
```

---

## Task 9: Leaflet respeta la preferencia

**Files:**
- Modify: `resources/views/components/publico/mapa.blade.php` (la construcción del mapa, alrededor de la línea 28)
- Modify: `tests/Feature/MovimientoTest.php`

- [ ] **Step 1: Leer el archivo y localizar la construcción del mapa**

```bash
grep -n "L.map\|fadeAnimation\|zoomAnimation\|markerZoomAnimation" resources/views/components/publico/mapa.blade.php
```

Anotar la línea exacta y las opciones que ya se pasan.

- [ ] **Step 2: Escribir la prueba que falla**

Añadir a `tests/Feature/MovimientoTest.php`:

```php
    /**
     * Leaflet anima el fundido de teselas y el zoom por su cuenta, y el
     * barrido de CSS no lo alcanza porque son transiciones, no keyframes.
     * El control existe en su propia API: hay que consultarla allí.
     */
    public function test_el_mapa_consulta_la_preferencia_de_movimiento(): void
    {
        $mapa = File::get(resource_path('views/components/publico/mapa.blade.php'));

        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $mapa);
        $this->assertStringContainsString('fadeAnimation', $mapa);
        $this->assertStringContainsString('zoomAnimation', $mapa);
        $this->assertStringContainsString('markerZoomAnimation', $mapa);
    }
```

- [ ] **Step 3: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_el_mapa_consulta_la_preferencia_de_movimiento
```

Esperado: FAIL.

- [ ] **Step 4: Pasar las tres opciones al construir el mapa**

Justo antes de la línea que crea el mapa, insertar:

```javascript
// Leaflet anima teselas, zoom y marcadores con transiciones propias, que el
// barrido de CSS no alcanza. Aquí es donde el control existe de verdad.
const movimientoReducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
```

Y añadir estas tres opciones al objeto de opciones de `L.map(...)`:

```javascript
    fadeAnimation: ! movimientoReducido,
    zoomAnimation: ! movimientoReducido,
    markerZoomAnimation: ! movimientoReducido,
```

- [ ] **Step 5: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: PASS en todas.

- [ ] **Step 6: Verificar que el mapa sigue funcionando**

```bash
npm run build
```

```bash
playwright-cli goto "http://localhost:8123/directorio?vista=mapa"
```

```bash
playwright-cli eval "document.querySelectorAll('.leaflet-tile').length" --raw
```

Esperado: un número mayor que 0 (las teselas cargaron).

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/publico/mapa.blade.php tests/Feature/MovimientoTest.php
git commit -m "Hace que el mapa consulte la preferencia que el CSS no le alcanza"
```

---

# FASE 2 — El componente de botón y el flujo de pago

## Task 10: Crear `x-publico.boton`

**Files:**
- Create: `resources/views/components/publico/boton.blade.php`
- Test: `tests/Feature/MovimientoTest.php`

**Interfaces:**
- Produces: componente `<x-publico.boton>` con props `variante` (`'primaria'|'contorno'`, por defecto `'primaria'`), `href` (`?string`, por defecto `null`), `tipo` (`string`, por defecto `'submit'`). Renderiza `<a>` si hay `href`, `<button>` si no. Todo atributo extra pasa por `$attributes->merge()`, incluidas las clases de layout (`w-full`, `sm:w-auto`, `flex-1`, `shrink-0`, `mt-*`, `block`, `inline-block`). Lo consumen las Tasks 11 y 12.

- [ ] **Step 1: Escribir la prueba que falla**

Añadir a `tests/Feature/MovimientoTest.php`:

```php
    /**
     * La cadena de submit primario estaba repetida IDÉNTICA ocho veces, y de
     * los 34 botones primarios 18 tenían `transition-colors` y 16 no: dos
     * botones iguales en padding y color se comportaban distinto al pasar el
     * ratón. Un componente es la única forma de que eso no vuelva a pasar.
     */
    public function test_el_boton_rinde_las_dos_variantes_con_acuse_de_pulsacion(): void
    {
        $primaria = \Illuminate\Support\Facades\Blade::render(
            '<x-publico.boton>Enviar solicitud</x-publico.boton>'
        );

        $this->assertStringContainsString('Enviar solicitud', $primaria);
        $this->assertStringContainsString('<button', $primaria);
        $this->assertStringContainsString('type="submit"', $primaria);
        $this->assertStringContainsString('bg-marca-500', $primaria);
        $this->assertStringContainsString('pulsable', $primaria);
        $this->assertStringContainsString('duration-(--duracion-boton)', $primaria);

        $contorno = \Illuminate\Support\Facades\Blade::render(
            '<x-publico.boton variante="contorno" href="/directorio">Ver el directorio</x-publico.boton>'
        );

        $this->assertStringContainsString('<a', $contorno);
        $this->assertStringContainsString('href="/directorio"', $contorno);
        $this->assertStringContainsString('border-linea-fuerte', $contorno);
        $this->assertStringNotContainsString('bg-marca-500', $contorno);
    }

    /** Nueve botones de envío llevaban `w-full ... sm:w-auto`: debe pasar. */
    public function test_el_boton_deja_pasar_las_clases_de_maquetacion(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render(
            '<x-publico.boton class="w-full sm:w-auto">Enviar</x-publico.boton>'
        );

        $this->assertStringContainsString('w-full', $html);
        $this->assertStringContainsString('sm:w-auto', $html);
        $this->assertStringContainsString('bg-marca-500', $html);
    }
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_el_boton_rinde_las_dos_variantes_con_acuse_de_pulsacion
```

Esperado: FAIL — `View [components.publico.boton] not found`.

- [ ] **Step 3: Crear el componente**

Crear `resources/views/components/publico/boton.blade.php`:

```blade
@props([
    'variante' => 'primaria',
    'href' => null,
    'tipo' => 'submit',
])

@php
    /*
     * Un solo portador para los 43 botones de acción del sitio. Antes la
     * cadena del submit primario estaba repetida idéntica ocho veces y la
     * `transition-colors` aparecía en 18 de 34: dos botones iguales se
     * comportaban distinto al pasar el ratón.
     *
     * `.pulsable` llega de fábrica: es el acuse de pulsación que el proyecto
     * no tenía en ningún sitio, y en táctil es el único que existe.
     */
    $base = 'inline-block rounded-xl px-6 py-3 text-center text-sm font-semibold pulsable transition-colors duration-(--duracion-boton) ease-color';

    $estilos = match ($variante) {
        'contorno' => 'border border-linea-fuerte text-tinta hover:border-marca-500/50 hover:bg-superficie-alta',
        default => 'bg-marca-500 text-white hover:bg-marca-600',
    };
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "{$base} {$estilos}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $tipo }}" {{ $attributes->merge(['class' => "{$base} {$estilos}"]) }}>
        {{ $slot }}
    </button>
@endif
```

- [ ] **Step 4: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: PASS en todas.

- [ ] **Step 5: Commit**

```bash
git add resources/views/components/publico/boton.blade.php tests/Feature/MovimientoTest.php
git commit -m "Extrae el boton de accion que estaba copiado 43 veces"
```

---

## Task 11: Adoptar el botón en las 34 primarias

**Files:**
- Modify: 23 archivos de vista (lista abajo)

**Interfaces:**
- Consumes: `<x-publico.boton>` de la Task 10.

- [ ] **Step 1: Capturar el estado visual ANTES**

Con el servidor arriba:

```bash
playwright-cli goto http://localhost:8123/ && playwright-cli screenshot antes-inicio.png
```

Repetir para `/afiliate`, `/contacto`, `/directorio`, `/empleo` y `/mi-cuenta`. Guardar las seis capturas.

- [ ] **Step 2: Sustituir las ocho copias idénticas del submit primario**

La cadena `w-full rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600 sm:w-auto` aparece **idéntica** en: `afiliate.blade.php:74`, `contacto.blade.php:50`, `empleo/index.blade.php:141`, `empleo/show.blade.php:110`, `eventos/show.blade.php:143`, `artistas/inscripcion.blade.php:56`, `proveedores/inscripcion.blade.php:37`.

En cada una, reemplazar el `<button ... class="...">Texto</button>` completo por:

```blade
<x-publico.boton class="w-full sm:w-auto">Texto</x-publico.boton>
```

conservando el texto original de cada botón y cualquier atributo que ya tuviera (`name`, `value`, `form`).

- [ ] **Step 3: Sustituir las cuatro copias del primario de enlace**

La cadena `rounded-xl bg-marca-500 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-marca-600` aparece idéntica en `errors/404.blade.php:18`, `mi-cuenta/sesion-equivocada.blade.php:37` y `:58`, y `mi-cuenta/entrar.blade.php:31` (esta con `w-full` delante).

Para las de `<a>`:

```blade
<x-publico.boton :href="route('inicio')">Volver al inicio</x-publico.boton>
```

Para la de `entrar.blade.php:31`, que es `<button>` con `w-full`:

```blade
<x-publico.boton class="w-full">Entrar</x-publico.boton>
```

- [ ] **Step 4: Listar y sustituir las 22 primarias restantes**

Primero obtener la lista exacta en la máquina, que es la única fuente fiable después de que los Steps 2 y 3 hayan movido líneas:

```bash
grep -rn "bg-marca-500" resources/views/publico/ resources/views/errors/ resources/views/components/publico/ | grep -v "x-publico.boton"
```

Cada resultado que sea un `<a>` o un `<button>` de acción se convierte. Confirmadas por el inventario, estas son las que deben aparecer: `inicio.blade.php:26` y `:264`, `afiliate.blade.php:51`, `directorio/index.blade.php:39`, `directorio/show.blade.php:132`, `empleo/index.blade.php:6`, `:38` y `:102`, `mi-cuenta/index.blade.php:78`, `mi-cuenta/vacantes/index.blade.php:15`, `pago/simulado.blade.php:78`, `components/publico/mi-cuenta/formulario-vacante.blade.php:35`, más las de `artistas/index.blade.php`, `proveedores/index.blade.php`, `eventos/show.blade.php` y `guia/index.blade.php` que devuelva el grep.

Descartar de esa lista, porque **no** son botones de acción y quedan fuera por diseño: `navbar.blade.php:51` y `:96` (navbar excluido), `tarjeta-asociado.blade.php` (la insignia «Destacado» es un `<span>`), `artistas/inscripcion.blade.php:46` (pseudo-elemento `file:`), y las ramas activas de los chips y de los controles segmentados (`@class` con dos ramas).

Regla de conversión: el padding, el radio y el tamaño de texto los pone el componente. Solo se conserva vía `class=` lo que sea **maquetación** (`w-full`, `sm:w-auto`, `flex-1`, `shrink-0`, `mt-*`, `text-center`). Todo lo que sea color, borde, `transition` o `hover:` se descarta: ya lo pone el componente.

**Dos trampas que hay que conocer antes de tocar el primer archivo.**

**`block` se traduce a `w-full`, nunca se arrastra.** El componente lleva `inline-block`, y en la hoja compilada de Tailwind `.inline-block` aparece *después* de `.block`. Como tienen la misma especificidad, gana `inline-block` siempre, da igual el orden en que se escriban las clases en el atributo. Un botón migrado con `class="block"` se encoge al ancho de su texto en vez de ocupar la fila, y **nada en el HTML delata el error**. Los que dependen de esto y hay que traducir:

| Archivo | Líneas |
|---|---|
| `publico/directorio/show.blade.php` | 132 |
| `publico/eventos/show.blade.php` | 90, 97 |
| `publico/proveedores/index.blade.php` | 57, 63 |
| `publico/artistas/show.blade.php` | 60, 66 |

**El prop se llama `tipo`, no `type`.** Si escribes `type="button"` por reflejo al traducir un `<button type="button">`, el atributo llega por `$attributes` y convive con el que pone el componente: el navegador se queda con el primero y el botón sigue siendo `submit`, sin ningún error visible. Usa `tipo="button"`. Antes de commitear, comprueba que no se coló ninguno:

```bash
grep -rn 'x-publico.boton[^>]*\stype=' resources/views/
```

Esperado: sin resultados.

**Excepción documentada:** `afiliate.blade.php:51` usa `rounded-lg` y `text-xs` (botón chico de WhatsApp). Se convierte igual y **acepta** el radio y el tamaño del componente: unificarlo es el objetivo. Anotarlo en el mensaje de commit.

**No tocar** en esta tarea: `artistas/inscripcion.blade.php:46` (es un `<input type="file">`; su botón lo pinta el navegador con el pseudo-elemento `file:` y no se puede migrar).

- [ ] **Step 5: Correr la suite completa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact
```

Esperado: sin fallos nuevos respecto a la línea base de la Task 3, Step 2.

- [ ] **Step 6: Capturar el estado visual DESPUÉS y comparar**

```bash
npm run build
```

```bash
playwright-cli goto http://localhost:8123/ && playwright-cli screenshot despues-inicio.png
```

Repetir para las seis rutas del Step 1 y comparar con las capturas de antes. **Diferencias esperadas:** ninguna en color ni tamaño; los 16 botones que no tenían `transition-colors` ahora funden. **Cualquier cambio de tamaño o de posición es una regresión**: revisar qué clase de maquetación se perdió.

- [ ] **Step 7: Commit**

```bash
git add resources/views/
git commit -m "Pasa las 34 primarias al componente y les da el acuse que faltaba"
```

---

## Task 12: Adoptar el botón en las 9 contorno-CTA

**Files:**
- Modify: `errors/404.blade.php:22`, `mi-cuenta/sesion-equivocada.blade.php:46` y `:62`, `empleo/index.blade.php:10`, `quienes-somos.blade.php:224`, `guia/index.blade.php:147`, `mi-cuenta/index.blade.php:20`, `mi-cuenta/vacantes/index.blade.php:80`, `mi-cuenta/vacantes/show.blade.php:66`, `inicio.blade.php:30`

**La trampa del `block` también aplica aquí** si algún CTA migrado la trae: el componente lleva `inline-block`, que en la hoja compilada gana siempre a `block` por orden de aparición, así que `block` se traduce a `w-full`, nunca se arrastra.

*(Corrección de una confusión previa de este plan: el enlace de correo de `proveedores/index.blade.php:63` y el de Instagram de `artistas/show.blade.php:66` llevan `block`, pero son contorno **utilitarios** —`border-linea` sin `-fuerte`, sin `font-semibold`— y pertenecen a los 17 del Step 2 que NO se migran. Al no migrarse, conservan su `block` original y la trampa no les aplica. Solo importará si una tarea futura los mete al componente.)*

Y el prop se llama `tipo`, no `type`: el mismo grep de verificación de la Task 11 aplica antes de commitear.

- [ ] **Step 1: Sustituir los nueve CTA secundarios**

Todos comparten el núcleo `border border-linea-fuerte ... font-semibold ... hover:border-marca-500/50`. Reemplazar cada uno por:

```blade
<x-publico.boton variante="contorno" :href="route('...')">Texto</x-publico.boton>
```

conservando solo las clases de maquetación (`mt-6`, `inline-block`, `px-5 py-2.5` **no**: el padding lo unifica el componente).

- [ ] **Step 2: NO tocar los otros 17 contorno**

Quedan deliberadamente fuera y se documentan aquí para que no parezcan olvidos:

- **Los 10 contorno-utilitarios** (`border-linea` sin `-fuerte`, sin `font-semibold`): los tres «Limpiar» (`directorio/index:44`, `empleo/index:43`, `artistas/index:21`), los enlaces externos de fichas (`directorio/show:145`, `artistas/show:65`, `proveedores/index:62`, `guia/index:119`), `directorio/index:89`, `pago/simulado:81`, `tarjeta-asociado:40` y `panel/cola:43`. Son enlaces utilitarios, no CTA: meterlos con `font-semibold` y `px-6 py-3` los haría gritar.
- **Los 7 chips de filtro** (`@class` con rama activa/inactiva en `boletin/index:9,17`, `proveedores/index:10,18`, `guia/index:15`). Piden un componente propio con prop `:activo`; y la especificación ya rechaza animar filtros que recargan la página.

- [ ] **Step 3: Aplicar `.enlace-accion` a los 26 fantasma**

La clase se creó en la Task 4 y hasta aquí no tiene consumidores. Listar los sitios:

```bash
grep -rn "hover:text-acento-fuerte\|text-apagado hover:text-acento\|text-tenue hover:text-fuerte" resources/views/publico/ resources/views/components/publico/
```

A cada uno **añadir** `enlace-accion` a su `class` existente. No quitar ni cambiar nada más: estos elementos no llevan padding hoy y no deben empezar a llevarlo — dárselo movería texto en 26 sitios. Lo único que hace la clase es unificar el fundido de color, que hoy está en 2 de los 26.

- [ ] **Step 4: Retirar la transición muerta del paginador**

`resources/views/vendor/pagination/tailwind.blade.php:11` pone `transition-colors` en la pastilla base, que heredan las variantes `$inerte` y `$actual` — y ninguna de las dos tiene `hover:`. Es una transición que no puede dispararse nunca.

Quitar `transition-colors` de la clase base y añadirla únicamente a la variante que sí tiene hover (la de los enlaces activos), junto con `duration-(--duracion-boton) ease-color`.

- [ ] **Step 5: Correr la suite**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact
```

Esperado: sin fallos nuevos. Ojo: `Paginator::$defaultView` es estático y Livewire lo reapunta entre pruebas — si alguna prueba de paginación falla de forma rara, necesita `Paginator::useTailwind()` al inicio del método (patrón de `TemaClaroOscuroTest:167`).

- [ ] **Step 6: Commit**

```bash
git add resources/views/
git commit -m "Pasa los nueve CTA al componente y unifica los enlaces de accion"
```

---

## Task 13: La alerta entra, y el error se anuncia como error

**Files:**
- Modify: `resources/views/components/publico/alerta.blade.php`
- Modify: `resources/css/app.css` (`@layer components`)
- Modify: `tests/Feature/MovimientoTest.php`

**Interfaces:**
- Produces: `<x-publico.alerta>` gana las props `tipo` (ya existía) y `animado` (`bool`, por defecto `true`).

- [ ] **Step 1: Escribir la prueba que falla**

Añadir a `tests/Feature/MovimientoTest.php`:

```php
    /**
     * La alerta es el único acuse de recibo del sitio tras enviar un
     * formulario, y aparecía de golpe. Entra por `transition` +
     * `@starting-style` y no por keyframes: así la cubre la mordaza del
     * cambio de tema, que apaga `transition` pero no `animation`.
     *
     * Y de paso: `role="status"` es una región educada, correcta para un
     * acuse pero no para un fallo. Un error necesita `role="alert"`.
     */
    public function test_la_alerta_entra_y_anuncia_el_error_como_error(): void
    {
        $exito = \Illuminate\Support\Facades\Blade::render(
            '<x-publico.alerta>Tu solicitud llegó.</x-publico.alerta>'
        );

        $this->assertStringContainsString('role="status"', $exito);
        $this->assertStringContainsString('alerta-animada', $exito);

        $error = \Illuminate\Support\Facades\Blade::render(
            '<x-publico.alerta tipo="error">No pudimos abrir la pasarela.</x-publico.alerta>'
        );

        $this->assertStringContainsString('role="alert"', $error);

        // El descargo estático de la guía no es un acuse: no debe animarse.
        $estatica = \Illuminate\Support\Facades\Blade::render(
            '<x-publico.alerta tipo="aviso" :animado="false">Texto fijo.</x-publico.alerta>'
        );

        $this->assertStringNotContainsString('alerta-animada', $estatica);
    }
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_la_alerta_entra_y_anuncia_el_error_como_error
```

Esperado: FAIL.

- [ ] **Step 3: Añadir el portador a `app.css`**

Dentro de `@layer components`, después de `.enlace-accion`:

```css

    /*
     * Entrada de la alerta. `@starting-style` y no `@keyframes`: la mordaza
     * del cambio de tema apaga `transition` y no `animation`, así que por
     * esta vía la cobertura sale gratis.
     *
     * Porcentaje y no píxeles: la alerta cambia de alto según el mensaje.
     * Y sin retardo: `role=status`/`role=alert` son regiones vivas, y el
     * anuncio del lector de pantalla no puede esperar a una animación.
     */
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

- [ ] **Step 4: Reescribir el componente**

Reemplazar `resources/views/components/publico/alerta.blade.php` líneas 1 y 16.

La línea 1:

```blade
@props(['tipo' => 'exito'])
```

pasa a:

```blade
@props(['tipo' => 'exito', 'animado' => true])
```

Dentro del bloque `@php` existente, añadir después de `$icono`:

```php
    /*
     * `role="status"` es una región educada: el lector la anuncia cuando
     * termina lo que está diciendo. Para un fallo eso llega tarde — un error
     * necesita `role="alert"`, que interrumpe.
     */
    $rol = $tipo === 'error' ? 'alert' : 'status';
    $movimiento = $animado ? ' alerta-animada' : '';
```

Y la línea 16:

```blade
<div role="status" {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border px-4 py-3.5 text-sm {$estilos}"]) }}>
```

pasa a:

```blade
<div role="{{ $rol }}" {{ $attributes->merge(['class' => "flex items-start gap-3 rounded-xl border px-4 py-3.5 text-sm {$estilos}{$movimiento}"]) }}>
```

- [ ] **Step 5: Marcar la alerta estática de la guía**

En `resources/views/publico/guia/index.blade.php`, líneas 130-133, reemplazar:

```blade
            {{-- Descargo --}}
            <x-publico.alerta tipo="aviso" class="mt-10">
                {{ ajuste('guia_descargo') }}
            </x-publico.alerta>
```

por:

```blade
            {{-- Descargo: texto fijo de la página, no un acuse de nada. --}}
            <x-publico.alerta tipo="aviso" :animado="false" class="mt-10">
                {{ ajuste('guia_descargo') }}
            </x-publico.alerta>
```

- [ ] **Step 6: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/publico/alerta.blade.php resources/views/publico/guia/index.blade.php resources/css/app.css tests/Feature/MovimientoTest.php
git commit -m "Hace entrar la alerta y anunciar el error como error"
```

---

## Task 14: El fallo de pago deja de perderse en silencio

**Files:**
- Modify: `resources/views/publico/mi-cuenta/index.blade.php:26-28`
- Modify: `resources/views/publico/mi-cuenta/vacantes/index.blade.php:24-28`
- Test: `tests/Feature/MiCuentaTest.php` o el que ya cubra `/mi-cuenta` (localizarlo primero)

Verificado en el inventario: `MiCuentaController::pagarMensualidad` (líneas 67-70) redirige con `->with('error', 'No pudimos abrir la pasarela de pago en este momento. Inténtalo de nuevo en unos minutos.')` cuando la pasarela cae, y la vista **solo** tiene `@if (session('exito'))`. El mensaje se genera, se registra en el log y **se pierde en pantalla**.

- [ ] **Step 1: Localizar la prueba existente del área de cuenta**

```bash
grep -rln "mi-cuenta" tests/Feature/
```

- [ ] **Step 2: Escribir la prueba que falla**

Añadir a la clase encontrada (o crear `tests/Feature/MiCuentaTest.php` con el esqueleto estándar y `use RefreshDatabase;`, porque esta sí toca base de datos):

```php
    /**
     * `pagarMensualidad` redirige con `->with('error', ...)` cuando la pasarela
     * cae, pero la vista solo pintaba `session('exito')`: el único mensaje de
     * fallo de pago del sistema se descartaba en silencio y la persona volvía
     * a pulsar sin saber qué había pasado.
     */
    public function test_la_cuenta_pinta_el_error_de_pasarela_caida(): void
    {
        $asociado = \App\Models\Asociado::factory()->create();
        $usuario = \App\Models\User::factory()->create(['asociado_id' => $asociado->id]);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['error' => 'No pudimos abrir la pasarela de pago en este momento.'])
            ->get(route('mi-cuenta.index'));

        $respuesta->assertOk();
        $respuesta->assertSee('No pudimos abrir la pasarela de pago en este momento.');
        $respuesta->assertSee('role="alert"', false);
    }
```

**Nota:** ajustar la creación del usuario y del asociado al patrón exacto de las factories del repositorio — mirar cómo lo hacen las pruebas vecinas antes de escribir esto.

- [ ] **Step 3: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_la_cuenta_pinta_el_error_de_pasarela_caida
```

Esperado: FAIL — el texto no aparece.

- [ ] **Step 4: Pintar el error**

En `resources/views/publico/mi-cuenta/index.blade.php`, después del bloque de las líneas 26-28:

```blade
        @if (session('exito'))
            <x-publico.alerta class="mt-8">{{ session('exito') }}</x-publico.alerta>
        @endif
```

añadir:

```blade
        @if (session('error'))
            <x-publico.alerta tipo="error" class="mt-8">{{ session('error') }}</x-publico.alerta>
        @endif
```

- [ ] **Step 5: Unificar el error hecho a mano de vacantes**

El proyecto tiene dos formas incompatibles de pintar `session('error')`: el componente (`eventos/show.blade.php:127`) y un `<div>` a mano (`mi-cuenta/vacantes/index.blade.php:24-28`). Reemplazar el `<div>` a mano por:

```blade
        @if (session('error'))
            <x-publico.alerta tipo="error" class="mt-8">{{ session('error') }}</x-publico.alerta>
        @endif
```

- [ ] **Step 6: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_la_cuenta_pinta_el_error_de_pasarela_caida
```

Esperado: PASS.

- [ ] **Step 7: Commit**

```bash
git add resources/views/publico/mi-cuenta/ tests/
git commit -m "Deja de descartar en silencio el fallo de la pasarela"
```

---

## Task 15: Estado de envío en los dos botones que cobran

**Files:**
- Modify: `resources/views/publico/mi-cuenta/index.blade.php:75-82` (el formulario de «Pagar ahora»)
- Modify: `resources/views/publico/pago/simulado.blade.php:50-84`
- Modify: `tests/Feature/MovimientoTest.php`

Verificado en el inventario: `pago/simulado.blade.php` **ya tiene** `x-data="{ metodo: 'pse' }"` en el `<form>` (líneas 50-51), así que `enviando` se suma a ese ámbito existente. Los dos botones (`name="decision"` con valores `aprobar` y `rechazar`, líneas 77-84) viven en el **mismo** formulario. El formulario de «Pagar ahora» no tiene `x-data`: hay que introducirlo.

- [ ] **Step 1: Escribir la prueba que falla**

Añadir a `tests/Feature/MovimientoTest.php`:

```php
    /**
     * El servidor ya se protege del doble cobro con la idempotencia de 24 h de
     * `MiCuentaController::cobroVigente`. Lo que faltaba era que la interfaz lo
     * contara: se pulsaba «Pagar ahora», no pasaba nada visible, y se volvía a
     * pulsar. En la pasarela hay que deshabilitar LOS DOS botones, no solo el
     * pulsado: viven en el mismo formulario.
     */
    public function test_los_botones_que_cobran_acusan_el_envio(): void
    {
        $cuenta = File::get(resource_path('views/publico/mi-cuenta/index.blade.php'));

        $this->assertStringContainsString("x-data=\"{ enviando: false }\"", $cuenta);
        $this->assertStringContainsString('x-on:submit="enviando = true"', $cuenta);
        $this->assertStringContainsString('x-bind:disabled="enviando"', $cuenta);

        $pasarela = File::get(resource_path('views/publico/pago/simulado.blade.php'));

        $this->assertStringContainsString('enviando', $pasarela);

        // Los dos botones, no solo el pulsado.
        $this->assertSame(
            2,
            substr_count($pasarela, 'x-bind:disabled="enviando"'),
            'La pasarela debe deshabilitar los dos botones: viven en el mismo formulario.'
        );
    }
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_los_botones_que_cobran_acusan_el_envio
```

Esperado: FAIL.

- [ ] **Step 3: Añadir el estado al formulario de «Pagar ahora»**

En `resources/views/publico/mi-cuenta/index.blade.php`, al `<form>` que envuelve el botón de la línea 78, añadir:

```blade
x-data="{ enviando: false }" x-on:submit="enviando = true"
```

Y al `<x-publico.boton>` que sustituyó al botón en la Task 11:

```blade
x-bind:disabled="enviando"
x-bind:class="enviando && 'opacity-55'"
x-text="enviando ? 'Abriendo la pasarela…' : 'Pagar ahora'"
```

- [ ] **Step 4: Añadir el estado a la pasarela simulada**

En `resources/views/publico/pago/simulado.blade.php`, ampliar el `x-data` existente de la línea 50:

```blade
x-data="{ metodo: 'pse' }"
```

a:

```blade
x-data="{ metodo: 'pse', enviando: false }" x-on:submit="enviando = true"
```

Y añadir a **cada uno** de los dos botones (líneas 78 y 82):

```blade
x-bind:disabled="enviando"
x-bind:class="enviando && 'opacity-55'"
```

- [ ] **Step 5: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php
```

Esperado: PASS.

- [ ] **Step 6: Commit**

```bash
git add resources/views/publico/mi-cuenta/index.blade.php resources/views/publico/pago/simulado.blade.php tests/Feature/MovimientoTest.php
git commit -m "Hace que los botones que cobran cuenten que ya se pulsaron"
```

---

# FASE 3 — Cambios de comportamiento (PR aparte)

## Task 16: El menú móvil sale del flujo

**Files:**
- Modify: `resources/views/components/publico/navbar.blade.php:19-20` y `:84`
- Create: `tests/Feature/MenuMovilTest.php`

- [ ] **Step 1: Escribir la prueba que falla**

Crear `tests/Feature/MenuMovilTest.php`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * El panel móvil vivía EN FLUJO dentro de un `<header sticky>` y se abría con
 * `x-collapse`, o sea interpolando `height` durante 250 ms: cada fotograma
 * reflowaba el documento y empujaba `<main>`.
 *
 * Sacarlo del flujo lo convierte en una capa, y una capa necesita tres
 * salidas que el acordeón no necesitaba. Sin ellas este cambio EMPEORA la
 * accesibilidad en vez de mejorarla, así que se vigilan aquí.
 */
class MenuMovilTest extends TestCase
{
    public function test_el_panel_movil_se_anima_sin_reflowear_el_documento(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        // Fuera la interpolación de altura.
        $this->assertStringNotContainsString('x-collapse', $navbar);

        // Superposición sobre el header, que ya es sticky y establece bloque
        // contenedor. Opaco: el header es translúcido y se vería a través.
        $this->assertStringContainsString('absolute inset-x-0 top-full', $navbar);
        $this->assertStringContainsString('bg-fondo', $navbar);
        $this->assertStringContainsString('overflow-y-auto', $navbar);

        // Solo opacity y transform, con la curva de cajón y salida más rápida.
        $this->assertStringContainsString('duration-(--duracion-panel)', $navbar);
        $this->assertStringContainsString('duration-(--duracion-salida)', $navbar);
        $this->assertStringContainsString('ease-cajon', $navbar);
    }

    public function test_la_capa_tiene_las_tres_salidas_que_un_acordeon_no_necesitaba(): void
    {
        $navbar = File::get(resource_path('views/components/publico/navbar.blade.php'));

        $this->assertStringContainsString('x-on:keydown.escape.window="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:click.outside="menuMovil = false"', $navbar);
        $this->assertStringContainsString('x-on:resize.window', $navbar);
    }
}
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MenuMovilTest.php
```

Esperado: FAIL en las dos.

- [ ] **Step 3: Dar al header las tres salidas**

Reemplazar las líneas 19-20 de `resources/views/components/publico/navbar.blade.php`:

```blade
<header x-data="{ menuMovil: false }"
        class="sticky top-0 z-40 border-b border-linea bg-fondo/85 backdrop-blur-md">
```

por:

```blade
{{-- Las tres salidas van en el <header> y no en el panel: el botón que
     alterna vive dentro del header, y si `click.outside` estuviera en el
     panel el clic del botón lo cerraría y lo abriría en el mismo gesto. --}}
<header x-data="{ menuMovil: false }"
        x-on:keydown.escape.window="menuMovil = false"
        x-on:click.outside="menuMovil = false"
        x-on:resize.window="if (window.innerWidth >= 1024) menuMovil = false"
        class="sticky top-0 z-40 border-b border-linea bg-fondo/85 backdrop-blur-md">
```

- [ ] **Step 4: Sacar el panel del flujo**

Reemplazar la línea 84:

```blade
    <div id="menu-movil" x-show="menuMovil" x-cloak x-collapse class="border-t border-linea bg-fondo lg:hidden">
```

por:

```blade
    <div id="menu-movil"
         x-show="menuMovil"
         x-cloak
         x-transition:enter="transition-[opacity,transform] ease-cajon duration-(--duracion-panel)"
         x-transition:enter-start="opacity-0 translate-y-(--asb-desplazamiento-panel)"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition-[opacity,transform] ease-cajon duration-(--duracion-salida)"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-(--asb-desplazamiento-panel)"
         class="absolute inset-x-0 top-full max-h-[calc(100dvh-4rem)] origin-top overflow-y-auto border-t border-linea bg-fondo shadow-lg lg:hidden">
```

- [ ] **Step 5: Correr y verificar que pasa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MenuMovilTest.php
```

Esperado: PASS.

- [ ] **Step 6: Verificar el comportamiento en el navegador real**

```bash
npm run build
```

```bash
playwright-cli resize 375 812 && playwright-cli goto http://localhost:8123/
```

Abrir el menú y comprobar las tres salidas:

```bash
playwright-cli click "Abrir menú"
```

```bash
playwright-cli eval "Alpine.\$data(document.querySelector('header')).menuMovil" --raw
```

Esperado: `true`.

```bash
playwright-cli press Escape
```

```bash
playwright-cli eval "Alpine.\$data(document.querySelector('header')).menuMovil" --raw
```

Esperado: `false`.

Repetir abriendo el menú y luego:

```bash
playwright-cli resize 1280 800
```

```bash
playwright-cli eval "Alpine.\$data(document.querySelector('header')).menuMovil" --raw
```

Esperado: `false`.

**Y lo que motivó el cambio:** con el menú abierto, comprobar que `<main>` no se mueve.

```bash
playwright-cli eval "document.querySelector('main').getBoundingClientRect().top" --raw
```

Esperado: el mismo valor con el menú abierto y cerrado.

- [ ] **Step 7: Grabar el vídeo de la pieza**

```bash
playwright-cli video-start menu-movil.webm && playwright-cli video-chapter "Menu movil superpuesto"
```

Abrir y cerrar el menú tres veces, luego:

```bash
playwright-cli video-stop
```

- [ ] **Step 8: Commit**

```bash
git add resources/views/components/publico/navbar.blade.php tests/Feature/MenuMovilTest.php
git commit -m "Saca el menu movil del flujo y le da las salidas que le faltaban"
```

---

## Task 17: El cruce del icono hamburguesa

**Files:**
- Modify: `resources/views/components/publico/navbar.blade.php:63-66`

- [ ] **Step 1: Sustituir el corte seco por un cruce**

Reemplazar las líneas 63-66:

```blade
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                <path x-show="! menuMovil" stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                <path x-show="menuMovil" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
```

por:

```blade
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                {{-- Los dos trazos se cruzan girando en sentidos opuestos. El
                     origen es el centro del lienzo de 24×24, no del <path>. --}}
                <path x-show="! menuMovil"
                      x-transition:enter="transition-[opacity,transform] ease-out duration-(--duracion-salida)"
                      x-transition:enter-start="opacity-0 -rotate-90"
                      x-transition:leave="transition-[opacity,transform] ease-out duration-(--duracion-salida)"
                      x-transition:leave-end="opacity-0 -rotate-90"
                      style="transform-origin: 12px 12px"
                      stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>
                <path x-show="menuMovil"
                      x-cloak
                      x-transition:enter="transition-[opacity,transform] ease-out duration-(--duracion-salida)"
                      x-transition:enter-start="opacity-0 rotate-90"
                      x-transition:leave="transition-[opacity,transform] ease-out duration-(--duracion-salida)"
                      x-transition:leave-end="opacity-0 rotate-90"
                      style="transform-origin: 12px 12px"
                      stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
```

- [ ] **Step 2: Verificar que la guardia sigue verde**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact tests/Feature/MovimientoTest.php tests/Feature/MenuMovilTest.php
```

Esperado: PASS.

- [ ] **Step 3: Verificar el giro en el navegador**

```bash
npm run build && playwright-cli resize 375 812 && playwright-cli goto http://localhost:8123/
```

```bash
playwright-cli eval "getComputedStyle(document.querySelectorAll('svg path')[0]).transformOrigin" --raw
```

Esperado: `12px 12px`.

- [ ] **Step 4: Commit**

```bash
git add resources/views/components/publico/navbar.blade.php
git commit -m "Cruza el icono del menu en vez de cortarlo"
```

---

## Task 18: Transiciones de vista entre documentos

**Files:**
- Modify: `resources/css/app.css` (final del archivo)
- Modify: `tests/Feature/MovimientoTest.php`

- [ ] **Step 1: Escribir la prueba que falla**

Añadir a `tests/Feature/MovimientoTest.php`:

```php
    /**
     * En un sitio de recarga completa, filtrar, paginar y abrir un detalle son
     * SIEMPRE navegación: no hay estado de cliente que animar. Las view
     * transitions son la única palanca real, y degradan a nada donde no haya
     * soporte.
     *
     * Viven en un árbol de pseudoelementos aparte, así que el barrido de
     * `*, *::before, *::after` NO las alcanza: necesitan su propia regla de
     * movimiento reducido o quedarían sin guarda.
     */
    public function test_las_transiciones_de_vista_tienen_su_propia_guarda(): void
    {
        $app = File::get(resource_path('css/app.css'));

        $this->assertStringContainsString('@view-transition', $app);
        $this->assertStringContainsString('navigation: auto', $app);
        $this->assertStringContainsString('::view-transition-old(root)', $app);
        $this->assertStringContainsString('animation-timing-function: var(--ease-out)', $app);

        // La guarda propia: el barrido con `*` no llega a estos pseudoelementos.
        $this->assertStringContainsString('::view-transition-group(*)', $app);
        $this->assertStringContainsString('animation: none !important', $app);
    }
```

- [ ] **Step 2: Correr y verificar que falla**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact --filter=test_las_transiciones_de_vista_tienen_su_propia_guarda
```

Esperado: FAIL.

- [ ] **Step 3: Añadir las transiciones de vista a `app.css`**

Al final del archivo, después del bloque de red de seguridad de la Task 2:

```css

/*
 * Transiciones de vista entre documentos.
 *
 * Este sitio recarga la página entera en cada navegación: filtrar, paginar y
 * abrir un detalle no tienen estado de cliente que animar. Esto es la única
 * palanca de movimiento real que queda, y degrada a nada donde no haya
 * soporte, así que el riesgo de regresión es cero.
 */
@view-transition {
    navigation: auto;
}

::view-transition-old(root),
::view-transition-new(root) {
    animation-duration: 180ms;
    animation-timing-function: var(--ease-out);
}

/*
 * Guarda propia, y no es redundante: los pseudoelementos de view transition
 * viven en un árbol aparte que `*, *::before, *::after` NO alcanza. Sin este
 * bloque, quien pidió menos movimiento seguiría viendo el fundido.
 */
@media (prefers-reduced-motion: reduce) {
    ::view-transition-group(*),
    ::view-transition-old(*),
    ::view-transition-new(*) {
        animation: none !important;
    }
}
```

- [ ] **Step 4: Emparejar la portada del listado con la de la ficha**

El fundido de raíz es genérico. Lo que hace que la navegación se sienta continua es que la foto de la tarjeta **sea** la foto de la ficha, y eso se consigue dándoles el mismo nombre en los dos documentos.

En `resources/views/components/publico/tarjeta-asociado.blade.php`, añadir al `<img>` de la línea 10:

```blade
style="view-transition-name: portada-{{ $asociado->id }}"
```

Y en `resources/views/publico/directorio/show.blade.php`, al `<img>` de la portada del establecimiento, el mismo atributo con el mismo `$asociado->id`.

El nombre debe ser **único dentro de cada documento**: en el listado lo garantiza el `id` de cada asociado, y en la ficha solo hay una portada. Si dos elementos comparten nombre en el mismo documento, el navegador descarta la transición entera en silencio.

- [ ] **Step 5: Verificar que los nombres no colisionan**

```bash
playwright-cli goto http://localhost:8123/directorio
```

```bash
playwright-cli eval "const n=[...document.querySelectorAll('[style*=view-transition-name]')].map(e=>e.style.viewTransitionName); return n.length + ' nombres, ' + new Set(n).size + ' unicos'" --raw
```

Esperado: los dos números iguales. Si difieren hay colisión y la transición no ocurrirá.

- [ ] **Step 6: Correr la suite completa**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact
```

Esperado: sin fallos nuevos.

- [ ] **Step 7: Verificar el fundido y su guarda en el navegador**

```bash
npm run build && playwright-cli goto http://localhost:8123/directorio
```

```bash
playwright-cli run-code "await page.emulateMedia({ reducedMotion: 'reduce' }); const v = await page.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--asb-levante')); return v"
```

Esperado: `0px`. Con esto queda probada la precisión del interruptor: el desplazamiento se anula mientras las duraciones siguen vivas.

```bash
playwright-cli video-start transiciones.webm && playwright-cli video-chapter "Navegacion listado a detalle"
```

Navegar del directorio a una ficha y volver, luego:

```bash
playwright-cli video-stop
```

- [ ] **Step 6: Commit**

```bash
git add resources/css/app.css tests/Feature/MovimientoTest.php
git commit -m "Anade las transiciones de vista y la guarda que el barrido no alcanza"
```

---

# Cierre

- [ ] **Correr la suite completa y comparar con la línea base**

```bash
"C:/Users/Predator/.config/php85/php.exe" artisan test --compact
```

- [ ] **Formatear**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Grabar el vídeo completo de las doce piezas** para que el dueño juzgue lo único que ninguna aserción responde: si se siente bien.

```bash
playwright-cli video-start rework-movimiento.webm
```

Recorrer: portada → directorio (hover de tarjeta) → ficha → menú de sesión → menú móvil a 375 px → formulario de contacto (envío y alerta) → `/mi-cuenta`. Luego:

```bash
playwright-cli video-stop
```

## Deuda anotada, para resolver con aprobación explícita

- **`@alpinejs/collapse` queda muerta** tras la Task 16: `navbar.blade.php:84` era su único consumidor en todo el repositorio, y `resources/js/app.js` la seguirá importando y registrando en sus líneas 2 y 4. Este plan no la desinstala porque `package.json` no se toca.
- **Los 7 chips de filtro** piden un componente propio con prop `:activo`. La cadena `@class` está repetida idéntica cuatro veces entre `boletin/index` y `proveedores/index`.
- **Los 2 controles segmentados** (`directorio/index:64` «Tarjetas/Mapa» y `eventos/index:11` «Próximos/Pasados») mezclan clases fantasma y primaria en el mismo elemento. Son un control segmentado, no botones.
- **`artistas/inscripcion:46`** pinta un botón primario completo con el pseudo-elemento `file:` de un `<input type="file">`. Unificarlo requiere una utilidad CSS aplicada con prefijo `file:`, no el componente.
- **Los dos «Afíliate» del navbar** (líneas 51 y 96) son el único `bg-marca-500` del sitio con `rounded-lg` en vez de `rounded-xl`, y el de móvil no tiene ni hover ni transición. Quedaron fuera por la exclusión del navbar.
- **`empleo/show:35`** usa la paleta acento para una miga «← volver» donde sus tres gemelas (`eventos/show:31`, `artistas/show:6`, `boletin/show:33`) usan `text-apagado hover:text-acento`. Incoherencia real, sin resolver.
