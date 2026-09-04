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
 *
 * ⚠️ **Todo lo que hay aquí sale de un documento oficial del gremio.** Los
 * comerciales son los de «BENEFICIOS AFILIADOS» (`material/nuevomaterial/`),
 * páginas «ALIADOS ESTRATÉGICOS», con sus condiciones y sus contactos tal
 * como los publica ASOBARES a sus afiliados. Antes había aquí cinco marcas
 * inventadas --Licorera del Quindío, Contingentix, Distribuidora Andina de
 * Alimentos, Sonido Pro Eje Cafetero y Seguros Nocturna-- con descuentos
 * igualmente inventados y URLs a `ejemplo.test`. Se retiraron: el esquema no
 * marca de dónde vino cada fila, así que en cuanto la oficina empiece a
 * editar nadie sabrá distinguir lo sembrado de lo real. **No vuelvas a meter
 * un aliado que no puedas señalar en un documento.**
 */
class AliadoSeeder extends Seeder
{
    /** De dónde sale cada condición, para quien audite esto en octubre. */
    public const string FUENTE_COMERCIALES = 'BENEFICIOS AFILIADOS · ASOBARES — «Aliados estratégicos»';

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

        // Los aliados estratégicos, con la condición y el canal de contacto
        // que publica el propio catálogo. Van sin logo a propósito: el gremio
        // tiene los oficiales y los sube desde el panel; dibujar un rectángulo
        // gris con el nombre de Securitas encima es peor que no poner nada, y
        // la tarjeta se ve bien sin él (`inicio.blade.php`).
        $comerciales = $this->aliadosEstrategicos();

        foreach ($institucionales as $orden => $aliado) {
            $aliado['tipo'] = TipoAliado::Institucional;
            $aliado['detalle_convenio'] = null;
            $aliado['orden'] = $orden + 1;
            // El logo de relleno es andamio de la demostración y solo de ahí.
            // En producción no se genera por dos razones, y cualquiera de las
            // dos basta: el disco de Laravel Cloud es efímero --al siguiente
            // despliegue el PNG ya no está y la portada queda con cuatro
            // imágenes rotas, porque la vista comprueba que el campo tenga
            // valor, no que el archivo exista-- y un rectángulo gris rotulado
            // «Gobernación del Quindío» en una URL pública con el nombre del
            // gremio encima es peor que la rejilla con solo el nombre, que se
            // ve bien. Los oficiales los sube el gremio desde el panel.
            $aliado['logo'] = app()->isProduction()
                ? null
                : $imagenes->generar("aliado-{$aliado['nombre']}", 'aliados', 480, 270);

            $this->sembrar($aliado);
        }

