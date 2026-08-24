# Vigencia y procedencia de la guía normativa (RF-60) — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Que cada trámite de la guía normativa diga contra qué fuente y en qué fecha se verificó, y que los decretos transitorios desaparezcan del sitio al vencer.

**Architecture:** Tres columnas nuevas en `requisitos_apertura`, con comportamiento asimétrico: `verificado_el`/`verificado_con` son informativos y nunca despublican nada; `vigente_hasta` sí saca la ficha del sitio al vencer, mediante un scope `vigente()` que se **compone** con el `publicado()` existente y que hay que aplicar en cuatro puertas distintas. El panel gana los campos y dos filtros; la vista pública gana tres marcas.

**Tech Stack:** PHP 8.5, Laravel 13, Filament 4, Livewire 3, PHPUnit 12, SQLite en pruebas, Tailwind 4.

**Spec:** [`docs/superpowers/specs/2026-08-24-vigencia-de-la-guia-normativa-design.md`](../specs/2026-08-24-vigencia-de-la-guia-normativa-design.md)

## Global Constraints

- **Identificadores, comentarios y textos en español.** Gobierna la convención existente del código, no `CLAUDE.md`.
- **Scopes al estilo del proyecto:** `public function scopeVigente(Builder $query): Builder`. El proyecto **no** usa el atributo `#[Scope]`.
- **Cero hexadecimales en las vistas.** Sólo tokens semánticos (`text-apagado`, `text-aviso-suave`, `border-aviso-linea`…). Hay una prueba de guardia que lo vigila.
- **Formato de fecha en vistas:** `translatedFormat('d \d\e F \d\e Y')`, que es la forma dominante del proyecto (6 usos). Funciona porque `APP_LOCALE=es` en `.env` y en `.env.example`.
- **Zona horaria:** `America/Bogota`. `now()` no adelanta vencimientos.
- **`MESES_HASTA_REVISION = 12`, borde estricto:** a los doce meses exactos **todavía no** necesita revisión; la necesita al día siguiente.
- **`vigente_hasta` incluye su último día.** El scope compara `>=`, nunca `>`.
- **No se toca `database/seeders/RequisitoAperturaSeeder.php`.** Es del bloque de la Persona 2.
- **Pint obligatorio:** `vendor/bin/pint --dirty --format agent` antes de cada commit.
- **Toda prueba nueva pasa el filtro de mutación:** romper el código a propósito y comprobar que la prueba se entera. Cinco defectos de este proyecto fueron pruebas en falso verde.

---

## File Structure

| Archivo | Responsabilidad | Acción |
|---|---|---|
| `database/migrations/…_agregar_la_vigencia_a_los_requisitos_de_apertura.php` | Las tres columnas y su índice | Crear |
| `app/Models/RequisitoApertura.php` | Casts, la constante, los cuatro predicados, el scope, la bitácora | Modificar |
| `database/factories/RequisitoAperturaFactory.php` | Estados `verificado()`, `transitorio()`, `caducado()` | Modificar |
| `app/Http/Controllers/Publico/GuiaController.php` | Tres de las cuatro puertas | Modificar |
| `app/Http/Controllers/SitemapController.php` | La cuarta puerta | Modificar |
| `app/Filament/Resources/RequisitoAperturas/Schemas/RequisitoAperturaForm.php` | Sección «Verificación y vigencia» | Modificar |
| `app/Filament/Resources/RequisitoAperturas/Tables/RequisitoAperturasTable.php` | Columnas con color y dos filtros | Modificar |
| `resources/views/publico/guia/index.blade.php` | Las tres marcas y la procedencia | Modificar |
| `tests/Feature/VigenciaDeLaGuiaTest.php` | Esquema, predicados, scope, las cuatro puertas, la vista | Crear |
| `tests/Feature/Panel/VigenciaEnElPanelTest.php` | Formulario, filtros, la guarda del flujo de aprobación | Crear |
| `docs/ingenieria/guia-normativa-armenia-fuente-oficial.md` | Los siete trámites, ya fechados | Modificar |
| `docs/ingenieria/matriz-de-pruebas.md` | RF-60 de ❌ a ✅ y las cifras | Modificar |

---

## Task 1: Esquema, casts, factory y bitácora

**Files:**
- Create: `database/migrations/<timestamp>_agregar_la_vigencia_a_los_requisitos_de_apertura.php`
- Modify: `app/Models/RequisitoApertura.php`
- Modify: `database/factories/RequisitoAperturaFactory.php`
- Test: `tests/Feature/VigenciaDeLaGuiaTest.php`

**Interfaces:**
- Consumes: nada.
- Produces: las columnas `verificado_el` (date, nullable), `verificado_con` (string, nullable), `vigente_hasta` (date, nullable); los estados de factory `verificado(?string $fecha = null)`, `transitorio(?string $hasta = null)`, `caducado()`.

- [ ] **Step 1: Crear el fichero de migración**

```bash
php artisan make:migration agregar_la_vigencia_a_los_requisitos_de_apertura --no-interaction
```

- [ ] **Step 2: Escribir la prueba que falla**

Crear `tests/Feature/VigenciaDeLaGuiaTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\RequisitoApertura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * RF-60: normativa vigente y decretos transitorios por municipio.
 *
 * La guía es el producto insignia y es información que alguien usa para
 * decidir si abre un negocio. Sin procedencia ni fecha, un trámite que
 * inventó el sembrador se lee igual que uno verificado contra la Alcaldía.
 */
class VigenciaDeLaGuiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_tabla_gana_las_tres_columnas_de_procedencia(): void
    {
        $columnas = Schema::getColumnListing('requisitos_apertura');

        $this->assertContains('verificado_el', $columnas);
        $this->assertContains('verificado_con', $columnas);
        $this->assertContains('vigente_hasta', $columnas);
    }

    public function test_las_dos_fechas_llegan_al_modelo_como_fechas_y_no_como_texto(): void
    {
        $requisito = RequisitoApertura::factory()->create([
            'verificado_el' => '2026-08-20',
            'vigente_hasta' => '2026-11-30',
        ]);

        $requisito->refresh();

        $this->assertInstanceOf(Carbon::class, $requisito->verificado_el);
        $this->assertInstanceOf(Carbon::class, $requisito->vigente_hasta);
        $this->assertSame('2026-08-20', $requisito->verificado_el->toDateString());
    }

    public function test_los_tres_campos_nacen_vacios_porque_nadie_ha_verificado_nada(): void
    {
        $requisito = RequisitoApertura::factory()->create();

        $this->assertNull($requisito->verificado_el);
        $this->assertNull($requisito->verificado_con);
        $this->assertNull($requisito->vigente_hasta);
    }

    public function test_la_bitacora_registra_quien_cambia_una_fecha_de_verificacion(): void
    {
        // Declarar «verifiqué esto contra la Alcaldía» es una afirmación de
        // autoridad sobre información legal: tiene que quedar rastro.
        $registrados = (new RequisitoApertura)->getActivitylogOptions()->logAttributes;

        $this->assertContains('verificado_el', $registrados);
        $this->assertContains('verificado_con', $registrados);
        $this->assertContains('vigente_hasta', $registrados);
    }

    public function test_los_estados_de_factory_producen_lo_que_prometen(): void
    {
        $this->assertNotNull(RequisitoApertura::factory()->verificado()->create()->verificado_el);
        $this->assertTrue(RequisitoApertura::factory()->caducado()->create()->vigente_hasta->isPast());
        $this->assertTrue(RequisitoApertura::factory()->transitorio()->create()->vigente_hasta->isFuture());
    }
}
```

