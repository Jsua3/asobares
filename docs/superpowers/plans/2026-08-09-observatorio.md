# Observatorio del gremio — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Una página `/admin/observatorio` que convierta el dato que la plataforma ya captura en el argumento gremial que la dirección lleva a una alcaldía, diciendo la verdad sobre lo que todavía no puede afirmar.

**Architecture:** Un servicio `MetricasDelObservatorio` concentra las seis agregaciones —todas en SQL, ninguna en memoria— y devuelve para cada una sus datos **y su tamaño de muestra**. Una página propia de Filament renderiza una banda de KPIs con `<x-panel.kpi>` y seis `ChartWidget` que leen del servicio. Cuando la muestra no alcanza el umbral, el widget **no dibuja**: rotula que aún no hay con qué afirmar. Un informe imprimible con CSS de impresión produce el PDF por el navegador, sin dependencias nuevas.

**Tech Stack:** Laravel 13.23, Filament 4.12, Livewire 3.8, Chart.js (empaquetado por Filament), Tailwind v4, SQLite (PostgreSQL en producción), PHPUnit 12, PHP 8.5.

## Global Constraints

- **Identificadores y comentarios en español. Decisión del dueño del 5 ago 2026, no la reabras.** `CLAUDE.md` se contradice sobre esto; gobierna la convención existente del código. **No es un defecto y no debe reportarse como tal.**
- **Cero colores cableados en vistas.** Solo tokens (`bg-superficie`, `text-tinta`, `border-linea`, `text-acento`, `text-aviso`…). El guardián `TemaClaroOscuroTest` recorre `resources/views/filament` y `resources/views/components/panel` y falla si aparece una clase de fábrica.
- **Ninguna cifra se presenta sin su n.** Es el principio #5 del spec y la razón de ser de este módulo: un gremio que lleva porcentajes sin tamaño de muestra ante una alcaldía pierde credibilidad una sola vez.
- **Agregación en SQL, nunca en memoria.** Nada de `->get()->groupBy()`. Ya se corrigió una vez en este proyecto.
- **Expresiones de fecha por motor.** SQLite hoy, PostgreSQL en producción. Copia el patrón de `app/Filament/Widgets/RecaudoMensual.php`, que resuelve con `match (DB::connection()->getDriverName())`.
- **Sin dependencias nuevas.** `CLAUDE.md` exige aprobación y el dueño eligió la vía sin librería para el informe.
- **La transparencia va en el cromo, nunca sobre una tabla de datos.**
- **Todo movimiento bajo `@media (prefers-reduced-motion: reduce)`.**
- PHPUnit, nunca Pest. `--no-interaction` en todo Artisan. `vendor/bin/pint --dirty --format agent` antes de cada commit. Commits en español **sin tildes en el mensaje**.
- `php artisan view:clear` **antes** de `npm run build`.
- Pruebas con `php artisan test --compact --filter=NombreDelTest`. La suite completa tarda ~2,5 min; córrela donde el plan lo pida.

## Decisiones tomadas por el dueño el 9 ago 2026

| Tema | Decisión |
|---|---|
| Gráficas de empleo sin datos | **Se construyen las seis**, pero las que no alcanzan muestra muestran un estado vacío honesto en vez de dibujar barras sobre n=2. La infraestructura queda lista; se llenan solas cuando la bolsa se mueva. |
| Informe descargable | **Página imprimible con CSS de impresión.** El navegador produce el PDF. Cero dependencias nuevas. |
| «Mapa de calor por municipio» | **Barras horizontales ordenadas**, no Leaflet. Cumple el propósito del spec sin tocar el esquema (los municipios no tienen coordenadas) ni meter Leaflet en el panel. |

## Lo que se midió antes de planear

Contra la base sembrada, el 9 ago 2026:

| Visualización | n real | Veredicto |
|---|---|---|
| Consultas de la guía por municipio | 732 en 8 municipios | sólida |
| Salud financiera | 177 transacciones aprobadas, 18 meses, 8/24 en mora | sólida |
| Composición del sector | 24 asociados en 6 categorías | modesta, honesta |
| Cobertura de proveedores | 10 proveedores en 6 categorías | flaca |
| Demanda laboral por área y mes | 7 vacantes, **todas del mismo mes** | sin serie |
| Oferta contra demanda por área | 7 vacantes + 7 aspirantes + 4 postulaciones en 7 áreas | ruido |

**Las dos últimas son justo las que el spec §4 llamaba «el argumento institucional».** Por eso el estado vacío honesto no es un adorno del plan: es lo que impide que el módulo mienta el 22 de septiembre.

## File Structure

| Archivo | Responsabilidad |
|---|---|
| `app/Panel/MetricasDelObservatorio.php` | **Nuevo.** Las seis agregaciones en SQL. Cada una devuelve un `SerieDelObservatorio`. Memoizado por instancia. |
| `app/Panel/SerieDelObservatorio.php` | **Nuevo.** Objeto de valor: etiquetas, valores por serie, `n`, y si hay muestra suficiente. |
| `app/Filament/Pages/Observatorio.php` | **Nuevo.** La página, su permiso, sus widgets y la acción de informe. |
| `resources/views/filament/pages/observatorio.blade.php` | **Nuevo.** Banda de KPIs con `<x-panel.kpi>` + rejilla de widgets. |
| `app/Filament/Widgets/Observatorio/*.php` | **Nuevos.** Seis `ChartWidget`, uno por visualización. |
| `resources/views/components/panel/sin-muestra.blade.php` | **Nuevo.** Estado vacío honesto reutilizable. |
| `app/Filament/Pages/InformeDelObservatorio.php` + su vista | **Nuevos.** El informe imprimible. |
| `resources/css/filament/admin/theme.css` | **Modificar.** Reglas `@media print`. |
| `database/seeders/RolYPermisoSeeder.php` | **Modificar.** Permiso `ver_observatorio`. |
| `tests/Feature/Panel/ObservatorioTest.php` | **Nuevo.** |
| `tests/Feature/Panel/MetricasDelObservatorioTest.php` | **Nuevo.** |

