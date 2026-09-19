<?php

namespace Tests\Feature;

use App\Filament\Resources\Asociados\Pages\ListAsociados;
use App\Models\Asociado;
use App\Models\User;
use App\Services\DescargarAccesosProvisionales;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class DescargarAccesosProvisionalesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function usuario(string $rol, ?Asociado $ficha = null, bool $provisional = false): User
    {
        $usuario = User::factory()->create(['asociado_id' => $ficha?->id]);
        $usuario->syncRoles([$rol]);
        $usuario->contrasena_provisional = $provisional;
        $usuario->save();

        return $usuario->fresh();
    }

    /** @return list<list<string>> */
    private function descargar(User $direccion): array
    {
        $respuesta = app(DescargarAccesosProvisionales::class)->descargar($direccion);

        $this->assertStringContainsString('attachment', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringContainsString('no-store', $respuesta->headers->get('Cache-Control'));
        $this->assertSame('no-cache', $respuesta->headers->get('Pragma'));

        ob_start();
        $respuesta->sendContent();
        $csv = ob_get_clean();
        $this->assertIsString($csv);

        $archivo = fopen('php://memory', 'w+');
        fwrite($archivo, $csv);
        rewind($archivo);
        $filas = [];

        while (($fila = fgetcsv($archivo, escape: '')) !== false) {
            $filas[] = $fila;
        }

        fclose($archivo);

        return $filas;
    }

    public function test_solo_direccion_puede_descargar_incluso_si_se_invoca_el_servicio_directamente(): void
    {
        $direccion = $this->usuario(User::ROL_SUPER_ADMIN);
        $subadmin = $this->usuario(User::ROL_SUBADMIN);
        $asociado = $this->usuario(User::ROL_ASOCIADO);

        $this->actingAs($direccion);
        Livewire::test(ListAsociados::class)->assertActionVisible('descargarAccesosProvisionales');

        $this->actingAs($subadmin);
        Livewire::test(ListAsociados::class)->assertActionHidden('descargarAccesosProvisionales');

        foreach ([$subadmin, $asociado] as $noAutorizado) {
            $this->actingAs($noAutorizado);
            try {
                app(DescargarAccesosProvisionales::class)->descargar($noAutorizado);
                $this->fail('Una cuenta sin Dirección pudo descargar accesos.');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
    }

    public function test_la_accion_del_panel_entrega_el_archivo_a_direccion(): void
    {
        $direccion = $this->usuario(User::ROL_SUPER_ADMIN);
        $this->actingAs($direccion);

        Livewire::test(ListAsociados::class)
            ->callAction('descargarAccesosProvisionales')
            ->assertFileDownloaded('accesos-provisionales.csv');
    }

    /**
     * En producción se descargó dos veces seguidas sin que nadie supiera que
     * cada descarga invalida el archivo anterior, y cada una tardó más de
     * diez segundos sin que la pantalla dijera por qué. El aviso nombra
     * cuántas cuentas cambian y que hay que esperar.
     */
    public function test_el_aviso_dice_cuantas_cuentas_cambian_y_que_hay_que_esperar(): void
    {
        $direccion = $this->usuario(User::ROL_SUPER_ADMIN);
        $this->actingAs($direccion);
        $this->usuario(User::ROL_ASOCIADO, Asociado::factory()->create(), provisional: true);
        $this->usuario(User::ROL_ASOCIADO, Asociado::factory()->create(), provisional: true);
        $this->usuario(User::ROL_ASOCIADO, Asociado::factory()->create(), provisional: false);

        Livewire::test(ListAsociados::class)
            ->mountAction('descargarAccesosProvisionales')
            ->assertMountedActionModalSee('las 2 cuentas')
            ->assertMountedActionModalSee('dejan de funcionar')
            ->assertMountedActionModalSee('no cierres ni recargues');
    }

    public function test_csv_solo_incluye_cuentas_provisionales_y_rota_las_claves(): void
    {
        $direccion = $this->usuario(User::ROL_SUPER_ADMIN);
        $this->actingAs($direccion);
        $ficha = Asociado::factory()->create([
            'nombre' => '=Local de prueba',
            'documento' => 'NIT-PRIVADO',
            'telefono_interno' => 'TELEFONO-PRIVADO',
        ]);
        $socio = $this->usuario(User::ROL_ASOCIADO, $ficha, provisional: true);
        $socio->name = '+Titular de prueba';
        $socio->email = 'socio@asobares.test';
        $socio->save();
        $definitivo = $this->usuario(User::ROL_ASOCIADO, Asociado::factory()->create(), provisional: false);
        $sinVinculo = $this->usuario(User::ROL_ASOCIADO, provisional: true);
        $hashDefinitivo = $definitivo->password;
        $hashSinVinculo = $sinVinculo->password;
        $hashAntes = $socio->password;
        $recuerdoAntes = $socio->getRememberToken();

        $primera = $this->descargar($direccion);
        $this->assertSame(['establecimiento', 'nombre', 'correo', 'contraseña'], $primera[0]);
        $this->assertCount(2, $primera);
        $this->assertSame(["'=Local de prueba", "'+Titular de prueba", 'socio@asobares.test'], array_slice($primera[1], 0, 3));
        $claveAnterior = $primera[1][3];
        $this->assertSame(12, strlen($claveAnterior));
        $this->assertMatchesRegularExpression('/[A-HJ-NP-Z]/', $claveAnterior);
        $this->assertMatchesRegularExpression('/[a-km-z]/', $claveAnterior);
        $this->assertMatchesRegularExpression('/[2-9]/', $claveAnterior);
        $this->assertMatchesRegularExpression('/[!#$%&*?]/', $claveAnterior);
        $this->assertDoesNotMatchRegularExpression('/[0O1lI]/', $claveAnterior);
        $this->assertNotSame($hashAntes, $socio->fresh()->password);
        $recuerdoPrimero = $socio->fresh()->getRememberToken();
        $this->assertNotSame($recuerdoAntes, $recuerdoPrimero);
        $this->assertTrue(Hash::check($claveAnterior, $socio->fresh()->password));

        $segunda = $this->descargar($direccion);
        $recuerdoDespues = $socio->fresh()->getRememberToken();
        $this->assertFalse(Hash::check($claveAnterior, $socio->fresh()->password));
        $this->assertTrue(Hash::check($segunda[1][3], $socio->fresh()->password));
        $this->assertNotSame($recuerdoPrimero, $recuerdoDespues);
        $this->assertSame($hashDefinitivo, $definitivo->fresh()->password);
        $this->assertSame($hashSinVinculo, $sinVinculo->fresh()->password);

        $hashTrasSegunda = $socio->fresh()->password;
        $socio->contrasena_provisional = false;
        $socio->save();
        $tercera = $this->descargar($direccion);
        $this->assertCount(1, $tercera);
        $this->assertSame($hashTrasSegunda, $socio->fresh()->password);

        $registro = Activity::query()->get()->toJson();
        $this->assertStringNotContainsString($claveAnterior, $registro);
        $this->assertStringNotContainsString($segunda[1][3], $registro);
        $this->assertStringNotContainsString('NIT-PRIVADO', json_encode($primera));
        $this->assertStringNotContainsString('TELEFONO-PRIVADO', json_encode($primera));
        $this->assertStringNotContainsString($claveAnterior, json_encode(session()->all()));
        $this->assertStringNotContainsString($segunda[1][3], json_encode(session()->all()));
    }

    public function test_todos_los_prefijos_de_formula_se_neutralizan_en_datos_de_la_base(): void
    {
        $direccion = $this->usuario(User::ROL_SUPER_ADMIN);
        $this->actingAs($direccion);

        foreach (['=', '+', '-', '@', "\t", "\r"] as $indice => $prefijo) {
            $ficha = Asociado::factory()->create(['nombre' => $prefijo.'Local '.$indice]);
            $usuario = $this->usuario(User::ROL_ASOCIADO, $ficha, provisional: true);
            $usuario->name = $prefijo.'Titular '.$indice;
            $usuario->save();
        }

        $filas = $this->descargar($direccion);
        $this->assertCount(7, $filas);

        foreach (array_slice($filas, 1) as $fila) {
            $this->assertStringStartsWith("'", $fila[0]);
            $this->assertStringStartsWith("'", $fila[1]);
        }
    }
}
