<?php

namespace Tests\Feature;

use App\Enums\TipoAliado;
use App\Models\Aliado;
use App\Models\Iniciativa;
use App\Models\RequisitoApertura;
use Database\Seeders\AliadoSeeder;
use Database\Seeders\BeneficioSeeder;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\IniciativaSeeder;
use Database\Seeders\MunicipioSeeder;
use Database\Seeders\RequisitoAperturaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Lo que se siembra en producción sale de un documento, o no se siembra.
 *
 * Estos siete sembradores son los únicos que van a la base real: catálogos,
 * contenido institucional y la guía normativa. Los demás --asociados,
 * eventos, PQR, transacciones-- son la demostración y `DatabaseSeeder` ya se
 * niega a correrlos en producción.
 *
 * ⚠️ **Esta prueba existe porque el esquema no marca la procedencia de una
 * fila.** No hay columna que distinga lo que sembró un seeder de lo que
 * escribió la oficina, y `updateOrCreate` sobrescribe por clave natural. En
 * cuanto el gremio empiece a editar, nadie va a poder decir de dónde salió
 * cada dato — así que la única defensa posible es impedir que entre inventado.
 *
 * Cada aserción se comprobó contra la versión ANTERIOR de estos sembradores,
 * que es donde el defecto existía de verdad:
 *
 * - `AliadoSeeder` traía cinco marcas inventadas con URLs a `ejemplo.test` y
 *   descuentos igualmente inventados («12 % sobre lista de precios», «cupo de
 *   crédito a 30 días»), y tres de ellas sin ningún convenio detrás.
 * - `RequisitoAperturaSeeder` traía costos inventados —180.000, 45.000,
 *   380.000, 290.000— y ninguna ficha decía de dónde salía.
 *
 * Las cuatro guardias se pusieron rojas con ese código y verdes con este.
 */
class SemillaInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(MunicipioSeeder::class);
        $this->seed(CategoriaSeeder::class);
        $this->seed(BeneficioSeeder::class);
        $this->seed(AliadoSeeder::class);
        $this->seed(IniciativaSeeder::class);
        $this->seed(RequisitoAperturaSeeder::class);
    }

    /**
     * El dominio reservado para ejemplos es la huella exacta de lo inventado:
     * `ejemplo.test`, `example.com` y `.test` en general no existen y nunca
     * van a existir (RFC 2606 y RFC 6761). Una URL así en la portada del
     * gremio es una marca de relleno con el logo de Asobares encima.
     */
    public function test_ningun_aliado_apunta_a_un_dominio_de_ejemplo(): void
    {
        $sospechosos = Aliado::query()
            ->get()
            ->filter(fn (Aliado $aliado): bool => $aliado->url !== null
                && preg_match('/\b(ejemplo|example)\b|\.(test|invalid|example)(\/|$)/i', $aliado->url) === 1)
            ->map(fn (Aliado $aliado): string => "{$aliado->nombre} → {$aliado->url}")
            ->values()
            ->all();

        $this->assertSame(
            [],
            $sospechosos,
            "Volvió un aliado de relleno. Si no puedes señalarlo en un documento del gremio, no se siembra:\n"
            .implode("\n", $sospechosos)
        );
    }

    /**
     * Un aliado comercial existe para ofrecerle algo al afiliado. Sin
     * `detalle_convenio` la ficha es un logo y una promesa vacía: el afiliado
     * inicia sesión —que es lo que la portada le pide— y no encuentra nada.
     */
    public function test_todo_aliado_comercial_trae_su_convenio(): void
    {
        $vacios = Aliado::query()
            ->where('tipo', TipoAliado::Comercial)
            ->get()
            ->filter(fn (Aliado $aliado): bool => blank($aliado->detalle_convenio))
            ->pluck('nombre')
            ->all();

        $this->assertSame(
            [],
            $vacios,
            'Estos aliados comerciales no tienen convenio que enseñar: '.implode(', ', $vacios)
        );

        $this->assertGreaterThanOrEqual(
            15,
            Aliado::query()->where('tipo', TipoAliado::Comercial)->count(),
            'El catálogo oficial trae diecinueve aliados estratégicos; se perdieron por el camino.'
        );
    }

    /**
     * §29.4: «Los costos de la guía normativa son de ejemplo. Publicar cifras
     * equivocadas de trámites legales en una URL pública es un riesgo del
     * gremio.» En la propia demostración se dijo «todavía no estamos
     * actualizados» (`R21 09:27`).
     *
     * La regla no es «nunca un costo»: es que un costo publicado tenga fecha
     * de verificación detrás. El día que alguien confirme la tarifa de
     * bomberos con bomberos, la pone y fecha la verificación, y esto sigue
     * verde.
     */
    public function test_ningun_costo_de_la_guia_se_publica_sin_verificar(): void
    {
        $sinRespaldo = RequisitoApertura::query()
            ->whereNotNull('costo_aproximado')
            ->whereNull('verificado_el')
            ->get()
            ->map(fn (RequisitoApertura $r): string => "{$r->entidad}: \${$r->costo_aproximado}")
            ->all();

        $this->assertSame(
            [],
            $sinRespaldo,
            "Hay costos de trámites legales publicados que nadie contrastó con la entidad:\n"
            .implode("\n", $sinRespaldo)
        );
    }

    /** Y cada trámite dice de qué documento salió, o no hay cómo auditarlo. */
    public function test_cada_tramite_de_la_guia_nombra_su_fuente(): void
    {
        $huerfanos = RequisitoApertura::query()
            ->get()
            ->filter(fn (RequisitoApertura $r): bool => blank($r->verificado_con))
            ->pluck('entidad')
            ->all();

        $this->assertSame(
            [],
            $huerfanos,
            'Estos trámites no dicen de dónde salieron: '.implode(', ', $huerfanos)
        );
    }

    /**
     * Las cinco iniciativas son las del TED gremial, con los estados que la
     * lámina les pone. Si alguien añade una sexta, que venga a justificarla.
     */
    public function test_las_iniciativas_son_las_cinco_del_ted(): void
    {
        $this->assertSame(
            [
                'Vibrarte',
                'Bares Verdes',
                'Blindando tu Negocio',
                'Noche Segura y Competitiva',
                'Diplomado en Gerencia de Bares',
            ],
            Iniciativa::query()->orderBy('orden')->pluck('nombre')->all()
        );
    }
}
