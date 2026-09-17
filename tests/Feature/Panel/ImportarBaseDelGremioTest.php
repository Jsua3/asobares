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
use DOMDocument;
use DOMElement;
use DOMXPath;
use Filament\Notifications\Livewire\Notifications as NotificacionesDelPanel;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

/**
 * La acción del panel con la que la dirección carga la base del gremio en
 * producción. Las reglas de fondo están probadas en los servicios; aquí se
 * prueba la puerta: quién la ve, qué contraseña acepta, que el archivo no se
 * queda en el disco, qué dice el aviso y que un fallo se dice.
 */
class ImportarBaseDelGremioTest extends TestCase
{
    use RefreshDatabase;

    private const string GENERICA = 'Provisional-Quindio-2026!';

    /** Rutas del estado del formulario de la acción montada, en el MessageBag de Livewire. */
    private const string CAMPO_CONTRASENA = 'mountedActions.0.data.contrasena_generica';

    private const string CAMPO_ARCHIVO = 'mountedActions.0.data.archivo';

    private const string CAMPO_CATEGORIA = 'mountedActions.0.data.categoria';

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
    private function subida(array $filas): File
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
    private function fila(string $nombre, string $correo, string $municipio = 'Armenia'): array
    {
        return [$nombre, 'Duena del Local', 'Descripción', '900123456', 'Calle 1', $municipio, '3001234567', $correo, '', '', '', '', '', ''];
    }

    /** La notificación que dejó la acción, reconstruida desde la sesión como lo hace el panel. */
    private function notificacionEnviada(): Notification
    {
        // No se usa ->assertNotified(): hace falta leer la notificación real,
        // no solo confirmar que hubo una, y ese método vacía la sesión con el
        // primer vistazo. Este es el mismo mecanismo que usa por dentro.
        $panelDeNotificaciones = new NotificacionesDelPanel;
        $panelDeNotificaciones->mount();
        $enviada = $panelDeNotificaciones->notifications->first();

        $this->assertInstanceOf(Notification::class, $enviada, 'Se esperaba una notificación.');

        return $enviada;
    }

    /** El cuerpo en el HTML que pinta el panel, después de su saneado. */
    private function cuerpoPintado(Notification $notificacion): DOMElement
    {
        $dom = new DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$notificacion->toHtml());
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        $cuerpo = (new DOMXPath($dom))
            ->query('//div[contains(concat(" ", normalize-space(@class), " "), " fi-no-notification-body ")]')
            ->item(0);

        $this->assertInstanceOf(DOMElement::class, $cuerpo, 'La notificación no pinta cuerpo.');

