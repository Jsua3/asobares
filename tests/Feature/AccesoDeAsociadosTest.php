<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los contactos de proveedores y el banco de talento dejaron de ser publicos:
 * son la contraprestacion de la cuota, no contenido de vitrina.
 *
 * Lo que se fija aqui es la frontera, que es lo unico que no se puede
 * comprobar mirando la pagina: que un anonimo no llegue, que un afiliado si,
 * que el equipo del gremio siga entrando por el panel y no por aqui, y --lo
 * mas facil de romper sin darse cuenta-- que la pagina publica que quedo en
 * pie no filtre ni un correo.
 */
class AccesoDeAsociadosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol, ?Asociado $asociado = null): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado?->id]);
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    private function afiliado(): User
    {
        return $this->crearUsuario(User::ROL_ASOCIADO, Asociado::factory()->publicado()->create());
    }

    // --- La frontera ---

    public function test_un_anonimo_no_alcanza_el_directorio_ni_el_banco(): void
    {
        $this->get(route('mi-cuenta.proveedores.index'))->assertRedirect(route('mi-cuenta.entrar'));
        $this->get(route('mi-cuenta.aspirantes.index'))->assertRedirect(route('mi-cuenta.entrar'));
    }

    public function test_un_afiliado_ve_los_contactos_del_directorio(): void
    {
        Proveedor::factory()->publicado()->verificado()->create([
            'nombre' => 'Hielo Del Quindio',
            'slug' => 'hielo-del-quindio',
            'correo' => 'ventas@hielo.test',
        ]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.proveedores.index'))
            ->assertOk()
            ->assertSee('Hielo Del Quindio')
            ->assertSee('ventas@hielo.test');
    }

    public function test_un_afiliado_ve_el_banco_de_talento(): void
    {
        Aspirante::factory()->create([
            'nombre' => 'Camila Bartender',
            'correo' => 'camila@aspirante.test',
            'estado' => EstadoDeGestion::Nuevo,
        ]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.aspirantes.index'))
            ->assertOk()
            ->assertSee('Camila Bartender')
            ->assertSee('camila@aspirante.test');
    }

    /**
     * El equipo del gremio no entra por /mi-cuenta: tiene el panel. No es un
     * 403 seco sino la vista que explica que sesion hay abierta, porque llegar
     * aqui con la sesion del panel pasa en cada demostracion.
     */
    public function test_el_equipo_del_gremio_no_entra_por_la_cuenta_del_afiliado(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN))
            ->get(route('mi-cuenta.proveedores.index'))
            ->assertForbidden();
    }

    public function test_un_usuario_con_rol_asociado_pero_sin_ficha_tampoco_entra(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_ASOCIADO))
            ->get(route('mi-cuenta.aspirantes.index'))
            ->assertForbidden();
    }

    // --- Lo que la cara publica ya no puede decir ---

    public function test_la_pagina_publica_de_proveedores_sigue_abierta_y_no_filtra_contactos(): void
    {
        Proveedor::factory()->publicado()->verificado()->create([
            'nombre' => 'Licores Del Eje',
            'slug' => 'licores-del-eje',
            'correo' => 'pedidos@licores.test',
            'whatsapp' => '3001234567',
        ]);

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertDontSee('Licores Del Eje')
            ->assertDontSee('pedidos@licores.test')
            ->assertDontSee('3001234567');
    }

    /**
     * La pagina publica sigue indexable a proposito: cerrar la URL entera
     * habria mandado a un login seco a quien llega desde un buscador. Lo que
     * si tiene que hacer es contar cuantos hay, que es el argumento de venta.
     */
    public function test_la_pagina_publica_cuenta_los_proveedores_sin_nombrarlos(): void
    {
        Proveedor::factory()->count(3)->publicado()->verificado()->create();

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertSee('3');
    }

    public function test_la_bolsa_de_empleo_sigue_siendo_publica(): void
    {
        $this->get(route('empleo.index'))->assertOk();
    }

    // --- Lo que el banco no debe mostrar ---

    public function test_el_banco_no_muestra_a_quien_el_gremio_descarto(): void
    {
        Aspirante::factory()->create(['nombre' => 'Perfil Descartado', 'estado' => EstadoDeGestion::Descartado]);
        Aspirante::factory()->create(['nombre' => 'Perfil Vigente', 'estado' => EstadoDeGestion::Nuevo]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.aspirantes.index'))
            ->assertOk()
            ->assertSee('Perfil Vigente')
            ->assertDontSee('Perfil Descartado');
    }

    public function test_el_banco_filtra_por_cargo(): void
    {
        $cargos = CargoDelSector::cases();

        Aspirante::factory()->create(['nombre' => 'Del Cargo Buscado', 'categoria_cargo' => $cargos[0]]);
        Aspirante::factory()->create(['nombre' => 'De Otro Cargo', 'categoria_cargo' => $cargos[1]]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.aspirantes.index', ['categoria' => $cargos[0]->value]))
            ->assertOk()
            ->assertSee('Del Cargo Buscado')
            ->assertDontSee('De Otro Cargo');
    }
}
