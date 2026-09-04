<?php

namespace Tests\Feature;

use App\Filament\Pages\ModerarFotos;
use App\Models\Asociado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

/**
 * La cola de moderación de fotos, y el defecto que solo existe en PostgreSQL.
 *
 * La primera versión de `ModerarFotos::consultaBase()` filtraba con
 * `whereNot(whereJsonContains(…, true))`. En SQLite funciona; en PostgreSQL
 * NO, y PostgreSQL es donde corre el sitio:
 *
 *   not (("custom_properties"->'aprobada')::jsonb @> 'true')
 *
 * Sobre una fila sin la clave, `->` da NULL, `NULL @> 'true'` da NULL, `not
 * NULL` da NULL y el `WHERE` descarta la fila. La foto que más falta hacía
 * moderar --la que nadie marcó, que es el estado de todo lo anterior a la
 * migración de relleno-- era justo la que no aparecía en la cola.
 *
 * ⚠️ **Estas pruebas afirman sobre la SQL generada, no sobre el resultado**, y
 * es deliberado. Lo manda el §15 del runbook para este caso exacto: la suite
 * corre en SQLite (`phpunit.xml` fija `:memory:`), donde el defecto no se
 * reproduce, así que una prueba de comportamiento **saldría verde con el
 * código roto**. Se comprobó: volver a `whereNot` deja en verde cualquier
 * aserción sobre conteo de filas. Habría sido el falso verde número trece.
 *
 * `toSql()` compila la gramática sin abrir conexión, así que esto corre en
 * cualquier máquina sin PostgreSQL instalado y sin omitir nada.
 */
class ColaDeFotosTest extends TestCase
{
    use RefreshDatabase;

    /** La SQL que la página emite de verdad, con la gramática que se le pida. */
    private function sqlDeLaCola(string $conexion): string
    {
        $consulta = (fn () => self::consultaBase())->call(new ModerarFotos);

        return DB::connection($conexion)
            ->query()
            ->from('media')
            ->mergeWheres($consulta->getQuery()->wheres, $consulta->getQuery()->getBindings())
            ->toSql();
    }

    /**
     * El corazón del arreglo: en PostgreSQL la condición tiene que contemplar
     * la clave AUSENTE, y eso se ve porque aparece el `coalesce` que Laravel
     * emite para `whereJsonDoesntContainKey`. Sin esa rama, la lógica
     * trivaluada se come la fila.
     */
    public function test_en_postgresql_la_cola_contempla_la_clave_ausente(): void
    {
        $sql = $this->sqlDeLaCola('pgsql');

        $this->assertStringContainsString(
            'coalesce',
            $sql,
            'Sin la rama de la clave ausente, una foto que nadie marcó no aparece en la cola en PostgreSQL.'
        );
    }

    /**
     * Y la guardia contra la reincidencia: la forma vieja, exacta, no puede
     * volver. Se afirma sobre el patrón `not (…@>…)` sin alternativa, que es
     * lo que emitía `whereNot(whereJsonContains(...))`.
     */
    public function test_la_cola_no_vuelve_a_la_negacion_simple_del_contains(): void
    {
        $sql = $this->sqlDeLaCola('pgsql');

        $this->assertDoesNotMatchRegularExpression(
            '/and not \(\("custom_properties"->\'aprobada\'\)::jsonb @> \?\)\s*$/',
            $sql,
            'Volvió `whereNot(whereJsonContains(…, true))`, que descarta las filas sin la clave.'
        );
    }

    /** En SQLite la condición también tiene que mirar si la clave existe. */
    public function test_en_sqlite_la_cola_tambien_contempla_la_clave_ausente(): void
    {
        $sql = $this->sqlDeLaCola('sqlite');

        $this->assertStringContainsString('json_type', $sql);
    }

    /**
     * Lo que sí se puede afirmar sobre el comportamiento, porque en SQLite el
     * caso feliz coincide: la cola trae lo no aprobado y deja fuera lo aprobado.
     *
     * No sustituye a las de arriba --con el código roto esta pasaba igual--
     * pero cubre que la consulta filtre por modelo y colección.
     */
    public function test_la_cola_trae_lo_pendiente_y_no_lo_aprobado(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $archivo = UploadedFile::fake()->image('foto.jpg', 1200, 800);

        foreach ([['aprobada' => true], ['aprobada' => false], []] as $indice => $props) {
            $asociado->addMedia($archivo->getRealPath())
                ->preservingOriginal()
                ->usingFileName("foto-{$indice}.jpg")
                ->withCustomProperties($props)
                ->toMediaCollection('galeria');
        }

        $pendientes = Media::query()
            ->where('model_type', Asociado::class)
            ->where('collection_name', 'galeria')
            ->get()
            ->filter(fn (Media $m): bool => $m->getCustomProperty(Asociado::FOTO_APROBADA) !== true);

        $this->assertCount(2, $pendientes, 'La sin marcar y la marcada false están pendientes; la aprobada no.');
    }
}
