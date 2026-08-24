<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Services\ImportadorDeAsociados;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

/**
 * La carga de la base real del gremio.
 *
 * Lo que se prueba aquí no es «que importe»: es que no haga de más. El archivo
 * trae datos de personas naturales y valoraciones comerciales internas, así
 * que las guardias importantes son las de contención — no publica, no otorga
 * autorización de habeas data, no borra por celda vacía y no despublica lo que
 * la dirección ya aprobó.
 */
class ImportacionDeAsociadosTest extends TestCase
{
    use RefreshDatabase;

    private string $archivo;

    protected function setUp(): void
    {
        parent::setUp();

        Categoria::query()->create(['nombre' => 'Bar', 'slug' => 'bar']);
        Municipio::query()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);
        Municipio::query()->create(['nombre' => 'La Tebaida', 'slug' => 'la-tebaida']);

        $this->archivo = tempnam(sys_get_temp_dir(), 'asobares').'.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->archivo)) {
            unlink($this->archivo);
        }

        parent::tearDown();
    }

    /**
     * Escribe un .xlsx con la MISMA forma que el archivo del gremio: banner,
     * filas en blanco y encabezados recién en la sexta fila.
     *
     * @param  list<list<string>>  $filas
     */
    private function archivoComoElDelGremio(array $filas): string
    {
        $escritor = new Writer;
        $escritor->openToFile($this->archivo);

        $escritor->addRow(Row::fromValues(['BASE DE DATOS QUINDIO']));
        $escritor->addRow(Row::fromValues(['']));
        $escritor->addRow(Row::fromValues(['']));
        $escritor->addRow(Row::fromValues(['']));
        $escritor->addRow(Row::fromValues(['']));
        $escritor->addRow(Row::fromValues([
            'Nombre del Establecimiento', 'Nombre ', 'Descripción del establecimiento', 'NIT',
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
    private function fila(string $nombre, string $municipio = 'Armenia', string ...$resto): array
    {
        return [
            $nombre, 'Cesar Ortiz', 'La fonda de la ciudad', '1094945432',
            'Cra 14 # 5 - 64', $municipio, '311 764 2319', 'contacto@ejemplo.test',
            "Lunes a viernes de 2pm a 12am\nSabado de 4pm a 3am",
            "Salsa\nCrossover", 'Pista de baile, licores', 'https://www.instagram.com/ejemplo',
            'ok', 'Mala puntacion en plataformas digitales',
        ];
    }

    private function importar(?string $categoria = 'Bar', ?string $fecha = null, ?string $origen = null)
    {
        return app(ImportadorDeAsociados::class)->importar($this->archivo, $categoria, $fecha, $origen);
    }

    // -----------------------------------------------------------------------
    // 1. Lo básico: encuentra la cabecera y mapea las columnas
    // -----------------------------------------------------------------------

    public function test_encuentra_la_cabecera_aunque_no_este_en_la_primera_fila(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $resultado = $this->importar();

        $this->assertFalse($resultado->tieneErrores(), implode(' | ', $resultado->errores()));
        $this->assertSame(1, $resultado->creados());
    }

    public function test_mapea_las_columnas_publicas_y_las_internas_a_sus_campos(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $this->importar();

        $asociado = Asociado::query()->where('slug', 'fonda-la-floresta')->firstOrFail();

        $this->assertSame('Fonda la Floresta', $asociado->nombre);
        $this->assertSame('Cra 14 # 5 - 64', $asociado->direccion);
        $this->assertStringContainsString('Salsa', (string) $asociado->genero_musical);
        $this->assertStringContainsString('Pista de baile', (string) $asociado->servicios);
        // Internos
        $this->assertSame('Cesar Ortiz', $asociado->representante);
        $this->assertSame('1094945432', $asociado->documento);
        $this->assertSame('311 764 2319', $asociado->telefono_interno);
        $this->assertSame('contacto@ejemplo.test', $asociado->correo_interno);
        $this->assertStringContainsString('Mala puntacion', (string) $asociado->notas_internas);
    }

    /**
     * El horario era `string`. 19 de las 41 filas reales traen varias franjas,
     * un renglón por franja: si la columna sigue siendo corta, el horario se
     * trunca o revienta según el motor.
     */
    public function test_guarda_el_horario_de_varias_lineas_completo(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $this->importar();

        $horario = (string) Asociado::query()->where('slug', 'fonda-la-floresta')->value('horario');

        $this->assertStringContainsString('Lunes a viernes', $horario);
        $this->assertStringContainsString('Sabado', $horario);
    }

    // -----------------------------------------------------------------------
    // 2. Contención: lo que la importación NO puede hacer
    // -----------------------------------------------------------------------

    public function test_nada_se_publica_al_importar(): void
    {
        $this->archivoComoElDelGremio([
            $this->fila('Fonda la Floresta'),
            $this->fila('Ruta 12', 'La Tebaida'),
        ]);

        $this->importar();

        $this->assertSame(2, Asociado::query()->count());
        $this->assertSame(
            0,
            Asociado::query()->where('estado', EstadoPublicacion::Publicado)->count(),
            'Abrir un Excel no puede publicar la ficha de nadie.'
        );
    }

    public function test_una_ficha_ya_publicada_no_se_despublica_al_reimportar(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);
        $this->importar();

        $asociado = Asociado::query()->where('slug', 'fonda-la-floresta')->firstOrFail();
        $asociado->update(['estado' => EstadoPublicacion::Publicado]);

        $resultado = $this->importar();

        $this->assertSame(1, $resultado->actualizados());
        $this->assertSame(EstadoPublicacion::Publicado, $asociado->fresh()->estado);
    }

    public function test_la_importacion_no_otorga_autorizacion_de_habeas_data(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $resultado = $this->importar();

        $asociado = Asociado::query()->where('slug', 'fonda-la-floresta')->firstOrFail();

        $this->assertNull($asociado->autorizacion_datos_at);
        $this->assertFalse($asociado->tieneAutorizacionDeDatos());
        $this->assertNotEmpty($resultado->avisos(), 'La carga sin autorización tiene que avisarlo.');
    }

    public function test_la_autorizacion_se_registra_solo_si_se_declara_con_su_soporte(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $this->importar('Bar', '2026-08-22', 'Formato firmado, acta 04');

        $asociado = Asociado::query()->where('slug', 'fonda-la-floresta')->firstOrFail();

        $this->assertTrue($asociado->tieneAutorizacionDeDatos());
        $this->assertSame('Formato firmado, acta 04', $asociado->autorizacion_datos_origen);
    }

    /**
     * El archivo real trae el correo vacío en 15 de 41 filas. Si una celda en
     * blanco borrara lo que la oficina escribió a mano, cada reimportación
     * destruiría trabajo.
     */
    public function test_una_celda_vacia_no_borra_lo_que_ya_estaba(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);
        $this->importar();

        $asociado = Asociado::query()->where('slug', 'fonda-la-floresta')->firstOrFail();
        $asociado->update(['lat' => 4.53, 'lng' => -75.68, 'whatsapp' => '3001112233']);

        $sinCorreo = $this->fila('Fonda la Floresta');
        $sinCorreo[7] = '';
        $this->archivoComoElDelGremio([$sinCorreo]);
        $this->importar();

        $asociado->refresh();

        $this->assertSame('contacto@ejemplo.test', $asociado->correo_interno);
        $this->assertSame(4.53, $asociado->lat, 'La importación no puede pisar lo que la oficina ajustó a mano.');
        $this->assertSame('3001112233', $asociado->whatsapp);
    }

    /**
     * El teléfono de la hoja es la forma de contactar al propietario, no un
     * dato que él haya pedido publicar. El campo público es `whatsapp` y lo
     * llena la oficina cuando el titular lo autoriza.
     */
    public function test_el_telefono_de_la_hoja_no_se_publica_solo(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $this->importar();

        $asociado = Asociado::query()->where('slug', 'fonda-la-floresta')->firstOrFail();

        $this->assertSame('311 764 2319', $asociado->telefono_interno);
        $this->assertNull($asociado->whatsapp);
    }

    // -----------------------------------------------------------------------
    // 3. Filas malas: se reportan con su número y no abortan el archivo
    // -----------------------------------------------------------------------

    public function test_un_municipio_de_fuera_del_quindio_se_rechaza_con_su_numero_de_fila(): void
    {
        $this->archivoComoElDelGremio([
            $this->fila('Fonda la Floresta'),
            $this->fila('Bar de Pereira', 'Pereira'),
            $this->fila('Ruta 12', 'La Tebaida'),
        ]);

        $resultado = $this->importar();

        $this->assertSame(2, $resultado->creados());
        $this->assertCount(1, $resultado->errores());
        $this->assertStringContainsString('Fila 8', $resultado->errores()[0]);
        $this->assertStringContainsString('Pereira', $resultado->errores()[0]);
    }

    public function test_sin_categoria_no_se_inventa_una(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $resultado = $this->importar(null);

        $this->assertSame(0, $resultado->creados());
        $this->assertStringContainsString('categoría', $resultado->errores()[0]);
    }

    public function test_una_categoria_inexistente_no_carga_nada(): void
    {
        $this->archivoComoElDelGremio([$this->fila('Fonda la Floresta')]);

        $resultado = $this->importar('Heladeria');

        $this->assertSame(0, Asociado::query()->count());
        $this->assertStringContainsString('Heladeria', $resultado->errores()[0]);
    }

    public function test_las_filas_en_blanco_del_final_no_cuentan_como_error(): void
    {
        $this->archivoComoElDelGremio([
            $this->fila('Fonda la Floresta'),
            ['', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ]);

        $resultado = $this->importar();

        $this->assertSame(1, $resultado->creados());
        $this->assertFalse($resultado->tieneErrores());
    }

    /**
     * La hoja trae el Instagram unas veces como URL y otras como arroba. El
     * campo se pinta como enlace: una arroba suelta produce un enlace relativo
     * que cae dentro del propio sitio.
     */
    public function test_el_instagram_en_arroba_se_convierte_en_enlace_absoluto(): void
    {
        $fila = $this->fila('Fonda la Floresta');
        $fila[11] = '@fondalafloresta';
        $this->archivoComoElDelGremio([$fila]);

        $this->importar();

        $this->assertSame(
            'https://www.instagram.com/fondalafloresta',
            Asociado::query()->where('slug', 'fonda-la-floresta')->value('instagram_url')
        );
    }

    public function test_un_archivo_sin_cabecera_reconocible_no_carga_nada(): void
    {
        file_put_contents($this->archivo, 'esto no es una hoja de calculo');

        $resultado = $this->importar();

        $this->assertSame(0, Asociado::query()->count());
        $this->assertTrue($resultado->tieneErrores());
    }
}