        foreach ($comerciales as $orden => $aliado) {
            $aliado['tipo'] = TipoAliado::Comercial;
            $aliado['orden'] = $orden + 1;
            $aliado['url'] ??= null;
            $aliado['logo'] = null;

            $this->sembrar($aliado);
        }
    }

    /**
     * Los diecinueve aliados estratégicos del catálogo oficial.
     *
     * `descripcion` es pública --sale en la portada--; `detalle_convenio` solo
     * lo ve el afiliado con sesión iniciada, y por eso es donde van la
     * condición concreta y el contacto directo, que es exactamente el público
     * al que ASOBARES le entrega este documento.
     *
     * ⚠️ El catálogo tiene un desliz de maquetación: en la primera página de
     * aliados el rótulo «Smart Language Systems» está sobre el logo de Energy
     * Economics Experts. La página siguiente lo aclara --SLS es el curso de
     * inglés-- así que aquí la consultoría de energía va con su nombre real.
     *
     * @return list<array<string, mixed>>
     */
    private function aliadosEstrategicos(): array
    {
        return [
            [
                'nombre' => 'Sayco Acinpro (OSA)',
                'descripcion' => 'Derechos de autor y conexos por las obras musicales que reproduce el establecimiento.',
                'detalle_convenio' => "Descuento del 16,6 % —equivalente a dos meses— para el pago anual, y del 8,3 % —un mes— si se paga en cuotas, por ser afiliado Asobares.\n\nContacto: director.zonauno@saycoacinpro.org.co · (+57) 310 204 0740",
            ],
            [
                'nombre' => 'Sayco',
                'descripcion' => 'Pago de derechos por los eventos de música en vivo.',
                'detalle_convenio' => "Descuento del 6 % en la liquidación del pago para eventos de música en vivo, por ser afiliado Asobares.\n\nContacto: proyectos@asobares.org · (+57) 323 289 7493",
            ],
            [
                'nombre' => 'Conacústica Ingeniería & Consultoría',
                'descripcion' => 'Mitigación de ruido, insonorización de espacios y diseño de sonido.',
                'detalle_convenio' => "Diagnóstico acústico gratuito y 5 % de descuento en la compra de productos y servicios, por ser afiliado Asobares.\n\nContacto: info@coninenierias.com · (+57) 311 836 7765",
            ],
            [
                'nombre' => 'Audionics',
                'descripcion' => 'Proyectos de audio, iluminación y vídeo profesional para el establecimiento.',
                'detalle_convenio' => "Asesoría en proyectos de audio, iluminación y vídeo profesional; automatización y control; suministro y diseño de sistemas de audio e iluminación; estudios de grabación profesional.\n\nContacto: comercial@audionicspro.com · (+57) 316 832 1992",
            ],
            [
                'nombre' => 'Delta Servicios',
                'descripcion' => 'Planes de emergencia y contingencia, y suministros de seguridad.',
                'detalle_convenio' => "Descuento entre el 5 % y el 10 % según el producto o servicio solicitado, por ser afiliado Asobares.\n\nContacto: deltaserviciosysuministros@gmail.com · (+57) 314 323 0248",
            ],
            [
                'nombre' => 'Energy Economics Experts',
                'descripcion' => 'Consultoría para optimizar el costo del servicio de energía eléctrica.',
                'detalle_convenio' => "Reducción de la tarifa energética a partir de consumos superiores a \$2.500.000, por ser afiliado Asobares.\n\nContacto: (+57) 317 854 6037",
            ],
            [
                'nombre' => 'Calidad de Energía',
                'descripcion' => 'Certificados RETIE y RETILAP de la instalación eléctrica.',
                'detalle_convenio' => "Tarifa especial en el certificado de RETIE y RETILAP para el establecimiento.\n\nContacto: ingenieria@calidadenergia.com · (+57) 315 771 4185",
            ],
            [
                'nombre' => 'Securitas',
                'descripcion' => 'Contratación de servicios de seguridad.',
                'detalle_convenio' => "Descuentos y planes especiales en la contratación de servicios de seguridad.\n\nContacto: (+57) 314 470 3336",
            ],
            [
                'nombre' => 'Colmedicos',
                'descripcion' => 'Exámenes médicos ocupacionales para el equipo de trabajo.',
                'detalle_convenio' => "Descuentos y prioridad en la cita para exámenes médicos de BPM: exámenes de ingreso, pruebas de alcoholemia, pruebas de consumo de sustancias psicoactivas y exámenes especializados. Cobertura nacional.\n\nContacto: profecionalpyme@colmedicos.com · (+57) 314 225 0313",
            ],
            [
                'nombre' => 'Manchego Álvarez',
                'descripcion' => 'Pólizas de seguros para el establecimiento.',
                'detalle_convenio' => "Descuento del 25 % en pólizas de seguros por ser afiliado Asobares.\n\nContacto: gerenteg.manchego@gmail.com · (+57) 311 598 3068",
            ],
            [
                'nombre' => 'Cooserpark',
                'descripcion' => 'Planes exequiales para el afiliado y su familia.',
                'detalle_convenio' => "Tarifa preferencial en planes exequiales hasta para diez beneficiarios y una mascota. Plan presidencial por \$20.000 mensuales por afiliado.\n\nContacto: comercial9@capillasdelafe.com · (+57) 314 451 5379",
            ],
            [
                'nombre' => 'Escargoth',
                'descripcion' => 'Trámites de licencia de construcción y uso de suelos.',
                'detalle_convenio' => "Asesoría gratuita, descuento y trato preferencial en trámites urbanísticos, por ser afiliado Asobares.\n\nContacto: c.i.grupoescargot_@hotmail.com · (+57) 322 397 6656 · (+57) 312 559 8615",
            ],
            [
                'nombre' => 'Wiwo',
                'descripcion' => 'Portal de publicación de empleos y consecución de personal.',
                'detalle_convenio' => "Un mes de acceso premium al portal de empleo por ser afiliado Asobares.\n\nContacto: contacto@wiwo.com.co · (+57) 311 211 6492",
            ],
            [
                'nombre' => 'Smart Language Systems',
                'descripcion' => 'Curso de inglés especializado en servicio de mesa y bar.',
                'detalle_convenio' => "Tarifa preferencial de \$22.700 + IVA por hora y persona, por ser afiliado Asobares.\n\nContacto: cnieto@sls-idiomas.com · (+57) 300 501 7043",
            ],
            [
                'nombre' => 'Doble A',
                'descripcion' => 'Agencia digital especializada en ocio nocturno.',
                'detalle_convenio' => "Tarifa preferencial en marketing y estrategia digital, creatividad, community management, producción de contenido, pauta digital, fotografía, vídeo, diseño gráfico, consultoría y redes sociales.\n\nContacto: contacto@somosdoblea.com · (+57) 311 639 8731",
            ],
            [
                'nombre' => 'Tikipal',
                'descripcion' => 'Aliado tecnológico: entradas digitales y gestión de pagos.',
                'detalle_convenio' => "Centraliza los comercios en una única plataforma que permite emitir entradas digitales, gestionar el pago y expandir la audiencia en tiempo real.\n\nContacto: team@tikipal.co · marketing@tikipal.co · (+57) 305 458 4137",
            ],
            [
                'nombre' => 'Manillas para Eventos',
                'descripcion' => 'Manillas de control de acceso para eventos y establecimiento.',
                'detalle_convenio' => "Descuento del 5 % sobre todo el portafolio, exclusivo para afiliados Asobares.\n\nContacto: ventas@manillasparaeventos.com · (+57) 313 610 7202",
            ],
            [
                'nombre' => 'Hielo Iglú',
                'descripcion' => 'Suministro permanente de hielo.',
                'detalle_convenio' => "Descuentos en el suministro permanente de hielo por ser afiliado Asobares.\n\nContacto: jorge.silva@hieloartico.com · (+57) 312 862 0460",
            ],
            [
                'nombre' => 'Colsubsidio',
                'descripcion' => 'Ensayaderos para bandas y grupos musicales.',
                'detalle_convenio' => "Ensayaderos para bandas y grupos con precios especiales.\n\nContacto: servicioalcliente@colsubsidio.com · (+57) 601 745 7900",
            ],
        ];
    }

    /** @param  array<string, mixed>  $aliado */
    private function sembrar(array $aliado): void
    {
        $aliado['estado'] = EstadoPublicacion::Publicado;
        $aliado['activo'] = true;

        Aliado::updateOrCreate(['nombre' => $aliado['nombre']], $aliado);
    }
}
