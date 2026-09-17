<?php

namespace Tests\Feature;

use Illuminate\Contracts\Filesystem\Filesystem;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Tests\TestCase;

/**
 * Lo que se sube al panel y nadie procesa —un modal que se cierra tras un
 * error de validación, o que se cancela— se queda en el temporal de Livewire:
 * la base del gremio con los datos de los afiliados, o el CSV de cartera con
 * sus saldos. Livewire solo purga lo de más de 24 horas, y solo cuando llega
 * otra subida; `subidas:depurar` lo borra pasada una hora.
 */
class DepuracionDeSubidasTest extends TestCase
{
    private Filesystem $disco;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disco = FileUploadConfiguration::storage();
        $this->disco->deleteDirectory(FileUploadConfiguration::directory());
    }

    /**
     * Una subida como las que deja Livewire: el archivo y, a su lado, el
     * `.json` con el nombre original.
     *
     * @return list<string>
     */
    private function subidaDeHace(int $minutos, string $nombre): array
    {
        $archivo = FileUploadConfiguration::directory()."/{$nombre}-metaYmFzZS54bHN4-.xlsx";
        $rutas = [$archivo, "{$archivo}.json"];

        foreach ($rutas as $ruta) {
            $this->disco->put($ruta, 'contenido');
            touch($this->disco->path($ruta), now()->subMinutes($minutos)->getTimestamp());
        }

        return $rutas;
    }

    public function test_borra_la_subida_de_hace_mas_de_una_hora_con_su_json(): void
    {
        $vieja = $this->subidaDeHace(61, 'vieja');

        $this->artisan('subidas:depurar')->assertSuccessful();

        foreach ($vieja as $ruta) {
            $this->assertFalse($this->disco->exists($ruta), "Se quedó {$ruta}.");
        }
    }

    /** Con una vieja al lado, para que la pasada sí borre algo. */
    public function test_conserva_la_subida_de_menos_de_una_hora(): void
    {
        $this->subidaDeHace(61, 'vieja');
        $reciente = $this->subidaDeHace(59, 'reciente');

        $this->artisan('subidas:depurar')->assertSuccessful();

        foreach ($reciente as $ruta) {
            $this->assertTrue($this->disco->exists($ruta), "Se borró {$ruta}.");
        }
    }

    public function test_el_simulacro_no_borra_nada(): void
    {
        $vieja = $this->subidaDeHace(61, 'vieja');

        $this->artisan('subidas:depurar', ['--pretend' => true])->assertSuccessful();

        foreach ($vieja as $ruta) {
            $this->assertTrue($this->disco->exists($ruta), "Se borró {$ruta}.");
        }
    }

    /**
     * El temporal vive en el disco privado de la aplicación, junto a archivos
     * que no son subidas de paso: la purga no sale de la carpeta de Livewire.
     */
    public function test_no_toca_nada_fuera_del_temporal_de_livewire(): void
    {
        $this->subidaDeHace(61, 'vieja');

        $ajeno = 'formatos/requisito.pdf';
        $this->disco->put($ajeno, 'contenido');
        touch($this->disco->path($ajeno), now()->subDays(3)->getTimestamp());

        $this->artisan('subidas:depurar')->assertSuccessful();

        $this->assertTrue($this->disco->exists($ajeno));
    }
}