---

## Task 1: Objeto de valor `SerieDelObservatorio`

**Files:**
- Create: `app/Panel/SerieDelObservatorio.php`
- Test: `tests/Feature/Panel/MetricasDelObservatorioTest.php`

**Interfaces:**
- Produces: clase `final readonly` con esta forma exacta, que consumen todas las tareas siguientes:

```php
new SerieDelObservatorio(
    etiquetas: ['Armenia', 'Calarcá'],      // list<string>
    series: ['Asociados' => [12, 4]],       // array<string, list<int|float>>
    n: 16,                                  // tamaño de muestra total
    unidad: 'asociados',                    // para el rótulo: "n = 16 asociados"
)
```

Métodos: `hayMuestraSuficiente(): bool` (n >= 30), `rotuloDeMuestra(): string`, `estaVacia(): bool`.

**El umbral es 30 y no se elige aquí por capricho:** es el mismo que ya usa `resources/views/components/panel/kpi.blade.php` para marcar «muestra pequeña», y es la regla de oro convencional por debajo de la cual una cifra no sostiene una afirmación. Que los dos sitios usen el mismo número es parte del punto — extrae la constante para que no puedan divergir.

- [ ] **Step 1: Escribir la prueba que falla**

```bash
php artisan make:test --phpunit Panel/MetricasDelObservatorioTest --no-interaction
```

`tests/Feature/Panel/MetricasDelObservatorioTest.php`:

```php
<?php

namespace Tests\Feature\Panel;

use App\Panel\SerieDelObservatorio;
use Tests\TestCase;

/**
 * El observatorio existe para llevar cifras a una alcaldía. Una serie que no
 * sabe cuántos datos la sostienen no sirve para eso.
 */
class MetricasDelObservatorioTest extends TestCase
{
    private function serie(int $n): SerieDelObservatorio
    {
        return new SerieDelObservatorio(
            etiquetas: ['Armenia', 'Calarcá'],
            series: ['Asociados' => [$n - 1, 1]],
            n: $n,
            unidad: 'asociados',
        );
    }

    public function test_una_serie_por_debajo_del_umbral_no_tiene_muestra_suficiente(): void
    {
        $this->assertFalse($this->serie(29)->hayMuestraSuficiente());
        $this->assertTrue($this->serie(30)->hayMuestraSuficiente());
    }

    public function test_el_rotulo_de_muestra_dice_cuantos_y_de_que(): void
    {
        $this->assertSame('n = 42 asociados', $this->serie(42)->rotuloDeMuestra());
    }

    public function test_una_serie_sin_datos_se_reconoce_vacia(): void
    {
        $vacia = new SerieDelObservatorio(etiquetas: [], series: [], n: 0, unidad: 'vacantes');

        $this->assertTrue($vacia->estaVacia());
        $this->assertFalse($this->serie(30)->estaVacia());
    }

    /**
     * El umbral vive en un solo sitio: si el componente KPI y el observatorio
     * usaran números distintos, la misma cifra sería «muestra pequeña» en una
     * tarjeta y suficiente en la gráfica de al lado.
     *
     * Se prueba por comportamiento y no buscando el número en el archivo:
     * después del paso 4 ese archivo ya no contiene el literal, sino la
     * referencia a la constante. Una aserción sobre el texto fallaría justo
     * después del arreglo que pretende verificar.
     */
    public function test_la_tarjeta_kpi_marca_muestra_pequena_con_el_mismo_umbral(): void
    {
        $limite = SerieDelObservatorio::MUESTRA_MINIMA;

        $justoDebajo = \Illuminate\Support\Facades\Blade::render(
            '<x-panel.kpi etiqueta="Mora" valor="18 %" :n="$n" />',
            ['n' => $limite - 1]
        );
        $justoEncima = \Illuminate\Support\Facades\Blade::render(
            '<x-panel.kpi etiqueta="Mora" valor="18 %" :n="$n" />',
            ['n' => $limite]
        );

        $this->assertStringContainsString('muestra pequeña', $justoDebajo);
        $this->assertStringNotContainsString('muestra pequeña', $justoEncima);
    }
}
```

- [ ] **Step 2: Ejecutar para verificar que falla**

```bash
php artisan test --compact --filter=MetricasDelObservatorioTest
```

Esperado: FAIL con «Class "App\Panel\SerieDelObservatorio" not found».

- [ ] **Step 3: Crear la clase**

```bash
php artisan make:class Panel/SerieDelObservatorio --no-interaction
```

`app/Panel/SerieDelObservatorio.php`:

```php
<?php

namespace App\Panel;

/**
 * Una serie del observatorio con su tamaño de muestra.
 *
 * El `n` no es metadato: es parte de la cifra. Este módulo existe para que la
 * dirección lleve datos a una alcaldía, y un porcentaje sin muestra detrás no
 * aguanta la primera pregunta.
 *
 * @param  list<string>  $etiquetas
 * @param  array<string, list<int|float>>  $series
 */
final readonly class SerieDelObservatorio
{
    /**
     * Regla de oro convencional: por debajo de treinta observaciones una
     * cifra no sostiene una afirmación. Es el mismo umbral que marca
     * «muestra pequeña» en la tarjeta KPI, y vive aquí para que no puedan
     * divergir.
     */
    public const int MUESTRA_MINIMA = 30;

    public function __construct(
        public array $etiquetas,
        public array $series,
        public int $n,
        public string $unidad,
    ) {}

    public function hayMuestraSuficiente(): bool
    {
        return $this->n >= self::MUESTRA_MINIMA;
    }

    public function estaVacia(): bool
    {
        return $this->etiquetas === [] || $this->n === 0;
    }

    public function rotuloDeMuestra(): string
    {
        return "n = {$this->n} {$this->unidad}";
    }
}
```

- [ ] **Step 4: Apuntar la tarjeta KPI a la misma constante**

En `resources/views/components/panel/kpi.blade.php`, sustituye el `30` literal del cálculo de `$muestraChica` por `\App\Panel\SerieDelObservatorio::MUESTRA_MINIMA`, y ajusta el comentario para que diga que el umbral se comparte con el observatorio.

