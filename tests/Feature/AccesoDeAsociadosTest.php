<?php

namespace Tests\Feature;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Proveedor;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los contactos de proveedores y el banco de talento dejaron de ser publicos:
 * son la contraprestacion de la cuota, no contenido de vitrina.
 *
 * Lo que se fija aqui es la frontera, que es lo unico que no se puede
 * comprobar mirando la pagina: que un anonimo no llegue, que un afiliado si,
 * que el equipo del gremio siga entrando por el panel y no por aqui, y --lo
 * mas facil de romper sin darse cuenta-- que la pagina publica que quedo en
 * pie no filtre ni un correo.
 */
class AccesoDeAsociadosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
    }

    private function crearUsuario(string $rol, ?Asociado $asociado = null): User
    {
        $usuario = User::factory()->create(['asociado_id' => $asociado?->id]);
        $usuario->syncRoles([$rol]);

        return $usuario->fresh();
    }

    private function afiliado(): User
    {
        return $this->crearUsuario(User::ROL_ASOCIADO, Asociado::factory()->publicado()->create());
    }

    // --- La frontera ---

    public function test_un_anonimo_no_alcanza_el_directorio_ni_el_banco(): void
    {
        $this->get(route('mi-cuenta.proveedores.index'))->assertRedirect(route('mi-cuenta.entrar'));
        $this->get(route('mi-cuenta.aspirantes.index'))->assertRedirect(route('mi-cuenta.entrar'));
        $this->get(route('mi-cuenta.artistas.index'))->assertRedirect(route('mi-cuenta.entrar'));
    }

    public function test_un_afiliado_ve_los_contactos_del_directorio(): void
    {
        Proveedor::factory()->publicado()->verificado()->create([
            'nombre' => 'Hielo Del Quindio',
            'slug' => 'hielo-del-quindio',
            'correo' => 'ventas@hielo.test',
        ]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.proveedores.index'))
            ->assertOk()
            ->assertSee('Hielo Del Quindio')
            ->assertSee('ventas@hielo.test');
    }

    public function test_un_afiliado_ve_el_banco_de_talento(): void
    {
        Aspirante::factory()->aprobado()->create([
            'nombre' => 'Camila Bartender',
            'correo' => 'camila@aspirante.test',
            'estado' => EstadoDeGestion::Nuevo,
        ]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.aspirantes.index'))
            ->assertOk()
            ->assertSee('Camila Bartender')
            ->assertSee('camila@aspirante.test');
    }

    /**
     * El equipo del gremio no entra por /mi-cuenta: tiene el panel. No es un
     * 403 seco sino la vista que explica que sesion hay abierta, porque llegar
     * aqui con la sesion del panel pasa en cada demostracion.
     */
    public function test_el_equipo_del_gremio_no_entra_por_la_cuenta_del_afiliado(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_SUBADMIN))
            ->get(route('mi-cuenta.proveedores.index'))
            ->assertForbidden();
    }

    public function test_un_usuario_con_rol_asociado_pero_sin_ficha_tampoco_entra(): void
    {
        $this->actingAs($this->crearUsuario(User::ROL_ASOCIADO))
            ->get(route('mi-cuenta.aspirantes.index'))
            ->assertForbidden();
    }

    // --- Lo que la cara publica ya no puede decir ---

    public function test_la_pagina_publica_de_proveedores_sigue_abierta_y_no_filtra_contactos(): void
    {
        Proveedor::factory()->publicado()->verificado()->create([
            'nombre' => 'Licores Del Eje',
            'slug' => 'licores-del-eje',
            'correo' => 'pedidos@licores.test',
            'whatsapp' => '3001234567',
        ]);

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertDontSee('Licores Del Eje')
            ->assertDontSee('pedidos@licores.test')
            ->assertDontSee('3001234567');
    }

    /**
     * La pagina publica sigue indexable a proposito: cerrar la URL entera
     * habria mandado a un login seco a quien llega desde un buscador. Lo que
     * si tiene que hacer es contar cuantos hay, que es el argumento de venta.
     */
    public function test_la_pagina_publica_cuenta_los_proveedores_sin_nombrarlos(): void
    {
        Proveedor::factory()->count(3)->publicado()->verificado()->create();

        $this->get(route('proveedores.index'))
            ->assertOk()
            ->assertSee('3');
    }

    public function test_la_bolsa_de_empleo_sigue_siendo_publica(): void
    {
        $this->get(route('empleo.index'))->assertOk();
    }

    // --- Lo que el banco no debe mostrar ---

    public function test_el_banco_no_muestra_a_quien_el_gremio_descarto(): void
    {
        Aspirante::factory()->aprobado()->create(['nombre' => 'Perfil Descartado', 'estado' => EstadoDeGestion::Descartado]);
        Aspirante::factory()->aprobado()->create(['nombre' => 'Perfil Vigente', 'estado' => EstadoDeGestion::Nuevo]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.aspirantes.index'))
            ->assertOk()
            ->assertSee('Perfil Vigente')
            ->assertDontSee('Perfil Descartado');
    }

    /**
     * Hasta el 8 de septiembre, quien dejaba su perfil en /empleo quedaba
     * visible para todos los establecimientos afiliados en el mismo segundo,
     * sin que nadie lo mirara. Son datos personales de un tercero.
     */
    public function test_el_banco_no_muestra_a_quien_la_secretaria_no_ha_aprobado(): void
    {
        Aspirante::factory()->create(['nombre' => 'Perfil Sin Revisar']);
        Aspirante::factory()->aprobado()->create(['nombre' => 'Perfil Ya Revisado']);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.aspirantes.index'))
            ->assertOk()
            ->assertSee('Perfil Ya Revisado')
            ->assertDontSee('Perfil Sin Revisar');
    }

    /**
     * Misma regla que una vacante publicada que su dueño edita: si el contenido
     * cambia despues de aprobado, vuelve a la cola. Si no, aprobar una vez seria
     * una llave para cambiar el perfil por cualquier otra cosa.
     */
    /**
     * ⚠️ Esta prueba cambió de contenido el 9 de septiembre de 2026, y conviene
     * saber por qué.
     *
     * Antes afirmaba que volver a enviar el formulario sobrescribía el perfil y
     * lo devolvía a revisión. La intención era buena --que aprobar una vez no
     * fuera una llave para cambiar el perfil por cualquier otra cosa sin que
     * nadie lo mirara-- pero el mecanismo abría un agujero: la clave de
     * `updateOrCreate` es un CORREO QUE TECLEA UN ANÓNIMO, sin verificación
     * ninguna. Con eso, quien conociera el correo de alguien del banco podía
     * reescribirle nombre, teléfono y cargo --desviando a los establecimientos
     * hacia otro número-- y, peor, sacarlo del banco con solo enviar el
     * formulario, porque cada envío ponía `aprobado_el` en nulo. A seis envíos
     * por minuto, vaciar el banco entero era cuestión de rato.
     *
     * La regla nueva cumple la misma intención y cierra el agujero: **un perfil
     * ya aprobado no se toca desde el formulario público**. Ni se sobrescribe ni
     * se desaprueba. Para cambiarlo hay que escribirle al gremio, y la
     * secretaría lo edita desde el panel.
     */
    public function test_un_perfil_ya_aprobado_no_se_puede_sobrescribir_desde_el_formulario(): void
    {
        $aspirante = Aspirante::factory()->aprobado()->create([
            'correo' => 'aprobada@aspirante.test',
            'nombre' => 'Nombre Real',
            'telefono' => '3001112233',
        ]);

        $this->post(route('empleo.aspirante'), [
            'nombre' => 'Nombre Suplantado',
            'correo' => 'aprobada@aspirante.test',
            'telefono' => '3009998877',
            'cargo_interes' => 'Chef',
            'categoria_cargo' => CargoDelSector::Cocina->value,
            'acepta_datos' => '1',
        ])->assertRedirect();

        $fresco = $aspirante->fresh();

        $this->assertSame('Nombre Real', $fresco->nombre, 'Un tercero no reescribe el perfil de nadie.');
        $this->assertSame('3001112233', $fresco->telefono, 'Ni le cambia el teléfono por el suyo.');
        $this->assertSame(1, Aspirante::count(), 'Tampoco se cuela un duplicado por la puerta de atrás.');
    }

    /**
     * El daño más barato del agujero anterior: no hacía falta ni suplantar a
     * nadie, bastaba con enviar el formulario con el correo de la víctima para
     * que `aprobado_el` volviera a nulo y su perfil desapareciera del banco.
     */
    public function test_un_tercero_no_puede_sacar_del_banco_a_un_perfil_aprobado(): void
    {
        $aspirante = Aspirante::factory()->aprobado()->create(['correo' => 'aprobada@aspirante.test']);

        $this->post(route('empleo.aspirante'), [
            'nombre' => 'Quien Sea',
            'correo' => 'aprobada@aspirante.test',
            'cargo_interes' => 'Chef',
            'categoria_cargo' => CargoDelSector::Cocina->value,
            'acepta_datos' => '1',
        ])->assertRedirect();

        $this->assertNotNull(
            $aspirante->fresh()->aprobado_el,
            'Nadie saca a otro del banco de talento enviando un formulario público.'
        );
    }

    /**
     * Corregir lo propio antes de que la secretaría lo mire sigue funcionando:
     * un perfil todavía sin aprobar no lo ve nadie más que la oficina, así que
     * dejarlo actualizable es la conveniencia de quien se equivocó tecleando y
     * no una llave sobre datos de nadie.
     */
    public function test_corregir_un_perfil_todavia_sin_aprobar_sigue_funcionando(): void
    {
        $aspirante = Aspirante::factory()->create([
            'correo' => 'pendiente@aspirante.test',
            'nombre' => 'Nombre Con Herrata',
            'aprobado_el' => null,
        ]);

        $this->post(route('empleo.aspirante'), [
            'nombre' => 'Nombre Corregido',
            'correo' => 'pendiente@aspirante.test',
            'cargo_interes' => 'Chef',
            'categoria_cargo' => CargoDelSector::Cocina->value,
            'acepta_datos' => '1',
        ])->assertRedirect();

        $this->assertSame('Nombre Corregido', $aspirante->fresh()->nombre);
        $this->assertSame(1, Aspirante::count(), 'Sigue siendo una persona, un registro.');
    }

    /**
     * El aviso tiene que ser el MISMO se conozca o no el correo. Si dijera
     * «ya tenías un perfil» a unos y «recibimos el tuyo» a otros, el formulario
     * se convertiría en un buscador: cualquiera podría averiguar, correo a
     * correo, quién está inscrito en el banco de talento. Eso es justo el dato
     * personal que este módulo existe para proteger.
     */
    public function test_el_aviso_no_delata_si_el_correo_ya_estaba_en_el_banco(): void
    {
        Aspirante::factory()->aprobado()->create(['correo' => 'conocida@aspirante.test']);

        $envio = fn (string $correo) => $this->post(route('empleo.aspirante'), [
            'nombre' => 'Alguien',
            'correo' => $correo,
            'cargo_interes' => 'Chef',
            'categoria_cargo' => CargoDelSector::Cocina->value,
            'acepta_datos' => '1',
        ])->getSession()->get('exito');

        $this->assertSame(
            $envio('desconocida@aspirante.test'),
            $envio('conocida@aspirante.test'),
            'El formulario no puede servir para averiguar quién está en el banco.'
        );
    }

    // --- Artistas: el contacto tambien es contraprestacion de la cuota ---

    /**
     * La ficha del artista NO se vacia como se vacio la de proveedores, y la
     * diferencia es deliberada: el escaparate --nombre, foto, genero, video--
     * es lo que el artista viene a buscar al inscribirse, y sacarlo del indice
     * le quitaria el motivo. Lo que se va detras de la sesion es el contacto,
     * que es lo que el afiliado paga.
     */
    public function test_la_ficha_publica_del_artista_conserva_el_escaparate_pero_no_el_contacto(): void
    {
        $artista = Artista::factory()->publicado()->create([
            'nombre' => 'Grupo Del Escaparate',
            'slug' => 'grupo-del-escaparate',
            'whatsapp' => '3009876543',
            'instagram_url' => 'https://instagram.com/grupo-del-escaparate',
        ]);

        $this->get(route('artistas.show', $artista))
            ->assertOk()
            ->assertSee('Grupo Del Escaparate')
            ->assertDontSee('3009876543')
            ->assertDontSee('instagram.com/grupo-del-escaparate');
    }

    public function test_el_listado_publico_de_artistas_tampoco_entrega_contactos(): void
    {
        Artista::factory()->publicado()->create([
            'nombre' => 'Duo Del Listado',
            'slug' => 'duo-del-listado',
            'whatsapp' => '3007654321',
            'instagram_url' => 'https://instagram.com/duo-del-listado',
        ]);

        $this->get(route('artistas.index'))
            ->assertOk()
            ->assertSee('Duo Del Listado')
            ->assertDontSee('3007654321')
            ->assertDontSee('instagram.com/duo-del-listado');
    }

    public function test_un_afiliado_ve_los_contactos_de_los_artistas(): void
    {
        Artista::factory()->publicado()->create([
            'nombre' => 'Orquesta Del Afiliado',
            'slug' => 'orquesta-del-afiliado',
            'whatsapp' => '3005554433',
            'instagram_url' => 'https://instagram.com/orquesta-del-afiliado',
        ]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.artistas.index'))
            ->assertOk()
            ->assertSee('Orquesta Del Afiliado')
            ->assertSee('3005554433')
            ->assertSee('instagram.com/orquesta-del-afiliado');
    }

    /**
     * El directorio del afiliado no es una puerta trasera a la moderacion: lo
     * que la secretaria no ha aprobado no se ve aqui tampoco.
     */
    public function test_un_artista_sin_aprobar_no_sale_en_el_directorio_del_afiliado(): void
    {
        Artista::factory()->pendiente()->create(['nombre' => 'Ficha Pendiente', 'slug' => 'ficha-pendiente']);
        Artista::factory()->publicado()->create(['nombre' => 'Ficha Publicada', 'slug' => 'ficha-publicada']);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.artistas.index'))
            ->assertOk()
            ->assertSee('Ficha Publicada')
            ->assertDontSee('Ficha Pendiente');
    }

    public function test_el_banco_filtra_por_cargo(): void
    {
        $cargos = CargoDelSector::cases();

        Aspirante::factory()->aprobado()->create(['nombre' => 'Del Cargo Buscado', 'categoria_cargo' => $cargos[0]]);
        Aspirante::factory()->aprobado()->create(['nombre' => 'De Otro Cargo', 'categoria_cargo' => $cargos[1]]);

        $this->actingAs($this->afiliado())
            ->get(route('mi-cuenta.aspirantes.index', ['categoria' => $cargos[0]->value]))
            ->assertOk()
            ->assertSee('Del Cargo Buscado')
            ->assertDontSee('De Otro Cargo');
    }
}