- [ ] **Step 3: Correr la prueba y comprobar que falla**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: FAIL — «Failed asserting that an array contains 'verificado_el'» y `Call to undefined method …verificado()`.

- [ ] **Step 4: Escribir la migración**

En el fichero creado en el Step 1:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La guía normativa gana procedencia y caducidad (RF-60).
 *
 * Hasta ahora la tabla decía qué pide cada entidad y cuánto cuesta, pero no
 * cuándo se comprobó ni contra qué. Los 18 registros que existen los escribió
 * el sembrador durante la construcción del demo, y un lector no tiene forma de
 * distinguirlos de los que sí vienen del documento oficial de la Alcaldía.
 *
 * Las tres columnas NO son la misma cosa, y por eso se comportan distinto:
 *
 * - `verificado_el` y `verificado_con` son **informativos**. Nunca despublican
 *   nada. Alimentan un filtro de trabajo en el panel y una marca honesta en el
 *   sitio: quien no tenga fecha lo dice en su cara.
 * - `vigente_hasta` sí **saca la ficha del sitio** al vencer, y existe sólo
 *   para lo que de verdad caduca: un decreto transitorio, una restricción por
 *   temporada. Vacío significa permanente, que es el caso normal.
 *
 * `date` y no `timestamp`: verificar es un acto con día, no con instante — la
 * Alcaldía y la Cámara fechan por día. Contrasta a propósito con
 * `autorizacion_datos_at`, que sí es `timestamp` porque la Ley 1581 pregunta
 * el momento exacto del consentimiento. Dos preguntas distintas, dos tipos.
 *
 * El índice va sólo en `vigente_hasta`: entra en la consulta pública en cada
 * visita a la guía y en cada generación del sitemap. `verificado_el` sólo se
 * filtra desde el panel, sobre decenas de filas.
 *
 * Sin relleno retroactivo, y esa ausencia es la decisión: inventarle una fecha
 * plausible a contenido que nadie verificó destruiría el mecanismo el mismo
 * día que se construye.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisitos_apertura', function (Blueprint $table): void {
            $table->date('verificado_el')->nullable()->after('estado');
            $table->string('verificado_con')->nullable()->after('verificado_el');
            $table->date('vigente_hasta')->nullable()->after('verificado_con');

            $table->index('vigente_hasta');
        });
    }

    public function down(): void
    {
        Schema::table('requisitos_apertura', function (Blueprint $table): void {
            $table->dropIndex(['vigente_hasta']);
            $table->dropColumn(['verificado_el', 'verificado_con', 'vigente_hasta']);
        });
    }
};
```

> ⚠️ En SQLite `after()` es decorativo: las columnas aterrizan al final de la tabla. Es inocuo y ya pasó con la ficha comercial del asociado. No lo leas como fallo de la migración.

- [ ] **Step 5: Añadir los casts y la bitácora al modelo**

En `app/Models/RequisitoApertura.php`, dentro de `casts()`:

```php
    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'checklist' => 'array',
            'costo_aproximado' => 'decimal:2',
            'verificado_el' => 'date',
            'vigente_hasta' => 'date',
        ];
    }
```

Y en `getActivitylogOptions()`, sustituir la línea del `logOnly`:

```php
            ->logOnly([
                'entidad', 'estado', 'municipio_id', 'costo_aproximado',
                // Cambiar una fecha de verificación es afirmar autoridad sobre
                // información legal: tiene que quedar quién y cuándo.
                'verificado_el', 'verificado_con', 'vigente_hasta',
            ])
```

- [ ] **Step 6: Añadir los tres estados a la factory**

En `database/factories/RequisitoAperturaFactory.php`, después de `publicado()`:

```php
    public function verificado(?string $fecha = null): self
    {
        return $this->state(fn (array $attributes) => [
            'verificado_el' => $fecha ?? now()->toDateString(),
            'verificado_con' => 'Documento oficial entregado por la entidad',
        ]);
    }

    /** Un decreto transitorio: caduca, pero todavía no. */
    public function transitorio(?string $hasta = null): self
    {
        return $this->state(fn (array $attributes) => [
            'vigente_hasta' => $hasta ?? now()->addMonth()->toDateString(),
        ]);
    }

    public function caducado(): self
    {
        return $this->state(fn (array $attributes) => [
            'vigente_hasta' => now()->subDay()->toDateString(),
        ]);
    }
```

- [ ] **Step 7: Correr la prueba y comprobar que pasa**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: PASS — 5 casos.

- [ ] **Step 8: Aplicar la migración a la base local y formatear**

```bash
php artisan migrate --no-interaction
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 9: Commit**