- [ ] **Step 5: Ejecutar hasta verde**

```bash
php artisan test --compact --filter=MetricasDelObservatorioTest
php artisan test --compact --filter=ComponentesDelPanelTest
```

Esperado: PASS en ambos. El segundo cubre las pruebas del KPI, que tocaste.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Panel/SerieDelObservatorio.php resources/views/components/panel/kpi.blade.php tests/Feature/Panel/MetricasDelObservatorioTest.php
git commit -m "Anade la serie del observatorio con su tamano de muestra

El n no es metadato, es parte de la cifra: este modulo existe para que la
direccion lleve datos a una alcaldia y un porcentaje sin muestra detras no
aguanta la primera pregunta. El umbral de 30 se extrae a constante para
que la tarjeta KPI y el observatorio no puedan divergir."
```

---

## Task 2: Servicio `MetricasDelObservatorio`

**Files:**
- Create: `app/Panel/MetricasDelObservatorio.php`
- Modify: `tests/Feature/Panel/MetricasDelObservatorioTest.php`

**Interfaces:**
- Consumes: `SerieDelObservatorio` (Tarea 1), los modelos `Asociado`, `Vacante`, `Aspirante`, `Postulacion`, `Proveedor`, `ConsultaGuia`, `Transaccion`, `Cartera`, `Municipio`, `Categoria`, y los enums `CargoDelSector` (7 casos) y `CategoriaProveedor` (7 casos).
- Produces: seis métodos públicos, cada uno devolviendo `SerieDelObservatorio`:

```php
presenciaPorMunicipio(): SerieDelObservatorio      // 3 series: asociados, vacantes, consultas
composicionDelSector(): SerieDelObservatorio       // 1 serie: asociados por categoría
saludFinanciera(): SerieDelObservatorio            // 2 series: recaudo mensual y tasa de mora, 18 meses
coberturaDeProveedores(): SerieDelObservatorio     // 1 serie: proveedores por categoría
demandaLaboralPorArea(): SerieDelObservatorio      // 1 serie por mes, apilada por área
ofertaContraDemanda(): SerieDelObservatorio        // 2 series por área: demanda y oferta
```

**Reglas que valen para los seis:**
- **Agregación en SQL.** Nada de traer modelos a memoria para contarlos.
- **Las expresiones de fecha se resuelven por motor**, copiando el patrón de `app/Filament/Widgets/RecaudoMensual.php`: un `match (DB::connection()->getDriverName())` con `to_char` para `pgsql`, `date_format` para `mysql`/`mariadb` y `strftime` por defecto. Extráelo a un método privado; seis copias del mismo `match` sería duplicación.
- **Solo cuenta lo publicado** donde el modelo tenga estado editorial: usa el scope `publicado()` de `EsPublicable`. Un asociado en borrador no es presencia del gremio.
- **Memoiza por instancia**, como hace `app/Panel/ColaDePendientes.php`: la página llama a varios métodos por render.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añade a `tests/Feature/Panel/MetricasDelObservatorioTest.php`. La clase necesita ahora `use Illuminate\Foundation\Testing\RefreshDatabase;`.

```php
    public function test_la_presencia_por_municipio_cuenta_las_tres_senales(): void
    {
        $armenia = \App\Models\Municipio::factory()->create(['nombre' => 'Armenia']);
        $salento = \App\Models\Municipio::factory()->create(['nombre' => 'Salento']);

        \App\Models\Asociado::factory()->count(3)->publicado()->for($armenia)->create();
        \App\Models\Asociado::factory()->publicado()->for($salento)->create();
        \App\Models\ConsultaGuia::factory()->count(5)->for($armenia)->create();

        $serie = app(\App\Panel\MetricasDelObservatorio::class)->presenciaPorMunicipio();

        $indiceArmenia = array_search('Armenia', $serie->etiquetas, true);
        $this->assertNotFalse($indiceArmenia);
        $this->assertSame(3, $serie->series['Asociados'][$indiceArmenia]);
        $this->assertSame(5, $serie->series['Consultas de la guía'][$indiceArmenia]);
    }

    /** Un asociado en borrador no es presencia del gremio. */
    public function test_la_presencia_ignora_lo_no_publicado(): void
    {
        $municipio = \App\Models\Municipio::factory()->create(['nombre' => 'Filandia']);
        \App\Models\Asociado::factory()->publicado()->for($municipio)->create();
        \App\Models\Asociado::factory()->for($municipio)->create([
            'estado' => \App\Enums\EstadoPublicacion::Borrador,
        ]);

        $serie = app(\App\Panel\MetricasDelObservatorio::class)->presenciaPorMunicipio();
        $indice = array_search('Filandia', $serie->etiquetas, true);

        $this->assertSame(1, $serie->series['Asociados'][$indice]);
    }

    public function test_las_seis_metricas_agregan_en_una_sola_consulta_por_serie(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $metricas = app(\App\Panel\MetricasDelObservatorio::class);

        foreach (['presenciaPorMunicipio', 'composicionDelSector', 'saludFinanciera',
            'coberturaDeProveedores', 'demandaLaboralPorArea', 'ofertaContraDemanda'] as $metodo) {
            $consultas = 0;
            \Illuminate\Support\Facades\DB::listen(function () use (&$consultas): void {
                $consultas++;
            });

            app(\App\Panel\MetricasDelObservatorio::class)->{$metodo}();

            $this->assertLessThanOrEqual(
                4,
                $consultas,
                "{$metodo} dispara {$consultas} consultas: se está agregando en memoria."
            );
        }

        $this->assertNotNull($metricas);
    }

    public function test_las_metricas_se_memoizan_dentro_de_la_misma_instancia(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $metricas = app(\App\Panel\MetricasDelObservatorio::class);
        $metricas->saludFinanciera();

        $consultas = 0;
        \Illuminate\Support\Facades\DB::listen(function () use (&$consultas): void {
            $consultas++;
        });

        $metricas->saludFinanciera();

        $this->assertSame(0, $consultas, 'La segunda llamada debe usar la memoizacion.');
    }

    /**
     * Con la semilla de hoy las dos métricas de empleo no llegan al umbral, y
     * eso es lo que la interfaz tiene que poder decir. Si algún día la semilla
     * crece, esta prueba cambia de sentido: afirma la regla, no el número.
     */
    public function test_las_metricas_declaran_si_tienen_muestra_suficiente(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $metricas = app(\App\Panel\MetricasDelObservatorio::class);

        $this->assertTrue(
            $metricas->presenciaPorMunicipio()->hayMuestraSuficiente(),
            'Las consultas de la guia sí tienen volumen.'
        );
        $this->assertSame(
            $metricas->ofertaContraDemanda()->n < \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA,
            ! $metricas->ofertaContraDemanda()->hayMuestraSuficiente()
        );
    }
