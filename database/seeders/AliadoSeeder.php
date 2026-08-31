<?php

namespace Database\Seeders;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoAliado;
use App\Models\Aliado;
use Database\Seeders\Support\GeneradorImagen;
use Illuminate\Database\Seeder;

/**
 * Marcas con convenio. El detalle de cada convenio es privado: solo lo ven
 * los asociados con sesión iniciada ("son convenios, no visible para todo
 * el mundo").
 */
class AliadoSeeder extends Seeder
{
    public function run(GeneradorImagen $imagenes): void
    {
        // OBS3-04. Los cuatro que nombro el directivo, en ese orden
        // (`R21 02:19-03:26`). Son entidades, no marcas: respaldan al gremio
        // en vez de venderle a sus afiliados, y por eso ninguna lleva
        // `detalle_convenio` --no hay descuento que enseniarle al afiliado--.
        //
        // OJO: los logos son de relleno, como los del resto de la
        // demostracion. Conseguir los oficiales en buena resolucion es un
        // insumo del gremio (Bloque D), y publicar el logo real de una
        // entidad publica sin tenerlo es peor que un rectangulo gris.
        $institucionales = [
            [
                'nombre' => 'Asobares Colombia',
                'url' => 'https://asobares.org',
                'descripcion' => 'La asociación nacional de la que el capítulo Quindío forma parte.',
            ],
            [
                'nombre' => 'Cámara de Comercio de Armenia y del Quindío',
                'url' => 'https://camaraarmenia.org.co',
                'descripcion' => 'Aliado institucional en formación empresarial y trámites de registro. La oficina del capítulo está en su sede.',
            ],
            [
                'nombre' => 'Comité Intergremial del Quindío',
                'url' => null,
                'descripcion' => 'Mesa donde los gremios del departamento concertan con las instituciones.',
            ],
            [
                'nombre' => 'Gobernación del Quindío',
                'url' => 'https://quindio.gov.co',
                'descripcion' => 'Entidad departamental con la que el gremio adelanta la agenda de economía nocturna.',
            ],
        ];

        $aliados = [
            [
                'nombre' => 'Licorera del Quindío',
                'url' => 'https://ejemplo.test/licorera-quindio',
                'descripcion' => 'Distribuidora regional de licores y destilados.',
                'detalle_convenio' => 'Descuento del 12 % sobre lista de precios para afiliados al día, con despacho sin costo en pedidos superiores a $800.000. Cupo de crédito a 30 días previa aprobación. Contacto comercial: convenios@ejemplo.test',
                'orden' => 1,
            ],
            [
                'nombre' => 'Contingentix',
                'url' => 'https://ejemplo.test/contingentix',
                'descripcion' => 'Asesoría en seguridad y salud en el trabajo para el sector.',
                'detalle_convenio' => 'Diagnóstico SG-SST inicial sin costo para afiliados. 20 % de descuento en la implementación completa y en las capacitaciones de brigada. Incluye acompañamiento en visitas del Ministerio de Trabajo.',
                'orden' => 2,
            ],
            [
                'nombre' => 'Distribuidora Andina de Alimentos',
                'url' => 'https://ejemplo.test/andina-alimentos',
                'descripcion' => 'Insumos de cocina y abastecimiento para restaurantes bar.',
                'detalle_convenio' => null,
                'orden' => 4,
            ],
            [
                'nombre' => 'Sonido Pro Eje Cafetero',
                'url' => 'https://ejemplo.test/sonido-pro',
                'descripcion' => 'Alquiler y mantenimiento de equipos de sonido e iluminación.',
                'detalle_convenio' => null,
                'orden' => 5,
            ],
            [
                'nombre' => 'Seguros Nocturna',
                'url' => 'https://ejemplo.test/seguros-nocturna',
                'descripcion' => 'Pólizas de responsabilidad civil adaptadas a establecimientos nocturnos.',
                'detalle_convenio' => null,
                'orden' => 6,
            ],
        ];

        foreach ($institucionales as $orden => $aliado) {
            $aliado['tipo'] = TipoAliado::Institucional;
            $aliado['detalle_convenio'] = null;
            $aliado['orden'] = $orden + 1;

            $this->sembrar($aliado, $imagenes);
        }

        foreach ($aliados as $aliado) {
            $aliado['tipo'] = TipoAliado::Comercial;

            $this->sembrar($aliado, $imagenes);
        }
    }

    /** @param  array<string, mixed>  $aliado */
    private function sembrar(array $aliado, GeneradorImagen $imagenes): void
    {
        $aliado['logo'] = $imagenes->generar("aliado-{$aliado['nombre']}", 'aliados', 480, 270);
        $aliado['estado'] = EstadoPublicacion::Publicado;
        $aliado['activo'] = true;

        Aliado::updateOrCreate(['nombre' => $aliado['nombre']], $aliado);
    }
}