```bash
git add database/migrations app/Models/RequisitoApertura.php database/factories/RequisitoAperturaFactory.php tests/Feature/VigenciaDeLaGuiaTest.php
git commit -m "Le da a la guia normativa donde guardar su procedencia" -m "La tabla decia que pide cada entidad y cuanto cuesta, pero no cuando se comprobo ni contra que. Los 18 registros que existen los escribio el sembrador y un lector no puede distinguirlos del documento oficial de la Alcaldia que llego el 20 de agosto." -m "Las tres columnas no son la misma cosa. verificado_el y verificado_con son informativos y no despublican nada. vigente_hasta si saca la ficha del sitio al vencer, y existe solo para lo que de verdad caduca. Vacio significa permanente, que es el caso normal." -m "date y no timestamp: verificar es un acto con dia. Contrasta con autorizacion_datos_at, que si es timestamp porque la Ley 1581 pregunta el momento exacto. Dos preguntas distintas." -m "Sin relleno retroactivo de los 18, y esa ausencia es la decision: inventarles una fecha plausible destruiria el mecanismo el mismo dia que se construye." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 2: Los cuatro predicados y el umbral

**Files:**
- Modify: `app/Models/RequisitoApertura.php`
- Test: `tests/Feature/VigenciaDeLaGuiaTest.php`

**Interfaces:**
- Consumes: las columnas y los estados de factory de la Task 1.
- Produces: `RequisitoApertura::MESES_HASTA_REVISION` (int, 12); `estaVerificado(): bool`; `necesitaRevision(): bool`; `esTransitorio(): bool`; `haCaducado(): bool`.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añadir a `tests/Feature/VigenciaDeLaGuiaTest.php`:

```php
    public function test_esta_verificado_solo_si_hay_fecha(): void
    {
        $this->assertFalse(RequisitoApertura::factory()->create()->estaVerificado());
        $this->assertTrue(RequisitoApertura::factory()->verificado()->create()->estaVerificado());
    }

    /**
     * El borde es estricto y por eso tiene tres casos y no dos: «más de un
     * año» y «un año o más» son reglas distintas y se leen igual en prosa.
     */
    public function test_el_umbral_de_revision_cuenta_desde_el_dia_siguiente_al_ano(): void
    {
        $meses = RequisitoApertura::MESES_HASTA_REVISION;

        $reciente = RequisitoApertura::factory()
            ->verificado(now()->subMonths($meses)->addDay()->toDateString())->create();
        $justoEnElBorde = RequisitoApertura::factory()
            ->verificado(now()->subMonths($meses)->toDateString())->create();
        $pasado = RequisitoApertura::factory()
            ->verificado(now()->subMonths($meses)->subDay()->toDateString())->create();

        $this->assertFalse($reciente->necesitaRevision(), 'Once meses no es revisión pendiente.');
        $this->assertFalse($justoEnElBorde->necesitaRevision(), 'A los doce meses exactos todavía sirve.');
        $this->assertTrue($pasado->necesitaRevision(), 'Al día siguiente del año, sí.');
    }

    public function test_lo_que_nadie_verifico_nunca_tambien_necesita_revision(): void
    {
        // No son el mismo estado para el lector, pero son la misma pila de
        // trabajo para la oficina, y el filtro del panel ataca esa pila.
        $this->assertTrue(RequisitoApertura::factory()->create()->necesitaRevision());
    }

    public function test_es_transitorio_solo_lo_que_tiene_fecha_de_vencimiento(): void
    {
        $this->assertFalse(RequisitoApertura::factory()->create()->esTransitorio());
        $this->assertTrue(RequisitoApertura::factory()->transitorio()->create()->esTransitorio());
    }

    /** «Vigente hasta el 30 de noviembre» incluye el 30. */
    public function test_un_decreto_vive_hasta_el_final_de_su_ultimo_dia(): void
    {
        $permanente = RequisitoApertura::factory()->create();
        $hoyEsSuUltimoDia = RequisitoApertura::factory()->transitorio(now()->toDateString())->create();
        $vencioAyer = RequisitoApertura::factory()->caducado()->create();

        $this->assertFalse($permanente->haCaducado(), 'Sin fecha no caduca nunca.');
        $this->assertFalse($hoyEsSuUltimoDia->haCaducado(), 'El último día todavía cuenta.');
        $this->assertTrue($vencioAyer->haCaducado());
    }
```

- [ ] **Step 2: Correr y comprobar que fallan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: FAIL — `Call to undefined method App\Models\RequisitoApertura::estaVerificado()`.

- [ ] **Step 3: Escribir la constante y los cuatro predicados**

En `app/Models/RequisitoApertura.php`, después de `protected $guarded`:

```php
    /**
     * Cuánto dura la tranquilidad de una verificación.
     *
     * Doce meses porque los trámites de apertura se mueven al ritmo de los
     * acuerdos municipales y de las tarifas anuales —la matrícula mercantil se
     * renueva antes del 31 de marzo de cada año—, así que un año es el ciclo
     * natural en el que algo cambia sin que nadie avise. No es una norma: es
     * criterio del gremio, y por eso vive aquí con su razón al lado y no en un
     * ajuste que nadie va a mirar.
     */
    public const int MESES_HASTA_REVISION = 12;
```

Y junto a `tieneAdjunto()` / `tieneCosto()`:

```php
    public function estaVerificado(): bool
    {
        return $this->verificado_el !== null;
    }

    /**
     * La pila de trabajo de la oficina: lo que nadie verificó nunca y lo que
     * se verificó hace demasiado. Son dos estados distintos para el lector
     * —la vista los distingue— pero el mismo trabajo pendiente.
     *
     * El borde es estricto: a los doce meses exactos todavía sirve; al día
     * siguiente, no.
     */
    public function necesitaRevision(): bool
    {
        if ($this->verificado_el === null) {
            return true;
        }

        return $this->verificado_el->startOfDay()->lt(
            now()->subMonths(self::MESES_HASTA_REVISION)->startOfDay()
        );
    }

    /** Un decreto con fecha de muerte. Lo normal es que un trámite no la tenga. */
    public function esTransitorio(): bool
    {
        return $this->vigente_hasta !== null;
    }

    /** «Vigente hasta el 30 de noviembre» incluye el 30: la comparación es estricta contra ayer. */
    public function haCaducado(): bool
    {
        return $this->vigente_hasta !== null
            && $this->vigente_hasta->startOfDay()->lt(now()->startOfDay());
    }