```

- [ ] **Step 2: Ejecutar para verificar que fallan**

```bash
php artisan test --compact --filter=MetricasDelObservatorioTest
```

Esperado: FAIL con «Class "App\Panel\MetricasDelObservatorio" not found».

- [ ] **Step 3: Crear el servicio**

```bash
php artisan make:class Panel/MetricasDelObservatorio --no-interaction
```

Escribe las seis agregaciones siguiendo las reglas de arriba. Guía por método:

- **`presenciaPorMunicipio()`** — tres `selectRaw` agrupados por `municipio_id` sobre `asociados` (publicados), `vacantes` (publicadas, vía su asociado) y `consultas_guia`; se combinan por municipio y se ordenan por consultas descendente. `n` = suma de las tres.
- **`composicionDelSector()`** — asociados publicados agrupados por `categoria_id`. `n` = total de asociados publicados. Unidad: `asociados`.
- **`saludFinanciera()`** — dieciocho meses: recaudo aprobado por mes (`sum(monto)`) y tasa de mora (`carteras` con `meses_mora > 0` sobre el total, que hoy es un valor único; represéntalo como línea plana comparativa o como segunda serie del mes en curso — **decídelo tú y comenta por qué**). `n` = transacciones aprobadas del periodo. Unidad: `transacciones`.
- **`coberturaDeProveedores()`** — proveedores publicados y vigentes agrupados por `categoria_proveedor`. **Incluye las categorías con cero**: el hueco es la información. `n` = proveedores. Unidad: `proveedores`.
- **`demandaLaboralPorArea()`** — vacantes por mes y por `categoria_cargo`, últimos doce meses. `n` = vacantes del periodo. Unidad: `vacantes`.
- **`ofertaContraDemanda()`** — por cada caso de `CargoDelSector`: demanda = vacantes publicadas; oferta = aspirantes + postulaciones de esa área. `n` = suma de ambas. Unidad: `registros`.

- [ ] **Step 4: Ejecutar hasta verde**

```bash
php artisan test --compact --filter=MetricasDelObservatorioTest
```

Esperado: PASS.

- [ ] **Step 5: Verificar los números a mano**

```bash
php artisan tinker --execute '$m = app(App\Panel\MetricasDelObservatorio::class);
foreach (["presenciaPorMunicipio","composicionDelSector","saludFinanciera","coberturaDeProveedores","demandaLaboralPorArea","ofertaContraDemanda"] as $x) {
  $s = $m->{$x}();
  printf("%-24s n=%-5d suficiente=%s etiquetas=%d\n", $x, $s->n, $s->hayMuestraSuficiente() ? "SI" : "no", count($s->etiquetas));
}'
```

**Pega esa salida en tu informe.** Es la que dice qué va a poder afirmar el observatorio y qué no.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Panel/MetricasDelObservatorio.php tests/Feature/Panel/MetricasDelObservatorioTest.php
git commit -m "Anade el servicio de metricas del observatorio

Las seis agregaciones en SQL, cada una devolviendo sus datos y su tamano
de muestra. Memoiza por instancia porque la pagina llama a varias por
render, igual que ColaDePendientes."
```

---

## Task 3: Estado vacío honesto

**Files:**
- Create: `resources/views/components/panel/sin-muestra.blade.php`
- Modify: `tests/Feature/Panel/ComponentesDelPanelTest.php`

**Interfaces:**
- Produces: `<x-panel.sin-muestra :serie="$serie" que="la demanda laboral por área" />`, que rinde el rótulo de muestra y la explicación de por qué no se dibuja nada.

**Es la pieza que impide que el módulo mienta.** Con 7 vacantes repartidas en 7 áreas, dibujar barras sugiere una tendencia que no existe. El texto tiene que decir dos cosas: cuántos datos hay, y que el observatorio ya mide aunque el sector todavía no alimente.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añade a `tests/Feature/Panel/ComponentesDelPanelTest.php`:

```php
    public function test_el_estado_sin_muestra_dice_cuantos_datos_hay_y_por_que_no_dibuja(): void
    {
        $serie = new \App\Panel\SerieDelObservatorio(
            etiquetas: ['Barra', 'Cocina'],
            series: ['Demanda' => [2, 1]],
            n: 3,
            unidad: 'vacantes',
        );

        $html = Blade::render(
            '<x-panel.sin-muestra :serie="$serie" que="la demanda laboral por área" />',
            ['serie' => $serie]
        );

        $this->assertStringContainsString('n = 3 vacantes', $html);
        $this->assertStringContainsString('la demanda laboral por área', $html);
        // La honestidad es el punto: se nombra el umbral que falta por alcanzar.
        $this->assertStringContainsString((string) \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA, $html);
    }

    public function test_el_estado_sin_muestra_no_usa_colores_cableados(): void
    {
        $fuente = File::get(resource_path('views/components/panel/sin-muestra.blade.php'));

        foreach (\Tests\Feature\TemaClaroOscuroTest::clasesProhibidas() as $patron => $motivo) {
            $this->assertSame(0, preg_match($patron, $fuente), "Clase cableada: {$motivo}");
        }
    }
```

- [ ] **Step 2: Ejecutar para verificar que fallan**

```bash
php artisan test --compact --filter=ComponentesDelPanelTest
```

