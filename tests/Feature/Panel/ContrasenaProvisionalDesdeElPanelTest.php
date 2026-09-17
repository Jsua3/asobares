<?php

namespace Tests\Feature\Panel;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Sin SMTP no hay «olvidé mi contraseña»: si un afiliado la olvida, la
 * oficina se la cambia en Usuarios. Esa contraseña la conoce la oficina, así
 * que vuelve a ser provisional.
 */
class ContrasenaProvisionalDesdeElPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion);
    }

    private function idDelRol(string $rol): int
    {
        return Role::findByName($rol)->id;
    }

    public function test_crear_un_afiliado_desde_el_panel_lo_deja_provisional(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Duena del Local',
                'email' => 'duena@merlin.test',
                'password' => 'ClaveDeLaOficina-2026!',
                'roles' => [$this->idDelRol(User::ROL_ASOCIADO)],
                'asociado_id' => Asociado::factory()->create()->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(User::query()->where('email', 'duena@merlin.test')->firstOrFail()->contrasena_provisional);
    }

    public function test_crear_a_alguien_del_equipo_no_lo_deja_provisional(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'Persona de la Oficina',
                'email' => 'oficina@gremio.test',
                'password' => 'ClaveDeLaOficina-2026!',
                'roles' => [$this->idDelRol(User::ROL_SUBADMIN)],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertFalse(User::query()->where('email', 'oficina@gremio.test')->firstOrFail()->contrasena_provisional);
    }

    public function test_escribirle_una_contrasena_a_un_afiliado_lo_vuelve_provisional(): void
    {
        $afiliado = User::factory()->create(['asociado_id' => Asociado::factory()->create()->id]);
        $afiliado->syncRoles([User::ROL_ASOCIADO]);

        Livewire::test(EditUser::class, ['record' => $afiliado->getRouteKey()])
            ->fillForm(['password' => 'ClaveDeLaOficina-2026!'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($afiliado->fresh()->contrasena_provisional);
    }

    public function test_editar_un_afiliado_sin_escribir_contrasena_no_lo_marca(): void
    {
        $afiliado = User::factory()->create(['asociado_id' => Asociado::factory()->create()->id]);
        $afiliado->syncRoles([User::ROL_ASOCIADO]);

        Livewire::test(EditUser::class, ['record' => $afiliado->getRouteKey()])
            ->fillForm(['name' => 'Nombre Corregido', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($afiliado->fresh()->contrasena_provisional);
    }

    /** El rol se guarda antes del gancho que marca: vale el rol nuevo. */
    public function test_pasar_a_afiliado_y_escribir_contrasena_en_el_mismo_guardado_lo_marca(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        Livewire::test(EditUser::class, ['record' => $usuario->getRouteKey()])
            ->fillForm([
                'roles' => [$this->idDelRol(User::ROL_ASOCIADO)],
                'asociado_id' => Asociado::factory()->create()->id,
                'password' => 'ClaveDeLaOficina-2026!',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($usuario->fresh()->contrasena_provisional);
    }

    public function test_la_tabla_filtra_a_quien_le_falta_cambiarla(): void
    {
        $provisional = User::factory()->create();
        $provisional->syncRoles([User::ROL_ASOCIADO]);
        $provisional->contrasena_provisional = true;
        $provisional->save();

        $propia = User::factory()->create();
        $propia->syncRoles([User::ROL_ASOCIADO]);

        Livewire::test(ListUsers::class)
            ->filterTable('contrasena_provisional', true)
            ->assertCanSeeTableRecords([$provisional])
            ->assertCanNotSeeTableRecords([$propia]);
    }
}
