<?php

namespace Tests\Feature;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Evento;
use App\Models\Postulacion;
use App\Models\Transaccion;
use App\Models\User;
use App\Models\Vacante;
use App\Policies\PoliticaDeContenido;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AutorizacionDeBorradoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    /** @return list<array{0: class-string<PoliticaDeContenido>, 1: class-string<Model>}> */
    public static function politicasDeContenido(): array
    {
        $pares = [];

        foreach (glob(dirname(__DIR__, 2).'/app/Policies/*Policy.php') ?: [] as $archivo) {
            $politica = 'App\Policies\\'.basename($archivo, '.php');

            if (! is_subclass_of($politica, PoliticaDeContenido::class)) {
                continue;
            }

            $modelo = 'App\Models\\'.str_replace('Policy', '', class_basename($politica));

            if (class_exists($modelo) && method_exists($modelo, 'factory')) {
                $pares[] = [$politica, $modelo];
            }
        }

        sort($pares);

        return $pares;
    }

    #[DataProvider('politicasDeContenido')]
    public function test_la_secretaria_no_borra_ningun_contenido_del_gremio(string $politica, string $modelo): void
    {
        $secretaria = $this->crearUsuario(User::ROL_SUBADMIN);
        $registro = $modelo::factory()->create();

        $this->assertFalse($secretaria->can('delete', $registro), class_basename($modelo).': la secretaria no borra.');
        $this->assertFalse($secretaria->can('deleteAny', $modelo), class_basename($modelo).': ni en lote.');
        $this->assertTrue($this->crearUsuario(User::ROL_SUPER_ADMIN)->can('delete', $registro));
    }

    public function test_el_super_administrador_no_puede_borrarse_a_si_mismo(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        $otro = $this->crearUsuario(User::ROL_SUBADMIN);

        $this->assertFalse($direccion->can('delete', $direccion));
        $this->assertTrue($direccion->can('delete', $otro));
    }

    public function test_el_panel_esconde_el_boton_de_borrar_la_cuenta_propia(): void
    {
        $direccion = $this->crearUsuario(User::ROL_SUPER_ADMIN);
        $otro = $this->crearUsuario(User::ROL_SUBADMIN);

        $this->actingAs($direccion);

        Livewire::test(EditUser::class, ['record' => $direccion->getKey()])
            ->assertActionHidden(TestAction::make('delete'));

        Livewire::test(EditUser::class, ['record' => $otro->getKey()])
            ->assertActionVisible(TestAction::make('delete'));
    }

    public function test_el_registro_contable_no_lo_borra_nadie(): void
    {
        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Mensualidad,
            'monto' => 50000,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Aprobada,
            'metodo' => MetodoPago::Pse,
        ]);

        foreach ([User::ROL_SUPER_ADMIN, User::ROL_SUBADMIN] as $rol) {
            $this->assertFalse($this->crearUsuario($rol)->can('delete', $transaccion), "{$rol} no borra una transaccion.");
        }
    }

    public function test_borrar_un_asociado_arrastra_su_cartera_sus_vacantes_y_sus_postulaciones(): void
    {
        $asociado = Asociado::factory()->create();
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 100000,
            'meses_mora' => 2,
            'actualizado_at' => now(),
        ]);
        $vacante = Vacante::factory()->for($asociado)->create();
        Postulacion::factory()->for($vacante)->create();

        $asociado->delete();

        $this->assertDatabaseCount('carteras', 0);
        $this->assertDatabaseCount('vacantes', 0);
        $this->assertDatabaseCount('postulaciones', 0);
    }

    public function test_borrar_un_evento_arrastra_sus_inscripciones_y_conserva_el_cobro(): void
    {
        $evento = Evento::factory()->publicado()->create();
        $inscripcion = $evento->inscripciones()->create([
            'nombre' => 'Persona Interesada',
            'correo' => 'persona@ejemplo.test',
            'telefono' => '3001234567',
            'acepta_datos' => true,
        ]);
        $transaccion = Transaccion::create([
            'referencia' => Transaccion::generarReferencia(),
            'concepto' => ConceptoTransaccion::Evento,
            'inscripcion_id' => $inscripcion->id,
            'monto' => 30000,
            'moneda' => 'COP',
            'estado' => EstadoTransaccion::Aprobada,
            'metodo' => MetodoPago::Pse,
        ]);

        $evento->delete();

        $this->assertDatabaseCount('inscripciones', 0);
        $this->assertDatabaseHas('transacciones', [
            'id' => $transaccion->id,
            'inscripcion_id' => null,
        ]);
    }
}
