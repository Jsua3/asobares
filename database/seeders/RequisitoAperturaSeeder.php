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
 * Los siete trámites de Armenia son, **literalmente**, el bloque que ya estaba
 * transcrito y listo para pegar en
 * `docs/ingenieria/guia-normativa-armenia-fuente-oficial.md` (§3). Ese
 * documento sale de `material/REQUISITOS APERTURA - ARMENIA.docx`, la jornada
 * «BLINDEMOS TU NEGOCIO ARMENIA» hecha con la Alcaldía de Armenia, que el
 * gremio entregó el **20 de agosto de 2026** — de ahí la fecha de
 * verificación, que no es ni una invención ni una omisión.
 *
 * La octava ficha no está en ese documento: es la lista de verificación de
 * `material/nuevomaterial/REQUERIMIENTOS BASICOS GENERALES - ESTABLECIMIENTO
 * NOCTURNO.docx`, apoyada en la **ley 1801 de 2016** y el **decreto 119**. Ese
 * papel no lleva fecha, así que va sin `verificado_el` y la guía lo dice en su
 * cara: «Sin verificar contra la fuente oficial».
 *
 * Tres consecuencias que no son descuido:
 *
 * - **Ningún `costo_aproximado`.** El documento oficial no trae ni una cifra.
 *   Inventarlas otra vez sería repetir el mismo defecto con mejor letra.
 * - **Solo Armenia.** Para Salento y Filandia no hay documento. Una guía
 *   incompleta y cierta vale más que una completa e inventada; el §5 de aquel
 *   documento ya cuenta los otros once municipios como pendiente del gremio.
 * - **Sin `adjunto`.** Los PDF que se generaban decían «Formato de ejemplo» y
 *   llevaban el nombre del gremio encima. Los formatos reales de las entidades
 *   hay que pedirlos, y el gremio los sube desde el panel.
 *
 * ⚠️ **Pendiente que este commit NO cierra:** el §5 de ese mismo documento
 * pide que la dirección confirme por escrito que esta es la versión vigente
 * antes de publicar, «porque es información que un empresario va a usar para
 * decidir si abre o no». Aquí salen publicadas --con su fuente y su fecha a la
 * vista, que es la salvaguarda que pedía el §29.4-- pero esa confirmación
 * sigue debiéndose, y desde el panel se pasan a borrador en un clic.
 *
 * Nota para quien resiembre sobre una base ya poblada: `updateOrCreate` va por
 * `(municipio_id, entidad)` y **no borra nada**, así que las fichas viejas de
 * Salento y Filandia siguen ahí. En desarrollo se limpian con
 * `migrate:fresh --seed`; en producción la tabla nació vacía.
 */
class RequisitoAperturaSeeder extends Seeder
{
    /** La fuente, tal cual, para que quede en la ficha y se pueda auditar. */
    public const string FUENTE_ARMENIA = 'Documento oficial de la Alcaldía de Armenia, campaña «Blindemos tu Negocio», entregado al gremio el 20 de agosto de 2026';

    public const string FUENTE_GENERAL = 'Requerimientos básicos generales — Asobares Capítulo Quindío (ley 1801 de 2016 y decreto 119)';