```

- [ ] **Step 4: Correr y comprobar que pasan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: PASS — 10 casos.

- [ ] **Step 5: Mutar a propósito y comprobar que la prueba se entera**

Cambiar temporalmente `lt(` por `lte(` en `haCaducado()`.
Run: `php artisan test --compact --filter=test_un_decreto_vive_hasta_el_final_de_su_ultimo_dia`
Expected: FAIL. **Revertir la mutación** y volver a correr: PASS.

- [ ] **Step 6: Formatear y commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/RequisitoApertura.php tests/Feature/VigenciaDeLaGuiaTest.php
git commit -m "Ensena al requisito a decir si esta rancio o si ya vencio" -m "Cuatro predicados y una constante con su razon escrita al lado. Doce meses porque los tramites de apertura se mueven al ritmo de los acuerdos municipales y de las tarifas anuales -la matricula mercantil se renueva antes del 31 de marzo-, asi que un año es el ciclo en el que algo cambia sin que nadie avise." -m "necesitaRevision junta a proposito lo que nadie verifico nunca con lo que se verifico hace demasiado. No son el mismo estado para el lector, pero son la misma pila de trabajo para la oficina." -m "Dos bordes que solo se descubren en produccion, y por eso tienen caso propio: a los doce meses exactos todavia sirve -la revision empieza al dia siguiente-, y un decreto vigente hasta el 30 de noviembre incluye el 30." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 3: El scope `vigente()`

**Files:**
- Modify: `app/Models/RequisitoApertura.php`
- Test: `tests/Feature/VigenciaDeLaGuiaTest.php`

**Interfaces:**
- Consumes: la columna `vigente_hasta` de la Task 1.
- Produces: `RequisitoApertura::vigente()` — scope que se **compone** con `publicado()` del trait `EsPublicable`.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añadir a `tests/Feature/VigenciaDeLaGuiaTest.php` (y `use App\Enums\EstadoPublicacion;` arriba):

```php
    public function test_el_scope_deja_pasar_lo_permanente_y_lo_que_aun_no_vence(): void
    {
        $permanente = RequisitoApertura::factory()->create();
        $futuro = RequisitoApertura::factory()->transitorio()->create();
        $hoy = RequisitoApertura::factory()->transitorio(now()->toDateString())->create();
        $vencido = RequisitoApertura::factory()->caducado()->create();

        $vigentes = RequisitoApertura::vigente()->pluck('id');

        $this->assertContains($permanente->id, $vigentes);
        $this->assertContains($futuro->id, $vigentes);
        $this->assertContains($hoy->id, $vigentes, 'El último día todavía cuenta.');
        $this->assertNotContains($vencido->id, $vigentes);
    }

    /**
     * El paréntesis del scope no es cosmético. Sin el grupo, el `orWhere` se
     * suelta y anula el `publicado()` que lo precede: la guía empezaría a
     * servir borradores. Esta prueba es la única que lo nota.
     */
    public function test_el_scope_no_anula_el_publicado_que_lo_precede(): void
    {
        $borradorPermanente = RequisitoApertura::factory()->create([
            'estado' => EstadoPublicacion::Borrador,
        ]);

        $ids = RequisitoApertura::publicado()->vigente()->pluck('id');

        $this->assertNotContains(
            $borradorPermanente->id,
            $ids,
            'Un borrador sin fecha de vencimiento NO puede colarse por el orWhere.'
        );
    }
```

- [ ] **Step 2: Correr y comprobar que fallan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: FAIL — `Call to undefined method …::vigente()`.

- [ ] **Step 3: Escribir el scope**

En `app/Models/RequisitoApertura.php`, junto a los predicados. Añadir `use Illuminate\Database\Eloquent\Builder;` a los imports:

```php
    /**
     * Lo que el sitio puede mostrar hoy: lo permanente y lo que aún no vence.
     *
     * Se compone con `publicado()` en vez de meterse dentro de él, porque el
     * panel y el observer usan `publicado()` y ahí un decreto vencido sí tiene
     * que seguir viéndose: alguien tiene que poder renovarlo.
     *
     * ⚠️ El `where` con cierre agrupa el `orWhere`. Sin el grupo, el `orWhere`
     * se suelta y anula el `publicado()` que lo precede, y la guía empieza a
     * servir borradores.
     */
    public function scopeVigente(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull('vigente_hasta')
            ->orWhere('vigente_hasta', '>=', now()->toDateString()));
    }
```

- [ ] **Step 4: Correr y comprobar que pasan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: PASS — 12 casos.

- [ ] **Step 5: Mutar el paréntesis y comprobar que la prueba se entera**

Sustituir temporalmente el cuerpo por la versión sin grupo:

```php
return $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>=', now()->toDateString());
```

Run: `php artisan test --compact --filter=test_el_scope_no_anula_el_publicado_que_lo_precede`
Expected: FAIL. **Revertir** y volver a correr: PASS.

- [ ] **Step 6: Formatear y commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/RequisitoApertura.php tests/Feature/VigenciaDeLaGuiaTest.php
git commit -m "Anade el scope que separa lo vigente de lo que ya vencio" -m "Se compone con publicado() en vez de meterse dentro, porque el panel y el observer usan publicado() y ahi un decreto vencido si tiene que seguir viendose: alguien tiene que poder renovarlo." -m "El parentesis que agrupa el orWhere no es cosmetico. Sin el, el orWhere se suelta y anula el publicado() que lo precede, y la guia empieza a servir borradores. Tiene prueba propia porque es el unico sitio donde se nota, y se verifico mutandolo." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 4: Las cuatro puertas

La tarea con consecuencia de seguridad. Un control que vive en la vista y no en la puerta es la misma familia de agujero que el §8.3 del runbook encontró con la política del bucket.

**Files:**
- Modify: `app/Http/Controllers/Publico/GuiaController.php`
- Modify: `app/Http/Controllers/SitemapController.php:67`
- Test: `tests/Feature/VigenciaDeLaGuiaTest.php`

**Interfaces:**
- Consumes: `scopeVigente()` y `haCaducado()` de las Tasks 2 y 3.
- Produces: nada que otras tareas consuman.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añadir a `tests/Feature/VigenciaDeLaGuiaTest.php` (imports nuevos: `App\Models\Municipio`, `Illuminate\Support\Facades\Storage`):

```php
    private function requisitoPublicado(Municipio $municipio, array $atributos = []): RequisitoApertura
    {
        return RequisitoApertura::factory()->publicado()->create(
            array_merge(['municipio_id' => $municipio->id], $atributos)
        );
    }

    public function test_un_decreto_vencido_no_aparece_en_la_guia(): void
    {
        $municipio = Municipio::factory()->create();
        $vivo = $this->requisitoPublicado($municipio, ['entidad' => 'Cuerpo de Bomberos']);
        $vencido = $this->requisitoPublicado($municipio, [
            'entidad' => 'Decreto de restricción horaria',
            'vigente_hasta' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee($vivo->entidad)
            ->assertDontSee($vencido->entidad);
    }

    public function test_un_municipio_cuya_guia_entera_vencio_no_sale_en_el_selector(): void
    {
        $vivo = Municipio::factory()->create(['nombre' => 'Armenia']);
        $apagado = Municipio::factory()->create(['nombre' => 'Pijao']);

        $this->requisitoPublicado($vivo);
        $this->requisitoPublicado($apagado, ['vigente_hasta' => now()->subDay()->toDateString()]);

        $respuesta = $this->get(route('guia.index'))->assertSuccessful();

        $respuesta->assertSee('Armenia');
        $respuesta->assertDontSee(route('guia.index', ['municipio' => $apagado->slug]), escape: false);
    }

    /**
     * La puerta que importa. Comprobar la caducidad sólo en la vista dejaría
     * el PDF del decreto vencido descargable por URL directa — el mismo
     * agujero que el §8.3 del runbook encontró en la política del bucket.
     */
    public function test_el_formato_de_un_decreto_vencido_no_se_puede_descargar(): void
    {
        Storage::fake(config('almacenamiento.privado'));
        Storage::disk(config('almacenamiento.privado'))->put('formatos/decreto.pdf', '%PDF-1.4');

        $municipio = Municipio::factory()->create();
        $vencido = $this->requisitoPublicado($municipio, [
            'adjunto' => 'formatos/decreto.pdf',
            'adjunto_nombre' => 'Decreto de restricción horaria',
            'vigente_hasta' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('guia.formato', $vencido))->assertNotFound();
    }

    public function test_el_formato_de_un_tramite_vigente_si_se_descarga(): void
    {
        // Contraprueba: sin ella, una descarga rota pasaría la prueba anterior.
        Storage::fake(config('almacenamiento.privado'));
        Storage::disk(config('almacenamiento.privado'))->put('formatos/bomberos.pdf', '%PDF-1.4');

        $municipio = Municipio::factory()->create();
        $vivo = $this->requisitoPublicado($municipio, [
            'adjunto' => 'formatos/bomberos.pdf',
            'adjunto_nombre' => 'Solicitud de visita',
        ]);

        $this->get(route('guia.formato', $vivo))->assertSuccessful();
    }

    public function test_el_sitemap_no_anuncia_un_municipio_cuya_guia_vencio(): void
    {
        $vivo = Municipio::factory()->create();
        $apagado = Municipio::factory()->create();

        $this->requisitoPublicado($vivo);
        $this->requisitoPublicado($apagado, ['vigente_hasta' => now()->subDay()->toDateString()]);

        $respuesta = $this->get('/sitemap.xml')->assertSuccessful();

        $respuesta->assertSee(route('guia.index', ['municipio' => $vivo->slug]), escape: false);
        $respuesta->assertDontSee(route('guia.index', ['municipio' => $apagado->slug]), escape: false);
    }
```

- [ ] **Step 2: Correr y comprobar que fallan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: FAIL en las cinco — el vencido se ve, el municipio apagado sale, y la descarga responde 200.

- [ ] **Step 3: Cerrar las tres puertas de `GuiaController`**

En `app/Http/Controllers/Publico/GuiaController.php`, método `index()`:

```php
        // Sólo se ofrecen municipios que ya tienen la guía levantada Y vigente:
        // uno cuyos trámites hayan caducado todos saldría en el selector con la
        // guía vacía.
        $municipiosConGuia = Municipio::whereHas('requisitos', fn ($q) => $q->publicado()->vigente())
            ->orderBy('nombre')
            ->get();
```

```php
        $requisitos = $seleccionado
            ? RequisitoApertura::publicado()
                ->vigente()
                ->where('municipio_id', $seleccionado->id)
                ->orderBy('orden')
                ->get()
            : collect();
```

Y en `descargarFormato()`, sustituir la primera guarda:

```php
        // La caducidad se comprueba AQUÍ y no sólo en la vista: los formatos
        // viven en el disco privado justamente para que esta sea la única
        // puerta, y un decreto vencido con PDF descargable por URL directa
        // sería el mismo agujero del §8.3 del runbook.
        abort_unless(
            $requisito->estaPublicado() && ! $requisito->haCaducado() && $requisito->tieneAdjunto(),
            404
        );
```

- [ ] **Step 4: Cerrar la cuarta puerta en el sitemap**

En `app/Http/Controllers/SitemapController.php`, línea 67:

```php
        // La guía por municipio son URLs distintas y de mucho valor para SEO.
        // Con `vigente()`, porque anunciarle a Google una guía vacía es peor
        // que no anunciarla.
        Municipio::whereHas('requisitos', fn ($q) => $q->publicado()->vigente())
```

- [ ] **Step 5: Correr y comprobar que pasan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: PASS — 17 casos.

- [ ] **Step 6: Mutar la puerta de la descarga**

Quitar temporalmente `&& ! $requisito->haCaducado()` de `descargarFormato()`.
Run: `php artisan test --compact --filter=test_el_formato_de_un_decreto_vencido_no_se_puede_descargar`
Expected: FAIL. **Revertir** y volver a correr: PASS.

- [ ] **Step 7: Correr la suite entera**

Run: `php artisan test --compact`
Expected: 0 fallos. Si alguna prueba vieja de la guía se pone roja, es señal legítima: significa que dependía de que nada caducara.

- [ ] **Step 8: Formatear y commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/Publico/GuiaController.php app/Http/Controllers/SitemapController.php tests/Feature/VigenciaDeLaGuiaTest.php
git commit -m "Cierra las cuatro puertas por las que sale la guia normativa" -m "Un decreto vencido no se filtra por una, se filtra por cuatro, y tres son faciles de olvidar porque fallan distinto: la lista lo muestra como vigente, el selector ofrece un municipio con la guia vacia, el sitemap se lo anuncia a Google, y la descarga entrega el PDF por URL directa." -m "La cuarta es la que importa. Los formatos viven en el disco privado justamente para que el controlador sea la unica puerta; comprobar la caducidad solo en la vista dejaba el PDF de un decreto vencido descargable saltandose el control. Es la misma familia de agujero que el 8.3 del runbook encontro en la politica del bucket, y se trata igual: el control va en la puerta, no en la vista." -m "Con contraprueba en la descarga, porque una descarga rota habria pasado la prueba del 404 igual de verde." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 5: El panel — formulario y columnas

**Files:**
- Modify: `app/Filament/Resources/RequisitoAperturas/Schemas/RequisitoAperturaForm.php`
- Modify: `app/Filament/Resources/RequisitoAperturas/Tables/RequisitoAperturasTable.php`
- Test: `tests/Feature/Panel/VigenciaEnElPanelTest.php`

**Interfaces:**
- Consumes: `estaVerificado()`, `necesitaRevision()`, `haCaducado()` de la Task 2.
- Produces: los tres campos editables desde `EditRequisitoApertura` / `CreateRequisitoApertura`.

> **Corrección de paso, en un archivo que ya se está tocando:** la tabla arrastra `TextColumn::make('municipio.id')` del generador — muestra el número de la relación donde va el nombre. Se corrige a `municipio.nombre` en esta tarea. Es una línea, está en el archivo que se edita, y una tabla de requisitos por municipio que no dice el municipio no sirve para el filtro que esta misma tarea añade.

- [ ] **Step 1: Escribir la prueba que falla**

Crear `tests/Feature/Panel/VigenciaEnElPanelTest.php`:

```php
<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\RequisitoAperturas\Pages\EditRequisitoApertura;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La oficina necesita saber qué trámites lleva sin revisar. La fecha sola no
 * basta: hace falta poder listar la pila de trabajo.
 */
class VigenciaEnElPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_direccion_registra_la_verificacion_desde_el_formulario(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $requisito = RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => Municipio::factory(),
        ]);

        Livewire::test(EditRequisitoApertura::class, ['record' => $requisito->getRouteKey()])
            ->fillForm([
                'verificado_el' => '2026-08-20',
                'verificado_con' => 'Documento oficial de la Alcaldía de Armenia',
                'vigente_hasta' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $requisito->refresh();

        $this->assertSame('2026-08-20', $requisito->verificado_el->toDateString());
        $this->assertSame('Documento oficial de la Alcaldía de Armenia', $requisito->verificado_con);
        $this->assertNull($requisito->vigente_hasta, 'Vacío significa permanente.');
    }

    public function test_la_tabla_muestra_el_nombre_del_municipio_y_no_su_numero(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create(['nombre' => 'Circasia']);
        RequisitoApertura::factory()->publicado()->create(['municipio_id' => $municipio->id]);

        // El recurso tiene slug propio: `/admin/requisitos`, no el que se
        // deduciría del nombre de la clase. Se usa la ruta con nombre.
        $this->get(route('filament.admin.resources.requisitos.index'))
            ->assertSuccessful()
            ->assertSee('Circasia');
    }
}
```

- [ ] **Step 2: Correr y comprobar que falla**

Run: `php artisan test --compact --filter=VigenciaEnElPanelTest`
Expected: FAIL — el formulario no conoce `verificado_el`, y la tabla muestra el id.

- [ ] **Step 3: Añadir la sección al formulario**

En `RequisitoAperturaForm.php`, añadir el import `use Filament\Forms\Components\DatePicker;` y una sección nueva al final del array, después de `Section::make('Publicación')`:

```php
                Section::make('Verificación y vigencia')
                    ->description('De dónde salió este trámite y hasta cuándo vale. Es información legal: alguien va a decidir si abre un negocio con esto.')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('verificado_el')
                            ->label('Verificado el')
                            ->native(false)
                            ->maxDate(now())
                            ->helperText('El día en que alguien contrastó este trámite contra la entidad. Déjalo vacío si nadie lo ha hecho: el sitio lo dirá.'),
                        TextInput::make('verificado_con')
                            ->label('Verificado con')
                            ->maxLength(255)
                            ->placeholder('Documento oficial de la Alcaldía de Armenia, 20 ago 2026')
                            ->helperText('Con qué. Un documento, un acta, un correo de la entidad.'),
                        DatePicker::make('vigente_hasta')
                            ->label('Vigente hasta')
                            ->native(false)
                            ->helperText('Sólo para decretos transitorios. Vacío significa permanente. Al pasar la fecha, el trámite deja de mostrarse en el sitio.')
                            ->columnSpanFull(),
                    ]),
```

- [ ] **Step 4: Añadir las columnas a la tabla**

En `RequisitoAperturasTable.php`, añadir los imports `use App\Models\RequisitoApertura;`, sustituir la primera columna y añadir dos:

```php
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->searchable()
                    ->sortable(),
```

Y después de la columna `estado`:

```php
                TextColumn::make('verificado_el')
                    ->label('Verificado')
                    ->date('d/m/Y')
                    ->placeholder('Sin verificar')
                    ->badge()
                    ->color(fn (RequisitoApertura $record): string => match (true) {
                        ! $record->estaVerificado() => 'gray',
                        $record->necesitaRevision() => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('vigente_hasta')
                    ->label('Vigente hasta')
                    ->date('d/m/Y')
                    ->placeholder('Permanente')
                    ->color(fn (RequisitoApertura $record): string => $record->haCaducado() ? 'danger' : 'gray')
                    ->sortable(),
```

- [ ] **Step 5: Correr y comprobar que pasan**

Run: `php artisan test --compact --filter=VigenciaEnElPanelTest`
Expected: PASS — 2 casos.

- [ ] **Step 6: Formatear y commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/RequisitoAperturas tests/Feature/Panel/VigenciaEnElPanelTest.php
git commit -m "Le da al panel donde escribir la procedencia de cada tramite" -m "Seccion propia y no tres campos sueltos entre el costo y el checklist: de donde salio un tramite y hasta cuando vale son de otra naturaleza que su contenido, y el texto de ayuda tiene que poder decir que vacio significa permanente." -m "De paso, la tabla mostraba municipio.id -el numero de la relacion donde va el nombre, arrastrado del generador-. Una tabla de requisitos por municipio que no dice el municipio no sirve para el filtro que viene detras." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 6: El panel — filtros y la guarda del flujo

**Files:**
- Modify: `app/Filament/Resources/RequisitoAperturas/Tables/RequisitoAperturasTable.php`
- Test: `tests/Feature/Panel/VigenciaEnElPanelTest.php`

**Interfaces:**
- Consumes: `MESES_HASTA_REVISION` de la Task 2.
- Produces: filtros `necesita_revision` y `caducados`.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añadir a `tests/Feature/Panel/VigenciaEnElPanelTest.php` (imports: `App\Enums\EstadoPublicacion`, `App\Filament\Resources\RequisitoAperturas\Pages\ListRequisitoAperturas`):

```php
    public function test_el_filtro_lista_lo_rancio_y_lo_que_nadie_verifico(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create();
        $meses = RequisitoApertura::MESES_HASTA_REVISION;

        $alDia = RequisitoApertura::factory()->publicado()->verificado()
            ->create(['municipio_id' => $municipio->id]);
        $rancio = RequisitoApertura::factory()->publicado()
            ->verificado(now()->subMonths($meses)->subDay()->toDateString())
            ->create(['municipio_id' => $municipio->id]);
        $sinVerificar = RequisitoApertura::factory()->publicado()
            ->create(['municipio_id' => $municipio->id]);

        Livewire::test(ListRequisitoAperturas::class)
            ->filterTable('necesita_revision')
            ->assertCanSeeTableRecords([$rancio, $sinVerificar])
            ->assertCanNotSeeTableRecords([$alDia]);
    }

    public function test_el_filtro_de_caducados_solo_trae_lo_que_ya_vencio(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create();

        $permanente = RequisitoApertura::factory()->publicado()
            ->create(['municipio_id' => $municipio->id]);
        $vigente = RequisitoApertura::factory()->publicado()->transitorio()
            ->create(['municipio_id' => $municipio->id]);
        $vencido = RequisitoApertura::factory()->publicado()->caducado()
            ->create(['municipio_id' => $municipio->id]);

        Livewire::test(ListRequisitoAperturas::class)
            ->filterTable('caducados')
            ->assertCanSeeTableRecords([$vencido])
            ->assertCanNotSeeTableRecords([$permanente, $vigente]);
    }

    /**
     * Declarar «verifiqué esto contra la Alcaldía» es una afirmación de
     * autoridad sobre información legal. No hace falta un permiso nuevo: el
     * FlujoDeAprobacionObserver ya devuelve a pendiente cualquier edición de
     * la secretaría sobre algo publicado. Lo que faltaba es que esa protección
     * dejara de ser incidental — una guarda que nadie comprueba se rompe el
     * día que alguien añade un atajo.
     */
    public function test_la_secretaria_que_feche_un_publicado_lo_devuelve_a_pendiente(): void
    {
        // ⚠️ El requisito se crea ANTES de `actingAs`, y el orden es la prueba.
        // Con la secretaría ya autenticada, el propio observer degradaría el
        // registro al crearlo, y el test pasaría por la razón equivocada:
        // estaría midiendo una ficha que nunca llegó a estar publicada.
        $requisito = RequisitoApertura::factory()->publicado()->create([
            'municipio_id' => Municipio::factory(),
        ]);

        $this->assertSame(EstadoPublicacion::Publicado, $requisito->fresh()->estado);

        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN));

        Livewire::test(EditRequisitoApertura::class, ['record' => $requisito->getRouteKey()])
            ->fillForm([
                'verificado_el' => now()->toDateString(),
                'verificado_con' => 'Lo confirmé por teléfono',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            EstadoPublicacion::PendienteAprobacion,
            $requisito->fresh()->estado,
            'Fechar un requisito publicado es una decisión de publicación: vuelve a la cola.'
        );
    }
```

- [ ] **Step 2: Correr y comprobar que fallan**

Run: `php artisan test --compact --filter=VigenciaEnElPanelTest`
Expected: FAIL en los dos filtros — «no filter named necesita_revision». El tercero puede pasar ya: es una guarda existente que se está fijando, y eso es correcto — su valor es que se ponga roja el día que alguien la rompa.

- [ ] **Step 3: Añadir los dos filtros**

En `RequisitoAperturasTable.php`, añadir los imports `use Filament\Tables\Filters\Filter;` y `use Illuminate\Database\Eloquent\Builder;`, y en `->filters([...])`, después del `SelectFilter` existente:

```php
                Filter::make('necesita_revision')
                    ->label('Necesita revisión')
                    // Junta las dos mitades de la pila de trabajo: lo que nadie
                    // verificó nunca y lo que se verificó hace más de un año.
                    // El borde coincide con RequisitoApertura::necesitaRevision().
                    ->query(fn (Builder $query): Builder => $query->where(fn (Builder $q) => $q
                        ->whereNull('verificado_el')
                        ->orWhere(
                            'verificado_el',
                            '<',
                            now()->subMonths(RequisitoApertura::MESES_HASTA_REVISION)->toDateString()
                        ))),

                Filter::make('caducados')
                    ->label('Caducados')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('vigente_hasta')
                        ->where('vigente_hasta', '<', now()->toDateString())),
```

- [ ] **Step 4: Correr y comprobar que pasan**

Run: `php artisan test --compact --filter=VigenciaEnElPanelTest`
Expected: PASS — 5 casos.

- [ ] **Step 5: Comprobar que el filtro y el predicado no se han separado**

Añadir a `tests/Feature/Panel/VigenciaEnElPanelTest.php`:

```php
    /**
     * El filtro repite en SQL la regla que el modelo expresa en PHP. Si una de
     * las dos se mueve sin la otra, la lista de trabajo miente. Esta prueba es
     * la costura.
     */
    public function test_el_filtro_y_el_predicado_coinciden_en_el_borde_exacto(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUPER_ADMIN));

        $municipio = Municipio::factory()->create();
        $meses = RequisitoApertura::MESES_HASTA_REVISION;

        $justoEnElBorde = RequisitoApertura::factory()->publicado()
            ->verificado(now()->subMonths($meses)->toDateString())
            ->create(['municipio_id' => $municipio->id]);

        $this->assertFalse($justoEnElBorde->necesitaRevision());

        Livewire::test(ListRequisitoAperturas::class)
            ->filterTable('necesita_revision')
            ->assertCanNotSeeTableRecords([$justoEnElBorde]);
    }