Esperado: FAIL con «View [components.panel.sin-muestra] not found».

- [ ] **Step 3: Crear el componente**

`resources/views/components/panel/sin-muestra.blade.php`:

```blade
@props(['serie', 'que'])

{{--
    Lo que se enseña cuando la muestra no da.

    Dibujar barras sobre tres observaciones sugiere una tendencia que no
    existe, y este modulo esta hecho para llevar cifras a una alcaldia. Decir
    «todavia no hay con que afirmar» es informacion; una grafica de adorno es
    lo contrario.
--}}
<div class="flex h-full flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-linea p-6 text-center">
    <x-filament::icon icon="heroicon-o-chart-bar" class="h-8 w-8 text-apagado" />

    <p class="text-sm font-medium text-tinta">Aún sin muestra suficiente</p>

    <p class="max-w-sm text-xs text-tenue">
        El observatorio ya mide {{ $que }}, pero hoy hay
        <span class="font-medium text-aviso">{{ $serie->rotuloDeMuestra() }}</span>
        y hacen falta al menos {{ \App\Panel\SerieDelObservatorio::MUESTRA_MINIMA }}
        para afirmar algo. La gráfica se llenará sola cuando el sector alimente el dato.
    </p>
</div>
```

- [ ] **Step 4: Ejecutar hasta verde**

```bash
php artisan test --compact --filter=ComponentesDelPanelTest
php artisan test --compact --filter=TemaClaroOscuroTest
```

Esperado: PASS en ambos.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/components/panel/sin-muestra.blade.php tests/Feature/Panel/ComponentesDelPanelTest.php
git commit -m "Anade el estado vacio honesto del observatorio

Dibujar barras sobre tres observaciones sugiere una tendencia que no
existe. Con 7 vacantes en 7 areas, las metricas de empleo no pueden
afirmar nada todavia, y decirlo es informacion; una grafica de adorno es
lo contrario."
```

---

## Task 4: Página `Observatorio` con su banda de KPIs

**Files:**
- Create: `app/Filament/Pages/Observatorio.php`
- Create: `resources/views/filament/pages/observatorio.blade.php`
- Modify: `database/seeders/RolYPermisoSeeder.php`
- Test: `tests/Feature/Panel/ObservatorioTest.php`

**Interfaces:**
- Consumes: `MetricasDelObservatorio` (Tarea 2), `<x-panel.kpi>` (ya existe desde F1).
- Produces: página en `/admin/observatorio`, permiso `ver_observatorio`, y `public function metricas(): MetricasDelObservatorio` que la vista y los widgets consumen.

**El permiso es exclusivo de la dirección.** Se añade `ver_observatorio` a la lista de permisos que `RolYPermisoSeeder` reserva a `super_admin` —junto a `ver_usuario`, `ver_ajustes`, `ver_cartera`, `ver_transaccion` y `ver_bitacora`— y **no** se concede a `subadmin`. Razón: el observatorio incluye salud financiera, que la secretaría no ve en ninguna otra pantalla; y su propósito es el argumento institucional, que es trabajo de dirección. La lista de exclusión de `subadmin` ya filtra por sufijo, así que comprueba si `_observatorio` cae dentro o hay que añadirlo explícitamente.

**Esta tarea resuelve además un hallazgo abierto de la rama anterior:** `<x-panel.kpi>` tenía cinco pruebas y **cero consumidores**. La banda de KPIs del observatorio es su consumidor previsto.

- [ ] **Step 1: Escribir las pruebas que fallan**

```bash
php artisan make:test --phpunit Panel/ObservatorioTest --no-interaction
```

`tests/Feature/Panel/ObservatorioTest.php`:

```php
<?php

namespace Tests\Feature\Panel;

use App\Filament\Pages\Observatorio;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El observatorio es el argumento que la dirección lleva a una alcaldía.
 */
class ObservatorioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_la_direccion_entra_al_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->get(Observatorio::getUrl())
            ->assertOk()
            ->assertSee('Observatorio del gremio');
    }

    /**
     * La frontera negativa, que es la que prueba algo: el observatorio lleva
     * salud financiera, y la secretaría no ve dinero en ninguna otra pantalla.
     */
    public function test_la_secretaria_no_entra_al_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        $this->assertFalse(Observatorio::canAccess());
        $this->get(Observatorio::getUrl())->assertForbidden();
    }

    public function test_la_banda_de_kpis_usa_el_componente_del_panel_y_rotula_su_n(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $respuesta = $this->get(Observatorio::getUrl());

        $respuesta->assertOk()
            // El componente KPI rinde este enlace cuando recibe `url`.
            ->assertSee('Ver detalle')
            // Y el principio #5 del spec: ninguna cifra sin su n.
            ->assertSee('n = ', false);
    }
}
```

- [ ] **Step 2: Ejecutar para verificar que fallan**

```bash
php artisan test --compact --filter=ObservatorioTest
```

Esperado: FAIL con «Class "App\Filament\Pages\Observatorio" not found».

- [ ] **Step 3: Crear la página y su vista**

Copia el estilo de `app/Filament/Pages/Bitacora.php` para las propiedades estáticas (`$navigationIcon`, `$navigationGroup`, `$navigationSort`, `$title`, `$navigationLabel`, `$slug`, `$view`). Grupo de navegación: **`Gremio`** (ya existe en `AdminPanelProvider::navigationGroups()`).

La página expone un método `metricas()` memoizado y controla el acceso con **el patrón exacto que ya usan `Bitacora` y `AjustesDelSitio`** (verificado en el código, no deducido): `canAccess()` viene del trait `Filament\Pages\Concerns\CanAuthorizeAccess`, y las páginas de este proyecto lo emparejan con un `mount()` que aborta:

```php
    public static function canAccess(): bool
    {
        return auth()->user()?->can('ver_observatorio') === true;
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);
    }
