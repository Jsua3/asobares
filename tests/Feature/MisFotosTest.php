<?php

namespace Tests\Feature;

use App\Http\Controllers\Publico\MisFotosController;
use App\Models\Asociado;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

/**
 * El propietario sube fotos y el gremio las aprueba (OBS3-13).
 *
 * En la demostración del 28 de agosto se afirmó que el afiliado sube fotos y
 * el gremio modera (`R23 00:48`), y el directivo puso la condición: «lo tienen
 * que aprobar ellos, no sea que pongan imágenes… exóticas» (R23 00:45-01:05).
 * El §27.3 punto 5 destapó que nada de eso existía: el flujo de aprobación era
 * el del estado del registro, no el de una carga del propietario, porque el
 * propietario no cargaba nada. Había que construir la carga antes de poder
 * moderarla.
 */
class MisFotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        Storage::fake(config('almacenamiento.publico'));

        // El limitador de peticiones cuenta por IP y vive en la cache, que NO
        // se reinicia entre pruebas: sin esto, las subidas de un caso se
        // suman a las del siguiente y la clase entera empieza a devolver 429
        // segun el orden en que corran. Es estado global, no del caso.
        Cache::flush();
    }

    private function duenioDe(Asociado $asociado): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado->id]);
        $usuario->syncRoles([User::ROL_ASOCIADO]);

        return $usuario->fresh();
    }

    private function imagen(string $nombre = 'fachada.jpg'): UploadedFile
    {
        return UploadedFile::fake()->image($nombre, 1200, 800);
    }

    public function test_el_duenio_ve_su_pagina_de_fotos(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->get(route('mi-cuenta.fotos.index'))
            ->assertSuccessful()
            ->assertSee('Mis fotos', escape: false);
    }

    /**
     * La política, ejercida DIRECTAMENTE.
     *
     * Esta prueba existe porque la de abajo no la protegía: comprueba que
     * `destroy` devuelve 404, y ese 404 lo produce la comprobación de
     * propiedad del propio controlador, no la política. Se descubrió mutando
     * --abrir la política a «por permiso o por propiedad», que es literalmente
     * la fuga del v6, dejaba la clase entera en verde--. El docblock prometía
     * lo que no probaba, que es la forma de falso verde que este proyecto ya
     * pagó once veces.
     *
     * `view` concede por permiso *o* por propiedad. Aquí no: solo propiedad.
     */
    public function test_la_politica_niega_por_permiso_y_solo_concede_por_propiedad(): void
    {
        $suyo = Asociado::factory()->publicado()->create();
        $ajeno = Asociado::factory()->publicado()->create();

        $directivoYDuenio = $this->duenioDe($suyo);
        $directivoYDuenio->givePermissionTo('publicar_asociado');
        $directivoYDuenio = $directivoYDuenio->fresh();

        $this->assertTrue(
            Gate::forUser($directivoYDuenio)->allows('gestionarFotosEnPortal', $suyo),
            'El dueño entra a lo suyo.'
        );

        $this->assertFalse(
            Gate::forUser($directivoYDuenio)->allows('gestionarFotosEnPortal', $ajeno),
            'Tener permiso de panel NO puede abrir la galería de otro establecimiento.'
        );

        // Y quien solo tiene permiso de panel, sin ser dueño de nada, tampoco.
        $soloPanel = User::factory()->create(['asociado_id' => null]);
        $soloPanel->syncRoles([User::ROL_SUBADMIN]);
        $soloPanel->givePermissionTo('publicar_asociado');

        $this->assertFalse(
            Gate::forUser($soloPanel->fresh())->allows('gestionarFotosEnPortal', $ajeno)
        );
    }

    /**
     * El mismo riesgo por la puerta HTTP: subir y borrar archivos en la ficha
     * de otro. Complementa a la de arriba --esta prueba el controlador, esa la
     * política-- y ninguna de las dos sustituye a la otra.
     */
    public function test_un_directivo_que_ademas_es_duenio_no_entra_a_la_ficha_ajena(): void
    {
        $suyo = Asociado::factory()->publicado()->create();
        $ajeno = Asociado::factory()->publicado()->create();

        $foto = $this->subirComo($this->duenioDe($ajeno), $ajeno);

        $directivoYDuenio = $this->duenioDe($suyo);
        $directivoYDuenio->givePermissionTo('publicar_asociado');

        // Entra a la suya, que es lo correcto...
        $this->actingAs($directivoYDuenio->fresh())
            ->get(route('mi-cuenta.fotos.index'))
            ->assertSuccessful()
            ->assertDontSee($foto->name, escape: false);

        // ...y no puede tocar la ajena aunque tenga permiso de panel.
        $this->actingAs($directivoYDuenio->fresh())
            ->delete(route('mi-cuenta.fotos.destroy', $foto))
            ->assertNotFound();

        $this->assertModelExists($foto);
    }

    public function test_el_equipo_del_gremio_no_entra_al_portal(): void
    {
        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);

        $this->actingAs($usuario->fresh())
            ->get(route('mi-cuenta.fotos.index'))
            ->assertForbidden();
    }

    /**
     * El corazón de OBS3-13: lo que sube el propietario NO sale hasta que
     * alguien del gremio lo mire. Sin esto la moderación es un adorno.
     */
    public function test_una_foto_recien_subida_nace_sin_aprobar_y_no_sale_en_la_ficha(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $foto = $this->subirComo($this->duenioDe($asociado), $asociado);

        $this->assertFalse((bool) $foto->getCustomProperty(Asociado::FOTO_APROBADA));
        $this->assertCount(0, $asociado->fresh()->fotosAprobadas());
        $this->assertCount(1, $asociado->fresh()->fotosPendientes());

        $this->get(route('directorio.show', $asociado))
            ->assertSuccessful()
            ->assertDontSee($foto->getUrl('thumb'), escape: false);
    }

    /** Y en cuanto la aprueban, sale. La moderación es un umbral, no un veto. */
    public function test_al_aprobarla_aparece_en_la_ficha_publica(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $foto = $this->subirComo($this->duenioDe($asociado), $asociado);

        $foto->setCustomProperty(Asociado::FOTO_APROBADA, true);
        $foto->save();

        $this->assertCount(1, $asociado->fresh()->fotosAprobadas());

        // La ficha pinta la conversion `thumb`, no el original: si se
        // afirmara sobre `getUrl()` la prueba pasaria por casualidad el dia
        // que alguien pusiera el original en la galeria.
        $this->get(route('directorio.show', $asociado))
            ->assertSuccessful()
            ->assertSee($foto->fresh()->getUrl('thumb'), escape: false);
    }

    /**
     * La extensión la decide el servidor, no quien sube.
     *
     * Un JPEG legítimo llamado «payload.html» pasa la validación de tipo --su
     * MIME es image/jpeg-- y quedaría servido como HTML desde el disco
     * público. Es el hallazgo del v4, aquí por una puerta nueva: el portal del
     * asociado, que no pasa por `SubidaSegura` porque eso es de Filament.
     */
    public function test_la_extension_sale_del_mime_y_no_del_nombre(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        // El ataque de verdad, no una imitacion: contenido JPEG legitimo con
        // un nombre de cliente que miente. `fake()->image('payload.html')` no
        // sirve --deduce el MIME de la extension y lo tumbaria la validacion
        // antes de llegar al codigo que se quiere probar--.
        $real = UploadedFile::fake()->image('inocente.jpg', 1200, 800);
        $enDisco = $real->getRealPath();

        $foto = $this->subirComo(
            $this->duenioDe($asociado),
            $asociado,
            new UploadedFile($enDisco, 'payload.html', 'image/jpeg', null, true)
        );

        $this->assertStringEndsWith('.jpg', $foto->file_name);
        $this->assertStringNotContainsString('.html', $foto->file_name);
    }

    public function test_el_duenio_puede_retirar_su_propia_foto(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $foto = $this->subirComo($this->duenioDe($asociado), $asociado);

        $this->actingAs($this->duenioDe($asociado))
            ->delete(route('mi-cuenta.fotos.destroy', $foto))
            ->assertRedirect();

        $this->assertModelMissing($foto);
    }

    /**
     * El identificador de la ruta no puede ser una llave maestra: sin la
     * comprobación de colección, serviría para borrar los formatos oficiales
     * de la guía normativa, que viven en otra colección del mismo sistema.
     */
    public function test_el_identificador_no_sirve_para_borrar_medios_de_otra_coleccion(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $ajeno = Asociado::factory()->publicado()->create();
        $fotoAjena = $this->subirComo($this->duenioDe($ajeno), $ajeno);

        $this->actingAs($this->duenioDe($asociado))
            ->delete(route('mi-cuenta.fotos.destroy', $fotoAjena))
            ->assertNotFound();

        $this->assertModelExists($fotoAjena);
    }

    public function test_no_se_pueden_subir_mas_del_maximo(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $duenio = $this->duenioDe($asociado);

        foreach (range(1, MisFotosController::MAXIMO_POR_ESTABLECIMIENTO) as $numero) {
            $this->subirComo($duenio, $asociado, $this->imagen("foto-{$numero}.jpg"));
        }

        $this->actingAs($duenio)
            ->post(route('mi-cuenta.fotos.store'), ['foto' => $this->imagen('una-mas.jpg')])
            ->assertSessionHasErrors('foto');

        $this->assertCount(
            MisFotosController::MAXIMO_POR_ESTABLECIMIENTO,
            $asociado->fresh()->getMedia('galeria')
        );
    }

    public function test_rechaza_lo_que_no_es_imagen(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.fotos.store'), [
                'foto' => UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('foto');

        $this->assertCount(0, $asociado->fresh()->getMedia('galeria'));
    }

    /** Una foto minúscula no sirve para una ficha, y avisarlo es más barato que moderarla. */
    public function test_rechaza_una_imagen_demasiado_pequenia(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        $this->actingAs($this->duenioDe($asociado))
            ->post(route('mi-cuenta.fotos.store'), [
                'foto' => UploadedFile::fake()->image('diminuta.jpg', 100, 80),
            ])
            ->assertSessionHasErrors('foto');
    }

    /** El dueño ve el motivo por el que le devolvieron una foto, sin llamar a la oficina. */
    public function test_el_duenio_lee_el_motivo_de_la_devolucion(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $foto = $this->subirComo($this->duenioDe($asociado), $asociado);

        $foto->setCustomProperty(Asociado::FOTO_MOTIVO, 'Se ve borrosa y aparece una persona sin autorización.');
        $foto->save();

        $this->actingAs($this->duenioDe($asociado))
            ->get(route('mi-cuenta.fotos.index'))
            ->assertSuccessful()
            ->assertSee('Se ve borrosa', escape: false);
    }

    /**
     * Sube una foto y comprueba que de verdad entro.
     *
     * `assertRedirect()` a secas no basta: un fallo de validacion tambien
     * redirige, asi que el ayudante daria por buena una subida que no ocurrio
     * y el caso siguiente mediria otra cosa. Se comprueba el conteo.
     */
    /**
     * El defecto SILENCIOSO: una foto sin la propiedad escrita.
     *
     * No es hipotetico. Es el estado en que estaban las dieciocho fotos de la
     * demostracion antes de la migracion de relleno, y el que tendra cualquier
     * medio que entre por una via que se olvide de sellarla. «Sin aprobar»
     * tiene que ser el defecto incluido el defecto por omision, o el olvido
     * publica material sin moderar.
     *
     * Esta prueba existe porque las demas NO la cubrian: todas suben por el
     * controlador, que siempre escribe la propiedad, asi que el valor por
     * defecto no se ejercia nunca. Se descubrio mutando --poner `true` como
     * defecto dejaba la clase en verde--.
     */
    public function test_una_foto_sin_la_propiedad_escrita_no_sale_al_sitio(): void
    {
        $asociado = Asociado::factory()->publicado()->create();

        // La referencia se retiene: el temporal de `fake()` se borra en cuanto
        // el objeto se destruye, y `addMedia()` no lo encontraria.
        $archivo = UploadedFile::fake()->image('antigua.jpg', 1200, 800);

        $asociado->addMedia($archivo->getRealPath())
            ->preservingOriginal()
            ->usingFileName('antigua.jpg')
            ->toMediaCollection('galeria');

        $foto = $asociado->fresh()->getMedia('galeria')->last();

        $this->assertNull(
            $foto->getCustomProperty(Asociado::FOTO_APROBADA),
            'El caso no sirve si la propiedad quedo escrita: hay que probar la ausencia.'
        );
        $this->assertCount(0, $asociado->fresh()->fotosAprobadas());
        $this->assertCount(1, $asociado->fresh()->fotosPendientes());

        $this->get(route('directorio.show', $asociado))
            ->assertSuccessful()
            ->assertDontSee($foto->getUrl('thumb'), escape: false);
    }

    private function subirComo(User $usuario, Asociado $asociado, ?UploadedFile $archivo = null): Media
    {
        $antes = $asociado->fresh()->getMedia('galeria')->count();

        $this->actingAs($usuario)
            ->post(route('mi-cuenta.fotos.store'), ['foto' => $archivo ?? $this->imagen()])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertCount($antes + 1, $asociado->fresh()->getMedia('galeria'), 'La subida no entro.');

        return $asociado->fresh()->getMedia('galeria')->last();
    }
}
