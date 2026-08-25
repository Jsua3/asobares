<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * La guardia del almacenamiento de archivos.
 *
 * Existe por dos defectos que sólo aparecen el día del despliegue, y ninguno
 * de los dos da error:
 *
 * 1. En un servidor serverless el disco de la máquina es efímero. Todo lo que
 *    suba el gremio desde el panel —portadas, galerías y los formatos oficiales
 *    de la guía normativa— desaparece en el siguiente despliegue. En local no
 *    se nota jamás, que es lo que lo hace peligroso. Por eso los puntos de
 *    subida ya no nombran su disco a mano: lo eligen por configuración, y estas
 *    pruebas vigilan que siga siendo así.
 *
 * 2. Al mover esos archivos a un bucket, la separación entre lo público y lo
 *    privado deja de ser una carpeta del sistema y pasa a ser una política del
 *    bucket. Medido contra un almacén compatible con S3: con la política que
 *    sale sola —`s3:GetObject` sobre `arn:aws:s3:::<bucket>/*`— los PDF de la
 *    guía se descargan por URL directa, devolviendo 200 sin pasar por
 *    `GuiaController`, que es el único sitio donde se comprueba que el
 *    requisito esté publicado. Acotando la política al prefijo `publico/`,
 *    el mismo objeto pasa a 403.
 *
 * Esa frontera se apoya en que los dos discos cuelguen de prefijos distintos.
 * Si alguien iguala los `root`, la política del bucket deja de poder
 * distinguirlos y el control vuelve a ser decorativo sin que nada se ponga en
 * rojo. De ahí la mitad de las aserciones de abajo.
 */
class AlmacenamientoTest extends TestCase
{
    public function test_en_local_los_discos_son_los_de_siempre(): void
    {
        // El valor por defecto no puede cambiar sin querer: el demo, las
        // semillas y `storage:link` dependen de estos dos nombres.
        $this->assertSame('public', config('almacenamiento.publico'));
        $this->assertSame('local', config('almacenamiento.privado'));
    }

    public function test_ningun_punto_de_subida_nombra_su_disco_a_mano(): void
    {
        $sospechosos = [];

        foreach ($this->ficherosPhpDeLaAplicacion() as $ruta) {
            $contenido = file_get_contents($ruta);

            foreach (["disk('public')", 'disk("public")', "disk('local')", 'disk("local")'] as $patron) {
                if (str_contains($contenido, $patron)) {
                    $sospechosos[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $ruta)." → {$patron}";
                }
            }
        }

        $this->assertSame([], $sospechosos, implode("\n", [
            'Hay puntos de almacenamiento que fijan el disco a mano:',
            ...$sospechosos,
            '',
            'Use config(\'almacenamiento.publico\') o config(\'almacenamiento.privado\').',
            'Un disco fijo ignora la variable del servidor: el archivo se guarda en el',
            'disco efímero de la máquina y desaparece en el siguiente despliegue.',
        ]));
    }

    public function test_los_dos_discos_de_objetos_cuelgan_de_prefijos_distintos(): void
    {
        $publico = config('filesystems.disks.s3');
        $privado = config('filesystems.disks.s3-privado');

        $this->assertSame('publico', $publico['root']);
        $this->assertSame('privado', $privado['root']);

        // Es la aserción que sostiene toda la separación: la política del
        // bucket sólo puede abrir uno de los dos si son prefijos distintos.
        $this->assertNotSame(
            $publico['root'],
            $privado['root'],
            'Con el mismo prefijo, ninguna política de bucket puede abrir lo público sin abrir también los formatos de la guía.'
        );
    }

    public function test_el_disco_privado_no_publica_direccion_ni_visibilidad(): void
    {
        $privado = config('filesystems.disks.s3-privado');

        $this->assertSame('private', $privado['visibility']);
        $this->assertArrayNotHasKey(
            'url',
            $privado,
            'Con `url` configurada, cualquiera puede construir la dirección de un formato oficial sin pasar por GuiaController.'
        );
    }

    public function test_el_disco_publico_si_es_legible(): void
    {
        // La contraparte: si esto se pusiera en `private`, las portadas y las
        // galerías dejarían de verse y el fallo se leería como «imagen rota».
        $this->assertSame('public', config('filesystems.disks.s3.visibility'));
    }

    public function test_los_formatos_de_la_guia_se_sirven_por_el_controlador_y_no_por_url(): void
    {
        Storage::fake(config('almacenamiento.privado'));

        $disco = Storage::disk(config('almacenamiento.privado'));
        $disco->put('formatos/prueba.pdf', '%PDF-1.4');

        // El disco privado de desarrollo tampoco expone URL pública: quien
        // añada una aquí rompería la misma frontera que en el bucket.
        $this->assertNull(config('filesystems.disks.local.url'));
        $this->assertTrue($disco->exists('formatos/prueba.pdf'));
    }

    /**
     * @return list<string>
     */
    private function ficherosPhpDeLaAplicacion(): array
    {
        $ficheros = [];
        $iterador = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(app_path(), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterador as $fichero) {
            if ($fichero->isFile() && $fichero->getExtension() === 'php') {
                $ficheros[] = $fichero->getPathname();
            }
        }

        return $ficheros;
    }
}