```

**Las dos partes son necesarias.** `canAccess()` por sí solo gobierna la navegación; el `abort_unless` del `mount()` es lo que produce el 403 que afirma la prueba. Sin él, la frontera negativa no pasaría.

La vista `resources/views/filament/pages/observatorio.blade.php` rinde una banda de cuatro `<x-panel.kpi>` con las cifras cabecera del gremio —elígelas de entre lo que el servicio ya calcula, y **pásales su `n`**— y debajo la rejilla de widgets.

**⚠️ Verifica en `vendor/filament/` cómo una página con `$view` propio rinde sus widgets.** No lo deduzcas: en este proyecto ya costó una ronda entera suponer una API de Filament que no existía (`Panel::getAssets()`). Averigua el mecanismo real —`getHeaderWidgets()`, el componente `x-filament-widgets::widgets`, o lo que use la versión 4.12— y déjalo anotado en tu informe.

- [ ] **Step 4: Añadir el permiso**

En `database/seeders/RolYPermisoSeeder.php`, añade `'ver_observatorio'` a la lista de permisos exclusivos de dirección (líneas ~63-69). **Comprueba que la exclusión de `subadmin` lo deja fuera**: la lista filtra por sufijos concretos, así que si `_observatorio` no está entre ellos, añádelo.

- [ ] **Step 5: Ejecutar hasta verde**

```bash
php artisan migrate:fresh --seed --no-interaction
php artisan test --compact --filter=ObservatorioTest
```

Esperado: PASS, 3 pruebas.

- [ ] **Step 6: Demostrar que la frontera negativa discrimina**

Concede temporalmente `ver_observatorio` a `subadmin` en el sembrador, resiembra, y comprueba que `test_la_secretaria_no_entra_al_observatorio` **falla**. Restaura, resiembra, confirma verde. Pega el rojo y el verde en el informe.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Pages/Observatorio.php resources/views/filament/pages database/seeders/RolYPermisoSeeder.php tests/Feature/Panel/ObservatorioTest.php
git commit -m "Anade la pagina del observatorio del gremio

Exclusiva de direccion: lleva salud financiera, que la secretaria no ve
en ninguna otra pantalla, y su proposito es el argumento institucional.
La banda de KPIs estrena por fin x-panel.kpi, que llevaba cinco pruebas
y ningun consumidor desde F1."
```

---

## Task 5: Las tres visualizaciones con datos sólidos

**Files:**
- Create: `app/Filament/Widgets/Observatorio/PresenciaPorMunicipio.php`
- Create: `app/Filament/Widgets/Observatorio/ComposicionDelSector.php`
- Create: `app/Filament/Widgets/Observatorio/SaludFinanciera.php`
- Modify: `tests/Feature/Panel/ObservatorioTest.php`

**Interfaces:**
- Consumes: `MetricasDelObservatorio`, `SerieDelObservatorio`.
- Produces: tres `ChartWidget` registrados en la página `Observatorio`, cada uno con su `heading`, su tipo de gráfica y **su rótulo de muestra visible**.

**Reglas de los tres:**
- Las claves `ticks` y `grid` deben **existir** en `getOptions()`, aunque vengan vacías: el plugin `resources/js/panel-graficas.js` solo escribe donde ya hay clave, y sin ellas los ejes se quedan en el gris de fábrica. Es la convención que sostiene todo el trabajo de tema del panel.
- El rótulo `n = …` va visible junto al título, no escondido.
- `PresenciaPorMunicipio` es de barras **horizontales** (`indexAxis: 'y'`): doce municipios con nombre largo no caben en vertical.
- `SaludFinanciera` cubre **dieciocho meses**, no el año en curso — ésa es la diferencia deliberada con `RecaudoMensual` del tablero, que es operativo. Ponlo en el comentario.

- [ ] **Step 1: Escribir las pruebas que fallan**

Añade a `ObservatorioTest`:

```php
    public function test_las_tres_visualizaciones_solidas_dibujan_y_rotulan_su_muestra(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        foreach ([
            \App\Filament\Widgets\Observatorio\PresenciaPorMunicipio::class,
            \App\Filament\Widgets\Observatorio\ComposicionDelSector::class,
            \App\Filament\Widgets\Observatorio\SaludFinanciera::class,
        ] as $widget) {
            \Livewire\Livewire::test($widget)->assertSee('n = ');
        }
    }

    public function test_los_widgets_del_observatorio_dejan_hueco_al_plugin_de_tema(): void
    {
        foreach (\Illuminate\Support\Facades\File::allFiles(app_path('Filament/Widgets/Observatorio')) as $archivo) {
            $contenido = $archivo->getContents();

            $this->assertStringContainsString(
                "'ticks'",
                $contenido,
                $archivo->getFilename().' debe declarar ticks aunque venga vacio: el plugin de tema solo escribe donde ya hay clave.'
            );
            $this->assertStringContainsString("'grid'", $contenido, $archivo->getFilename().' debe declarar grid.');
        }
    }
```

- [ ] **Step 2: Ejecutar para verificar que fallan**

```bash
php artisan test --compact --filter=ObservatorioTest
```

Esperado: FAIL por clases inexistentes.

- [ ] **Step 3: Crear los tres widgets**

Copia la estructura de `app/Filament/Widgets/RecaudoMensual.php`: `heading`, `getData()`, `getType()`, `getOptions()`, `canView()`. Los tres leen del servicio, no consultan por su cuenta.

`canView()` de los tres exige `ver_observatorio`.

- [ ] **Step 4: Registrarlos en la página**

Por el mecanismo que averiguaste en la Tarea 4.

- [ ] **Step 5: Ejecutar hasta verde y mirar la pantalla**

```bash
php artisan test --compact --filter=ObservatorioTest
```

Y abre `/admin/observatorio` como `direccion@asobaresquindio.test` (`Asobares2026*`). **Comprueba en los dos temas** que los ejes siguen el tema y que los rótulos de muestra se leen. Si el navegador de la sesión no compone fotogramas, verifica por DOM y dilo en el informe.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Widgets/Observatorio tests/Feature/Panel/ObservatorioTest.php app/Filament/Pages/Observatorio.php
git commit -m "Anade las tres visualizaciones del observatorio que si tienen datos

