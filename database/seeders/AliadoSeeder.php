<?php

namespace Database\Seeders;

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
                'nombre' => 'Cámara de Comercio de Armenia y del Quindío',
                'url' => 'https://camaraarmenia.org.co',
                'descripcion' => 'Aliado institucional en formación empresarial y trámites de registro.',
                'detalle_convenio' => null,
                'orden' => 3,
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

        foreach ($aliados as $aliado) {
            $aliado['logo'] = $imagenes->generar("aliado-{$aliado['nombre']}", 'aliados', 480, 270);
            $aliado['activo'] = true;

            Aliado::updateOrCreate(['nombre' => $aliado['nombre']], $aliado);
        }
    }
}
