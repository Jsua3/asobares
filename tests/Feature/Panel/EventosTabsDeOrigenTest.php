<?php

namespace Tests\Feature\Panel;

use App\Enums\OrigenEvento;
use App\Filament\Resources\Eventos\Pages\ListEventos;
use App\Models\Aliado;
use App\Models\Evento;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Todos | ASOBARES | Aliados | Comunidad, sobre el mismo `EventoResource`:
 * cada pestaña solo cambia la consulta del listado, usando el campo `origen`
 * que ya existe. Sin modelo ni tabla nueva.
 *
 * La pestaña ASOBARES reutiliza `Evento::scopeDelGremio()`, que además de
 * `origen = asobares` acepta `origen` nulo —el estado de un huérfano
 * anterior a la columna—. No se puede probar ese caso insertando un
 * `origen` nulo de verdad: la columna es `NOT NULL` con `default('asobares')`
 * desde la migración `2026_09_15_000001_anade_origen_y_aliado_a_eventos`, y
 * la que sigue (`2026_09_15_194011_rellena_origen_asobares_en_eventos_huerfanos`)
 * ya normalizó en base los huérfanos que había. Bajo este esquema no existe
 * fila con `origen` nulo para insertar; el `whereNull` del scope es una
 * defensa que ya no tiene caso real que ejercitar en una base nueva.
 */
class EventosTabsDeOrigenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        $this->actingAs($usuario->fresh());
    }

    public function test_las_pestanas_filtran_por_el_campo_origen(): void
    {
        $asobares = Evento::factory()->create(['origen' => OrigenEvento::Asobares]);

        $aliado = Aliado::factory()->create();
        $eventoDeAliado = Evento::factory()->create([
            'origen' => OrigenEvento::Aliado,
            'aliado_id' => $aliado->id,
        ]);

        $comunidad = Evento::factory()->create(['origen' => OrigenEvento::Comunidad]);

        $lista = Livewire::test(ListEventos::class);

        $lista->set('activeTab', 'todos')
            ->assertCanSeeTableRecords([$asobares, $eventoDeAliado, $comunidad]);

        $lista->set('activeTab', 'asobares')
            ->assertCanSeeTableRecords([$asobares])
            ->assertCanNotSeeTableRecords([$eventoDeAliado, $comunidad]);

        $lista->set('activeTab', 'aliados')
            ->assertCanSeeTableRecords([$eventoDeAliado])
            ->assertCanNotSeeTableRecords([$asobares, $comunidad]);

        $lista->set('activeTab', 'comunidad')
            ->assertCanSeeTableRecords([$comunidad])
            ->assertCanNotSeeTableRecords([$asobares, $eventoDeAliado]);
    }

    /**
     * Editar, despublicar y borrar siguen siendo las mismas acciones de
     * `EventosTable` en cualquier pestaña: la pestaña no es un sistema
     * paralelo, solo acota la consulta.
     */
    public function test_las_acciones_de_fila_son_las_mismas_en_cualquier_pestana(): void
    {
        Evento::factory()->create(['origen' => OrigenEvento::Comunidad]);

        $lista = Livewire::test(ListEventos::class)->set('activeTab', 'comunidad');

        $acciones = $lista->instance()->getTable()->getRecordActions();
        $this->assertCount(1, $acciones);
        $this->assertSame(['aprobar', 'devolver', 'edit'], array_keys($acciones[0]->getFlatActions()));
    }
}
