<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\User;
use App\Services\AltaDeCuentasDeAfiliados;
use App\Services\ImportacionDeLaBaseDelGremio;
use App\Services\ResultadoDeAltaDeCuentas;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;
use Tests\TestCase;

/**
 * Fichas y cuentas de la base del gremio, como un solo bloque: si las cuentas
 * revientan, no queda ni una ficha nueva.
 */
class ImportacionDeLaBaseDelGremioTest extends TestCase
{
    use RefreshDatabase;

    private const string GENERICA = 'Provisional-Quindio-2026!';

    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        Categoria::query()->create(['nombre' => 'Bar', 'slug' => 'bar']);
        Municipio::query()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);

        $this->archivo = tempnam(sys_get_temp_dir(), 'asobares').'.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivo)) {
            unlink($this->archivo);
        }

        parent::tearDown();
    }

    /** @param  list<list<string>>  $filas */
    private function archivoComoElDelGremio(array $filas): string
    {
        $escritor = new Writer;
        $escritor->openToFile($this->archivo);

        $escritor->addRow(Row::fromValues(['BASE DE DATOS QUINDIO']));

        foreach ([1, 2, 3, 4] as $vacia) {
            $escritor->addRow(Row::fromValues(['']));
        }

        $escritor->addRow(Row::fromValues([
            'Nombre del Establecimiento', 'Nombre', 'Descripción del establecimiento', 'NIT',
            'Dirección', 'Municipio', 'Telefono', 'Correo', 'Horario de Atención',
            'Genero Musical', 'Servicios ofrecidos', 'Perfil Instagram', '', 'Menciones adicionales',
        ]));

        foreach ($filas as $fila) {
            $escritor->addRow(Row::fromValues($fila));
        }

        $escritor->close();

        return $this->archivo;
    }

    /** @return list<string> */
    private function fila(string $nombre, string $correo, string $municipio = 'Armenia'): array
    {
        return [$nombre, 'Duena del Local', 'Descripción', '900123456', 'Calle 1', $municipio, '3001234567', $correo, '', '', '', '', '', ''];
    }

    public function test_importa_las_fichas_y_crea_las_cuentas_pedidas(): void
    {
        $ruta = $this->archivoComoElDelGremio([
            $this->fila('Bar Uno', 'uno@bar.test'),
            $this->fila('Bar Dos', 'dos@bar.test'),
        ]);

        $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);

        $this->assertSame(2, $resultado['carga']->creados());
        $this->assertSame(2, $resultado['cuentas']?->creadas());
        $this->assertSame(2, User::role(User::ROL_ASOCIADO)->where('contrasena_provisional', true)->count());
    }

    public function test_sin_contrasena_no_crea_cuentas(): void
    {
        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar Uno', 'uno@bar.test')]);

        $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', null);

        $this->assertNull($resultado['cuentas']);
        $this->assertSame(1, Asociado::query()->count());
        $this->assertSame(0, User::query()->count());
    }

    public function test_solo_crea_cuentas_para_las_fichas_del_archivo(): void
    {
        Asociado::factory()->create(['correo_interno' => 'ajena@bar.test']);
        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar Uno', 'uno@bar.test')]);

        app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);

        $this->assertSame(0, User::query()->where('email', 'ajena@bar.test')->count());
        $this->assertSame(1, User::query()->where('email', 'uno@bar.test')->count());
    }

    public function test_una_fila_rechazada_no_recibe_cuenta(): void
    {
        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar de Afuera', 'afuera@bar.test', 'Pereira')]);

        $resultado = app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);

        $this->assertTrue($resultado['carga']->tieneErrores());
        $this->assertSame(0, User::query()->count());
    }

    public function test_si_las_cuentas_revientan_no_queda_ninguna_ficha_nueva(): void
    {
        $this->app->instance(AltaDeCuentasDeAfiliados::class, new class extends AltaDeCuentasDeAfiliados
        {
            public function crear(Collection $fichas, string $contrasenaGenerica): ResultadoDeAltaDeCuentas
            {
                throw new RuntimeException('Falla simulada a mitad de las cuentas');
            }
        });

        $ruta = $this->archivoComoElDelGremio([$this->fila('Bar Uno', 'uno@bar.test')]);

        try {
            app(ImportacionDeLaBaseDelGremio::class)->importar($ruta, 'Bar', self::GENERICA);
            $this->fail('La falla de las cuentas tenía que subir.');
        } catch (RuntimeException) {
            // Esperado.
        }

        $this->assertSame(0, Asociado::query()->count());
        $this->assertSame(0, User::query()->count());
    }
}