        return $cuerpo;
    }

    /**
     * Las líneas del cuerpo como se leen en pantalla: el texto que queda entre
     * un `<br>` y el siguiente.
     *
     * @return list<string>
     */
    private function lineasDelCuerpo(Notification $notificacion): array
    {
        $lineas = [''];

        foreach ($this->cuerpoPintado($notificacion)->childNodes as $nodo) {
            if ($nodo instanceof DOMElement && $nodo->tagName === 'br') {
                $lineas[] = '';

                continue;
            }

            $lineas[array_key_last($lineas)] .= $nodo->textContent;
        }

        return array_map(trim(...), $lineas);
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

    /**
     * «Bar Dos» no trae correo: la ficha entra igual, pero sin cuenta. El
     * aviso tiene que decir cuál ficha se quedó sin cuenta y por qué
     * (spec §4.2), no solo cuántas cuentas se crearon.
     */
    public function test_el_resumen_dice_cada_ficha_sin_cuenta_con_su_motivo(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test'), $this->fila('Bar Dos', '')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => self::GENERICA,
                'contrasena_generica_confirmation' => self::GENERICA,
            ])
            ->assertHasNoFormErrors();

        $this->assertSame(2, Asociado::query()->count());
        $this->assertSame(1, User::role(User::ROL_ASOCIADO)->count());

        $enviada = $this->notificacionEnviada();

        $this->assertSame('warning', $enviada->getStatus());
        $this->assertSame(
            'Fichas: 2 creadas · 0 actualizadas. Cuentas: 1 cuenta creada · 1 ficha sin cuenta.',
            $enviada->getTitle(),
        );
        $this->assertSame(['«Bar Dos»: sin correo'], $this->lineasDelCuerpo($enviada));
    }

    /**
     * El panel pinta el cuerpo como HTML: un salto de línea de texto no separa
     * nada y todas las fichas se leerían en un mismo párrafo.
     */
    public function test_cada_ficha_sin_cuenta_sale_en_su_propia_linea(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([
                    $this->fila('Bar Uno', 'uno@bar.test'),
                    $this->fila('Bar Dos', ''),
                    $this->fila('Bar Tres', 'tres@hotmail'),
                ]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => self::GENERICA,
                'contrasena_generica_confirmation' => self::GENERICA,
            ])
            ->assertHasNoFormErrors();

        $this->assertSame(
            ['«Bar Dos»: sin correo', '«Bar Tres»: correo inválido'],
            $this->lineasDelCuerpo($this->notificacionEnviada()),
        );
    }

    /**
     * Cada línea trae texto de la hoja, y el saneado del panel deja pasar
     * enlaces y estilos: lo que venga en la hoja se lee, no se pinta.
     */
    public function test_el_html_que_trae_la_hoja_sale_como_texto(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));
        $nombre = 'Bar <a href="https://x.test">Uno</a>';

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila($nombre, '', 'Pereira')]),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $enviada = $this->notificacionEnviada();

        $this->assertSame(0, $this->cuerpoPintado($enviada)->getElementsByTagName('a')->length);
        $this->assertSame(
            ["Fila 7: «{$nombre}»: el municipio «Pereira» no está en el catálogo del Quindío."],
            $this->lineasDelCuerpo($enviada),
        );
    }

    /** Hasta 40 líneas: las demás se cuentan al final, para no hacer del aviso un muro. */
    public function test_el_aviso_muestra_cuarenta_lineas_y_cuenta_las_demas(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $filas = [];

        for ($numero = 1; $numero <= 41; $numero++) {
            $filas[] = $this->fila(sprintf('Bar %02d', $numero), '', 'Pereira');
        }

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: ['archivo' => $this->subida($filas), 'categoria' => 'Bar'])
            ->assertHasNoFormErrors();

        $lineas = $this->lineasDelCuerpo($this->notificacionEnviada());

        $this->assertCount(41, $lineas);
        $this->assertStringStartsWith('Fila 7: «Bar 01»', $lineas[0]);
        $this->assertStringStartsWith('Fila 46: «Bar 40»', $lineas[39]);
        $this->assertSame('…y 1 más.', $lineas[40]);
    }

    /**
     * Volver a subir el archivo corregido: la ficha cuya cuenta ya existe se
     * cuenta en el título y no sale como una ficha sin cuenta que corregir.
     */
    public function test_reimportar_cuenta_aparte_las_fichas_que_ya_tenian_cuenta(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $importarConCuentas = function (): void {
            Livewire::test(ListAsociados::class)
                ->callAction('importar', data: [
                    'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                    'categoria' => 'Bar',
                    'crear_cuentas' => true,
                    'contrasena_generica' => self::GENERICA,
                    'contrasena_generica_confirmation' => self::GENERICA,
                ])
                ->assertHasNoFormErrors();
        };

        $importarConCuentas();
        $this->assertSame('success', $this->notificacionEnviada()->getStatus());

        $importarConCuentas();
        $enviada = $this->notificacionEnviada();

        $this->assertSame('success', $enviada->getStatus());
        $this->assertSame(
            'Fichas: 0 creadas · 1 actualizada. Cuentas: 0 cuentas creadas · 1 ficha ya tenía cuenta.',
            $enviada->getTitle(),
        );
    }

    /**
     * Fichas que ya existen, filas del archivo como [nombre, municipio] y el
     * título que tiene que salir.
     *
     * @return array<string, array{list<string>, list<array{string, string}>, string}>
     */
    public static function titulosDeLasFichas(): array
    {
        return [
            'una creada' => [[], [['Bar Uno', 'Armenia']], 'Fichas: 1 creada · 0 actualizadas.'],
            'dos actualizadas' => [['Bar Uno', 'Bar Dos'], [['Bar Uno', 'Armenia'], ['Bar Dos', 'Armenia']], 'Fichas: 0 creadas · 2 actualizadas.'],
            'una actualizada y una fila con problemas' => [['Bar Uno'], [['Bar Uno', 'Armenia'], ['Bar de Afuera', 'Pereira']], 'Fichas: 0 creadas · 1 actualizada · 1 con problemas.'],
        ];
    }

    /**
     * @param  list<string>  $existentes
     * @param  list<array{string, string}>  $filas
     */
    #[DataProvider('titulosDeLasFichas')]
    public function test_el_titulo_concuerda_con_las_fichas(array $existentes, array $filas, string $titulo): void
    {
        foreach ($existentes as $nombreExistente) {
            Asociado::factory()->create(['nombre' => $nombreExistente, 'slug' => Str::slug($nombreExistente)]);
        }

        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida(array_map(fn (array $fila): array => $this->fila($fila[0], '', $fila[1]), $filas)),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame($titulo, $this->notificacionEnviada()->getTitle());
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

    /**
     * El mensaje de una excepción de la base lleva los valores del SQL —correos,
     * nombres, el hash de la genérica— y en producción el registro sale por
     * stderr: lo que se reporta dice dónde falló, no con qué datos.
     */
    public function test_si_la_importacion_revienta_lo_dice_borra_el_archivo_y_reporta_sin_datos_de_la_hoja(): void
    {
        $falla = new RuntimeException('SQLSTATE[23505]: Key (email)=(duena@merlin.test) already exists.', 23505);

        $this->app->instance(ImportacionDeLaBaseDelGremio::class, new class($falla) extends ImportacionDeLaBaseDelGremio
        {
            public function __construct(private RuntimeException $falla) {}

            public function importar(string $ruta, string $categoriaPorDefecto, ?string $contrasenaGenerica): array
            {
                throw $this->falla;
            }
        });

        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Exceptions::fake();

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
            ])
            ->assertNotified('La importación no se aplicó');

        Exceptions::assertReportedCount(1);
        Exceptions::assertReported(fn (RuntimeException $reportada): bool => ! str_contains($reportada->getMessage(), 'duena@merlin.test')
            && $reportada->getPrevious() === null
            && str_contains($reportada->getMessage(), RuntimeException::class)
            && str_contains($reportada->getMessage(), '23505')
            && str_contains($reportada->getMessage(), $falla->getFile().':'.$falla->getLine()));
        $this->assertSame([], FileUploadConfiguration::storage()->allFiles(FileUploadConfiguration::directory()));
    }

    /**
     * Sin `lang/`, una regla sin mensaje propio pinta su clave cruda
     * (`validation.required`) en el modal.
     */
    public function test_sin_archivo_ni_categoria_el_modal_lo_dice_en_espanol(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $componente = Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [])
            ->assertHasFormErrors(['archivo', 'categoria']);

        $this->assertContains('Sube el archivo de la base del gremio.', $componente->errors()->get(self::CAMPO_ARCHIVO));
        $this->assertContains('Elige la categoría para las filas que no traen una.', $componente->errors()->get(self::CAMPO_CATEGORIA));
    }

    /**
     * Nombre, kilobytes y tipo que se reporta del archivo, y el mensaje.
     *
     * @return array<string, array{string, int, string, string}>
     */
    public static function archivosQueNoSirven(): array
    {
        return [
            'no es una hoja' => ['base.pdf', 10, 'application/pdf', 'El archivo tiene que ser una hoja de Excel (.xlsx).'],
            'pesa más de 4 MB' => ['base.xlsx', 4097, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'El archivo pesa más de 4 MB.'],
        ];
    }

    #[DataProvider('archivosQueNoSirven')]
    public function test_un_archivo_que_no_sirve_se_explica_en_espanol(string $nombre, int $kilobytes, string $tipo, string $mensaje): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        $componente = Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => UploadedFile::fake()->create($nombre, $kilobytes, $tipo),
                'categoria' => 'Bar',
            ])
            ->assertHasFormErrors(['archivo']);

        $this->assertContains($mensaje, $componente->errors()->get(self::CAMPO_ARCHIVO));
        $this->assertSame(0, Asociado::query()->count());
    }

    /**
     * Fuera de las pruebas el tipo se lee del contenido, y un .xlsx es un zip:
     * una copia guardada con otra herramienta puede leerse como
     * `application/zip`, y no por eso deja de ser la hoja.
     */
    public function test_una_hoja_que_se_lee_como_zip_tambien_entra(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')])->mimeType('application/zip'),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame(1, Asociado::query()->count());
    }

    /** Un zip que no es una hoja pasa el tipo, pero el importador lo dice y no crea nada. */
    public function test_un_zip_que_no_es_una_hoja_lo_reporta_el_importador(): void
    {
        $zip = new ZipArchive;
        $zip->open($this->archivo, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('notas.txt', 'Esto no es una hoja de cálculo.');
        $zip->close();

        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => UploadedFile::fake()
                    ->createWithContent('base.xlsx', (string) file_get_contents($this->archivo))
                    ->mimeType('application/zip'),
                'categoria' => 'Bar',
            ])
            ->assertHasNoFormErrors();

        $enviada = $this->notificacionEnviada();

        $this->assertSame('warning', $enviada->getStatus());
        $this->assertStringStartsWith('No se pudo leer el archivo', $this->lineasDelCuerpo($enviada)[0]);
        $this->assertSame(0, Asociado::query()->count());
    }

    /**
     * Con un error de validación la acción no corre y su `finally` tampoco: el
     * archivo y el `.json` que Livewire deja a su lado se quedan en el temporal,
     * igual que si se cancela el modal. Los recoge la purga de cada hora.
     */
    public function test_lo_que_deja_un_error_de_validacion_lo_recoge_la_purga_de_subidas(): void
    {
        $this->actingAs($this->usuario(User::ROL_SUPER_ADMIN));

        Livewire::test(ListAsociados::class)
            ->callAction('importar', data: [
                'archivo' => $this->subida([$this->fila('Bar Uno', 'uno@bar.test')]),
                'categoria' => 'Bar',
                'crear_cuentas' => true,
                'contrasena_generica' => 'CordilleraQuindio2026',
                'contrasena_generica_confirmation' => 'CordilleraQuindio2026',
            ])
            ->assertHasFormErrors(['contrasena_generica']);

        $temporal = FileUploadConfiguration::storage();
        $this->assertNotSame([], $temporal->allFiles(FileUploadConfiguration::directory()));

        $this->travel(61)->minutes();
        $this->artisan('subidas:depurar')->assertSuccessful();

        $this->assertSame([], $temporal->allFiles(FileUploadConfiguration::directory()));
    }
}
