<?php

namespace Tests\Feature;

use App\Models\Artista;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La tarifa del artista no se publica (OBS3-08).
 *
 * El directivo lo razonó desde el lado del que contrata: «de pronto no lo
 * contacto porque se sesga de una vez con el precio» (R21 14:01). Alguien
 * propuso dejarlo «a decisión del artista, si quiere ponerle precio» (R21
 * 14:31) y él lo descartó en la frase siguiente: «no, no, no, yo no le
 * pondría precio» (R21 14:37). Ratificado en la tercera grabación: «el tema
 * de los artistas, la tarifa, pues eso al parecer se elimina» (R23 09:49).
 *
 * Por eso NO hay bandera `publicar_tarifa`: se construyó lo que se decidió,
 * no lo que se propuso. El §27.8 lo cierra por escrito --«no devolver la
 * tarifa del artista a la ficha pública aunque el campo siga en el modelo»--
 * y esta prueba es lo que lo hace exigible.
 */
class TarifaDelArtistaTest extends TestCase
{
    use RefreshDatabase;

    /** Un número con separadores de miles inconfundibles en el HTML. */
    private const int TARIFA = 1234567;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * La ficha pública no enseña el número por ninguna de sus formas: ni
     * formateado con `pesos()`, ni crudo, ni con los decimales que le pone el
     * cast del modelo.
     */
    public function test_la_ficha_publica_no_enseña_la_tarifa(): void
    {
        $artista = $this->artistaConTarifa();

        $respuesta = $this->get(route('artistas.show', $artista))->assertOk();

        foreach ($this->formasDelNumero() as $forma) {
            $respuesta->assertDontSee($forma, escape: false);
        }
    }

    /** Y la tarjeta del listado tampoco, que es donde primero se ve. */
    public function test_la_tarjeta_del_listado_no_enseña_la_tarifa(): void
    {
        $this->artistaConTarifa();

        $respuesta = $this->get(route('artistas.index'))->assertOk();

        foreach ($this->formasDelNumero() as $forma) {
            $respuesta->assertDontSee($forma, escape: false);
        }
    }

    /**
     * En su lugar se lee la leyenda, que el gremio edita desde el panel
     * porque el acta ofrecía dos redacciones: «a convenir» o «según el
     * evento».
     */
    public function test_en_su_lugar_se_lee_la_leyenda_editable(): void
    {
        $artista = $this->artistaConTarifa();

        Setting::query()->where('clave', 'artistas_tarifa_leyenda')
            ->first()?->update(['valor' => 'SEGUN EL EVENTO']);

        $this->get(route('artistas.show', $artista))->assertOk()->assertSee('SEGUN EL EVENTO', escape: false);
        $this->get(route('artistas.index'))->assertOk()->assertSee('SEGUN EL EVENTO', escape: false);
    }

    /**
     * Un artista SIN tarifa se ve igual que uno con ella. Si no fuera así, el
     * hueco delataría el precio por ausencia: quien mirase dos fichas sabría
     * cuál de los dos cobra caro.
     */
    public function test_con_tarifa_y_sin_tarifa_la_ficha_se_lee_igual(): void
    {
        $con = $this->artistaConTarifa('con-tarifa');
        $sin = Artista::factory()->publicado()->create([
            'nombre' => 'Artista Sin Tarifa', 'slug' => 'sin-tarifa', 'tarifa_desde' => null,
        ]);

        $leyenda = ajuste('artistas_tarifa_leyenda');

        $this->get(route('artistas.show', $con))->assertOk()->assertSee($leyenda, escape: false);
        $this->get(route('artistas.show', $sin))->assertOk()->assertSee($leyenda, escape: false);
    }

    /**
     * El dato NO se borra: el gremio lo sigue necesitando para recomendar a
     * alguien por teléfono. Lo que cambia es quién lo ve.
     */
    public function test_la_tarifa_se_conserva_en_el_modelo(): void
    {
        $artista = $this->artistaConTarifa();

        $this->assertSame(
            number_format(self::TARIFA, 2, '.', ''),
            (string) $artista->fresh()->tarifa_desde,
            'La tarifa tiene que seguir guardada: se retira de la vista, no del sistema.'
        );
    }

    /**
     * Y el formulario de inscripción sigue pidiéndola, pero ya no promete que
     * se publique. La ayuda vieja decía «déjalo vacío si prefieres decir "a
     * convenir"», que después de OBS3-08 es falso: diga lo que diga el
     * artista, su ficha lee la leyenda.
     */
    public function test_el_formulario_avisa_que_la_tarifa_no_se_publica(): void
    {
        $this->get(route('artistas.inscripcion'))
            ->assertOk()
            ->assertSee('nunca aparece un precio', escape: false)
            ->assertDontSee('Déjalo vacío si prefieres decir', escape: false);
    }

    private function artistaConTarifa(string $slug = 'artista-con-tarifa'): Artista
    {
        return Artista::factory()->publicado()->create([
            'nombre' => 'Artista Con Tarifa '.$slug,
            'slug' => $slug,
            'tarifa_desde' => self::TARIFA,
        ]);
    }

    /** @return list<string> */
    private function formasDelNumero(): array
    {
        return [
            pesos(self::TARIFA),            // $1.234.567
            (string) self::TARIFA,          // 1234567
            number_format(self::TARIFA, 2, '.', ''), // 1234567.00, el cast decimal:2
        ];
    }
}