```

Run: `php artisan test --compact --filter=VigenciaEnElPanelTest`
Expected: PASS — 6 casos.

- [ ] **Step 6: Formatear y commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/RequisitoAperturas tests/Feature/Panel/VigenciaEnElPanelTest.php
git commit -m "Convierte la fecha en una pila de trabajo que la oficina puede atacar" -m "Dos filtros. Necesita revision junta lo que nadie verifico nunca con lo que se verifico hace mas de un año, que son estados distintos para el lector pero el mismo trabajo pendiente. Caducados trae lo que ya vencio, que sigue viendose en el panel a proposito: alguien tiene que poder renovarlo." -m "El filtro repite en SQL la regla que el modelo expresa en PHP, asi que hay una prueba de costura sobre el borde exacto: si una de las dos se mueve sin la otra, la lista de trabajo miente." -m "Y queda fijada una guarda que hasta hoy era incidental: la secretaria que feche un requisito publicado lo devuelve a pendiente. No hace falta permiso nuevo -el FlujoDeAprobacionObserver ya lo hacia- pero ninguna prueba lo ejercitaba, y una guarda que nadie comprueba se rompe el dia que alguien añade un atajo." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 7: La vista pública — las tres marcas

**Files:**
- Modify: `resources/views/publico/guia/index.blade.php`
- Test: `tests/Feature/VigenciaDeLaGuiaTest.php`

**Interfaces:**
- Consumes: `estaVerificado()`, `esTransitorio()` de la Task 2; la consulta ya filtrada de la Task 4.
- Produces: nada.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añadir a `tests/Feature/VigenciaDeLaGuiaTest.php`:

```php
    public function test_un_tramite_verificado_ensena_su_fecha_y_su_fuente(): void
    {
        $municipio = Municipio::factory()->create();
        $this->requisitoPublicado($municipio, [
            'verificado_el' => '2026-08-20',
            'verificado_con' => 'Documento oficial de la Alcaldía de Armenia',
        ]);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee('Verificado el 20 de agosto de 2026')
            ->assertSee('Documento oficial de la Alcaldía de Armenia');
    }

    public function test_un_tramite_sin_fechar_lo_dice_en_su_cara(): void
    {
        $municipio = Municipio::factory()->create();
        $this->requisitoPublicado($municipio);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee('Sin verificar contra la fuente oficial')
            ->assertDontSee('Verificado el');
    }

    public function test_un_decreto_transitorio_anuncia_hasta_cuando_vale(): void
    {
        $municipio = Municipio::factory()->create();
        $this->requisitoPublicado($municipio, ['vigente_hasta' => '2026-11-30']);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee('Vigente hasta el 30 de noviembre de 2026');
    }

    /**
     * Contraprueba. Sin ella, una vista que no renderizara ningún trámite
     * pasaría las tres pruebas anteriores en verde.
     */
    public function test_un_tramite_permanente_no_anuncia_ninguna_caducidad(): void
    {
        $municipio = Municipio::factory()->create();
        $requisito = $this->requisitoPublicado($municipio, ['entidad' => 'Cámara de Comercio']);

        $this->get(route('guia.index', ['municipio' => $municipio->slug]))
            ->assertSuccessful()
            ->assertSee($requisito->entidad)
            ->assertDontSee('Vigente hasta');
    }
