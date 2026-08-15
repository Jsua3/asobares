<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Enums\TipoMensaje;
use App\Models\Artista;
use App\Models\Aspirante;
use App\Models\Evento;
use App\Models\Inscripcion;
use App\Models\Mensaje;
use App\Models\Municipio;
use App\Models\Postulacion;
use App\Models\Proveedor;
use App\Models\Vacante;
use Database\Seeders\SettingSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * G12 (Ley 1581): el consentimiento se guardaba sin evidencia. Un booleano y
 * una fecha no responden lo que la SIC pregunta ante un reclamo del titular:
 * desde dónde se autorizó, con qué navegador y qué versión de la política
 * estaba publicada al aceptar. El sello vive en un único trait
 * (`ProtegeFormularioPublico`), así que la evidencia cubre los seis
 * formularios públicos a la vez — y aquí se verifica en cada uno, porque un
 * formulario que dejara de usar el trait perdería la constancia en silencio.
 */
class EvidenciaDelConsentimientoTest extends TestCase
{
    use RefreshDatabase;

    private const string AGENTE = 'NavegadorDePrueba/1.0';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingSeeder::class);
    }

    private function verificarEvidencia(?Model $registro, string $formulario): void
    {
        $this->assertNotNull($registro, "El formulario de {$formulario} no guardó ningún registro.");
        $this->assertSame('127.0.0.1', $registro->consentimiento_ip, "{$formulario}: no quedó la IP del consentimiento.");
        $this->assertSame(self::AGENTE, $registro->consentimiento_agente, "{$formulario}: no quedó el agente del consentimiento.");
        $this->assertNotNull($registro->consentimiento_politica, "{$formulario}: no quedó la versión de la política aceptada.");
        $this->assertSame(
            (string) ajuste('politica_actualizacion'),
            $registro->consentimiento_politica,
            "{$formulario}: la versión guardada no es la que la política publica como su vigencia."
        );
    }

    public function test_postularse_guarda_la_evidencia_del_consentimiento(): void
    {
        $vacante = Vacante::factory()->publicado()->create();

        $this->withHeaders(['User-Agent' => self::AGENTE])->post(route('empleo.postular', $vacante), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'acepta_datos' => '1',
        ]);

        $this->verificarEvidencia(Postulacion::first(), 'postulación');
    }

    public function test_dejar_el_perfil_guarda_la_evidencia_del_consentimiento(): void
    {
        $this->withHeaders(['User-Agent' => self::AGENTE])->post(route('empleo.aspirante'), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'cargo_interes' => 'Bartender',
            'categoria_cargo' => CargoDelSector::Barra->value,
            'acepta_datos' => '1',
        ]);

        $this->verificarEvidencia(Aspirante::first(), 'banco de talento');
    }

    public function test_escribir_al_gremio_guarda_la_evidencia_del_consentimiento(): void
    {
        $this->withHeaders(['User-Agent' => self::AGENTE])->post(route('contacto.store'), [
            'tipo' => TipoMensaje::Contacto->value,
            'nombre' => 'Paula Restrepo',
            'correo' => 'paula@ejemplo.test',
            'mensaje' => 'Quisiera información sobre las cifras del Observatorio.',
            'acepta_datos' => '1',
        ]);

        $this->verificarEvidencia(Mensaje::first(), 'contacto');
    }

    public function test_inscribirse_a_un_evento_guarda_la_evidencia_del_consentimiento(): void
    {
        $evento = Evento::create([
            'titulo' => 'Capacitación gratuita',
            'slug' => 'capacitacion-gratuita',
            'descripcion' => 'Manejo responsable de barras.',
            'fecha_inicio' => now()->addDays(15),
            'precio' => 0,
            'permite_inscripcion' => true,
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $this->withHeaders(['User-Agent' => self::AGENTE])->post(route('eventos.inscribir', $evento), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'telefono' => '3134470091',
            'acepta_datos' => '1',
        ]);

        $this->verificarEvidencia(Inscripcion::first(), 'inscripción');
    }

    public function test_inscribirse_como_artista_guarda_la_evidencia_del_consentimiento(): void
    {
        $this->withHeaders(['User-Agent' => self::AGENTE])->post(route('artistas.inscripcion.store'), [
            'nombre' => 'DJ Tornamesa',
            'tipo' => TipoArtista::Dj->value,
            'municipio_id' => Municipio::factory()->create()->id,
            'whatsapp' => '3151189203',
            'acepta_datos' => '1',
        ]);

        $this->verificarEvidencia(Artista::first(), 'artista');
    }

    public function test_inscribirse_como_proveedor_guarda_la_evidencia_del_consentimiento(): void
    {
        $this->withHeaders(['User-Agent' => self::AGENTE])->post(route('proveedores.inscripcion.store'), [
            'nombre' => 'Hielos del Quindío',
            'categoria_proveedor' => CategoriaProveedor::Hielo->value,
            'municipio_id' => Municipio::factory()->create()->id,
            'whatsapp' => '3151189203',
            'correo' => 'ventas@hielos.test',
            'acepta_datos' => '1',
        ]);

        $this->verificarEvidencia(Proveedor::first(), 'proveedor');
    }

    /**
     * La política publicada tiene que decir lo que el sistema hace de verdad
     * (G12): los plazos se leen de la configuración, no de un texto que se
     * quede viejo cuando alguien cambie una variable.
     */
    public function test_la_politica_publica_los_plazos_reales_de_retencion(): void
    {
        config([
            'bolsas.retencion_postulaciones_meses' => 7,
            'bolsas.retencion_postulaciones_maximo_meses' => 19,
            'bolsas.retencion_aspirantes_meses' => 14,
            'retencion.contacto_meses' => 5,
            'retencion.pqr_meses' => 25,
            'retencion.inscripciones_meses' => 26,
        ]);

        $respuesta = $this->get('/politica-de-datos');

        $respuesta->assertSuccessful();
        $respuesta->assertSee('7 meses');
        $respuesta->assertSee('19 meses');
        $respuesta->assertSee('14 meses');
        $respuesta->assertSee('5 meses');
        $respuesta->assertSee('25 meses');
        $respuesta->assertSee('26 meses');
    }
}
