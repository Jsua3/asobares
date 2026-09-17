<?php

namespace Tests\Feature\Panel;

use App\Console\Commands\CrearUsuarioDelPanel;
use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Asociados\Pages\ListAsociados;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\User;
use App\Services\ImportacionDeLaBaseDelGremio;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/**
 * La acción del panel con la que la dirección carga la base del gremio en
 * producción. Las reglas de fondo están probadas en los servicios; aquí se
 * prueba la puerta: quién la ve, qué contraseña acepta, que el archivo no se
 * queda en el disco y que un fallo se dice.
 */
class ImportarBaseDelGremioTest extends TestCase
{
    use RefreshDatabase;

    private const string GENERICA = 'Provisional-Quindio-2026!';

    /** Ruta del estado del formulario de la acción montada, en el MessageBag de Livewire. */
    private const string CAMPO_CONTRASENA = 'mountedActions.0.data.contrasena_generica';

    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        Categoria::query()->create(['nombre' => 'Bar', 'slug' => 'bar']);
        Municipio::query()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);

        $this->archivo = tempnam(sys_get_temp_dir(), 'asobares').'.xlsx';

        // Las subidas de otras pruebas se quedan en el disco temporal de
        // Livewire: se vacía para que «no quedó nada» mida solo esta prueba.
        FileUploadConfiguration::storage()->deleteDirectory(FileUploadConfiguration::directory());
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivo)) {
            unlink($this->archivo);
        }

        parent::tearDown();
    }

    private function usuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    /** @param  list<list<string>>  $filas */
    private function subida(array $filas): UploadedFile
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

        return UploadedFile::fake()->createWithContent('base.xlsx', (string) file_get_contents($this->archivo));
    }

    /** @return list<string> */
    private function fila(string $nombre, string $correo): array
    {
        return [$nombre, 'Duena del Local', 'Descripción', '900123456', 'Calle 1', 'Armenia', '3001234567', $correo, '', '', '', '', '', ''];
    }

    public function test_la_direccion_ve_la_accion(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)->assertActionVisible('importar');
    }

    /** La secretaría crea asociados pero no usuarios: no puede repartir accesos. */
    public function test_la_secretaria_no_ve_la_accion(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUBADMIN));

        Livewire::test(ListAsociados::class)->assertActionHidden('importar');
    }

    public function test_importa_fichas_en_borrador_y_crea_cuentas_provisionales(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test'), $this->fila('Bar Dos', 'dos@bar.test')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => self::GENERICA,
                'contrasena_generica_confirmation' => self::GENERICA,
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame(2, Asociado::query()->where('estado', EstadoPublicacion::Borrador)->count());
        $this->assertSame(2, User::role(User::ROL_ASOCIADO)->where('contrasena_provisional', true)->count());
    }

    public function test_sin_marcar_la_casilla_no_crea_cuentas(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame(1, Asociado::query()->count());
        $this->assertSame(0, User::role(User::ROL_ASOCIADO)->count());
    }

    /** @return array<string, array{string, string}> */
    public static function contrasenasQueNoSirven(): array
    {
        return [
            'la del demo' => [CrearUsuarioDelPanel::CLAVE_PUBLICADA, 'Esa es la contraseña del demo, publicada en el repositorio. Elige otra.'],
            'sin símbolo' => ['CordilleraQuindio2026', 'La contraseña necesita al menos un símbolo.'],
            'sin número' => ['Cordillera-Quindio!', 'La contraseña necesita al menos un número.'],
            'sin mayúscula' => ['cordillera-quindio-2026!', 'La contraseña necesita al menos una mayúscula y una minúscula.'],
            'corta' => ['Corta-1!a', 'La contraseña necesita al menos 12 caracteres.'],
        ];
    }

    #[DataProvider('contrasenasQueNoSirven')]
    public function test_la_contrasena_generica_tiene_que_cumplir_la_politica(string $clave, string $mensaje): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $componente = Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => $clave,
                'contrasena_generica_confirmation' => $clave,
            ])
            ->assertHasFormErrors(['contrasena_generica']);

        // Si esta aserción no encuentra la clave, imprime
        // array_keys($componente->errors()->toArray()) para ver la ruta real.
        $this->assertContains($mensaje, $componente->errors()->get(self::CAMPO_CONTRASENA));
        $this->assertSame(0, Asociado::query()->count());
    }

    public function test_la_confirmacion_tiene_que_coincidir(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $componente = Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => self::GENERICA,
                'contrasena_generica_confirmation' => 'Otra-Distinta-2026!',
            ])
            ->assertHasFormErrors(['contrasena_generica']);

        $this->assertContains('La confirmación no coincide.', $componente->errors()->get(self::CAMPO_CONTRASENA));
    }

    public function test_el_archivo_subido_no_se_queda_en_el_disco(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame([], FileUploadConfiguration::storage()->allFiles(FileUploadConfiguration::directory()));
    }

    public function test_si_la_importacion_revienta_lo_dice_y_borra_el_archivo(): void
    {
        $this->app->instance(ImportacionDeLaBaseDelGremio::class, new class extends ImportacionDeLaBaseDelGremio
        {
            public function __construct() {}

            public function importar(string $ruta, string $categoriaPorDefecto, ?string $contrasenaGenerica): array
            {
                throw new RuntimeException('Falla simulada');
            }
        });

        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
            ])
            ->assertNotified('La importación no se aplicó');

        $this->assertSame([], FileUploadConfiguration::storage()->allFiles(FileUploadConfiguration::directory()));
    }
}
