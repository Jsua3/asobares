<?php

namespace Tests\Feature\Panel;

use App\Filament\Pages\Bitacora;
use App\Filament\Resources\Aspirantes\Pages\ListAspirantes;
use App\Models\Aspirante;
use App\Models\Noticia;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * RF-39: la bitácora la lee la oficina, no un programador.
 *
 * La página no muestra la descripción que guarda cada modelo: arma su propia
 * frase con «quién + verbo + tipo + etiqueta» para que se lea igual venga de
 * donde venga. Esta clase vigila que esa frase siga siendo castellano y que
 * ningún tipo de contenido nuevo acabe leyéndose como «actualizó un registro».
 *
 * No existía ninguna prueba de esta página hasta el 9 de septiembre de 2026.
 */
class BitacoraTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        Filament::setCurrentPanel('admin');
    }

    private function direccion(): User
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        return $usuario->fresh();
    }

    /**
     * Aprobar y retirar del banco son el mismo evento técnico --`updated`-- y
     * significan cosas opuestas: uno entrega el teléfono y el correo de una
     * persona a todos los afiliados y el otro se los quita. Con la frase genérica
     * las dos filas se leerían idénticas («actualizó el perfil de Camila»), que
     * es tanto como no registrar nada: quien audita necesita saber la dirección.
     */
    public function test_la_bitacora_distingue_entrar_y_salir_del_banco_de_talento(): void
    {
        $usuario = $this->direccion();
        $this->actingAs($usuario);

        $aspirante = Aspirante::factory()->create(['nombre' => 'Camila Restrepo']);

        Livewire::test(ListAspirantes::class)
            ->callAction(TestAction::make('aprobar')->table($aspirante));

        Livewire::test(Bitacora::class)
            ->assertSee('entra al banco de talento')
            ->assertDontSee('actualizó un registro');

        Livewire::test(ListAspirantes::class)
            ->callAction(TestAction::make('retirar')->table($aspirante->fresh()));

        Livewire::test(Bitacora::class)->assertSee('sale del banco de talento');
    }

    /** El resto del contenido conserva la frase de siempre. */
    public function test_el_contenido_editorial_conserva_la_frase_de_siempre(): void
    {
        $usuario = $this->direccion();
        $this->actingAs($usuario);

        Noticia::factory()->create(['titulo' => 'Una entrada del boletín']);

        Livewire::test(Bitacora::class)
            ->assertSee($usuario->name)
            ->assertSee('la entrada del boletín');
    }

    /**
     * Ningún tipo de contenido puede quedar sin traducir: «un registro» es el
     * respaldo para lo imprevisto, no un destino aceptable para un módulo que ya
     * existe. Se comprueba contra los nombres de registro que el código declara,
     * y no contra los que haya en la base, para que falle el día en que alguien
     * añada `LogsActivity` a un modelo y se olvide de esta tabla.
     */
    public function test_ningun_modelo_que_registra_actividad_se_queda_sin_traduccion(): void
    {
        $traducidos = array_keys(Bitacora::TIPOS);

        foreach ($this->nombresDeRegistroDeclarados() as $nombre) {
            $this->assertContains(
                $nombre,
                $traducidos,
                "«{$nombre}» escribe en la bitácora y no está en Bitacora::TIPOS: la oficina lo leería como «un registro»."
            );
        }
    }

    /**
     * Los `useLogName('…')` que declaran los modelos, leídos del código fuente.
     *
     * @return list<string>
     */
    private function nombresDeRegistroDeclarados(): array
    {
        $nombres = [];

        foreach (glob(app_path('Models/*.php')) ?: [] as $archivo) {
            if (preg_match("/useLogName\('([a-z_]+)'\)/", (string) file_get_contents($archivo), $coincidencia) === 1) {
                $nombres[] = $coincidencia[1];
            }
        }

        return $nombres;
    }
}
