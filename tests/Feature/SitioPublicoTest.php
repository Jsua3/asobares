<?php

namespace Tests\Feature;

use App\Enums\EstadoPublicacion;
use App\Models\Artista;
use App\Models\Asociado;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Setting;
use App\Models\Vacante;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Todas las rutas públicas responden y no filtran datos internos.
 */
class SitioPublicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Los bloques JSON-LD van dentro de un <script>, así que un campo editable
     * desde el panel no puede cerrar la etiqueta. La secretaría, que por
     * diseño no puede publicar, si no ejecutaría código en el navegador de
     * quien sí publica.
     */
    public function test_el_json_ld_no_permite_cerrar_la_etiqueta_script(): void
    {
        $carga = '</script><script>alert(document.domain)</script>';

        $asociado = Asociado::factory()->publicado()->create([
            'nombre' => "La Cava{$carga}",
            'slug' => 'la-cava-con-carga',
        ]);

        $respuesta = $this->get(route('directorio.show', $asociado))->assertSuccessful();

        $respuesta->assertDontSee($carga, escape: false);
        $respuesta->assertDontSee('<script>alert(document.domain)', escape: false);
        // Y sigue estando ahí, escapado, dentro del JSON-LD.
        $respuesta->assertSee('<', escape: false);
    }

    public function test_el_json_ld_de_una_noticia_tampoco_se_puede_romper(): void
    {
        $noticia = Noticia::create([
            'titulo' => 'Cifras</script><script>alert(1)</script>',
            'slug' => 'cifras-con-carga',
            'extracto' => 'Un extracto cualquiera del observatorio.',
            'contenido' => '<p>Cuerpo de la noticia.</p>',
            'publicado_at' => now()->subDay(),
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $this->get(route('boletin.show', $noticia))
            ->assertSuccessful()
            ->assertDontSee('<script>alert(1)', escape: false);
    }

    /**
     * El cuerpo del boletín se imprime sin escapar porque viene de un editor
     * enriquecido, así que todo depende de que el saneo corra de verdad.
     * `symfony/html-sanitizer` está declarado en composer.json por esto, y no
     * se deja llegar de rebote como dependencia de Filament.
     */
    public function test_el_contenido_del_boletin_se_sanea_antes_de_mostrarse(): void
    {
        $noticia = Noticia::create([
            'titulo' => 'Panorama del sector',
            'slug' => 'panorama-del-sector',
            'extracto' => 'Cifras del observatorio.',
            'contenido' => '<p>Texto legítimo.</p><script>alert(1)</script>'
                .'<a href="javascript:alert(2)">enlace</a><img src=x onerror="alert(3)">',
            'publicado_at' => now()->subDay(),
            'estado' => EstadoPublicacion::Publicado,
        ]);

        $respuesta = $this->get(route('boletin.show', $noticia))->assertSuccessful();

        $respuesta->assertSee('Texto legítimo.', escape: false);
        $respuesta->assertDontSee('<script>alert(1)', escape: false);
        $respuesta->assertDontSee('javascript:alert(2)', escape: false);
        $respuesta->assertDontSee('onerror', escape: false);
    }

    /**
     * N6: las otras tres páginas de detalle (boletín, eventos, directorio)
     * empujan su JSON-LD con `@push('jsonld')` para que aterrice en el
     * `<head>`. El detalle de vacante debe seguir el mismo patrón.
     */
    public function test_el_json_ld_del_detalle_de_vacante_aterriza_en_el_head(): void
    {
        $vacante = Vacante::factory()->publicado()->create();

        $contenido = $this->get(route('empleo.show', $vacante))->assertSuccessful()->getContent();

        $posicionScript = strpos($contenido, 'application/ld+json');
        $posicionCierreHead = strpos($contenido, '</head>');

        $this->assertNotFalse($posicionScript, 'El bloque JSON-LD debe estar presente.');
        $this->assertNotFalse($posicionCierreHead);
        $this->assertLessThan($posicionCierreHead, $posicionScript);
    }

    /**
     * La portada le dice a Google quién es el gremio: la organización, con sus
     * datos de contacto de los ajustes, y el sitio que publica.
     */
    public function test_la_portada_declara_la_organizacion_y_el_sitio(): void
    {
        $grafo = $this->jsonLdDe(route('inicio'))['@graph'];
        $organizacion = collect($grafo)->firstWhere('@type', 'Organization');
        $sitio = collect($grafo)->firstWhere('@type', 'WebSite');

        $this->assertNotNull($organizacion, 'Falta la organización.');
        $this->assertNotNull($sitio, 'Falta el sitio.');

        $this->assertSame(ajuste('sitio_nombre'), $organizacion['name']);
        $this->assertSame(route('inicio'), $organizacion['url']);
        $this->assertSame(asset('img/favicon.png'), $organizacion['logo']);
        $this->assertSame(ajuste('contacto_correo'), $organizacion['email']);
        $this->assertSame(ajuste('contacto_whatsapp_visible'), $organizacion['telephone']);
        $this->assertSame(['https://instagram.com/'.ajuste('contacto_instagram')], $organizacion['sameAs']);
        $this->assertSame(ajuste('contacto_direccion'), $organizacion['address']['streetAddress']);

        $this->assertSame(ajuste('sitio_nombre'), $sitio['name']);
        $this->assertSame(['@id' => $organizacion['@id']], $sitio['publisher']);
    }

    /**
     * Lo que la oficina deja vacío en el panel desaparece del bloque en vez de
     * salir como texto en blanco o como un Instagram sin usuario.
     */
    public function test_los_datos_vacios_de_la_organizacion_se_caen(): void
    {
        Setting::query()->whereIn('clave', ['contacto_instagram', 'contacto_direccion', 'contacto_correo'])
            ->get()
            ->each->update(['valor' => '']);

        $organizacion = collect($this->jsonLdDe(route('inicio'))['@graph'])->firstWhere('@type', 'Organization');

        $this->assertArrayNotHasKey('sameAs', $organizacion);
        $this->assertArrayNotHasKey('address', $organizacion);
        $this->assertArrayNotHasKey('email', $organizacion);
        $this->assertSame(ajuste('sitio_nombre'), $organizacion['name']);
    }

    public function test_el_json_ld_de_la_portada_no_se_puede_romper_desde_los_ajustes(): void
    {
        Setting::query()->where('clave', 'contacto_direccion')->first()
            ->update(['valor' => 'Piso 3</script><script>alert(1)</script>']);

        $this->get(route('inicio'))
            ->assertSuccessful()
            ->assertDontSee('<script>alert(1)', escape: false);
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonLdDe(string $url): array
    {
        $contenido = $this->get($url)->assertSuccessful()->getContent();

        $this->assertSame(1, preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $contenido, $bloque), 'Falta el bloque JSON-LD.');
        $this->assertLessThan(strpos($contenido, '</head>'), strpos($contenido, 'application/ld+json'));

        return json_decode($bloque[1], true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return list<array{0: string}> */
    public static function rutasPublicas(): array
    {
        return array_map(fn (string $ruta): array => [$ruta], [
            '/',
            '/quienes-somos',
            '/directorio',
            '/directorio?municipio=salento',
            '/directorio?categoria=cafe&vista=mapa',
            '/abre-tu-negocio',
            '/abre-tu-negocio?municipio=salento',
            '/empleo',
            '/artistas',
            '/proveedores',
            '/eventos',
            '/eventos?cuando=pasados',
            // El proveedor es estático, así que la fecha va literal y no
            // `now()`: un mes sin eventos tiene que devolver 200 igual —la
            // rejilla se pinta vacía— y eso es justo lo que se afirma.
            '/eventos/calendario/2026/09',
            '/boletin',
            '/boletin?categoria=observatorio',
            '/afiliate',
            '/aliados',
            '/contacto',
            '/politica-de-datos',
            '/mi-cuenta/entrar',
            '/sitemap.xml',
            '/robots.txt',
        ]);
    }

    #[DataProvider('rutasPublicas')]
    public function test_las_rutas_publicas_responden(string $ruta): void
    {
        $this->get($ruta)->assertSuccessful();
    }

    public function test_las_fichas_de_detalle_responden(): void
    {
        $this->get(route('directorio.show', Asociado::publicado()->first()))->assertSuccessful();
        $this->get(route('eventos.show', Evento::publicado()->first()))->assertSuccessful();
        $this->get(route('boletin.show', Noticia::visible()->first()))->assertSuccessful();
        $this->get(route('artistas.show', Artista::publicado()->first()))->assertSuccessful();
    }

    public function test_la_ficha_publica_no_filtra_los_datos_internos_del_asociado(): void
    {
        $asociado = Asociado::publicado()->whereNotNull('notas_internas')->firstOrFail();

        $respuesta = $this->get(route('directorio.show', $asociado));

        $respuesta->assertDontSee($asociado->notas_internas, escape: false);
        $respuesta->assertDontSee($asociado->correo_interno);
        $respuesta->assertDontSee($asociado->representante);
    }

    public function test_un_asociado_sin_publicar_no_es_visible(): void
    {
        $borrador = Asociado::factory()->create();

        $this->get(route('directorio.show', $borrador))->assertNotFound();
        $this->get('/directorio')->assertDontSee($borrador->nombre);
    }

    /**
     * El calendario entra al sitemap con el mes EN CURSO y fechado. Con la ruta
     * sin fecha (`eventos.calendario.hoy`) el mapa listaría una redirección, y
     * enumerando meses listaría una lista infinita: los enlaces de mes anterior
     * y siguiente no tienen tope por diseño.
     */
    public function test_el_sitemap_lista_el_calendario_del_mes_en_curso(): void
    {
        // Reloj congelado en el último segundo del año: sin congelar, la prueba
        // y el controlador leen la fecha en momentos distintos, y una ejecución
        // que cruce la medianoche de fin de mes compara meses distintos.
        $this->travelTo(Carbon::create(2026, 12, 31, 23, 59, 59));

        $respuesta = $this->get('/sitemap.xml')->assertSuccessful();

        $respuesta->assertSee(route('eventos.calendario', [2026, '12']), escape: false);
        $respuesta->assertDontSee(route('eventos.calendario.hoy').'<', escape: false);
    }

    /**
     * La ficha de la vacante trae JSON-LD `JobPosting` completo, que es el
     * marcado con el que una oferta entra en Google Jobs. Sin la URL en el mapa
     * del sitio ese marcado casi no puede hacer su trabajo: Google tiene que
     * descubrir la dirección primero.
     */
    public function test_el_sitemap_lista_las_vacantes_publicadas(): void
    {
        $vacante = Vacante::factory()->create([
            'estado' => EstadoPublicacion::Publicado,
            'cerrada_at' => null,
            'fecha_limite' => now()->addMonth(),
        ]);

        $this->get('/sitemap.xml')
            ->assertSuccessful()
            ->assertSee(route('empleo.show', $vacante), escape: false);
    }

    /**
     * Anunciarle a Google una oferta que ya no se puede atender es peor que no
     * anunciarla: el visitante llega a una vacante muerta. Mismo criterio que
     * la guía normativa, que entra al mapa con `vigente()`.
     */
    public function test_el_sitemap_no_lista_una_vacante_cerrada_ni_una_vencida(): void
    {
        $cerrada = Vacante::factory()->create([
            'estado' => EstadoPublicacion::Publicado,
            'cerrada_at' => now()->subDay(),
        ]);

        $vencida = Vacante::factory()->create([
            'estado' => EstadoPublicacion::Publicado,
            'cerrada_at' => null,
            'fecha_limite' => now()->subDay(),
        ]);

        $borrador = Vacante::factory()->create([
            'estado' => EstadoPublicacion::Borrador,
            'cerrada_at' => null,
            'fecha_limite' => now()->addMonth(),
        ]);

        $respuesta = $this->get('/sitemap.xml')->assertSuccessful();

        $respuesta->assertDontSee(route('empleo.show', $cerrada), escape: false);
        $respuesta->assertDontSee(route('empleo.show', $vencida), escape: false);
        $respuesta->assertDontSee(route('empleo.show', $borrador), escape: false);
    }

    public function test_una_pagina_inexistente_devuelve_404_con_la_marca(): void
    {
        $this->get('/directorio/no-existe-este-bar')
            ->assertNotFound()
            ->assertSee('ASOBARES');
    }

    /**
     * `/abre-tu-negocio` inserta una fila en `consultas_guia` por cada visita
     * con `?municipio=`: sin límite, un bucle sobre esa cadena envenena la
     * cifra que el observatorio le enseña a una alcaldía. El límite es 30 por
     * minuto (ver el porqué en `routes/web.php`), así que la petición 31
     * dentro del mismo minuto debe rebotar con 429.
     */
    public function test_la_guia_normativa_tiene_limite_de_peticiones(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->get('/abre-tu-negocio')->assertSuccessful();
        }

        $this->get('/abre-tu-negocio')->assertStatus(429);
    }

    /**
     * `/abre-tu-negocio/formato/{requisito}` también inserta una fila en
     * `consultas_guia` (ver `GuiaController::descargarFormato`): sin límite, 40
     * peticiones seguidas producirían 40 filas. El límite es 10 por minuto (ver
     * el porqué en `routes/web.php`).
     *
     * `ThrottleRequests` va antes que `SubstituteBindings` en la prioridad por
     * defecto de Laravel, así que el límite corta antes de que la ruta
     * intente resolver `{requisito}`: no hace falta un requisito real con
     * adjunto para probarlo, un id inexistente basta y prueba además que el
     * límite protege incluso antes de llegar a los `abort_unless` del
     * controlador.
     */
    public function test_descargar_formato_de_la_guia_tiene_limite_de_peticiones(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->get('/abre-tu-negocio/formato/999999')->assertNotFound();
        }

        $this->get('/abre-tu-negocio/formato/999999')->assertStatus(429);
    }

    /**
     * Los botones del mapa hablan español.
     *
     * Leaflet pinta su control de zoom con `title` y `aria-label` en inglés
     * --Zoom in / Zoom out--, así que en un sitio en español el globito sale en
     * inglés y un lector de pantalla lo anuncia en inglés, aunque es el único
     * texto de interfaz que no escribimos. Comprobado leyendo los atributos del
     * control ya pintado.
     *
     * Se apaga el control de fábrica y se añade uno rotulado, en vez de
     * reescribir el DOM después: la opción es de la propia librería y por tanto
     * sobrevive a que Leaflet vuelva a dibujar el control, cosa que hace.
     *
     * ⚠️ El guion del mapa vive dentro de un ATRIBUTO de Alpine, así que ni un
     * comentario puede llevar comillas dobles: cierran el atributo y el mapa
     * desaparece con un `SyntaxError`.
     *
     * Roturas: devolver `zoomControl` a su valor de fábrica; quitar cualquiera
     * de los dos rótulos.
     */
    public function test_los_botones_del_mapa_estan_en_espanol(): void
    {
        $mapa = File::get(resource_path('views/components/publico/mapa.blade.php'));

        $this->assertStringContainsString('zoomControl: false', $mapa, 'sin apagar el de fabrica, Leaflet pinta el suyo en ingles');
        $this->assertStringContainsString("zoomInTitle: 'Acercar el mapa'", $mapa);
        $this->assertStringContainsString("zoomOutTitle: 'Alejar el mapa'", $mapa);

        // No se vigila aquí que el guion lleve comillas dobles, porque esa
        // aserción NO PUEDE FALLAR: aislar el atributo con `x-init="([^"]*)"`
        // corta justo en la primera comilla doble, así que el trozo capturado
        // nunca contiene ninguna. Una guardia que no puede ponerse roja es peor
        // que no tenerla, porque ocupa el sitio de la que sí serviría.
        //
        // La regla vive donde se puede leer: en la cabecera del propio
        // componente, con el aviso de que una comilla doble mata el mapa.
    }
}