    /** El día en que el gremio entregó el documento. Ver §3 del documento citado. */
    public const string VERIFICADO_EL = '2026-08-20';

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
                        'verificado_el' => self::VERIFICADO_EL,
                        'verificado_con' => self::FUENTE_ARMENIA,
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
                    'entidad' => 'Alcaldía de Armenia — Planeación municipal y Curaduría',
                    'descripcion' => 'El certificado de uso de suelos autoriza que en esa dirección pueda funcionar un bar o una discoteca. Son dos puertas distintas: Planeación municipal emite el concepto y la Curaduría ciudadana el documento oficial. Consúltalo ANTES de firmar el arriendo.',
                    'checklist' => [
                        'Concepto de uso de suelo emitido por Planeación municipal',
                        'Documento oficial expedido por la Curaduría ciudadana',
                        'Verificar que la actividad económica del RUT coincida con el concepto de uso de suelo',
                    ],
                    'enlace_externo' => 'https://armenia.gov.co',
                ],
                [
                    'entidad' => 'Cámara de Comercio de Armenia y del Quindío',
                    'descripcion' => 'La matrícula mercantil es el registro legal del establecimiento. Debe estar renovada antes del 31 de marzo de cada año.',
                    'checklist' => [
                        'Formulario RUES diligenciado (persona natural o jurídica)',
                        'Cédula del propietario o del representante legal',
                        'RUT expedido por la DIAN',
                        'Consulta previa de homonimia del nombre comercial',
                        'Renovación al día: vence el 31 de marzo de cada año',
                    ],
                    'enlace_externo' => 'https://camaraarmenia.org.co',
                ],
                [
                    'entidad' => 'Secretaría de Salud Municipal — Concepto sanitario',
                    'descripcion' => 'Es la visita de los inspectores de salud. Se solicita por correo a servicioalcliente@armenia.gov.co con el asunto VISITA SANITARIA, adjuntando nombre del establecimiento, dirección y RUT. El requisito se cumple con el acta de visita con concepto favorable o, mientras llega, con la solicitud radicada.',
                    'checklist' => [
                        'Solicitud enviada a servicioalcliente@armenia.gov.co — asunto: VISITA SANITARIA',
                        'Adjuntar nombre del establecimiento, dirección y RUT',
                        'Certificado vigente de control de plagas',
                        'Certificados de manipulación de alimentos (si aplica)',
                        'Condiciones locativas de los baños en regla',
                        'Acta de visita con concepto favorable, o la solicitud de visita radicada',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo Oficial de Bomberos de Armenia',
                    'descripcion' => 'Certificado de seguridad humana y contra incendios emitido por el Cuerpo Oficial de Bomberos de Armenia.',
                    'checklist' => [
                        'Extintores vigentes y con recarga certificada',
                        'Señalización de rutas de evacuación',
                        'Luces de emergencia',
                        'Botiquín de primeros auxilios dotado',
                    ],
                ],
                [
                    'entidad' => 'Corporación Autónoma Regional del Quindío (CRQ) — Intensidad auditiva',
                    'descripcion' => 'Cumplimiento de los niveles de decibeles permitidos. La medición se solicita a la entidad por correo electrónico. Asegúrate de que el establecimiento tenga el aislamiento acústico necesario para no generar impacto sobre la vecindad.',
                    'checklist' => [
                        'Solicitud de medición de intensidad auditiva enviada a la CRQ',
                        'Aislamiento acústico verificado',
                        'Medición dentro de los niveles de decibeles permitidos',
                    ],
                    'enlace_externo' => 'https://crq.gov.co',
                ],
                [
                    'entidad' => 'Sayco y Acinpro — Derechos de autor',
                    'descripcion' => 'Comprobante de pago por la comunicación pública de música. Si eres afiliado a ASOBARES Quindío, el gremio puede revisar tu tarifa y darte acceso a descuentos con marcas aliadas.',
                    'checklist' => [
                        'Formulario de declaración del establecimiento',
                        'Comprobante de pago por comunicación pública de música',
                        'Consultar con ASOBARES la revisión de tarifa para afiliados',
                    ],
                ],
                [
                    'entidad' => 'Policía Nacional — Notificación de apertura',
                    'descripcion' => 'Solicitud escrita a la estación de policía correspondiente notificando la apertura de un establecimiento de comercio en la zona. Solo aplica a establecimientos abiertos después del año 2019.',
                    'checklist' => [
                        'Solicitud escrita dirigida a la estación de policía de la zona',
                        'Aplica únicamente si el establecimiento abrió después de 2019',
                    ],
                ],
                [
                    // La octava no sale de la jornada con la Alcaldía sino de la
                    // lista que el gremio lleva a la visita. Ese papel no está
                    // fechado, así que va sin `verificado_el` y la guía lo dice.
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
                    'verificado_el' => null,
                    'verificado_con' => self::FUENTE_GENERAL,
                ],
            ],
        ];
    }
}