```

- [ ] **Step 2: Correr y comprobar que fallan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: FAIL — ninguna de las tres cadenas está en el HTML.

- [ ] **Step 3: Añadir las marcas al resumen**

En `resources/views/publico/guia/index.blade.php`, dentro del `<summary>`, después del bloque `@if ($requisito->tieneAdjunto())`:

```blade
                                    @if ($requisito->estaVerificado())
                                        <span class="text-exito-suave">
                                            · Verificado el {{ $requisito->verificado_el->translatedFormat('d \d\e F \d\e Y') }}
                                        </span>
                                    @else
                                        <span class="text-aviso-suave">· Sin verificar contra la fuente oficial</span>
                                    @endif

                                    @if ($requisito->esTransitorio())
                                        <span class="rounded-full border border-aviso-linea bg-aviso-fondo px-2.5 py-0.5 text-aviso-suave">
                                            Vigente hasta el {{ $requisito->vigente_hasta->translatedFormat('d \d\e F \d\e Y') }}
                                        </span>
                                    @endif
```

- [ ] **Step 4: Añadir la procedencia al cuerpo**

En el mismo fichero, dentro del `<div class="border-t border-linea …">`, justo después del bloque `@if ($requisito->descripcion)`:

```blade
                            @if ($requisito->verificado_con)
                                <p class="mt-3 text-xs text-apagado">
                                    Fuente: {{ $requisito->verificado_con }}
                                </p>
                            @endif
