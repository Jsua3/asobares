<?php

namespace Tests\Feature;

use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AjustesDelSitioFaseDosTest extends TestCase
{
    use RefreshDatabase;

    private const array PAGINAS = [
        ['directorio.index', 'directorio_titulo'],
        ['contacto', 'contacto_titulo_pagina'],
        ['afiliate', 'afiliate_beneficios_titulo'],
        ['guia.index', 'guia_cta_titulo'],
        ['empleo.index', 'empleo_perfil_titulo'],
        ['artistas.index', 'artistas_bloque_titulo'],
        ['proveedores.index', 'proveedores_beneficio_titulo'],
        ['eventos.index', 'eventos_titulo'],
        ['boletin.index', 'boletin_titulo'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_las_paginas_publicas_de_fase_dos_obedecen_a_sus_ajustes(): void
    {
        foreach (self::PAGINAS as $indice => [$ruta, $clave]) {
            $valor = "TEXTO EDITABLE FASE DOS {$indice}";
            $ajuste = Setting::query()->where('clave', $clave)->first();

            $this->assertNotNull($ajuste, "El ajuste «{$clave}» no está sembrado.");
            $ajuste->update(['valor' => $valor]);

            $this->get(route($ruta))->assertOk()->assertSee($valor, escape: false);
        }
    }

    public function test_las_descripciones_seo_principales_se_pueden_editar(): void
    {
        $ajustes = [
            'seo_directorio_descripcion' => ['directorio.index', 'SEO DIRECTORIO EDITADO'],
            'seo_contacto_descripcion' => ['contacto', 'SEO CONTACTO EDITADO'],
            'seo_afiliate_descripcion' => ['afiliate', 'SEO AFILIACION EDITADO'],
            'seo_guia_descripcion' => ['guia.index', 'SEO GUIA EDITADO'],
            'seo_empleo_descripcion' => ['empleo.index', 'SEO EMPLEO EDITADO'],
            'seo_artistas_descripcion' => ['artistas.index', 'SEO ARTISTAS EDITADO'],
            'seo_proveedores_descripcion' => ['proveedores.index', 'SEO PROVEEDORES EDITADO'],
            'seo_eventos_descripcion' => ['eventos.index', 'SEO EVENTOS EDITADO'],
            'seo_boletin_descripcion' => ['boletin.index', 'SEO BOLETIN EDITADO'],
        ];

        foreach ($ajustes as $clave => [$ruta, $valor]) {
            $ajuste = Setting::query()->where('clave', $clave)->first();

            $this->assertNotNull($ajuste, "El ajuste «{$clave}» no está sembrado.");
            $ajuste->update(['valor' => $valor]);

            $this->get(route($ruta))->assertOk()->assertSee($valor, escape: false);
        }
    }
}
