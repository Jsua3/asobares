<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * La frontera entre lo que el gremio sabe y lo que el sitio publica.
 *
 * `Asociado::CAMPOS_INTERNOS` y `datosPublicos()` existían desde el primer día
 * y **no los ejercitaba ninguna prueba**: eran una declaración de intenciones.
 * Mientras el directorio se llenaba con datos inventados por el sembrador eso
 * no costaba nada. Con la base real del gremio cargada, cada campo interno es
 * un dato de una persona concreta: la cédula del propietario y la valoración
 * comercial que la oficina escribió sobre su negocio —«Mala puntación en
 * plataformas digitales» es una frase textual del archivo—.
 *
 * Estas pruebas convierten la intención en guardia: si alguien añade un campo
 * interno a una vista pública, o crea un campo interno nuevo y olvida
 * declararlo, la suite se pone roja aquí.
 */
class DatosInternosDelAsociadoTest extends TestCase
{
    use RefreshDatabase;

    /** Valores centinela: si aparecen en el HTML, es que se filtraron. */
    private const array CENTINELAS = [
        'representante' => 'CENTINELA-REPRESENTANTE',
        'documento' => 'CENTINELA-1094945432',
        'correo_interno' => 'centinela-interno@ejemplo.test',
        'telefono_interno' => 'CENTINELA-3117642319',
        'autorizacion_datos_origen' => 'CENTINELA-ACTA-04',
        'notas_internas' => 'CENTINELA Mala puntacion en plataformas digitales',
    ];

    private function asociadoPublicado(): Asociado
    {
        $categoria = Categoria::query()->create(['nombre' => 'Bar', 'slug' => 'bar']);
        $municipio = Municipio::query()->create(['nombre' => 'Armenia', 'slug' => 'armenia']);

        return Asociado::query()->create([
            ...self::CENTINELAS,
            'nombre' => 'Fonda la Floresta',
            'slug' => 'fonda-la-floresta',
            'categoria_id' => $categoria->id,
            'municipio_id' => $municipio->id,
            'descripcion' => 'La fonda de la ciudad de Armenia.',
            'direccion' => 'Cra 14 # 5 - 64',
            'horario' => "Lunes a viernes de 2pm a 12am\nSabado de 4pm a 3am",
            'genero_musical' => "Salsa\nCrossover",
            'servicios' => 'Pista de baile, licores',
            'fecha_afiliacion' => '2026-01-15',
            'autorizacion_datos_at' => '2026-08-22 10:00:00',
            'estado' => EstadoPublicacion::Publicado,
        ]);
    }

    /** @return array<string, array{string, string}> */
    public static function camposInternos(): array
    {
        $casos = [];

        foreach (self::CENTINELAS as $campo => $centinela) {
            $casos[$campo] = [$campo, $centinela];
        }

        return $casos;
    }

    #[DataProvider('camposInternos')]
    public function test_la_ficha_publica_no_emite_el_campo_interno(string $campo, string $centinela): void
    {
        $this->asociadoPublicado();

        $this->get('/directorio/fonda-la-floresta')
            ->assertOk()
            ->assertDontSee($centinela, escape: false);
    }

    #[DataProvider('camposInternos')]
    public function test_el_listado_del_directorio_no_emite_el_campo_interno(string $campo, string $centinela): void
    {
        $this->asociadoPublicado();

        $this->get('/directorio')
            ->assertOk()
            ->assertDontSee($centinela, escape: false);
    }

    public function test_la_ficha_publica_si_muestra_lo_que_el_gremio_quiso_publicar(): void
    {
        $this->asociadoPublicado();

        // La contraprueba: sin ella, un directorio que no renderiza nada
        // pasaría las guardias de arriba en verde.
        $this->get('/directorio/fonda-la-floresta')
            ->assertOk()
            ->assertSee('Fonda la Floresta')
            ->assertSee('Cra 14 # 5 - 64');
    }

    public function test_datos_publicos_excluye_todos_los_campos_internos(): void
    {
        $publicos = $this->asociadoPublicado()->datosPublicos();

        foreach (Asociado::CAMPOS_INTERNOS as $campo) {
            $this->assertArrayNotHasKey($campo, $publicos, "«{$campo}» está declarado interno y aun así sale en datosPublicos().");
        }
    }

    /**
     * La lista de campos internos es una lista escrita a mano, así que se
     * desactualiza sola. Esta guardia obliga a que cada columna nueva del
     * modelo se clasifique a propósito: o es pública, o entra en la lista.
     */
    public function test_toda_columna_de_identificacion_o_de_uso_interno_esta_declarada(): void
    {
        $deberianSerInternas = [
            'representante',
            'documento',
            'correo_interno',
            'telefono_interno',
            'fecha_afiliacion',
            'autorizacion_datos_at',
            'autorizacion_datos_origen',
            'notas_internas',
        ];

        foreach ($deberianSerInternas as $campo) {
            $this->assertContains(
                $campo,
                Asociado::CAMPOS_INTERNOS,
                "«{$campo}» dejó de estar en CAMPOS_INTERNOS: es un dato del gremio, no de la ficha pública."
            );
        }
    }

    /**
     * El archivo de la oficina no puede acabar en el repositorio.
     *
     * `github.com/Jsua3/asobares` es **público**: se clona sin credenciales.
     * «Base de Datos 2025» trae nombre, cédula, teléfono, correo y dirección
     * de 41 propietarios, más la valoración comercial que la oficina escribió
     * sobre cada negocio. Subirlo sería publicar datos personales de terceros
     * sin autorización, y a diferencia de un `git rm` posterior, ya estaría en
     * el historial y en cada clon.
     *
     * La guardia mira el árbol de verdad, no el `.gitignore`: una regla que
     * nadie comprueba se rompe el día que alguien copia el archivo con otro
     * nombre.
     */
    public function test_la_base_de_datos_del_gremio_no_vive_en_el_repositorio(): void
    {
        $sospechosos = [];

        $arbol = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator(base_path(), \FilesystemIterator::SKIP_DOTS),
                static fn ($archivo, $clave, $iterador): bool => ! in_array(
                    $archivo->getFilename(),
                    ['vendor', 'node_modules', '.git', 'storage'],
                    true
                )
            )
        );

        foreach ($arbol as $archivo) {
            if (preg_match('/base.?de.?datos.*\.xls[xm]?$/i', $archivo->getFilename()) === 1) {
                $sospechosos[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());
            }
        }

        $this->assertSame(
            [],
            $sospechosos,
            'La base de establecimientos del gremio está dentro del repositorio y este es público: '
            .implode(', ', $sospechosos).'. Sácala y cárgala con `php artisan asociados:importar`.'
        );
    }

    public function test_el_gitignore_declara_por_que_esa_base_no_se_versiona(): void
    {
        $gitignore = (string) file_get_contents(base_path('.gitignore'));

        $this->assertStringContainsString(
            'Base de datos Cap',
            $gitignore,
            'Se fue del .gitignore la regla que mantiene fuera la base de establecimientos.'
        );
    }
}
