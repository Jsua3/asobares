<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Enums\OrigenEvento;
use App\Enums\TipoEvento;
use App\Filament\Resources\Eventos\EventoResource;
use App\Filament\Resources\Eventos\Pages\CreateEvento;
use App\Filament\Resources\Eventos\Pages\EditEvento;
use App\Filament\Resources\Eventos\Pages\ListEventos;
use App\Models\Aliado;
use App\Models\Evento;
use App\Models\User;
use App\Support\Formulario;
use Database\Seeders\RolYPermisoSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EventoComunitarioTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $cambios = []): array
    {
        return array_merge([
            'titulo' => 'Encuentro comunitario de música',
            'fecha' => '2026-09-20',
            'hora_inicio' => '19:00',
            'hora_fin' => '22:00',
            'lugar' => 'Armenia',
            'descripcion' => 'Una noche de música local.',
            'enlace_externo' => 'https://example.com/evento',
        ], $cambios);
    }

    public function test_un_visitante_publica_y_ve_su_evento_inmediatamente(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos())
            ->assertRedirect(route('eventos.calendario', [2026, '09']));

        $evento = Evento::firstOrFail();
        $this->assertSame(EstadoPublicacion::Publicado, $evento->estado);
        $this->assertSame(OrigenEvento::Comunidad, $evento->origen);
        $this->assertNull($evento->aliado_id);
        $this->assertFalse($evento->permite_inscripcion);
        $this->assertNull($evento->cupos);
        $this->assertFalse($evento->admiteInscripciones());

        $this->get(route('eventos.calendario', [2026, '09']))
            ->assertSuccessful()
            ->assertSee($evento->titulo);
        $this->get(route('eventos.show', $evento))
            ->assertSuccessful()
            ->assertSee('Ver enlace del evento')
            ->assertDontSee('ASOBARES Capítulo Quindío</dd>', false)
            ->assertDontSee('Registrarme en la Nacional');
    }

    public function test_un_visitante_con_sesion_tambien_publica_sin_obtener_permisos_admin(): void
    {
        $usuario = User::factory()->create();
        $this->actingAs($usuario);

        $this->post(route('eventos.comunidad.store'), $this->datos())->assertRedirect();

        $this->assertSame(EstadoPublicacion::Publicado, Evento::firstOrFail()->estado);
        $this->assertFalse($usuario->can('publicar_evento'));
    }

    public function test_el_alta_publica_avisa_una_vez_a_cada_moderador_autorizado(): void
    {
        $this->seed(RolYPermisoSeeder::class);

        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $otraDireccion = User::factory()->create();
        $otraDireccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $secretaria = User::factory()->create();
        $secretaria->syncRoles([User::ROL_SUBADMIN]);
        $asociado = User::factory()->create();
        $asociado->syncRoles([User::ROL_ASOCIADO]);

        $this->post(route('eventos.comunidad.store'), $this->datos())->assertRedirect();

        $evento = Evento::firstOrFail();
        $this->assertSame(EstadoPublicacion::Publicado, $evento->estado);
        $this->assertSame(1, $direccion->notifications()->count());
        $this->assertSame(1, $otraDireccion->notifications()->count());
        $this->assertSame(0, $secretaria->notifications()->count());
        $this->assertSame(0, $asociado->notifications()->count());
        $this->assertSame(2, DatabaseNotification::count());

        $aviso = $direccion->notifications()->firstOrFail();
        $this->assertNull($aviso->read_at);
        $this->assertSame('filament', $aviso->data['format']);
        $this->assertSame('Nuevo evento de la comunidad', $aviso->data['title']);
        $this->assertStringContainsString($evento->titulo, $aviso->data['body']);
        $this->assertStringContainsString('20/09/2026', $aviso->data['body']);
        $this->assertSame('Revisar evento', $aviso->data['actions'][0]['label']);
        $this->assertSame(EventoResource::getUrl('edit', ['record' => $evento], panel: 'admin'), $aviso->data['actions'][0]['url']);
        $this->assertTrue($aviso->data['actions'][0]['shouldMarkAsRead']);
    }

    public function test_editar_y_despublicar_no_duplican_el_aviso_de_alta(): void
    {
        $this->seed(RolYPermisoSeeder::class);
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);

        $this->post(route('eventos.comunidad.store'), $this->datos())->assertRedirect();
        $evento = Evento::firstOrFail();
        $this->assertSame(1, $direccion->notifications()->count());

        $this->actingAs($direccion->fresh());
        $evento->update(['titulo' => 'Título ajustado']);
        $evento->update(['estado' => EstadoPublicacion::Borrador]);
        $evento->update(['estado' => EstadoPublicacion::Publicado]);

        $this->assertSame(1, $direccion->notifications()->count());
    }

    public function test_crear_eventos_asobares_y_aliado_desde_filament_no_emite_alerta_comunitaria(): void
    {
        $this->seed(RolYPermisoSeeder::class);
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->actingAs($direccion->fresh());

        $aliado = Aliado::factory()->visible()->create();
        $formulario = [
            'titulo' => 'Encuentro institucional',
            'slug' => 'encuentro-institucional',
            'tipo' => TipoEvento::Evento->value,
            'origen' => OrigenEvento::Asobares->value,
            'aliado_id' => null,
            'lugar' => 'Armenia',
            'fecha_inicio' => '2026-09-20 18:00:00',
            'fecha_fin' => null,
            'descripcion' => 'Encuentro del gremio.',
            'permite_inscripcion' => false,
            'enlace_externo' => null,
            'cupos' => null,
            'precio' => 0,
            'imagen' => null,
            'estado' => EstadoPublicacion::Borrador->value,
        ];

        Livewire::test(CreateEvento::class)->fillForm($formulario)->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateEvento::class)->fillForm([
            ...$formulario,
            'titulo' => 'Encuentro de aliado',
            'slug' => 'encuentro-de-aliado',
            'origen' => OrigenEvento::Aliado->value,
            'aliado_id' => $aliado->id,
        ])->call('create')->assertHasNoFormErrors();

        $this->assertSame(2, Evento::count());
        $this->assertSame(0, $direccion->notifications()->count());
    }

    public function test_varios_eventos_comparten_el_mismo_dia(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos());
        $this->post(route('eventos.comunidad.store'), $this->datos(['titulo' => 'Segunda función']));

        $this->assertSame(2, Evento::count());
        $this->get(route('eventos.calendario', [2026, '09']))
            ->assertSee('Encuentro comunitario de música')
            ->assertSee('Segunda función');
    }

    public function test_el_publico_no_controla_campos_administrativos(): void
    {
        $aliado = Aliado::factory()->create();

        $this->post(route('eventos.comunidad.store'), $this->datos([
            'estado' => EstadoPublicacion::Borrador->value,
            'origen' => OrigenEvento::Aliado->value,
            'aliado_id' => $aliado->id,
            'tipo' => TipoEvento::Capacitacion->value,
            'permite_inscripcion' => true,
            'cupos' => 100,
            'precio' => 80000,
            'slug' => 'slug-impuesto',
            'id' => 999,
        ]))->assertRedirect();

        $evento = Evento::firstOrFail();
        $this->assertSame(OrigenEvento::Comunidad, $evento->origen);
        $this->assertSame(EstadoPublicacion::Publicado, $evento->estado);
        $this->assertSame(TipoEvento::Evento, $evento->tipo);
        $this->assertNull($evento->aliado_id);
        $this->assertFalse($evento->permite_inscripcion);
        $this->assertNull($evento->cupos);
        $this->assertSame('0.00', $evento->precio);
        $this->assertNotSame('slug-impuesto', $evento->slug);
        $this->assertNotSame(999, $evento->id);
    }

    public function test_url_solo_acepta_http_o_https(): void
    {
        foreach (['javascript:alert(1)', 'ftp://example.com/evento', 'data:text/html,test'] as $url) {
            $this->post(route('eventos.comunidad.store'), $this->datos(['enlace_externo' => $url]))
                ->assertSessionHasErrors('enlace_externo');
        }

        $this->assertSame(0, Evento::count());
    }

    public function test_html_se_rechaza_y_el_texto_se_escapa_en_la_ficha(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos(['descripcion' => '<script>alert(1)</script>']))
            ->assertSessionHasErrors('descripcion');
        $this->assertSame(0, Evento::count());

        $this->post(route('eventos.comunidad.store'), $this->datos(['descripcion' => 'Música & cultura']))
            ->assertRedirect();
        $this->get(route('eventos.show', Evento::firstOrFail()))
            ->assertSee('Música &amp; cultura', false);
    }

    public function test_una_imagen_valida_se_guarda_con_nombre_del_servidor(): void
    {
        Storage::fake(config('almacenamiento.publico'));
        $this->post(route('eventos.comunidad.store'), $this->datos([
            'imagen' => UploadedFile::fake()->image('nombre-del-visitante.jpg'),
        ]))->assertRedirect();

        $imagen = Evento::firstOrFail()->imagen;
        $this->assertStringStartsWith('eventos/', $imagen);
        $this->assertStringEndsWith('.jpg', $imagen);
        $this->assertStringNotContainsString('nombre-del-visitante', $imagen);
        Storage::disk(config('almacenamiento.publico'))->assertExists($imagen);
    }

    public function test_archivos_no_permitidos_y_svg_no_se_publican(): void
    {
        foreach (['archivo.html' => 'text/html', 'afiche.svg' => 'image/svg+xml'] as $nombre => $mime) {
            $this->post(route('eventos.comunidad.store'), $this->datos([
                'imagen' => UploadedFile::fake()->create($nombre, 1, $mime),
            ]))->assertSessionHasErrors('imagen');
        }

        $this->assertSame(0, Evento::count());
    }

    public function test_imagen_de_mas_de_cinco_megas_se_rechaza(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos([
            'imagen' => UploadedFile::fake()->image('grande.png')->size(5121),
        ]))->assertSessionHasErrors('imagen');

        $this->assertSame(0, Evento::count());
    }

    public function test_el_honeypot_bloquea_bots(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos([
            Formulario::CAMPO_TRAMPA => 'rellenado',
        ]))->assertStatus(422);

        $this->assertSame(0, Evento::count());
    }

    public function test_el_limite_de_envios_es_independiente(): void
    {
        Cache::flush();

        for ($intento = 0; $intento < 3; $intento++) {
            $this->post(route('eventos.comunidad.store'), $this->datos(['titulo' => '']))
                ->assertSessionHasErrors('titulo');
        }

        $this->post(route('eventos.comunidad.store'), $this->datos())->assertStatus(429);
        $this->assertSame(0, Evento::count());
    }

    public function test_un_evento_que_cruza_medianoche_termina_al_dia_siguiente(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos(['hora_inicio' => '22:00', 'hora_fin' => '02:00']))
            ->assertRedirect();

        $evento = Evento::firstOrFail();
        $this->assertSame('2026-09-20 22:00', $evento->fecha_inicio->format('Y-m-d H:i'));
        $this->assertSame('2026-09-21 02:00', $evento->fecha_fin->format('Y-m-d H:i'));
    }

    public function test_el_panel_despublica_y_elimina_eventos_comunitarios(): void
    {
        $this->seed(RolYPermisoSeeder::class);
        $direccion = User::factory()->create();
        $direccion->syncRoles([User::ROL_SUPER_ADMIN]);
        $this->post(route('eventos.comunidad.store'), $this->datos());
        $evento = Evento::firstOrFail();

        $this->actingAs($direccion->fresh());
        Livewire::test(ListEventos::class)
            ->assertSee('Comunidad')
            ->assertSee('Sin precio informado')
            ->assertDontSee('Gratuito')
            ->callAction(TestAction::make('devolver')->table($evento))
            ->assertHasNoErrors();

        $this->assertSame(EstadoPublicacion::Borrador, $evento->fresh()->estado);
        $this->get(route('eventos.show', $evento))->assertNotFound();

        Livewire::test(EditEvento::class, ['record' => $evento->getRouteKey()])
            ->callAction('delete')
            ->assertHasNoErrors();
        $this->assertDatabaseMissing('eventos', ['id' => $evento->id]);
    }

    public function test_la_ficha_comunitaria_no_inventa_organizador_ni_precio(): void
    {
        $this->post(route('eventos.comunidad.store'), $this->datos())->assertRedirect();
        $evento = Evento::firstOrFail();

        $this->assertNull($evento->organizadorJsonLd());
        $this->assertNull($evento->organizadorVisible());
        $this->assertFalse($evento->admiteInscripciones());

        $this->get(route('eventos.show', $evento))
            ->assertSuccessful()
            ->assertDontSee('"organizer"', false)
            ->assertDontSee('"offers"', false)
            ->assertDontSee('Entrada libre')
            ->assertDontSee('Registrarme en la Nacional')
            ->assertDontSee('Inscribirme y pagar');
    }

    public function test_el_calendario_tiene_dias_seleccionables_incluso_si_esta_vacio(): void
    {
        $this->get(route('eventos.calendario', [2026, '09']))
            ->assertSuccessful()
            ->assertSee('data-fecha="2026-09-20"', false)
            ->assertSee('Agregar evento el 20 de septiembre de 2026')
            ->assertSee('Selecciona un día para agregar un evento')
            ->assertSee('<dialog', false)
            ->assertSee('name="_token"', false);
    }
}
