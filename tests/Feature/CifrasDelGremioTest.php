<?php

namespace Tests\Feature;

use App\Filament\Pages\AjustesDelSitio;
use App\Models\Setting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolYPermisoSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * La franja «El gremio en cifras» de la portada (D-25, opción 1; Acta 05).
 *
 * Cuatro cifras del capítulo que la oficina teclea en el panel cada quince
 * días con el archivo de la contadora. Tres reglas: vacía no se pinta —el
 * sitio nace sin cifras y no presume nada, igual que el directorio—; se
 * pintan solo las ranuras que tengan número; y «Actualizado el» es la fecha
 * de la última cifra que cambió, no la del último guardado de cualquier
 * ajuste. La cuarta regla es del sembrador: resembrar no pisa lo que la
 * oficina tecleó.
 */
class CifrasDelGremioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
    }

    /**
     * Edita por el camino que dispara `saved` e invalida la caché de
     * ajustes (ver `PortadaEditableTest::editarAjuste`).
     */
    private function teclear(string $clave, string $valor): void
    {
        Setting::query()->where('clave', $clave)->firstOrFail()->update(['valor' => $valor]);
    }

    private function entrarAlPanel(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->actingAs($usuario->fresh());
    }

    private function fecha(CarbonImmutable $dia): string
    {
        return 'Actualizado el '.$dia->translatedFormat('d \d\e F \d\e Y');
    }

    public function test_sin_cifras_la_franja_no_se_pinta(): void
    {
        $titulo = (string) ajuste('portada_gremio_titulo');

        $this->assertNotSame('', $titulo, 'El título de la franja del gremio no está sembrado.');

        $this->get('/')->assertOk()
            ->assertDontSee('cifras-gremio')
            ->assertDontSee($titulo);
    }

    public function test_las_cifras_se_pintan_con_su_detalle_su_titulo_y_su_fecha(): void
    {
        $hoy = CarbonImmutable::parse('2026-09-01 10:00:00');
        $this->travelTo($hoy);

        $this->teclear('portada_gremio_titulo', 'TITULO DEL GREMIO EDITADO DESDE EL PANEL');
        $this->teclear('gremio_cifra_1', '4.831');
        $this->teclear('gremio_cifra_1_detalle', 'empleos directos en los establecimientos afiliados');
        $this->teclear('gremio_cifra_3', '92,4 %');
        $this->teclear('gremio_cifra_3_detalle', 'de los afiliados al día con su cuota');

        $this->get('/')->assertOk()
            ->assertSee('cifras-gremio')
            ->assertSee('TITULO DEL GREMIO EDITADO DESDE EL PANEL')
            ->assertSee('4.831')
            ->assertSee('empleos directos en los establecimientos afiliados')
            ->assertSee('92,4 %')
            ->assertSee('de los afiliados al día con su cuota')
            ->assertSee($this->fecha($hoy));
    }

    public function test_una_ranura_sin_numero_no_se_pinta_aunque_tenga_detalle(): void
    {
        $this->teclear('gremio_cifra_2', '4.831');
        $this->teclear('gremio_cifra_2_detalle', 'empleos directos en los establecimientos afiliados');
        $this->teclear('gremio_cifra_4_detalle', 'DETALLE HUERFANO SIN NUMERO');

        $this->get('/')->assertOk()
            ->assertSee('4.831')
            ->assertSee('empleos directos en los establecimientos afiliados')
            ->assertDontSee('DETALLE HUERFANO SIN NUMERO');
    }

    /** El camino real: la oficina teclea en «Ajustes del sitio». */
    public function test_el_panel_ofrece_las_cifras_y_la_portada_las_obedece(): void
    {
        $this->entrarAlPanel();

        Livewire::test(AjustesDelSitio::class)
            ->fillForm([
                'gremio_cifra_2' => '4.831',
                'gremio_cifra_2_detalle' => 'empleos directos en los establecimientos afiliados',
            ])
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertSame('4.831', ajuste('gremio_cifra_2'));

        $this->get('/')->assertOk()
            ->assertSee('4.831')
            ->assertSee('empleos directos en los establecimientos afiliados');
    }

    /**
     * «Ajustes del sitio» guardaba TODAS las claves en cada guardado, y una
     * actualización masiva sella `updated_at` aunque el valor no cambie: la
     * fecha de la franja habría sido la del último guardado de cualquier
     * cosa. Solo lo que cambia se escribe.
     */
    public function test_la_fecha_es_la_de_la_ultima_cifra_y_no_la_del_ultimo_guardado(): void
    {
        $this->entrarAlPanel();

        $diaDeLasCifras = CarbonImmutable::parse('2026-09-01 10:00:00');
        $this->travelTo($diaDeLasCifras);

        Livewire::test(AjustesDelSitio::class)
            ->fillForm([
                'gremio_cifra_1' => '4.831',
                'gremio_cifra_1_detalle' => 'empleos directos en los establecimientos afiliados',
            ])
            ->call('guardar')
            ->assertHasNoErrors();

        $otroDia = CarbonImmutable::parse('2026-09-15 10:00:00');
        $this->travelTo($otroDia);

        Livewire::test(AjustesDelSitio::class)
            ->fillForm(['hero_titulo' => 'Otro título del hero, dos semanas después'])
            ->call('guardar')
            ->assertHasNoErrors();

        $this->get('/')->assertOk()
            ->assertSee($this->fecha($diaDeLasCifras))
            ->assertDontSee($this->fecha($otroDia));
    }

    /**
     * `SettingSeeder` hace `updateOrCreate`: volver a sembrar —para añadir
     * un texto nuevo, por ejemplo— devolvía las cifras a vacío y la franja
     * desaparecía sin aviso. Las cifras las escribe la oficina, no el
     * sembrador (D-14 sigue abierta para el resto de ajustes).
     */
    public function test_resembrar_no_pisa_las_cifras_que_tecleo_la_oficina(): void
    {
        $this->teclear('gremio_cifra_1', '4.831');
        $this->teclear('gremio_cifra_1_detalle', 'empleos directos en los establecimientos afiliados');

        $this->seed(SettingSeeder::class);

        $this->assertSame('4.831', Setting::query()->where('clave', 'gremio_cifra_1')->value('valor'));
        $this->assertSame(
            'empleos directos en los establecimientos afiliados',
            Setting::query()->where('clave', 'gremio_cifra_1_detalle')->value('valor')
        );
    }
}