```

- [ ] **Step 5: Correr y comprobar que pasan**

Run: `php artisan test --compact --filter=VigenciaDeLaGuiaTest`
Expected: PASS — 21 casos.

- [ ] **Step 6: Comprobar que no se coló ningún hexadecimal**

Run: `php artisan test --compact --filter=TemaClaroOscuro`
Expected: PASS. Las clases usadas son tokens (`text-exito-suave`, `text-aviso-suave`, `border-aviso-linea`, `bg-aviso-fondo`), no colores crudos.

- [ ] **Step 7: Reconstruir el frontend y mirarlo**

```bash
npm run build
```

Levantar la vista previa y comprobar los tres estados en tema claro y oscuro sobre `/abre-tu-negocio`.

- [ ] **Step 8: Formatear y commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/publico/guia/index.blade.php tests/Feature/VigenciaDeLaGuiaTest.php
git commit -m "Hace que cada tramite diga de donde salio, o confiese que no lo sabe" -m "Tres marcas en la ficha: verificado con su fecha, sin verificar contra la fuente oficial, y hasta cuando vale si es transitorio. Mas la procedencia en el cuerpo, que es lo que convierte la marca en algo comprobable." -m "Lo que no tiene fecha se publica igual y lo dice. Es el patron que el Observatorio ya usa con aun sin muestra suficiente y que la matriz usa al declarar sus huecos: se declara la carencia, no se esconde. Exigir fecha para publicar habria apagado hoy las tres guias que hay, en las capturas y en la demo." -m "Con contraprueba, porque una vista que no renderizara ningun tramite habria pasado las tres marcas en verde." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

---

## Task 8: El documento de Ingrid y la matriz

**Files:**
- Modify: `docs/ingenieria/guia-normativa-armenia-fuente-oficial.md`
- Modify: `docs/ingenieria/matriz-de-pruebas.md:86` (fila RF-60) y `:221` (sección 5) y la tabla de cobertura
- Test: ninguno nuevo; se re-ejecuta la suite completa.

**Interfaces:**
- Consumes: todo lo anterior.
- Produces: nada.

- [ ] **Step 1: Añadir los dos campos a los siete trámites de Armenia**

En `docs/ingenieria/guia-normativa-armenia-fuente-oficial.md`, en el bloque de código PHP de la §3, añadir a **cada uno** de los siete trámites, después de `'orden' => N,`:

```php
        'verificado_el' => '2026-08-20',
        'verificado_con' => 'Documento oficial de la Alcaldía de Armenia, campaña «Blindemos tu Negocio», entregado al gremio el 20 de agosto de 2026',