Presencia por municipio, composicion del sector y salud financiera a 18
meses. Las tres declaran ticks y grid vacios para que el plugin de tema
tenga donde escribir, y ensenan su n al lado del titulo."
```

---

## Task 6: Las tres visualizaciones flacas, con su estado vacío

**Files:**
- Create: `app/Filament/Widgets/Observatorio/CoberturaDeProveedores.php`
- Create: `app/Filament/Widgets/Observatorio/DemandaLaboralPorArea.php`
- Create: `app/Filament/Widgets/Observatorio/OfertaContraDemanda.php`
- Modify: `tests/Feature/Panel/ObservatorioTest.php`

**Interfaces:**
- Consumes: `MetricasDelObservatorio`, `<x-panel.sin-muestra>` (Tarea 3).
- Produces: tres widgets que **deciden si dibujan** según `hayMuestraSuficiente()`.

**Éste es el corazón honesto del módulo.** Un `ChartWidget` de Filament siempre dibuja; para poder no dibujar, estos tres necesitan una vista propia que decida. Averigua el camino más limpio en Filament 4.12 —un `Widget` con `$view` propio que rinda o bien el gráfico o bien `<x-panel.sin-muestra>`— y **anótalo en tu informe**.

- [ ] **Step 1: Escribir las pruebas que fallan**

```php
    /**
     * Con la semilla de hoy la oferta contra demanda no llega al umbral, así
     * que la pantalla tiene que decirlo en vez de dibujar barras sobre n=2.
     */
    public function test_una_visualizacion_sin_muestra_no_dibuja_y_lo_explica(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        \Livewire\Livewire::test(\App\Filament\Widgets\Observatorio\OfertaContraDemanda::class)
            ->assertSee('Aún sin muestra suficiente')
            ->assertSee('n = ');
    }

    /**
     * Y cuando el dato llegue, dibuja. Si esta prueba se cae, es que el
     * estado vacío se quedó pegado y el observatorio nunca enseñará empleo.
     */
    public function test_la_misma_visualizacion_dibuja_en_cuanto_hay_muestra(): void
    {
        // `publicado()` es un estado real de ambas factories, verificado.
        $asociado = \App\Models\Asociado::factory()->publicado()->create();
        \App\Models\Vacante::factory()->count(35)->publicado()->for($asociado)->create([
            'categoria_cargo' => \App\Enums\CargoDelSector::Barra,
        ]);

        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        \Livewire\Livewire::test(\App\Filament\Widgets\Observatorio\OfertaContraDemanda::class)
            ->assertDontSee('Aún sin muestra suficiente');
    }
```

- [ ] **Step 2: Ejecutar para verificar que fallan**

```bash
php artisan test --compact --filter=ObservatorioTest
```

- [ ] **Step 3: Crear los tres widgets con su bifurcación**

**`CoberturaDeProveedores` incluye las categorías con cero.** El hueco es la información: «no hay ni un proveedor de mantenimiento en la base» es exactamente lo que el gremio necesita saber.

- [ ] **Step 4: Ejecutar hasta verde**

```bash
php artisan test --compact --filter=ObservatorioTest
```

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Widgets/Observatorio tests/Feature/Panel/ObservatorioTest.php
git commit -m "Anade las tres visualizaciones flacas con su estado vacio honesto

Con 7 vacantes en 7 areas, dibujar barras sugiere una tendencia que no
existe. Estas tres deciden si dibujan segun su muestra, y cuando no da,
dicen cuantos datos hay y cuantos faltan. La cobertura de proveedores
incluye las categorias en cero a proposito: el hueco es la informacion."
```

---

## Task 7: Informe imprimible

**Files:**
- Create: `app/Filament/Pages/InformeDelObservatorio.php`
- Create: `resources/views/filament/pages/informe-del-observatorio.blade.php`
- Modify: `resources/css/filament/admin/theme.css`
- Modify: `app/Filament/Pages/Observatorio.php` (acción «Descargar informe»)
- Modify: `tests/Feature/Panel/ObservatorioTest.php`

**Interfaces:**
- Consumes: `MetricasDelObservatorio`.
- Produces: `/admin/observatorio/informe`, mismo permiso, pensada para papel.

**Lo que convierte esto en herramienta gremial no es la pantalla, es el objeto que la dirección deja sobre la mesa.** El informe lleva: la marca del gremio, la fecha de generación, las seis series **con su n**, y un descargo explícito sobre las que no alcanzan muestra.

**CSS de impresión:** `@page { margin }`, saltos controlados (`break-inside: avoid` en cada bloque), y ocultar el cromo del panel al imprimir (`.fi-sidebar`, `.fi-topbar` y los botones). Todo dentro de `@media print` en el tema. Sin colores cableados.

- [ ] **Step 1: Escribir las pruebas que fallan**

```php
    public function test_el_informe_lleva_marca_fecha_y_el_n_de_cada_serie(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $this->get(\App\Filament\Pages\InformeDelObservatorio::getUrl())
            ->assertOk()
            ->assertSee('ASOBARES')
            ->assertSee(now()->format('d/m/Y'))
            ->assertSee('n = ', false)
            // El descargo es obligatorio: hay series que no alcanzan muestra.
            ->assertSee('muestra');
    }

    public function test_el_informe_es_igual_de_exclusivo_que_el_observatorio(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        $this->get(\App\Filament\Pages\InformeDelObservatorio::getUrl())->assertForbidden();
    }

    public function test_el_tema_esconde_el_cromo_del_panel_al_imprimir(): void
    {
        $tema = \Illuminate\Support\Facades\File::get(
            resource_path('css/filament/admin/theme.css')
        );

        $this->assertStringContainsString('@media print', $tema);
    }
```

- [ ] **Step 2: Ejecutar para verificar que fallan**

```bash
php artisan test --compact --filter=ObservatorioTest
```

- [ ] **Step 3: Crear la página, la vista y el CSS de impresión**

- [ ] **Step 4: Añadir la acción en el observatorio**

Un botón «Descargar informe» que lleve a la página del informe. Comenta que el PDF lo produce el navegador y por qué se eligió así: **cero dependencias nuevas, y el informe sale con la tipografía y los colores reales del gremio.**

