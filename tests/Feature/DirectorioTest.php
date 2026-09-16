<?php

namespace Tests\Feature;

use App\Models\Asociado;
use App\Models\Cartera;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_muestra_solo_establecimientos_publicados(): void
    {
        $publicado = Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);
        $borrador = Asociado::factory()->create(['nombre' => 'Bar Todavía En Borrador']);

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($publicado->nombre);
        $respuesta->assertDontSee($borrador->nombre);
    }

    public function test_los_filtros_se_combinan_y_la_busqueda_por_nombre_funciona(): void
    {
        $municipio = Municipio::factory()->create(['nombre' => 'Salento', 'slug' => 'salento-test']);
        $categoria = Categoria::factory()->create(['nombre' => 'Café', 'slug' => 'cafe-test']);

        $buscado = Asociado::factory()->publicado()->create([
            'nombre' => 'La Cava Distinta',
            'municipio_id' => $municipio->id,
            'categoria_id' => $categoria->id,
        ]);
        Asociado::factory()->publicado()->create([
            'nombre' => 'Bar De Otro Municipio',
            'categoria_id' => $categoria->id,
        ]);
        Asociado::factory()->publicado()->create([
            'nombre' => 'La Cava Distinta en otra categoría',
            'municipio_id' => $municipio->id,
        ]);

        $respuesta = $this->get(route('directorio.index', [
            'q' => 'Cava Distinta',
            'municipio' => $municipio->slug,
            'categoria' => $categoria->slug,
        ]));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($buscado->nombre);
        $respuesta->assertDontSee('Bar De Otro Municipio');
        $respuesta->assertDontSee('en otra categoría');
    }

    public function test_el_directorio_renderiza_panel_lateral_y_control_movil_de_filtros(): void
    {
        Asociado::factory()->publicado()->create();

        $this->get(route('directorio.index'))
            ->assertSuccessful()
            ->assertSee('Buscar por nombre')
            ->assertSee('id="directorio-filtros-panel"', escape: false)
            ->assertSee('id="directorio-filtros-drawer"', escape: false)
            ->assertSee('aria-controls="directorio-filtros-panel"', escape: false)
            ->assertSee('aria-controls="directorio-filtros-drawer"', escape: false);
    }

    public function test_los_filtros_preservan_la_vista_en_el_formulario(): void
    {
        $this->get(route('directorio.index', ['vista' => 'mapa']))
            ->assertSuccessful()
            ->assertSee('name="vista" value="mapa"', escape: false);
    }

    public function test_las_opciones_y_cifras_solo_salen_de_asociados_publicados(): void
    {
        Municipio::factory()->create(['nombre' => 'Municipio Sin Fichas', 'slug' => 'municipio-sin-fichas']);
        $municipioBorrador = Municipio::factory()->create(['nombre' => 'Municipio En Borrador', 'slug' => 'municipio-en-borrador']);
        $municipioPublicado = Municipio::factory()->create(['nombre' => 'Municipio Visible', 'slug' => 'municipio-visible']);

        Categoria::factory()->create(['nombre' => 'Categoría Sin Fichas', 'slug' => 'categoria-sin-fichas']);
        $categoriaBorrador = Categoria::factory()->create(['nombre' => 'Categoría En Borrador', 'slug' => 'categoria-en-borrador']);
        $categoriaPublicada = Categoria::factory()->create(['nombre' => 'Categoría Visible', 'slug' => 'categoria-visible']);

        Asociado::factory()->create([
            'nombre' => 'Establecimiento Oculto',
            'municipio_id' => $municipioBorrador->id,
            'categoria_id' => $categoriaBorrador->id,
        ]);

        $visible = Asociado::factory()->publicado()->create([
            'nombre' => 'Establecimiento Visible',
            'municipio_id' => $municipioPublicado->id,
            'categoria_id' => $categoriaPublicada->id,
        ]);

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($visible->nombre);
        $cobertura = $this->cifrasDeCobertura($respuesta->getContent());

        $this->assertSame(['1', '1'], $this->cifraDe($cobertura, 'establecimiento'), 'la cifra de establecimientos no sale solo de asociados publicados');
        $this->assertSame(['1', '1'], $this->cifraDe($cobertura, 'municipio'), 'la cifra de municipios no sale solo de asociados publicados');
        $this->assertSame(['1', '1'], $this->cifraDe($cobertura, 'categoría'), 'la cifra de categorías no sale solo de asociados publicados');
        $respuesta->assertSee('Municipio Visible');
        $respuesta->assertSee('Categoría Visible');
        $respuesta->assertDontSee('Establecimiento Oculto');
        $respuesta->assertDontSee('Municipio Sin Fichas');
        $respuesta->assertDontSee('Municipio En Borrador');
        $respuesta->assertDontSee('Categoría Sin Fichas');
        $respuesta->assertDontSee('Categoría En Borrador');

        $this->get(route('directorio.index', [
            'municipio' => $municipioPublicado->slug,
            'categoria' => $categoriaPublicada->slug,
        ]))
            ->assertSuccessful()
            ->assertSee($visible->nombre)
            ->assertSee('Municipio Visible')
            ->assertSee('Categoría Visible');
    }

    public function test_el_estado_vacio_no_muestra_opciones_sin_establecimientos_publicados(): void
    {
        Municipio::factory()->create(['nombre' => 'Municipio Administrativo', 'slug' => 'municipio-administrativo']);
        Categoria::factory()->create(['nombre' => 'Categoría Administrativa', 'slug' => 'categoria-administrativa']);

        $html = $this->get(route('directorio.index'))
            ->assertSuccessful()
            ->assertSee('Todavía no hay establecimientos publicados')
            ->assertSee('establecimientos')
            ->assertSee('municipios')
            ->assertSee('categorías')
            ->assertDontSee('Municipio Administrativo')
            ->assertDontSee('Categoría Administrativa')
            ->getContent();

        /*
         * Las TRES cifras, no una cualquiera. La guardia vigila que sin
         * establecimientos publicados el Directorio no anuncie cobertura que no
         * tiene, y con «existe alguna cifra en cero» le bastaba con que una de
         * las tres lo estuviera: pasaba en verde con los municipios en uno.
         */
        foreach ($this->cifrasDeCobertura($html) as $rotulo => [$anunciada, $pintada]) {
            $this->assertSame('0', $anunciada, "sin establecimientos publicados la cifra de «{$rotulo}» no es cero");
            $this->assertSame('0', $pintada, "sin establecimientos publicados el Directorio pinta «{$rotulo}» distinto de cero");
        }
    }

    /**
     * El dato de cobertura cuyo rótulo habla de `$deQue`.
     *
     * Se busca por la raíz y no por la palabra exacta porque el rótulo se
     * pluraliza con su propia cifra --«1 municipio», «0 municipios»--: buscando
     * la palabra exacta, una cifra equivocada haría fallar la guardia por no
     * encontrar la clave, y no por la cifra, que es lo que mira.
     *
     * @param  array<string, array{0: string, 1: string}>  $cobertura
     * @return array{0: string, 1: string}
     */
    private function cifraDe(array $cobertura, string $deQue): array
    {
        $coincidencias = array_filter(
            $cobertura,
            static fn (string $rotulo): bool => str_starts_with($rotulo, $deQue),
            ARRAY_FILTER_USE_KEY
        );

        $this->assertCount(
            1,
            $coincidencias,
            "el Directorio no pinta una sola cifra de «{$deQue}»; pinta ".json_encode(array_keys($cobertura), JSON_UNESCAPED_UNICODE)
        );

        return reset($coincidencias);
    }

    /**
     * Cada dato de cobertura del Directorio, indexado por su rótulo visible:
     * `[rótulo => [valor de data-cifra-final, dígito pintado]]`.
     *
     * Se lee del árbol y no del texto del marcado porque lo que la prueba
     * vigila es la CIFRA, no cómo se escribe la etiqueta que la envuelve: un
     * atributo nuevo en el rótulo, un envoltorio para animar el contador o un
     * cambio de `<strong>`/`<span>` no tocan lo que el Directorio afirma. El
     * rótulo se arma con el texto del dato que no cuelga de la cifra, así que
     * tampoco depende de que los dos sean hermanos inmediatos.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private function cifrasDeCobertura(string $html): array
    {
        $xpath = $this->xpathDe($html);
        $cobertura = [];

        foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " directorio-editorial-cifras__dato ")]') as $dato) {
            $cifra = $xpath->query('.//*[@data-cifra-final]', $dato)->item(0);

            $this->assertNotNull($cifra, 'un dato de cobertura no pinta su cifra');

            $rotulo = '';

            foreach ($xpath->query('.//text()[not(ancestor::*[@data-cifra-final])]', $dato) as $texto) {
                $rotulo .= $texto->nodeValue;
            }

            $rotulo = trim(preg_replace('/\s+/u', ' ', $rotulo));

            $this->assertNotSame('', $rotulo, 'una cifra de cobertura no dice qué cuenta');

            $cobertura[$rotulo] = [$cifra->getAttribute('data-cifra-final'), trim($cifra->textContent)];
        }

        $this->assertNotEmpty($cobertura, 'el Directorio no pinta el bloque de cobertura');

        return $cobertura;
    }

    /** El documento servido, listo para consultar por XPath. */
    private function xpathDe(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $erroresPrevios = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($erroresPrevios);

        return new \DOMXPath($dom);
    }

    public function test_el_directorio_usa_tarjetas_uniformes_y_conserva_distincion_de_destacado(): void
    {
        Asociado::factory()->destacado()->create(['nombre' => 'Bar Amnesia']);
        Asociado::factory()->publicado()->create(['nombre' => 'BBC Pub']);

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSee('Destacado');
        $respuesta->assertDontSee('tarjeta-escena');
        $respuesta->assertDontSee('sm:col-span-2');
        $respuesta->assertDontSee('lg:col-span-2');
    }

    public function test_el_directorio_ordena_ignorando_solo_el_prefijo_bar(): void
    {
        foreach ([
            'Restaurante Aaa',
            'BBC Pub',
            'Bar Zafiro',
            'BAR Baco',
            'bAr Amnesia',
            'bar Cava',
        ] as $nombre) {
            Asociado::factory()->publicado()->create(['nombre' => $nombre]);
        }

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSeeInOrder([
            'bAr Amnesia',
            'BAR Baco',
            'BBC Pub',
            'bar Cava',
            'Restaurante Aaa',
            'Bar Zafiro',
        ]);
        $respuesta->assertSee('Bar Zafiro');
    }

    public function test_los_destacados_conservan_prioridad_y_orden_comercial_interno(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bar Amnesia']);
        Asociado::factory()->destacado()->create(['nombre' => 'Bar Zulu']);
        Asociado::factory()->destacado()->create(['nombre' => 'BAR Baco']);

        $respuesta = $this->get(route('directorio.index'));

        $respuesta->assertSuccessful();
        $respuesta->assertSeeInOrder([
            'BAR Baco',
            'Bar Zulu',
            'Bar Amnesia',
        ]);
    }

    public function test_el_orden_comercial_ocurre_antes_de_paginar(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bar Zulu']);

        foreach (range(1, 12) as $indice) {
            Asociado::factory()->publicado()->create(['nombre' => sprintf('BBC Pub %02d', $indice)]);
        }

        $this->get(route('directorio.index'))
            ->assertSuccessful()
            ->assertSee('BBC Pub 01')
            ->assertSee('BBC Pub 12')
            ->assertDontSee('Bar Zulu');

        $this->get(route('directorio.index', ['page' => 2]))
            ->assertSuccessful()
            ->assertSee('Bar Zulu');
    }

    public function test_un_parametro_invalido_no_tumba_la_pagina(): void
    {
        $this->get(route('directorio.index', ['municipio' => 'no-existe']))
            ->assertSessionHasErrors('municipio');

        $this->get(route('directorio.index', ['categoria' => 'no-existe']))
            ->assertSessionHasErrors('categoria');

        $this->get(route('directorio.index', ['vista' => 'satelite']))
            ->assertSessionHasErrors('vista');
    }

    public function test_sin_establecimientos_el_directorio_no_habla_de_filtros(): void
    {
        $this->get(route('directorio.index'))
            ->assertSuccessful()
            ->assertSee('Todavía no hay establecimientos publicados')
            ->assertDontSee('con ese filtro');
    }

    public function test_un_filtro_sin_resultados_lo_dice_con_claridad(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);

        $this->get(route('directorio.index', ['q' => 'zzzz-no-existe']))
            ->assertSuccessful()
            ->assertSee('No encontramos establecimientos con ese filtro')
            ->assertDontSee('Bruma Gastrobar');
    }

    public function test_solo_vista_mapa_no_se_hace_pasar_por_un_filtro(): void
    {
        $this->get(route('directorio.index', ['vista' => 'mapa']))
            ->assertSuccessful()
            ->assertDontSee('Limpiar');
    }

    public function test_el_mapa_sin_resultados_no_dice_que_faltan_coordenadas(): void
    {
        Asociado::factory()->publicado()->create(['nombre' => 'Bruma Gastrobar']);

        $this->get(route('directorio.index', ['vista' => 'mapa', 'q' => 'zzzz-no-existe']))
            ->assertSuccessful()
            ->assertSee('No encontramos establecimientos con ese filtro')
            ->assertDontSee('tiene ubicación registrada');
    }

    public function test_el_mapa_avisa_cuando_hay_fichas_pero_ninguna_tiene_pin(): void
    {
        Asociado::factory()->publicado()->create([
            'nombre' => 'Bar Sin Coordenadas',
            'lat' => null,
            'lng' => null,
        ]);

        $this->get(route('directorio.index', ['vista' => 'mapa']))
            ->assertSuccessful()
            ->assertSee('1 establecimiento')
            ->assertSee('Ningún establecimiento de este filtro tiene ubicación registrada');
    }

    public function test_la_ficha_y_el_listado_no_exponen_datos_internos_ni_de_cartera(): void
    {
        $asociado = Asociado::factory()->publicado()->create([
            'nombre' => 'Bruma Gastrobar',
            'correo_interno' => 'oficina-secreta@gremio.test',
            'representante' => 'Natalia Representante Unica',
            'telefono_interno' => '3009998877',
            'notas_internas' => 'Mora administrativa interna XYZ',
        ]);
        Cartera::create([
            'asociado_id' => $asociado->id,
            'saldo_pendiente' => 150000,
            'meses_mora' => 3,
            'actualizado_at' => now(),
        ]);

        foreach ([
            route('directorio.index'),
            route('directorio.show', $asociado),
            route('directorio.index', ['vista' => 'mapa']),
        ] as $url) {
            $respuesta = $this->get($url)->assertSuccessful();
            $respuesta->assertDontSee('oficina-secreta@gremio.test');
            $respuesta->assertDontSee('Natalia Representante Unica');
            $respuesta->assertDontSee('3009998877');
            $respuesta->assertDontSee('Mora administrativa interna XYZ');
            $respuesta->assertDontSee('$150.000');
        }
    }

    public function test_la_ficha_no_lista_vacantes_cerradas_y_enlaza_las_vivas(): void
    {
        $asociado = Asociado::factory()->publicado()->create();
        $viva = Vacante::factory()->for($asociado)->publicado()->create(['cargo' => 'Bartender de viernes']);
        Vacante::factory()->for($asociado)->publicado()->cerrada()->create(['cargo' => 'Mesero ya contratado']);
        Vacante::factory()->for($asociado)->publicado()->vencida()->create(['cargo' => 'Portero de anoche']);
        Vacante::factory()->for($asociado)->pendiente()->create(['cargo' => 'Cocinero sin aprobar']);

        $respuesta = $this->get(route('directorio.show', $asociado));

        $respuesta->assertSuccessful();
        $respuesta->assertSee($viva->cargo);
        $respuesta->assertSee(route('empleo.show', $viva), escape: false);
        $respuesta->assertDontSee('Mesero ya contratado');
        $respuesta->assertDontSee('Portero de anoche');
        $respuesta->assertDontSee('Cocinero sin aprobar');
    }
}
