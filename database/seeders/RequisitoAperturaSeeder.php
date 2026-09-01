<?php

namespace Database\Seeders;

use App\Enums\EstadoPublicacion;
use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Illuminate\Database\Seeder;

/**
 * El producto insignia: la guía normativa por municipio.
 *
 * «Es el punto donde caen siempre los negocios y los cierran.»
 *
 * ⚠️ **Esto se rehízo entero contra la fuente oficial.** Lo que había era una
 * guía de tres municipios --Armenia, Salento y Filandia-- con **costos
 * inventados** (180.000, 45.000, 380.000, 290.000…) y dos formatos PDF que se
 * generaban al vuelo rotulados «Formato de ejemplo». En la demostración del
 * gremio ya se había dicho «todavía no estamos actualizados» (`R21 09:27`), y
 * el §29.4 lo deja escrito: publicar cifras equivocadas de trámites legales
 * en una URL con el nombre del gremio encima es un riesgo del gremio, no del
 * equipo.
 *
 * Ahora sale de dos documentos del gremio, palabra por palabra:
 *
 * - `material/REQUISITOS APERTURA - ARMENIA.docx` — la jornada «BLINDEMOS TU
 *   NEGOCIO ARMENIA», hecha con la Alcaldía de Armenia. De ahí salen los
 *   siete trámites, en su orden, con lo que cada uno es y qué piden.
 * - `material/nuevomaterial/REQUERIMIENTOS BASICOS GENERALES - ESTABLECIMIENTO
 *   NOCTURNO.docx` — la lista de verificación que el gremio lleva a la visita,
 *   apoyada en la **ley 1801 de 2016** (Código Nacional de Policía) y el
 *   **decreto 119**. Es la octava ficha.
 *
 * Dos consecuencias que no son descuido:
 *
 * - **Ningún `costo_aproximado`.** El documento oficial no trae ni una cifra.
 *   Inventarlas otra vez sería repetir el mismo defecto con mejor letra.
 * - **Solo Armenia.** Para Salento y Filandia no hay documento. Una guía
 *   incompleta y cierta vale más que una completa e inventada, y el gremio
 *   añade los otros municipios desde el panel cuando tenga la fuente.
 *
 * `verificado_el` se queda en `null` a propósito: el documento no lleva fecha
 * y nadie ha vuelto a contrastar estos trámites con cada entidad. La guía
 * muestra entonces «Sin verificar contra la fuente oficial», que es la verdad.
 * `verificado_con` sí nombra de dónde salió, para que se pueda auditar.
 *
 * Nota para quien resiembre sobre una base ya poblada: `updateOrCreate` va por
 * `(municipio_id, entidad)` y **no borra nada**, así que las fichas viejas de
 * Salento y Filandia siguen ahí. En desarrollo se limpian con
 * `migrate:fresh --seed`; en producción la tabla nació vacía.
 */
class RequisitoAperturaSeeder extends Seeder
{
    /** La fuente, tal cual, para que quede en la ficha y se pueda auditar. */
    public const string FUENTE_ARMENIA = 'Jornada «Blindemos tu Negocio Armenia» — Alcaldía de Armenia y Asobares Capítulo Quindío';

    public const string FUENTE_GENERAL = 'Requerimientos básicos generales — Asobares Capítulo Quindío (ley 1801 de 2016 y decreto 119)';

    public function run(): void
    {
        foreach ($this->guiaPorMunicipio() as $slug => $requisitos) {
            $municipio = Municipio::where('slug', $slug)->firstOrFail();

            foreach ($requisitos as $orden => $requisito) {
                RequisitoApertura::updateOrCreate(
                    ['municipio_id' => $municipio->id, 'entidad' => $requisito['entidad']],
                    $requisito + [
                        'municipio_id' => $municipio->id,
                        'orden' => $orden + 1,
                        'estado' => EstadoPublicacion::Publicado,
                        // El documento no trae cifras. No se rellenan.
                        'costo_aproximado' => null,
                        'verificado_el' => null,
                    ]
                );
            }
        }
    }