- [ ] **Step 5: Ejecutar y comprobar la impresión**

```bash
php artisan view:clear && npm run build
php artisan test --compact --filter=ObservatorioTest
```

Abre el informe y usa la vista previa de impresión del navegador. **Comprueba que el cromo del panel desaparece y que ningún bloque se parte a mitad.** Si no puedes previsualizar, inspecciona los estilos aplicados con `matchMedia('print')` y dilo en el informe.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Pages resources/views/filament/pages resources/css/filament/admin/theme.css tests/Feature/Panel/ObservatorioTest.php
git commit -m "Anade el informe imprimible del observatorio

Lo que convierte esto en herramienta gremial no es la pantalla, es el
objeto que la direccion deja sobre la mesa en una alcaldia. Se produce
con CSS de impresion y lo convierte el navegador: cero dependencias
nuevas y sale con la tipografia y los colores reales del gremio."
```

---

## Task 8: Verificación de conjunto

**Files:**
- Modify: `tests/Feature/Panel/ObservatorioTest.php` (si algo falta)

- [ ] **Step 1: Verificación completa**

```bash
php artisan migrate:fresh --seed --no-interaction
php artisan view:clear && npm run build
php artisan test --compact
```

Esperado: **toda la suite en verde**. El punto de partida de esta fase son 458 pruebas. Si algo preexistente se rompe, **arréglalo afirmando la regla nueva**, nunca excluyendo el caso.

- [ ] **Step 2: Recorrido real**

Entra como `direccion@asobaresquindio.test` y como `oficina@asobaresquindio.test` (`Asobares2026*`) y comprueba:
- La dirección ve el observatorio en el menú; la secretaría **no**.
- Las seis visualizaciones: tres dibujan, tres explican por qué no.
- Los rótulos `n = …` se leen en todas.
- Los dos temas: ejes que siguen el tema, nada ilegible.
- El informe imprimible en vista previa.

**Pega en tu informe el texto real de las cuatro tarjetas KPI y el estado de cada una de las seis visualizaciones.**

- [ ] **Step 3: Medir que la página no dispara consultas de más**

```bash
php artisan tinker --execute '...'
```

Con `DB::listen` alrededor de un render completo de la página, cuenta las consultas y **pégalo en el informe**. La memoización del servicio debería mantenerlo acotado; si aparecen decenas, algo está pidiendo instancias nuevas —es exactamente el fallo que ya apareció en el tablero con `ColaDePendientes`.

- [ ] **Step 4: Commit final si hizo falta arreglar algo**

---

## Autorrevisión del plan

**Cobertura del spec §4:**

| Requisito | Tarea |
|---|---|
| Demanda laboral por área y mes | 2, 6 |
| Oferta contra demanda por área | 2, 6 |
| Presencia por municipio (barras, decisión del 9 ago) | 2, 5 |
| Composición del sector | 2, 5 |
| Cobertura de proveedores (con las de cero) | 2, 6 |
| Salud financiera a 18 meses | 2, 5 |
| Sin solapamiento con el tablero (18 meses vs año en curso) | 5 |
| Botón «Descargar informe» | 7 |
| Cada gráfica con su n | 1, 3, 5, 6 |
| Aviso de muestra pequeña | 1, 3, 6 |

**Desviaciones conscientes del spec, ambas decididas por el dueño el 9 ago 2026:**

1. **«Mapa de calor» pasa a barras horizontales ordenadas.** Los municipios no tienen coordenadas y el componente Leaflet del sitio publica sus assets en stacks que el panel no tiene. Las barras cumplen el propósito declarado —«dónde está el gremio y dónde hay movimiento»— y comparan las tres señales mejor que un mapa. El mapa queda como mejora identificada.
2. **El informe se produce con CSS de impresión**, no con una librería de PDF. `CLAUDE.md` exige aprobación para dependencias nuevas y no hay ninguna librería de PDF en el proyecto; `GeneradorPdf` escribe PDF 1.4 a mano para los formatos de relleno del sembrador y no sirve para esto.

**Supuestos verificados contra el código antes de dar el plan por bueno:**

- `AsociadoFactory::publicado()` y `VacanteFactory::publicado()` **existen** como estados (`database/factories/`), igual que `VacanteFactory::pendiente()` y `::cerrada()`.
- `canAccess()` viene del trait `Filament\Pages\Concerns\CanAuthorizeAccess`, y el proyecto lo empareja con `abort_unless(self::canAccess(), 403)` en `mount()` — patrón copiado literalmente de `Bitacora.php:64-72` y `AjustesDelSitio.php:57-64`, no deducido.
- `CargoDelSector` tiene **7 casos** (`administracion`, `cocina`, `barra`, `servicio`, `seguridad`, `aseo`, `otros`) y `CategoriaProveedor` otros 7 (`hielo`, `licores`, `alimentos`, `aseo`, `seguridad`, `mantenimiento`, `otros`).
- `municipios` tiene **solo** `id, nombre, slug, timestamps` — sin coordenadas. De ahí que el mapa pase a barras.
- El grupo de navegación `Gremio` ya existe en `AdminPanelProvider::navigationGroups()`.

**Riesgos abiertos que el implementador debe verificar, no asumir:**

- **Cómo rinde widgets una página de Filament 4.12 con `$view` propio.** En este proyecto ya costó una ronda entera suponer que `Panel::getAssets()` existía. Las Tareas 4 y 6 exigen mirarlo en `vendor/` y anotarlo.
- **Si un `ChartWidget` puede no dibujar.** La Tarea 6 depende de ello; puede que haga falta un `Widget` con vista propia en vez de un `ChartWidget`.
- **La tasa de mora en `saludFinanciera()`** es hoy un valor único, no una serie: `carteras` guarda el estado actual, no su historia. El plan deja al implementador decidir cómo representarlo y **exige que lo comente**. Si acaba siendo una línea plana, conviene decirlo en el informe antes que dibujar una tendencia falsa — el mismo principio que ordena todo este módulo.
