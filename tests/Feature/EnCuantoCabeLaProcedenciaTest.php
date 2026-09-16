<?php

namespace Tests\Feature;

use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Database\Seeders\MunicipioSeeder;
use Database\Seeders\RequisitoAperturaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Lo que la suite no puede ver por sí sola: el ancho de las columnas.
 *
 * El proyecto corre **SQLite en desarrollo y PostgreSQL 17 en producción**
 * (decisión del 3 de agosto), y `phpunit.xml` fuerza SQLite en memoria. Ahí
 * está la trampa: **SQLite ignora la longitud declarada de un `varchar`** y
 * PostgreSQL la aplica. Un texto de 256 caracteres en una columna de 255 pasa
 * verde toda la suite y revienta el sembrador a mitad del despliegue con
 * «value too long for type character varying(255)», dejando la tabla a medias.
 *
 * No es hipotético: la primera redacción de `FUENTE_EXCEL` daba **254 de 255**
 * en la ficha más larga de la guía, a un carácter del desbordamiento, y ninguna
 * de las once pruebas de `GuiaDeLosDoceMunicipiosTest` podía verlo.
 *
 * Así que la longitud se mide aquí a mano, que es la única forma de que SQLite
 * no la tape.
 */
class EnCuantoCabeLaProcedenciaTest extends TestCase
{
    use RefreshDatabase;

    /** Lo que `$table->string(...)` declara en las migraciones. */
    private const int ANCHO_VARCHAR = 255;

    /** @return list<array{string}> */
    public static function columnasDeTexto(): array
    {
        return [
            'entidad' => ['entidad'],
            'verificado_con' => ['verificado_con'],
            'enlace_externo' => ['enlace_externo'],
            'adjunto' => ['adjunto'],
            'adjunto_nombre' => ['adjunto_nombre'],
        ];
    }

    #[DataProvider('columnasDeTexto')]
    public function test_ninguna_ficha_sembrada_desborda_su_columna(string $columna): void
    {
        $this->seed(MunicipioSeeder::class);
        $this->seed(RequisitoAperturaSeeder::class);

        $largas = RequisitoApertura::query()
            ->get()
            ->filter(fn (RequisitoApertura $r): bool => mb_strlen((string) $r->{$columna}) > self::ANCHO_VARCHAR)
            ->map(fn (RequisitoApertura $r): string => sprintf(
                '%s (%d caracteres)',
                $r->entidad,
                mb_strlen((string) $r->{$columna})
            ))
            ->all();

        $this->assertSame(
            [],
            $largas,
            "La columna «{$columna}» es varchar(".self::ANCHO_VARCHAR.') y estas fichas no caben. '
            .'En SQLite pasa; en el PostgreSQL de producción el sembrador aborta a mitad: '
            .implode(' · ', $largas)
        );
    }

    /**
     * Y el margen, que es lo que de verdad evita volver aquí.
     *
     * Rozar el límite no es estar a salvo: basta una norma más larga en un
     * trámite nuevo para desbordarlo. Se exige holgura de verdad, no un
     * carácter.
     */
    public function test_la_procedencia_deja_holgura_y_no_va_al_filo(): void
    {
        $this->seed(MunicipioSeeder::class);
        $this->seed(RequisitoAperturaSeeder::class);

        $masLarga = RequisitoApertura::query()
            ->get()
            ->map(fn (RequisitoApertura $r): int => mb_strlen((string) $r->verificado_con))
            ->max();

        $this->assertLessThanOrEqual(
            200,
            $masLarga,
            "La procedencia más larga mide {$masLarga} de ".self::ANCHO_VARCHAR.' caracteres. '
            .'Cabe, pero sin margen: acorta `FUENTE_EXCEL` antes de que una norma nueva la desborde.'
        );
    }

    /**
     * Deja constancia ejecutable de POR QUÉ hace falta esta clase.
     *
     * Si un día la suite dejara de correr sobre SQLite, esta prueba se pondría
     * roja y sería la señal de que las de arriba ya las cubre el motor.
     */
    public function test_sqlite_deja_pasar_lo_que_postgresql_rechazaria(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName(), 'La suite dejó de correr sobre SQLite.');

        $municipio = Municipio::factory()->create();

        RequisitoApertura::factory()->create([
            'municipio_id' => $municipio->id,
            'verificado_con' => str_repeat('x', self::ANCHO_VARCHAR + 45),
        ]);

        $this->assertSame(
            self::ANCHO_VARCHAR + 45,
            mb_strlen((string) RequisitoApertura::query()->latest('id')->first()?->verificado_con),
            'SQLite guardó el texto entero pese a declararse varchar(255): por eso la longitud se mide a mano.'
        );
    }
}
