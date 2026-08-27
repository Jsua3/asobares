<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Mail\NuevaPostulacion;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Postulacion;
use App\Models\Vacante;
use App\Support\Formulario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BolsaDeEmpleoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_muro_muestra_solo_vacantes_publicadas_y_vivas(): void
    {
        $publicada = Vacante::factory()->publicado()->create(['cargo' => 'Bartender publicado']);
        $pendiente = Vacante::factory()->pendiente()->create(['cargo' => 'Cocinero pendiente']);
        $cerrada = Vacante::factory()->publicado()->cerrada()->create(['cargo' => 'Mesero ya contratado']);
        $vencida = Vacante::factory()->publicado()->vencida()->create(['cargo' => 'Portero de anoche']);

        $respuesta = $this->get(route('empleo.index'));

        $respuesta->assertSee($publicada->cargo);
        $respuesta->assertDontSee($pendiente->cargo);
        $respuesta->assertDontSee($cerrada->cargo);
        $respuesta->assertDontSee($vencida->cargo);
    }

    public function test_el_filtro_por_area_del_establecimiento_funciona(): void
    {
        Vacante::factory()->publicado()->create([
            'cargo' => 'Bartender de barra',
            'categoria_cargo' => CargoDelSector::Barra,
        ]);
        Vacante::factory()->publicado()->create([
            'cargo' => 'Auxiliar de cocina',
            'categoria_cargo' => CargoDelSector::Cocina,
        ]);

        $respuesta = $this->get(route('empleo.index', ['categoria' => CargoDelSector::Cocina->value]));

        $respuesta->assertSee('Auxiliar de cocina');
        $respuesta->assertDontSee('Bartender de barra');
    }

    public function test_el_filtro_por_municipio_usa_el_del_establecimiento(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        Vacante::factory()->for($asociado)->publicado()->create(['cargo' => 'Cargo del municipio buscado']);
        Vacante::factory()->publicado()->create(['cargo' => 'Cargo de otro municipio']);

        $respuesta = $this->get(route('empleo.index', ['municipio' => $asociado->municipio->slug]));

        $respuesta->assertSee('Cargo del municipio buscado');
        $respuesta->assertDontSee('Cargo de otro municipio');
    }

    public function test_una_categoria_inventada_no_rompe_la_pagina(): void
    {
        $this->get(route('empleo.index', ['categoria' => 'no-existe']))->assertSessionHasErrors('categoria');
    }

    public function test_el_detalle_muestra_la_vacante_publicada(): void
    {
        $vacante = Vacante::factory()->publicado()->create([
            'cargo' => 'Chef de cocina',
            'descripcion' => 'Lidera la cocina y el diseño de la carta.',
        ]);

        $respuesta = $this->get(route('empleo.show', $vacante));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('Chef de cocina');
        $respuesta->assertSee('Lidera la cocina y el diseño de la carta.');
        $respuesta->assertSee('JobPosting', escape: false);
    }

    public function test_el_detalle_de_una_vacante_no_publicada_da_404(): void
    {
        foreach ([
            Vacante::factory()->pendiente()->create(),
            Vacante::factory()->publicado()->cerrada()->create(),
            Vacante::factory()->publicado()->vencida()->create(),
        ] as $vacante) {
            $this->get(route('empleo.show', $vacante))->assertNotFound();
        }
    }

    public function test_la_postulacion_queda_guardada_y_avisa_al_establecimiento(): void
    {
        Mail::fake();

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $this->post(route('empleo.postular', $vacante), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'telefono' => '3145598821',
            'experiencia' => 'Tres años en barra de discoteca.',
            'acepta_datos' => '1',
        ])->assertSessionHas('exito');

        $postulacion = Postulacion::firstOrFail();

        $this->assertSame($vacante->id, $postulacion->vacante_id);
        $this->assertTrue($postulacion->acepta_datos);
        $this->assertNotNull($postulacion->consentimiento_at);

        Mail::assertSent(NuevaPostulacion::class, 1);
    }

    public function test_postularse_dos_veces_a_la_misma_vacante_no_duplica(): void
    {
        Mail::fake();

        $vacante = Vacante::factory()->publicado()->create();
        $datos = [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'acepta_datos' => '1',
        ];

        $this->post(route('empleo.postular', $vacante), $datos);
        $this->post(route('empleo.postular', $vacante), $datos)->assertSessionHas('exito');

        $this->assertSame(1, Postulacion::count());
        Mail::assertSent(NuevaPostulacion::class, 1);
    }

    public function test_la_postulacion_exige_la_autorizacion_de_datos(): void
    {
        $vacante = Vacante::factory()->publicado()->create();

        $this->post(route('empleo.postular', $vacante), [
            'nombre' => 'Sin Autorización',
            'correo' => 'sin@ejemplo.test',
        ])->assertSessionHasErrors('acepta_datos');

        $this->assertSame(0, Postulacion::count());
    }

    public function test_el_honeypot_descarta_la_postulacion_sin_dar_pistas(): void
    {
        $vacante = Vacante::factory()->publicado()->create();

        $this->post(route('empleo.postular', $vacante), [
            'nombre' => 'Bot',
            'correo' => 'bot@ejemplo.test',
            'acepta_datos' => '1',
            Formulario::CAMPO_TRAMPA => 'soy-un-bot',
        ])->assertStatus(422);

        $this->assertSame(0, Postulacion::count());
    }

    public function test_no_se_puede_postular_a_una_vacante_cerrada_o_vencida(): void
    {
        foreach ([
            Vacante::factory()->publicado()->cerrada()->create(),
            Vacante::factory()->publicado()->vencida()->create(),
            Vacante::factory()->pendiente()->create(),
        ] as $vacante) {
            $this->post(route('empleo.postular', $vacante), [
                'nombre' => 'Tarde',
                'correo' => 'tarde@ejemplo.test',
                'acepta_datos' => '1',
            ])->assertNotFound();
        }

        $this->assertSame(0, Postulacion::count());
    }

    public function test_una_condicion_de_carrera_al_postular_no_revienta_ni_duplica_el_correo(): void
    {
        Mail::fake();

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => 'oficina@bar.test']);
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        // Simula la carrera: justo antes de que el controlador inserte su
        // fila, "otra petición" (un doble clic, un reintento por una red
        // lenta) ya insertó una postulación para el mismo (vacante_id,
        // correo) y avisó al establecimiento. El `create()` del controlador
        // choca entonces contra el índice único, tal como pasaría con dos
        // peticiones casi simultáneas.
        // El hook es de un solo disparo. La inserción del controlador va en
        // un savepoint (portabilidad con PostgreSQL: sin él, la violación
        // aborta la transacción de RefreshDatabase entera), y ese savepoint
        // se lleva al rival al hacer rollback, porque esta simulación lo
        // planta en la MISMA conexión — en la vida real vive en otra petición
        // y sobrevive. El updateOrCreate del catch reintenta entonces el
        // create, y si el hook volviera a plantar al rival, la carrera no
        // terminaría nunca.
        $rivalYaGano = false;

        Postulacion::creating(function () use ($vacante, &$rivalYaGano): void {
            if ($rivalYaGano) {
                return;
            }

            $rivalYaGano = true;

            $id = DB::table('postulaciones')->insertGetId([
                'vacante_id' => $vacante->id,
                'nombre' => 'Ganó la carrera',
                'correo' => 'duvan@ejemplo.test',
                'acepta_datos' => true,
                'consentimiento_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Mail::to('oficina@bar.test')->send(new NuevaPostulacion(Postulacion::findOrFail($id)));
        });

        $respuesta = $this->post(route('empleo.postular', $vacante), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'acepta_datos' => '1',
        ]);

        Postulacion::flushEventListeners();

        $respuesta->assertSessionHas('exito');
        $this->assertSame(1, Postulacion::count());
        Mail::assertSent(NuevaPostulacion::class, 1);
    }

    public function test_postularse_a_un_asociado_sin_correo_registrado_no_revienta(): void
    {
        Mail::fake();

        $asociado = Asociado::factory()->publicado()->create(['correo_interno' => null]);
        $vacante = Vacante::factory()->for($asociado)->publicado()->create();

        $this->post(route('empleo.postular', $vacante), [
            'nombre' => 'Duván Marín',
            'correo' => 'duvan@ejemplo.test',
            'acepta_datos' => '1',
        ])->assertSessionHas('exito');

        $this->assertSame(1, Postulacion::count());
        Mail::assertNothingSent();
    }

    public function test_dejar_el_perfil_dos_veces_actualiza_en_vez_de_duplicar(): void
    {
        $base = [
            'correo' => 'duvan@ejemplo.test',
            'cargo_interes' => 'Bartender',
            'categoria_cargo' => CargoDelSector::Barra->value,
            'acepta_datos' => '1',
        ];

        $this->post(route('empleo.aspirante'), $base + ['nombre' => 'Duvan Marin']);
        $this->post(route('empleo.aspirante'), $base + ['nombre' => 'Duván Alexis Marín']);

        $this->assertSame(1, Aspirante::count());
        $this->assertSame('Duván Alexis Marín', Aspirante::firstOrFail()->nombre);
    }

    public function test_sin_vacantes_el_muro_no_habla_de_filtros(): void
    {
        $this->get(route('empleo.index'))
            ->assertSuccessful()
            ->assertSee('Todavía no hay vacantes abiertas')
            ->assertDontSee('No hay vacantes con ese filtro');
    }

    public function test_un_filtro_sin_resultados_lo_dice_con_claridad(): void
    {
        Vacante::factory()->publicado()->create([
            'cargo' => 'Bartender de barra',
            'categoria_cargo' => CargoDelSector::Barra,
        ]);

        $this->get(route('empleo.index', ['categoria' => CargoDelSector::Cocina->value]))
            ->assertSuccessful()
            ->assertSee('No hay vacantes con ese filtro')
            ->assertDontSee('Bartender de barra');
    }

    public function test_el_error_del_perfil_devuelve_al_formulario(): void
    {
        $this->from(route('empleo.index'))
            ->post(route('empleo.aspirante'), [
                'nombre' => 'Sin correo',
                'cargo_interes' => 'Bartender',
                'categoria_cargo' => CargoDelSector::Barra->value,
                'acepta_datos' => '1',
            ])
            ->assertRedirect(route('empleo.index').'#perfil')
            ->assertSessionHasErrors('correo');
    }

    public function test_el_error_de_la_postulacion_devuelve_al_formulario(): void
    {
        $vacante = Vacante::factory()->publicado()->create();

        $this->from(route('empleo.show', $vacante))
            ->post(route('empleo.postular', $vacante), [
                'nombre' => 'Sin correo',
                'acepta_datos' => '1',
            ])
            ->assertRedirect(route('empleo.show', $vacante).'#postularme')
            ->assertSessionHasErrors('correo');
    }

    public function test_el_muro_y_el_detalle_no_exponen_datos_de_quien_se_postulo(): void
    {
        $vacante = Vacante::factory()->publicado()->create();
        Postulacion::factory()->for($vacante)->create([
            'nombre' => 'Candidato Privado',
            'correo' => 'candidato.privado@ejemplo.test',
        ]);

        $this->get(route('empleo.index'))
            ->assertDontSee('candidato.privado@ejemplo.test')
            ->assertDontSee('Candidato Privado');

        $this->get(route('empleo.show', $vacante))
            ->assertDontSee('candidato.privado@ejemplo.test')
            ->assertDontSee('Candidato Privado');
    }
}
