<?php

namespace Database\Seeders;

use App\Models\Beneficio;
use Illuminate\Database\Seeder;

/**
 * Los 5 beneficios institucionales, textuales, que entrega el gremio.
 *
 * Los cinco títulos son los que el capítulo viene presentando. Las
 * descripciones se ajustaron contra el catálogo oficial «BENEFICIOS
 * AFILIADOS» (`material/nuevomaterial/`), que es el documento que ASOBARES
 * entrega a sus afiliados: de ahí salen las cifras --16,6 % y 8,3 % con OSA,
 * 6 % con Sayco-- y los nombres de las mesas en las que el gremio se sienta.
 * Antes decían lo mismo en general, pero con detalles que el documento no
 * respalda.
 */
class BeneficioSeeder extends Seeder
{
    public function run(): void
    {
        $beneficios = [
            [
                'titulo' => 'Representación gremial',
                'descripcion' => 'Una sola voz ante la Alcaldía, la Gobernación y las Secretarías de Salud, Gobierno y Planeación, y un puesto en las mesas de trabajo con el SENA, el DANE y el Viceministerio de Turismo. Cuando cambia una norma de horarios o de ruido, el gremio se sienta en la mesa a proponer, no a enterarse después.',
                'icono' => 'heroicon-o-megaphone',
                'orden' => 1,
            ],
            [
                'titulo' => 'Descuentos en SAYCO y OSA',
                'descripcion' => 'Con la Organización Sayco-Acinpro, 16,6 % de descuento —dos meses— si pagas el año completo, u 8,3 % —un mes— si pagas en cuotas. Con Sayco, 6 % en la liquidación de los eventos de música en vivo. Las dos tarifas son por ser afiliado.',
                'icono' => 'heroicon-o-musical-note',
                'orden' => 2,
            ],
            [
                'titulo' => 'Beneficios con aliados estratégicos',
                'descripcion' => 'Convenios vigentes en seguridad, insonorización, energía, seguros, exámenes médicos, empleo, formación y suministros. El detalle de cada convenio, con su descuento y su contacto, está disponible al iniciar sesión.',
                'icono' => 'heroicon-o-gift',
                'orden' => 3,
            ],
            [
                'titulo' => 'Formación empresarial',
                'descripcion' => 'Invitación permanente a las capacitaciones, conferencias y talleres del gremio, y orientación para quien apenas está montando su negocio: qué normas debe cumplir y en qué orden.',
                'icono' => 'heroicon-o-academic-cap',
                'orden' => 4,
            ],
            [
                'titulo' => 'Orientación jurídica gratuita',
                'descripcion' => 'Orientación jurídica y contable especializada en el sector, y asesoría en los requisitos legales de funcionamiento, la licencia de construcción y el uso de suelos. Sin costo para el afiliado.',
                'icono' => 'heroicon-o-scale',
                'orden' => 5,
            ],
        ];

        foreach ($beneficios as $beneficio) {
            Beneficio::updateOrCreate(['titulo' => $beneficio['titulo']], $beneficio);
        }
    }
}
