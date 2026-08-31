<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * El WhatsApp institucional no promete lo que no cumple (OBS3-14).
 *
 * El directivo preguntó por el enlace de WhatsApp: «¿si ese tiene respuesta?»
 * (R21 11:15) y pidió «hay que automatizarlo, para que quede automático» (R21
 * 11:21-11:24). En la misma mesa quedó claro de quién es esa tarea: «eso me
 * toca decir» (R21 11:28).
 *
 * Automatizar WhatsApp NO es código de esta plataforma --es WhatsApp Business,
 * una cuenta y una configuración del gremio-- y el §27.2 lo cierra así:
 * «decirlo así y no prometer nada». Lo que sí estaba en nuestra mano era dejar
 * de insinuarlo: el botón decía «Escribirnos YA por WhatsApp», y ese «ya» es
 * lo que hace que el silencio de una noche se lea como abandono.
 */
class AvisoDelWhatsappTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** @return list<array{string}> */
    public static function paginasConWhatsappInstitucional(): array
    {
        return [
            'afíliate' => ['afiliate'],
            'contacto' => ['contacto'],
        ];
    }

    #[DataProvider('paginasConWhatsappInstitucional')]
    public function test_el_aviso_acompania_al_whatsapp_institucional(string $ruta): void
    {
        $this->get(route($ruta))
            ->assertOk()
            ->assertSee(ajuste('contacto_whatsapp_aviso'), escape: false);
    }

    /**
     * El aviso lo edita el gremio: el día que contraten WhatsApp Business y de
     * verdad automaticen, el texto cambia desde el panel y no desde un editor.
     */
    public function test_el_aviso_es_editable(): void
    {
        Setting::query()->where('clave', 'contacto_whatsapp_aviso')
            ->first()?->update(['valor' => 'AHORA SI RESPONDE UN BOT']);

        $this->get(route('contacto'))->assertOk()->assertSee('AHORA SI RESPONDE UN BOT', escape: false);
    }

    /**
     * La guardia: que no vuelva ningún «ya», «al instante» o «inmediato»
     * pegado al WhatsApp. Es la promesa concreta que se retiró, y volvería
     * sola en cuanto alguien quiera que el botón suene más enérgico.
     */
    public function test_ninguna_vista_promete_respuesta_inmediata_por_whatsapp(): void
    {
        $prohibidas = [
            '/Escribirnos ya por WhatsApp/iu',
            '/WhatsApp[^<]{0,40}\b(al instante|inmediat\w+|24\/7|respuesta autom)/iu',
            '/\b(al instante|inmediat\w+|24\/7)\b[^<]{0,40}WhatsApp/iu',
        ];

        $hallazgos = [];

        foreach (File::allFiles(resource_path('views/publico')) as $archivo) {
            $contenido = $archivo->getContents();
            $ruta = str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo->getPathname());

            foreach ($prohibidas as $patron) {
                if (preg_match_all($patron, $contenido, $coincidencias) > 0) {
                    $hallazgos[] = $ruta.' → '.implode(', ', array_unique($coincidencias[0]));
                }
            }
        }

        $this->assertSame(
            [],
            $hallazgos,
            "El WhatsApp del gremio lo contesta una persona. No prometas inmediatez:\n".implode("\n", $hallazgos)
        );
    }
}
