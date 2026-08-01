<?php

namespace Database\Seeders;

use App\Enums\CategoriaNoticia;
use App\Enums\EstadoPublicacion;
use App\Models\Noticia;
use Database\Seeders\Support\GeneradorImagen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Boletín deliberadamente sobrio: ~mensual, con datos que envía la Nacional.
 * Las cifras del Observatorio son reales (marzo de 2026).
 */
class NoticiaSeeder extends Seeder
{
    public function run(GeneradorImagen $imagenes): void
    {
        foreach ($this->noticias() as $noticia) {
            $noticia['slug'] = Str::slug($noticia['titulo']);
            $noticia['imagen'] = $imagenes->generar("noticia-{$noticia['titulo']}", 'noticias', 1200, 675);
            $noticia['estado'] = EstadoPublicacion::Publicado;

            Noticia::updateOrCreate(['slug' => $noticia['slug']], $noticia);
        }
    }

    /** @return list<array<string, mixed>> */
    private function noticias(): array
    {
        return [
            [
                'titulo' => 'La economía nocturna genera el 12,65 % del empleo en Armenia',
                'categoria' => CategoriaNoticia::Observatorio,
                'extracto' => 'El Observatorio Económico Nocturno entrega su medición de marzo: uno de cada ocho empleos de la ciudad nace en bares, gastrobares, cafés y discotecas.',
                'contenido' => '<p>La primera medición completa del Observatorio Económico Nocturno confirma lo que el gremio venía sosteniendo: la vida nocturna no es un sector marginal de la economía de Armenia, sino <strong>uno de sus mayores empleadores</strong>.</p><p>Según los datos de marzo de 2026, la economía nocturna genera el <strong>12,65 %</strong> del empleo de la ciudad. El ingreso medio mensual del sector se ubica en <strong>$2.104.124</strong>, por encima del salario mínimo legal vigente.</p><p>La cifra que enciende alertas es otra: la <strong>informalidad llega al 72,82 %</strong>. Casi tres de cada cuatro personas que trabajan en el sector lo hacen sin contrato formal ni seguridad social completa. Cerrar esa brecha es el objetivo declarado del capítulo para los próximos dos años.</p><p>Los datos fueron entregados por Asobares Colombia a partir del levantamiento del Observatorio, y se actualizarán mensualmente.</p>',
                'publicado_at' => now()->subDays(12),
            ],
            [
                'titulo' => 'Radiografía del trabajador nocturno: joven y con brecha salarial de género',
                'categoria' => CategoriaNoticia::Observatorio,
                'extracto' => 'El 35,28 % de quienes trabajan en el sector tiene 28 años o menos. Las mujeres ganan en promedio 26,55 % menos que los hombres en los mismos cargos.',
                'contenido' => '<p>El segundo bloque de cifras del Observatorio Económico Nocturno describe quién sostiene la noche del Quindío.</p><p><strong>El 35,28 % de los trabajadores del sector tiene 28 años o menos.</strong> Para buena parte de ellos, un bar o un café es el primer empleo formal de su vida: la puerta de entrada al mercado laboral en una región donde el desempleo juvenil sigue siendo alto.</p><p>El dato más incómodo es la <strong>brecha salarial de género: −26,55 %</strong>. A igual cargo, las mujeres del sector ganan en promedio una cuarta parte menos que sus compañeros hombres. El capítulo asumió el compromiso de llevar esta cifra a la mesa de trabajo con las secretarías y de construir, con sus afiliados, una ruta de equiparación.</p><p>ASOBARES Quindío publicará la evolución de este indicador en cada entrega del boletín.</p>',
                'publicado_at' => now()->subDays(40),
            ],
            [
                'titulo' => 'Lo que viene: guía normativa para los 12 municipios del Quindío',
                'categoria' => CategoriaNoticia::Proyecto,
                'extracto' => 'Arrancamos con Armenia, Salento y Filandia. La meta es documentar el trámite completo de apertura en todo el departamento, con formatos descargables.',
                'contenido' => '<p>Ningún gremio del país tiene una guía normativa por municipio. Ese es exactamente el vacío que ASOBARES Quindío decidió llenar.</p><p>La razón es simple y la conoce cualquiera que haya abierto un establecimiento: <strong>el trámite es donde caen los negocios y donde los cierran</strong>. Un certificado de uso de suelos consultado después de firmar el arriendo, un concepto de bomberos que nadie sabía que costaba distinto en cada municipio, un formato de la Policía que se diligencia mal y hay que repetir.</p><p>La primera entrega cubre <strong>Armenia, Salento y Filandia</strong>, con seis entidades por municipio, checklist por trámite, costo aproximado y los formatos oficiales listos para descargar y diligenciar.</p><p>La meta para 2027 es cubrir los doce municipios del departamento. La directora ejecutiva ya tiene línea directa con las secretarías, y Bomberos y Salud entregaron sus checklists oficiales.</p>',
                'publicado_at' => now()->subDays(5),
            ],
            [
                'titulo' => 'El gremio se sentó con la Secretaría de Gobierno por los horarios',
                'categoria' => CategoriaNoticia::Noticia,
                'extracto' => 'Mesa de trabajo sobre la reglamentación de horarios en Armenia y su efecto sobre el empleo del sector.',
                'contenido' => '<p>Una delegación del capítulo se reunió con la Secretaría de Gobierno de Armenia y la Policía Metropolitana para discutir la reglamentación de horarios de los establecimientos nocturnos.</p><p>La posición del gremio fue clara: <strong>toda restricción de horario es una decisión de empleo</strong>, y debe evaluarse con las cifras del Observatorio en la mano. Cada hora de cierre anticipado se traduce en turnos que no se pagan.</p><p>Se acordó constituir una mesa permanente con participación del gremio antes de cualquier modificación al decreto vigente.</p>',
                'publicado_at' => now()->subDays(22),
            ],
            [
                'titulo' => 'Nuevo convenio: 12 % de descuento en licores para afiliados al día',
                'categoria' => CategoriaNoticia::Noticia,
                'extracto' => 'El capítulo cerró un acuerdo con Licorera del Quindío. El detalle del convenio está disponible al iniciar sesión.',
                'contenido' => '<p>ASOBARES Capítulo Quindío firmó un nuevo convenio con Licorera del Quindío que otorga condiciones comerciales preferenciales a los establecimientos afiliados que estén al día con su mensualidad.</p><p>El acuerdo incluye descuento sobre lista de precios, despacho sin costo por encima de cierto monto y cupo de crédito previa aprobación.</p><p>Como todos los convenios del gremio, <strong>las condiciones exactas son información privada de los afiliados</strong> y se consultan iniciando sesión en la sección de beneficios.</p>',
                'publicado_at' => now()->subDays(55),
            ],
            [
                'titulo' => '40 cupos para la certificación en manipulación de alimentos',
                'categoria' => CategoriaNoticia::Noticia,
                'extracto' => 'Capacitación gratuita con la Secretaría de Salud, pensada para las cocinas pequeñas de bares y gastrobares.',
                'contenido' => '<p>El capítulo abrió inscripciones para la certificación en manipulación de alimentos, dictada junto con la Secretaría de Salud y pensada específicamente para las cocinas de bares y gastrobares, no para restaurantes grandes.</p><p>El contenido cubre almacenamiento en frío, cadena de temperatura, control de plagas y —lo que más preocupa a los dueños— <strong>el papeleo exacto que revisa el inspector cuando llega sin avisar</strong>.</p><p>La capacitación es gratuita y entrega certificado individual. Los cupos son limitados y se asignan por orden de inscripción.</p>',
                'publicado_at' => now()->subDays(8),
            ],
        ];
    }
}