```

Y añadir un párrafo al final de la §2, antes de la §3:

```markdown
> ✅ **Estos siete entran fechados.** Desde el 24 de agosto la tabla tiene `verificado_el` y `verificado_con`, así que el contenido oficial no llega como los 18 anteriores —sin procedencia y sin fecha— sino con el documento del que salió. El bloque de la §3 ya los trae rellenos: pégalos tal cual. Si algún trámite fuera transitorio habría además un `'vigente_hasta'`, pero ninguno de los siete lo es: son todos permanentes.
```

- [ ] **Step 2: Cerrar RF-60 en la matriz**

En `docs/ingenieria/matriz-de-pruebas.md`, sustituir la fila RF-60 (línea 86):

```markdown
| RF-60 | *(nuevo ERS v3)* | Normativa vigente y decretos transitorios por municipio | `VigenciaDeLaGuiaTest`, `VigenciaEnElPanelTest` | ✅ Cada trámite guarda con qué fuente y en qué fecha se verificó; los decretos transitorios caducan solos y desaparecen del sitio por las cuatro puertas, incluida la descarga del formato |
```

Y en la §5, sustituir el punto 6:

```markdown
6. ~~**RF-60 — Sin cobertura.**~~ **Cerrado el 24 de agosto de 2026.** La tabla admitía el dato pero no probaba la vigencia; ahora `vigente_hasta` saca del sitio lo que venció, con prueba en las cuatro puertas por las que sale la guía —lista, selector de municipios, sitemap y **descarga del formato**, que era la única con consecuencia de seguridad—. **RF-05 sigue sin cobertura** a la espera de la decisión DPV-05.
```

- [ ] **Step 3: Correr la suite completa y anotar las cifras reales**

```bash
php artisan test --compact
grep -rhoE "function test_[a-zA-Z0-9_]+" tests/ | wc -l
find tests -name "*Test.php" | wc -l
```

**Las cifras salen de esa salida, nunca de una suma.** Es la regla que el propio documento fija y que ya se incumplió una vez: el 789 del 21 de agosto se calculó sumando y la corrida real daba 791.

Sustituir la tabla de cobertura con los valores medidos y añadir arriba de la nota del 23 de agosto:

```markdown
> ✅ **Última ejecución — <fecha de hoy>.** Sobre el árbol de trabajo, con `php artisan test --compact` y PHP 8.5.9 con `intl` y `gd`: **<casos> casos · <pasan> pasan · 11 omitidas · 0 fallos · <aserciones> aserciones · <duración> s**. Los casos nuevos respecto al 23 de agosto cierran RF-60: la vigencia de la guía normativa en el modelo, en las cuatro puertas por las que sale y en el panel.
```

Y en la tabla de cobertura, subir la línea de requisitos funcionales de **45 de 50** a **46 de 50**.

- [ ] **Step 4: Commit**

```bash
git add docs/ingenieria/guia-normativa-armenia-fuente-oficial.md docs/ingenieria/matriz-de-pruebas.md
git commit -m "Cierra RF-60 en la matriz y le deja a Ingrid los siete tramites ya fechados" -m "RF-60 -normativa vigente y decretos transitorios por municipio- era uno de los dos unicos requisitos funcionales sin ninguna cobertura. Queda cerrado: cada tramite guarda con que fuente y en que fecha se verifico, y lo transitorio caduca solo por las cuatro puertas, incluida la descarga del formato." -m "El documento que se le dejo a la Persona 2 trae ahora los siete tramites de Armenia con verificado_el y verificado_con rellenos. El contenido oficial no entra como los 18 anteriores -sin procedencia y sin fecha- sino con el documento del que salio." -m "RF-05 sigue sin cobertura, a la espera de DPV-05, y asi queda dicho." -m "Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

- [ ] **Step 5: Empujar**

```bash
git push origin main
```

---

## Verificación final

- [ ] `php artisan test --compact` → 0 fallos, 11 omitidas
- [ ] `vendor/bin/pint --test --format agent` → passed
- [ ] `git status --porcelain` → vacío
- [ ] `git log --oneline origin/main..main` → vacío
- [ ] La guía en `/abre-tu-negocio` enseña las tres marcas en tema claro y oscuro
- [ ] Un decreto caducado creado a mano desde el panel desaparece del sitio y su formato responde 404