    /** @return array<string, list<array<string, mixed>>> */
    private function guiaPorMunicipio(): array
    {
        return [
            'armenia' => [
                [
                    'entidad' => 'Alcaldía de Armenia — Uso de suelos',
                    'descripcion' => 'Es el certificado que autoriza que en esa dirección puede funcionar un bar o una discoteca. Lo emiten Planeación Municipal (concepto) y la Curaduría Ciudadana (documento oficial). Consúltalo ANTES de firmar el arriendo.',
                    'checklist' => [
                        'Concepto de uso de suelo de Planeación Municipal',
                        'Documento oficial de la Curaduría Ciudadana',
                        'Verificar que la actividad económica del RUT coincida con el concepto de uso de suelo emitido por la curaduría',
                    ],
                    'enlace_externo' => 'https://armenia.gov.co',
                    'verificado_con' => self::FUENTE_ARMENIA,
                ],
                [
                    'entidad' => 'Secretaría de Salud de Armenia',
                    'descripcion' => 'Concepto sanitario: la visita de los inspectores de salud. Vale el acta de visita con concepto favorable o, mientras llega, la solicitud de visita radicada. La visita se pide al correo servicioalcliente@armenia.gov.co con el asunto «VISITA SANITARIA», adjuntando nombre y dirección del establecimiento y el RUT.',
                    'checklist' => [
                        'Acta de visita con concepto favorable, o solicitud de visita radicada',
                        'Control de plagas con certificado vigente',
                        'Manipulación de alimentos, si aplica',
                        'Condiciones locativas de los baños',
                    ],
                    'verificado_con' => self::FUENTE_ARMENIA,
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos de Armenia',
                    'descripcion' => 'Seguridad humana y contra incendios: el certificado que emite el Cuerpo Oficial de Bomberos de Armenia.',
                    'checklist' => [
                        'Extintores vigentes',
                        'Señalización de rutas de evacuación',
                        'Luces de emergencia',
                        'Botiquín de primeros auxilios',
                    ],
                    'verificado_con' => self::FUENTE_ARMENIA,
                ],
                [
                    'entidad' => 'Sayco y Acinpro',
                    'descripcion' => 'Derechos de autor: el comprobante de pago por la comunicación pública de música. Si eres afiliado a Asobares Quindío, el gremio puede revisar tu tarifa y darte descuentos con marcas aliadas.',
                    'checklist' => [
                        'Comprobante de pago por la comunicación pública de música',
                    ],
                    'verificado_con' => self::FUENTE_ARMENIA,
                ],
                [
                    'entidad' => 'Cámara de Comercio de Armenia y del Quindío',
                    'descripcion' => 'La matrícula mercantil es el registro legal del establecimiento. Debe estar renovada a 31 de marzo de cada año.',
                    'checklist' => [
                        'Matrícula mercantil del establecimiento',
                        'Renovación al día: vence el 31 de marzo de cada año',
                    ],
                    'enlace_externo' => 'https://camaraarmenia.org.co',
                    'verificado_con' => self::FUENTE_ARMENIA,
                ],
                [
                    'entidad' => 'CRQ — Corporación Autónoma Regional del Quindío',
                    'descripcion' => 'Intensidad auditiva: el cumplimiento de los niveles de decibeles permitidos. La medición se le solicita a la entidad por correo electrónico.',
                    'checklist' => [
                        'Solicitud de medición de intensidad auditiva a la CRQ',
                        'Aislamiento acústico suficiente para no generar impacto negativo en la vecindad',
                    ],
                    'verificado_con' => self::FUENTE_ARMENIA,
                ],
                [
                    'entidad' => 'Policía Nacional — Notificación de apertura',
                    'descripcion' => 'Solicitud escrita a la estación de policía correspondiente para notificar la apertura de un establecimiento de comercio en la zona. Solo aplica a los establecimientos abiertos después de 2019.',
                    'checklist' => [
                        'Solicitud escrita radicada en la estación de policía correspondiente',
                        'Aplica únicamente a establecimientos con apertura posterior a 2019',
                    ],
                    'verificado_con' => self::FUENTE_ARMENIA,
                ],
                [
                    'entidad' => 'Documentación general del establecimiento (ley 1801 de 2016)',
                    'descripcion' => 'La lista que el gremio revisa en la visita, según la ley 1801 (Código Nacional de Policía) y el decreto 119. Ten estos documentos vigentes y a la mano: es lo que piden en un operativo.',
                    'checklist' => [
                        'Cámara de comercio del establecimiento',
                        'RUT con las actividades económicas 5630, 5611, 9007 o 9008',
                        'Certificado de bomberos, o radicado de la solicitud',
                        'Concepto sanitario vigente, o radicado de la solicitud de visita',
                        'Comunicado de notificación de apertura firmado o recibido por la policía local',
                        'Uso de suelos o licencia de construcción',
                        'Certificado o recibo de pago de derechos de autor',
                        'Plan de saneamiento básico',
                        'Certificado de lavado de tanques',
                        'Certificado de control de plagas',
                        'Carné de manipulación de alimentos de los colaboradores',
                        'Certificados médicos de los colaboradores',
                        'Certificado RETIE y RETILAP',
                        'Certificación de resolución de facturación (DIAN)',
                        'Plan de salud y seguridad en el trabajo',
                        'Avisos de espacio libre de humo',
                        'Aviso de prohibición de expendio de bebidas a menores de edad',
                        'Aviso «Usted está siendo grabado y monitoreado»',
                        'Acta de propinas',
                    ],
                    'verificado_con' => self::FUENTE_GENERAL,
                ],
            ],
        ];
    }
}
