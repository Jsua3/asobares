<?php

namespace Tests\Feature;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use App\Filament\Resources\Mensajes\MensajeResource;
use App\Filament\Resources\Mensajes\Pages\ListMensajes;
use App\Mail\MensajeRecibido;
use App\Mail\NuevaSolicitudAfiliacion;
use App\Models\Categoria;
use App\Models\Mensaje;
use App\Models\Municipio;
use App\Models\Setting;
use App\Models\SolicitudAfiliacion;
use App\Models\User;
use Database\Seeders\RolYPermisoSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Que al gremio le avisen cuando entra un mensaje (Acta 08, A-04).
 *
 * Hasta el 9 de septiembre de 2026 el formulario de contacto guardaba el
 * mensaje y, si era PQR, le mandaba el acuse **al ciudadano**. Al gremio no le
 * avisaba nadie: la única señal era una tarjeta del tablero, o sea que alguien
 * tenía que entrar al panel y mirar. Una PQR tiene plazo legal de respuesta de
 * quince días hábiles (Ley 1755 de 2015) y el reloj corría sin que nadie lo
 * viera.
 *
 * Y el ajuste `contacto_correo_destino` --que el panel ofrece editar con la
 * etiqueta «Correo que recibe los formularios»-- no lo leía **ni una sola línea
 * del proyecto**: la oficina podía cambiarlo, guardarlo y ver el aviso verde sin
 * que cambiara nada.
 */
class AvisoDeMensajeAlGremioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolYPermisoSeeder::class);
        $this->seed(SettingSeeder::class);
        Mail::fake();
    }

    /** @param array<string, mixed> $extra */
    private function radicarPqr(array $extra = []): void
    {
        $this->post(route('contacto.store'), [
            'nombre' => 'Ciudadana Preocupada',
            'correo' => 'ciudadana@ejemplo.test',
            'asunto' => 'Ruido en la calle 14',
            'mensaje' => 'El bar de la esquina cierra a las cuatro de la mañana.',
            'tipo' => TipoMensaje::Pqr->value,
            'acepta_datos' => '1',
            ...$extra,
        ])->assertRedirect();
    }

    // --- El aviso sale ---

    public function test_radicar_una_pqr_avisa_al_gremio(): void
    {
        $this->radicarPqr();

        Mail::assertSent(MensajeRecibido::class, 1);
    }

    /**
     * El destinatario sale del ajuste, que es justo lo que ese ajuste prometía
     * y no cumplía. Si esto se rompiera volveríamos a tener un campo en el panel
     * que no cambia nada.
     */
    public function test_el_aviso_va_al_correo_que_dice_el_ajuste(): void
    {
        Setting::query()->where('clave', 'contacto_correo_destino')->update(['valor' => 'bandeja@gremio.test']);
        Setting::olvidarCache();

        $this->radicarPqr();

        Mail::assertSent(
            MensajeRecibido::class,
            fn (MensajeRecibido $correo): bool => $correo->hasTo('bandeja@gremio.test')
        );
    }

    /** Un mensaje de contacto corriente también se avisa: la bandeja es la misma. */
    public function test_un_mensaje_de_contacto_corriente_tambien_avisa(): void
    {
        $this->post(route('contacto.store'), [
            'nombre' => 'Alguien',
            'correo' => 'alguien@ejemplo.test',
            'asunto' => 'Una consulta',
            'mensaje' => 'Quisiera saber cómo afiliarme.',
            'tipo' => TipoMensaje::Contacto->value,
            'acepta_datos' => '1',
        ])->assertRedirect();

        Mail::assertSent(MensajeRecibido::class, 1);
    }

    /** Y la solicitud de afiliación, que entra por otro controlador y otro flujo. */
    public function test_la_solicitud_de_afiliacion_tambien_avisa(): void
    {
        $municipio = Municipio::factory()->create();
        $categoria = Categoria::factory()->create();

        $this->post(route('afiliate.store'), [
            'solicitante_nombre' => 'Sandra Ríos',
            'solicitante_identificacion' => '1094.123.456',
            'solicitante_telefono' => '3145551234',
            'solicitante_correo' => 'sandra@ejemplo.test',
            'solicitante_cargo' => 'Propietaria',
            'establecimiento_nombre' => 'Bruma Gastrobar',
            'razon_social' => 'Bruma Gastrobar S.A.S.',
            'nit' => '901234567-8',
            'municipio_id' => $municipio->id,
            'direccion' => 'Calle 10 # 12-34',
            'establecimiento_telefono' => '3145555678',
            'establecimiento_correo' => 'hola@bruma.test',
            'categoria_id' => $categoria->id,
            'descripcion' => 'Gastrobar con operación nocturna y música en vivo.',
            'acepta_datos' => '1',
        ])->assertRedirect();

        $this->assertSame(1, SolicitudAfiliacion::count());
        $this->assertSame(0, Mensaje::where('tipo', TipoMensaje::Afiliacion)->count());
        Mail::assertSent(NuevaSolicitudAfiliacion::class, 1);
    }

    // --- El aviso no manda sobre la petición ---

    /**
     * §9, D-23: el correo saliente no tumba la petición que lo dispara. La PQR ya
     * quedó radicada y el ciudadano necesita su número aunque el transporte esté
     * caído --que es como ha estado producción desde el primer despliegue--.
     */
    public function test_si_el_aviso_falla_la_pqr_queda_radicada_igual(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP caído'));

        $this->radicarPqr();

        $this->assertDatabaseCount('mensajes', 1);
        $this->assertNotNull(Mensaje::first()->radicado);
    }

    /**
     * Vaciar el ajuste apaga el aviso, igual que vaciar el número apaga el botón
     * de WhatsApp. No es un error: es cómo se desactiva.
     */
    public function test_sin_correo_de_destino_no_se_intenta_el_aviso(): void
    {
        Setting::query()->where('clave', 'contacto_correo_destino')->update(['valor' => '']);
        Setting::olvidarCache();

        $this->radicarPqr();

        Mail::assertNotSent(MensajeRecibido::class);
        $this->assertDatabaseCount('mensajes', 1);
    }

    // --- El reloj del plazo legal ---

    /**
     * Quince días hábiles desde que entra (Ley 1755 de 2015).
     *
     * Se cuentan días de semana y **no se descuentan los festivos**: hacerlo bien
     * exige el calendario colombiano, que no está en el proyecto. El error va del
     * lado seguro --sin festivos la fecha sale ANTES que la legal, nunca después--
     * y así el panel avisa con margen en vez de tarde.
     */
    public function test_una_pqr_vence_a_los_quince_dias_habiles(): void
    {
        $pqr = Mensaje::factory()->create([
            'tipo' => TipoMensaje::Pqr,
            'radicado' => 'PQR-2026-0001',
            'estado' => EstadoMensaje::Nuevo,
            'created_at' => '2026-09-01',  // martes
        ]);

        $this->assertSame('2026-09-22', $pqr->venceEl()?->toDateString());
    }

    public function test_un_mensaje_que_no_es_pqr_no_tiene_plazo_legal(): void
    {
        $mensaje = Mensaje::factory()->create(['tipo' => TipoMensaje::Contacto, 'radicado' => null]);

        $this->assertNull($mensaje->venceEl(), 'Solo la PQR tiene plazo de ley.');
    }

    public function test_una_pqr_respondida_ya_no_tiene_plazo_corriendo(): void
    {
        $pqr = Mensaje::factory()->create([
            'tipo' => TipoMensaje::Pqr,
            'radicado' => 'PQR-2026-0002',
            'estado' => EstadoMensaje::Respondido,
            'respondido_at' => now(),
            'created_at' => now()->subMonths(2),
        ]);

        $this->assertNull($pqr->venceEl(), 'Respondida no corre plazo.');
        $this->assertFalse($pqr->plazoVencido());
    }

    public function test_una_pqr_pasada_de_plazo_se_reconoce_como_vencida(): void
    {
        $pqr = Mensaje::factory()->create([
            'tipo' => TipoMensaje::Pqr,
            'radicado' => 'PQR-2026-0003',
            'estado' => EstadoMensaje::Nuevo,
            'created_at' => now()->subMonths(3),
        ]);

        $this->assertTrue($pqr->plazoVencido());
    }

    public function test_una_pqr_recien_radicada_no_esta_vencida(): void
    {
        $pqr = Mensaje::factory()->create([
            'tipo' => TipoMensaje::Pqr,
            'radicado' => 'PQR-2026-0004',
            'estado' => EstadoMensaje::Nuevo,
            'created_at' => now(),
        ]);

        $this->assertFalse($pqr->plazoVencido());
    }

    // --- La bandeja lo enseña ---

    /**
     * Un contador en el menú, que es donde mira quien entra al panel a otra cosa.
     * Sin él, enterarse de una PQR nueva exige acordarse de abrir la bandeja.
     */
    public function test_la_bandeja_lleva_contador_de_mensajes_sin_responder(): void
    {
        Mensaje::factory()->count(3)->create(['estado' => EstadoMensaje::Nuevo]);
        Mensaje::factory()->create(['estado' => EstadoMensaje::Respondido]);

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);
        $this->actingAs($usuario->fresh());

        $this->assertSame('3', MensajeResource::getNavigationBadge());
    }

    /**
     * El plazo tiene que verse donde se trabaja, no solo en el menú: la
     * secretaría abre la bandeja y necesita saber cuál urge sin abrir una por
     * una. Y con algo más que color, que es lo que exige RNF-12.
     */
    public function test_la_bandeja_enseña_el_plazo_de_cada_pqr(): void
    {
        $urgente = Mensaje::factory()->pqr()->create(['created_at' => now()->subMonths(3)]);
        $holgada = Mensaje::factory()->pqr()->create(['created_at' => now()]);
        $corriente = Mensaje::factory()->create(['nombre' => 'Un Contacto Corriente']);

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);
        $this->actingAs($usuario->fresh());

        Livewire::test(ListMensajes::class)
            ->assertCanSeeTableRecords([$urgente, $holgada, $corriente])
            ->assertSee('Vencida')
            ->assertDontSee('Vencida el');
    }

    /** El filtro que la secretaría usa cuando llega el lunes. */
    public function test_la_bandeja_puede_filtrar_las_pqr_pasadas_de_plazo(): void
    {
        $vencida = Mensaje::factory()->pqr()->create(['created_at' => now()->subMonths(3)]);
        $aTiempo = Mensaje::factory()->pqr()->create(['created_at' => now()]);

        $usuario = User::factory()->create();
        $usuario->syncRoles([User::ROL_SUBADMIN]);
        $this->actingAs($usuario->fresh());

        Livewire::test(ListMensajes::class)
            ->filterTable('plazo_vencido')
            ->assertCanSeeTableRecords([$vencida])
            ->assertCanNotSeeTableRecords([$aTiempo]);
    }

    /** Una PQR pasada de plazo pone el contador en rojo, no en gris. */
    public function test_una_pqr_vencida_pone_el_contador_en_rojo(): void
    {
        Mensaje::factory()->create([
            'tipo' => TipoMensaje::Pqr,
            'radicado' => 'PQR-2026-0005',
            'estado' => EstadoMensaje::Nuevo,
            'created_at' => now()->subMonths(3),
        ]);

        $this->assertSame('danger', MensajeResource::getNavigationBadgeColor());
    }
}
