<?php

namespace Tests\Feature\Panel;

use App\Models\ConsultaGuia;
use App\Models\Municipio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * El mapa de calor del observatorio necesita saber en qué municipios la gente
 * consulta la guía. Es la señal más valiosa que la página insignia produce.
 */
class ConsultasDeGuiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_deja_una_fila_con_su_municipio(): void
    {
        $municipio = Municipio::factory()->create();

        ConsultaGuia::registrar($municipio->id);

        $this->assertDatabaseCount('consultas_guia', 1);
        $this->assertDatabaseHas('consultas_guia', [
            'municipio_id' => $municipio->id,
            'requisito_apertura_id' => null,
        ]);
    }

    /**
     * Sin IP, sin agente de usuario, sin sesión: es un conteo agregado, no un
     * rastro de personas. Si alguien añade trazabilidad, esto se pone rojo.
     */
    public function test_la_tabla_no_guarda_ningun_dato_personal(): void
    {
        $columnas = Schema::getColumnListing('consultas_guia');

        $this->assertEqualsCanonicalizing(
            ['id', 'municipio_id', 'requisito_apertura_id', 'created_at', 'updated_at'],
            $columnas,
            'La tabla de consultas es un conteo anónimo: no debe ganar columnas de rastreo.'
        );

        foreach (['ip', 'ip_address', 'user_agent', 'agente', 'session_id', 'user_id'] as $prohibida) {
            $this->assertNotContains($prohibida, $columnas);
        }
    }

    public function test_las_consultas_se_pueden_agrupar_por_municipio(): void
    {
        $armenia = Municipio::factory()->create(['nombre' => 'Armenia']);
        $salento = Municipio::factory()->create(['nombre' => 'Salento']);

        ConsultaGuia::factory()->count(5)->for($armenia)->create();
        ConsultaGuia::factory()->count(2)->for($salento)->create();

        $porMunicipio = ConsultaGuia::query()
            ->selectRaw('municipio_id, count(*) as total')
            ->groupBy('municipio_id')
            ->pluck('total', 'municipio_id');

        $this->assertSame(5, (int) $porMunicipio[$armenia->id]);
        $this->assertSame(2, (int) $porMunicipio[$salento->id]);
    }
}
