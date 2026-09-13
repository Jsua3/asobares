<?php

namespace Tests\Feature\Panel;

use App\Enums\EstadoPublicidad;
use App\Filament\Resources\Publicidades\Pages\CreatePublicidad;
use App\Filament\Resources\Publicidades\Pages\ListPublicidades;
use App\Models\Publicidad;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * El módulo de publicidad escribe con tildes, como el resto del panel:
 * rótulos, ayudas, acciones, estados y mensajes de validación.
 */
class PublicidadConTildesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    public function test_el_formulario_rotula_y_explica_con_tildes(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $leidos = [];
        $formulario = Livewire::test(CreatePublicidad::class);

        foreach (['telefono', 'ubicacion', 'imagen'] as $campo) {
            $formulario->assertFormFieldExists($campo, function (Field $encontrado) use (&$leidos, $campo): bool {
                $leidos[$campo] = $encontrado->getLabel();

                return true;
            });
        }

        $this->assertSame(
            ['telefono' => 'Teléfono', 'ubicacion' => 'Ubicación', 'imagen' => 'Pieza gráfica'],
            $leidos
        );

        $formulario
            ->assertSee('Valor administrativo en pesos colombianos. No calcula descuentos, IVA ni facturación.')
            ->assertSee('JPG, PNG o WebP, máximo 5 MB. Es obligatoria para publicar.')
            ->assertSee('La dirección puede cambiar el estado o usar las acciones de la tabla.');
    }

    public function test_la_secretaria_lee_con_tildes_que_la_publicacion_queda_para_aprobacion(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUBADMIN));

        Livewire::test(CreatePublicidad::class)
            ->assertSee('Puedes redactar la pauta; la publicación queda para aprobación.');
    }

    public function test_la_tabla_rotula_filtra_y_nombra_sus_acciones_con_tildes(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $borrador = Publicidad::factory()->create();
        $publicada = Publicidad::factory()->create(['estado' => EstadoPublicidad::Pagada]);
        $publicada->update(['estado' => EstadoPublicidad::Publicada]);

        $leidos = [];

        Livewire::test(ListPublicidades::class)
            ->assertTableColumnExists('ubicacion', function (TextColumn $columna) use (&$leidos): bool {
                $leidos['columna'] = $columna->getLabel();

                return true;
            })
            ->assertTableFilterExists('ubicacion', function (SelectFilter $filtro) use (&$leidos): bool {
                $leidos['filtro'] = $filtro->getLabel();

                return true;
            })
            ->assertSee('No visible públicamente')
            ->assertActionHasLabel(TestAction::make('enviar_aprobacion')->table($borrador), 'Enviar a aprobación')
            ->assertActionHasLabel(TestAction::make('retirar_publicacion')->table($publicada), 'Retirar publicación');

        $this->assertSame(['columna' => 'Ubicación', 'filtro' => 'Ubicación'], $leidos);
    }

    public function test_la_tabla_vacia_lo_dice_con_tildes(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        Livewire::test(ListPublicidades::class)
            ->assertSee('Las pautas pagadas para Inicio y Directorio aparecerán aquí.');
    }

    public function test_el_estado_pendiente_de_aprobacion_lleva_tilde(): void
    {
        $this->assertSame('Pendiente de aprobación', EstadoPublicidad::PendienteAprobacion->getLabel());
    }

    public function test_publicar_una_pauta_sin_imagen_lo_explica_con_tildes(): void
    {
        $this->actingAs($this->usuarioCon(User::ROL_SUPER_ADMIN));

        $pauta = Publicidad::factory()->create([
            'estado' => EstadoPublicidad::Pagada,
            'imagen' => null,
        ]);

        try {
            $pauta->update(['estado' => EstadoPublicidad::Publicada]);
            $this->fail('Una pauta sin imagen no puede publicarse.');
        } catch (ValidationException $excepcion) {
            $this->assertSame(
                'Para publicar la pauta debe tener imagen y fechas válidas.',
                $excepcion->errors()['estado'][0]
            );
        }
    }
}
