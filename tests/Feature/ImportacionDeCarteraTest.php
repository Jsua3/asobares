<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Cartera;
use App\Services\ImportadorDeCartera;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
