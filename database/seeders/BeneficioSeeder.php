<?php

namespace Database\Seeders;

use App\Models\Beneficio;
use Illuminate\Database\Seeder;

/**
 * Los 5 beneficios institucionales, textuales, que entrega el gremio.
 */
class BeneficioSeeder extends Seeder
{
    public function run(): void
    {
        $beneficios = [
            [
                'titulo' => 'Representación gremial',
                'descripcion' => 'Una sola voz ante la Alcaldía, la Gobernación y las Secretarías de Salud, Gobierno y Planeación. Cuando cambia una norma de horarios o de ruido, el gremio se sienta en la mesa a proponer, no a enterarse después.',
                'icono' => 'heroicon-o-megaphone',
                'orden' => 1,
            ],
            [
                'titulo' => 'Descuentos en SAYCO y OSA',
                'descripcion' => 'Tarifas preferenciales en el pago de derechos de autor y derechos conexos, negociadas por el capítulo para sus afiliados.',
                'icono' => 'heroicon-o-musical-note',
                'orden' => 2,
            ],
            [
                'titulo' => 'Beneficios con aliados estratégicos',
                'descripcion' => 'Convenios vigentes con licoreras, proveedores de tecnología y servicios para el sector. El detalle de cada convenio está disponible al iniciar sesión.',
                'icono' => 'heroicon-o-gift',
                'orden' => 3,
            ],
            [
                'titulo' => 'Formación empresarial',
                'descripcion' => 'Capacitaciones en manipulación de alimentos, servicio, costos y normatividad, dictadas con aliados y con la Cámara de Comercio de Armenia y del Quindío.',
                'icono' => 'heroicon-o-academic-cap',
                'orden' => 4,
            ],
            [
                'titulo' => 'Orientación jurídica gratuita',
                'descripcion' => 'Acompañamiento ante visitas de control, requerimientos de autoridades y trámites de apertura o renovación, sin costo para el afiliado.',
                'icono' => 'heroicon-o-scale',
                'orden' => 5,
            ],
        ];

        foreach ($beneficios as $beneficio) {
            Beneficio::updateOrCreate(['titulo' => $beneficio['titulo']], $beneficio);
        }
    }
}
