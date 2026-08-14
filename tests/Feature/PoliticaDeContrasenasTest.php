<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * RF-40. El campo de contraseña sólo tenía longitud máxima, así que nada
 * impedía dejar una sola letra guardando la cuenta que gobierna los pagos.
 */
class PoliticaDeContrasenasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion->fresh());
    }

    /** @return array<string, mixed> */
    private function datos(string $clave): array
    {
        return [
            'name' => 'Cuenta Nueva',
            'email' => 'cuenta.nueva@asobares.test',
            'password' => $clave,
            'roles' => [Role::where('name', User::ROL_SUBADMIN)->firstOrFail()->id],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function clavesDebiles(): array
    {
        return [
            'una sola letra' => ['a'],
            'corta' => ['Asob2026*'],
            'sin mayúsculas' => ['asobares2026*quindio'],
            'sin números' => ['Asobares*Quindio*Bar'],
            'sin símbolos' => ['Asobares2026Quindio'],
        ];
    }

    #[DataProvider('clavesDebiles')]
    public function test_no_se_acepta_una_contrasena_debil(string $clave): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm($this->datos($clave))
            ->call('create')
            ->assertHasFormErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'cuenta.nueva@asobares.test']);
    }

    public function test_una_contrasena_fuerte_si_pasa(): void
    {
        Livewire::test(CreateUser::class)
            ->fillForm($this->datos('Asobares2026*Quindio'))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'cuenta.nueva@asobares.test']);
    }

    /**
     * Editar sin tocar la contraseña deja el campo vacío a propósito: la regla
     * de fortaleza no puede convertirse en un bloqueo para cambiar un nombre.
     */
    public function test_editar_sin_cambiar_la_contrasena_no_exige_nada(): void
    {
        $usuario = User::factory()->create(['password' => Hash::make('Asobares2026*Quindio')]);
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        Livewire::test(EditUser::class, ['record' => $usuario->getRouteKey()])
            ->fillForm(['name' => 'Nombre Corregido', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nombre Corregido', $usuario->fresh()->name);
    }
}
