<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Cartera;
use App\Services\ImportadorDeCartera;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ImportacionDeCarteraTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $contenido): string
    {
        $ruta = tempnam(sys_get_temp_dir(), 'cartera').'.csv';
        file_put_contents($ruta, $contenido);

        return $ruta;
    }

    public function test_importar_actualiza_los_saldos(): void
    {
        $bar = Asociado::factory()->create(['nombre' => 'Bar La Cava', 'slug' => 'bar-la-cava']);
        $cafe = Asociado::factory()->create(['nombre' => 'Café Cordillera', 'slug' => 'cafe-cordillera']);

        $resultado = app(ImportadorDeCartera::class)->importar($this->csv(
            "establecimiento,saldo_pendiente,meses_mora,ultimo_pago\n".
            "Bar La Cava,150000,3,2026-05-01\n".
            "Café Cordillera,0,0,2026-08-01\n"
        ));

        $this->assertFalse($resultado->tieneErrores(), implode(' | ', $resultado->errores()));
        $this->assertSame(2, $resultado->actualizados());

        $this->assertSame('150000.00', Cartera::where('asociado_id', $bar->id)->value('saldo_pendiente'));
        $this->assertSame(3, Cartera::where('asociado_id', $bar->id)->value('meses_mora'));
        $this->assertSame(0, Cartera::where('asociado_id', $cafe->id)->value('meses_mora'));
    }

    /**
     * El archivo llega del computador de la contadora y puede venir en formato
     * colombiano o inglés. Antes se borraban todos los puntos, así que
     * «1250.75» se importaba como 125.075: cada saldo multiplicado por cien.
     *
     * @param  array{0: string, 1: string}  $caso
     */
    #[DataProvider('cifrasDeDinero')]
    public function test_el_punto_decimal_no_se_confunde_con_separador_de_miles(string $enElArchivo, string $enLaBase): void
    {
        $bar = Asociado::factory()->create(['nombre' => 'Bar La Cava', 'slug' => 'bar-la-cava']);

        $resultado = app(ImportadorDeCartera::class)->importar($this->csv(
            "establecimiento,saldo_pendiente,meses_mora\n".
            "Bar La Cava,\"{$enElArchivo}\",1\n"
        ));

        $this->assertFalse($resultado->tieneErrores(), implode(' | ', $resultado->errores()));
        $this->assertSame($enLaBase, Cartera::where('asociado_id', $bar->id)->value('saldo_pendiente'));
    }

    /** @return list<array{0: string, 1: string}> */
    public static function cifrasDeDinero(): array
    {
        return [
            'formato inglés con decimales' => ['1250.75', '1250.75'],
            'formato colombiano de miles' => ['1.250.000', '1250000.00'],
            'formato colombiano completo' => ['1.250.000,50', '1250000.50'],
            'formato inglés completo' => ['1,250,000.75', '1250000.75'],
            'entero pelado' => ['150000', '150000.00'],
            'con símbolo de peso' => ['$ 150.000', '150000.00'],
        ];
    }

    public function test_una_celda_de_saldo_vacia_es_un_error_y_no_borra_la_deuda(): void
    {
        $bar = Asociado::factory()->create(['nombre' => 'Bar La Cava', 'slug' => 'bar-la-cava']);
        Cartera::create([
            'asociado_id' => $bar->id,
            'saldo_pendiente' => 300000,
            'meses_mora' => 6,
            'actualizado_at' => now(),
        ]);

        $resultado = app(ImportadorDeCartera::class)->importar($this->csv(
            "establecimiento,saldo_pendiente,meses_mora\n".
            "Bar La Cava,,\n"
        ));

        $this->assertTrue($resultado->tieneErrores());
        $this->assertSame(0, $resultado->actualizados());
        $this->assertStringContainsString('viene vacío', $resultado->errores()[0]);

        $this->assertSame(
            '300000.00',
            Cartera::where('asociado_id', $bar->id)->value('saldo_pendiente'),
            'Una casilla en blanco no puede perdonar una deuda.'
        );
    }

    public function test_una_fila_mala_no_aborta_el_resto(): void
    {
        Asociado::factory()->create(['nombre' => 'Bar La Cava', 'slug' => 'bar-la-cava']);

        $resultado = app(ImportadorDeCartera::class)->importar($this->csv(
            "establecimiento,saldo_pendiente,meses_mora\n".
            "Bar Que No Existe,50000,1\n".
            "Bar La Cava,200000,4\n".
            "Bar La Cava,no-es-un-numero,2\n"
        ));

        $this->assertSame(1, $resultado->actualizados(), 'La fila válida debe importarse igual.');
        $this->assertCount(2, $resultado->errores());
        $this->assertStringContainsString('No existe un asociado', $resultado->errores()[0]);
        $this->assertStringContainsString('saldo pendiente no es un número', $resultado->errores()[1]);
    }

    public function test_rechaza_un_archivo_sin_las_columnas_necesarias(): void
    {
        $resultado = app(ImportadorDeCartera::class)->importar($this->csv("nombre,plata\nBar,100\n"));

        $this->assertSame(0, $resultado->actualizados());
        $this->assertStringContainsString('le faltan estas columnas', $resultado->errores()[0]);
    }

    public function test_tolera_encabezados_con_tildes_mayusculas_y_montos_con_separadores(): void
    {
        $bar = Asociado::factory()->create(['nombre' => 'Bar La Cava', 'slug' => 'bar-la-cava']);

        $resultado = app(ImportadorDeCartera::class)->importar($this->csv(
            "Establecimiento,Saldo Pendiente,Meses Mora,Último Pago\n".
            "Bar La Cava,\"\$1.250.000\",5,15/06/2026\n"
        ));

        $this->assertFalse($resultado->tieneErrores(), implode(' | ', $resultado->errores()));
        $this->assertSame('1250000.00', Cartera::where('asociado_id', $bar->id)->value('saldo_pendiente'));
        $this->assertSame('2026-06-15', Cartera::where('asociado_id', $bar->id)->first()->ultimo_pago_at->format('Y-m-d'));
    }
}
