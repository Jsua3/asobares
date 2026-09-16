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
 * ⚠️ **Todo sale de la fuente oficial.** Ni costos inventados ni formatos PDF
 * rotulados «Formato de ejemplo»: publicar cifras equivocadas de trámites
 * legales en una URL con el nombre del gremio encima es un riesgo del gremio,
 * no del equipo.
 *
 * Los siete trámites de Armenia son, **literalmente**, el bloque transcrito en
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
 * - **Ningún `costo_aproximado`.** El documento oficial no trae ni una cifra,
 *   e inventarlas sería publicar costos sin respaldo.
 * - **Solo Armenia.** Para Salento y Filandia no hay documento. Una guía
 *   incompleta y cierta vale más que una completa e inventada; el §5 de aquel
 *   documento cuenta los otros once municipios como pendiente del gremio.
 * - **Sin `adjunto`.** Un formato de ejemplo con el nombre del gremio encima
 *   no es un formato de la entidad. Los reales hay que pedirlos, y el gremio
 *   los sube desde el panel.
 *
 * ⚠️ **Pendiente:** el §5 de ese mismo documento pide que la dirección
 * confirme por escrito que esta es la versión vigente antes de publicar,
 * «porque es información que un empresario va a usar para decidir si abre o
 * no». Aquí salen publicadas --con su fuente y su fecha a la vista, que es la
 * salvaguarda mínima-- pero esa confirmación sigue debiéndose, y desde el
 * panel se pasan a borrador en un clic.
 *
 * Nota para quien resiembre sobre una base ya poblada: `updateOrCreate` va por
 * `(municipio_id, entidad)` y **no borra nada**, así que las fichas de
 * Salento y Filandia de siembras anteriores siguen ahí. En desarrollo se
 * limpian con `migrate:fresh --seed`; en producción la tabla nació vacía.
 */
class RequisitoAperturaSeeder extends Seeder
{
    /** La fuente, tal cual, para que quede en la ficha y se pueda auditar. */
    public const string FUENTE_ARMENIA = 'Documento oficial de la Alcaldía de Armenia, campaña «Blindemos tu Negocio», entregado al gremio el 20 de agosto de 2026';

    public const string FUENTE_GENERAL = 'Requerimientos básicos generales — Asobares Capítulo Quindío (ley 1801 de 2016 y decreto 119)';

    /** El día en que el gremio entregó el documento. Ver §3 del documento citado. */
    public const string VERIFICADO_EL = '2026-08-20';

    /**
     * El archivo con el que el gremio pidió levantar los otros once municipios.
     *
     * ⚠️ **Corto a propósito.** `verificado_con` es `varchar(255)` y aquí se le
     * concatena la norma de cada trámite. La primera redacción daba **254 de
     * 255** en la ficha más larga: en SQLite pasaba —ignora el límite— y en el
     * PostgreSQL de producción habría reventado el sembrador a mitad con
     * «value too long for type character varying(255)». La suite corre sobre
     * SQLite, así que ninguna prueba lo habría visto.
     *
     * `EnCuantoCabeLaProcedenciaTest` lo mide ahora. Si esta frase crece o
     * llega una norma más larga, esa guardia se pone roja antes del despliegue.
     */
    public const string FUENTE_EXCEL = 'Guía normativa «Abre tu negocio», ASOBARES Capítulo Quindío, 15 de septiembre de 2026';

    /** El día en que el gremio entregó ese archivo. Mismo criterio que Armenia. */
    public const string VERIFICADO_EL_EXCEL = '2026-09-15';

    /**
     * Los bloques por municipio salen publicados, pero SIN `verificado_el`, asi
     * que la guía les pinta «Sin verificar contra la fuente oficial» en la cara.
     *
     * Es exactamente para lo que se creó esa columna en RF-60. El archivo del
     * gremio trae las doce filas en «Pendiente» y sus celdas de costo y enlace
     * rotuladas «a confirmar»: publicarlas calladas sería mentir, y guardarlas
     * dejaría la guía en un municipio de doce.
     *
     * Para dejarlas en borrador hasta que la dirección confirme (D-21), cambia
     * esta constante a `EstadoPublicacion::Borrador`. Es la única línea.
     */
    public const EstadoPublicacion ESTADO_POR_MUNICIPIO = EstadoPublicacion::Publicado;

    /**
     * Los nueve trámites de norma nacional, iguales en los doce municipios.
     *
     * Salen de la hoja «Marco general» del archivo del gremio del 15 de
     * septiembre. Se repiten en cada municipio porque una ficha pertenece a un
     * municipio: el esquema no tiene concepto de «nacional», y quien abre en
     * Pijao necesita la lista entera, no media.
     *
     * Cada uno cita su norma en `verificado_con`, que es lo que lo hace
     * auditable: el lector puede ir a la ley y comprobarlo.
     *
     * @var list<array{entidad: string, descripcion: string, checklist: list<string>, base: string}>
     */
    public const array MARCO_GENERAL = [
        [
            'entidad' => 'Registro Único Tributario (RUT) — DIAN',
            'descripcion' => 'Antes de iniciar operación, y actualizarlo si cambia la actividad económica. Se solicita junto con la matrícula mercantil en la Cámara de Comercio de Armenia y del Quindío (presencial o virtual, cualquier sede municipal).',
            'checklist' => [
                'Documento de identidad',
                'Datos del establecimiento y actividad económica (código CIIU: bar, discoteca, gastrobar, etc.)',
                'Vigencia: Permanente; se actualiza ante cambios',
            ],
            'base' => 'Estatuto Tributario; trámite unificado con Cámara de Comercio',
        ],
        [
            'entidad' => 'Matrícula Mercantil — Cámara de Comercio de Armenia y del Quindío',
            'descripcion' => 'Antes de abrir el establecimiento. Presentarse en cualquier sede de la CCAQ (Armenia o sedes municipales) o realizar el trámite virtual; la Cámara notifica de oficio a Bomberos, Tesorería/Rentas y Sayco-Acinpro (solo para domicilio Armenia; en otros municipios cada comerciante debe notificar directamente).',
            'checklist' => [
                'Cédula',
                'RUT (o solicitarlo en el mismo trámite)',
                'Definición clara de la actividad mercantil a registrar',
                'Vigencia: Debe renovarse cada año, dentro de los 3 primeros meses',
            ],
            'base' => 'Código de Comercio; Ley 232 de 1995',
        ],
        [
            'entidad' => 'Concepto de Uso del Suelo — Secretaría de Planeación de cada municipio',
            'descripcion' => 'Antes de firmar el contrato de arrendamiento o adecuar el local, y de matricularlo. Solicitud escrita o virtual en Planeación Municipal; la entidad certifica si la actividad está permitida en esa zona.',
            'checklist' => [
                'Dirección exacta del local',
                'Actividad a desarrollar (bar/discoteca/gastrobar)',
                'Plan de Ordenamiento Territorial (POT) o Esquema de Ordenamiento (EOT) vigente del municipio',
                'Vigencia: La certifica Planeación de cada municipio; revisar si vence o es indefinida mientras no cambie el POT',
            ],
            'base' => 'Ley 388 de 1997 (POT); Ley 232 de 1995',
        ],
        [
            'entidad' => 'Comunicación de apertura a la Policía — Estación o Subestación de Policía de la jurisdicción',
            'descripcion' => 'Al momento de abrir el establecimiento (trámite único). Carta dirigida al comandante de estación/subestación informando la apertura.',
            'checklist' => [
                'Nombre del establecimiento',
                'Dirección',
                'Actividad y horarios',
                'Vigencia: Trámite único, no se renueva',
            ],
            'base' => 'Ley 232 de 1995; Código Nacional de Seguridad y Convivencia Ciudadana (Ley 1801 de 2016)',
        ],
        [
            'entidad' => 'Concepto Técnico de Bomberos — Cuerpo de Bomberos del municipio donde está el establecimiento',
            'descripcion' => 'Al abrir, y luego en las visitas periódicas de inspección. Solicitar visita técnica; Bomberos inspecciona en ~15 días hábiles y expide concepto favorable o requerimientos a subsanar (30 días calendario).',
            'checklist' => [
                'RUT, matrícula mercantil, datos de contacto del establecimiento',
                'Cumplir condiciones de seguridad humana y contraincendios',
                'Vigencia: Sujeto a inspecciones periódicas según nivel de riesgo del establecimiento',
            ],
            'base' => 'Ley 1575 de 2012; Resolución 0661 de 2014 del Ministerio del Interior',
        ],
        [
            'entidad' => 'Concepto Sanitario — Secretaría de Salud municipal o departamental',
            'descripcion' => 'Al abrir, si el establecimiento prepara/expende alimentos o bebidas. Solicitar visita de inspección sanitaria ante la Secretaría de Salud correspondiente.',
            'checklist' => [
                'Condiciones sanitarias del local',
                'Manipulación de alimentos si aplica',
                'Carnés de manipulación vigentes del personal',
                'Vigencia: Sujeto a visitas de inspección, vigilancia y control (IVC) periódicas',
            ],
            'base' => 'Ley 09 de 1979; Decreto 3075 de 1997',
        ],
        [
            'entidad' => 'Derechos de Autor (Sayco-Acinpro) — Organización Sayco-Acinpro',
            'descripcion' => 'Al abrir, si se ejecutará música u obras protegidas en el establecimiento. Afiliarse y pagar la tarifa correspondiente, o tramitar certificado de no uso si no se reproduce música protegida.',
            'checklist' => [
                'Datos del establecimiento y aforo',
                'Tipo de música/eventos',
                'Vigencia: Renovación anual',
            ],
            'base' => 'Ley 23 de 1982 (derechos de autor)',
        ],
        [
            'entidad' => 'Registro Nacional de Turismo (si aplica) — Ministerio de Comercio, Industria y Turismo',
            'descripcion' => 'Si el establecimiento se ofrece como prestador de servicios turísticos (bar/discoteca dentro de oferta turística). Registro virtual en la plataforma del RNT.',
            'checklist' => [
                'Matrícula mercantil vigente',
                'Información del establecimiento',
                'Vigencia: Renovación anual',
            ],
            'base' => 'Ley 1101 de 2006',
        ],
        [
            'entidad' => 'Cumplimiento de horarios y ruido — Alcaldía municipal / Policía Nacional',
            'descripcion' => 'Operación permanente. Verificar el Código de Policía municipal y las normas de convivencia vigentes en cada Alcaldía.',
            'checklist' => [
                'Conocer el horario autorizado para venta y consumo de licor y los límites de ruido aplicables al municipio',
                'Vigencia: Permanente, sujeto a control',
            ],
            'base' => 'Ley 1801 de 2016 (Código Nacional de Seguridad y Convivencia Ciudadana)',
        ],
    ];

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

        $this->sembrarLosOnceMunicipiosDelExcel();
    }

    /**
     * Los once municipios que faltaban, desde el archivo del gremio del 15 sep.
     *
     * Cada uno recibe el marco general (nueve trámites de norma nacional) y
     * después sus cuatro bloques locales. El orden no es decorativo: primero
     * qué hay que hacer, luego a qué puerta de este municipio se toca.
     */
    private function sembrarLosOnceMunicipiosDelExcel(): void
    {
        foreach ($this->guiaPorMunicipioDelExcel() as $slug => $bloques) {
            $municipio = Municipio::where('slug', $slug)->firstOrFail();
            $orden = 0;

            foreach (self::MARCO_GENERAL as $tramite) {
                RequisitoApertura::updateOrCreate(
                    ['municipio_id' => $municipio->id, 'entidad' => $tramite['entidad']],
                    [
                        'descripcion' => $tramite['descripcion'],
                        'checklist' => $tramite['checklist'],
                        'municipio_id' => $municipio->id,
                        'orden' => ++$orden,
                        'estado' => EstadoPublicacion::Publicado,
                        // El archivo no trae ni una cifra por trámite. No se rellenan.
                        'costo_aproximado' => null,
                        'verificado_el' => self::VERIFICADO_EL_EXCEL,
                        'verificado_con' => self::FUENTE_EXCEL.'; base normativa: '.$tramite['base'],
                    ]
                );
            }

            foreach ($bloques as $bloque) {
                RequisitoApertura::updateOrCreate(
                    ['municipio_id' => $municipio->id, 'entidad' => $bloque['entidad']],
                    $bloque + [
                        'municipio_id' => $municipio->id,
                        'orden' => ++$orden,
                        'estado' => self::ESTADO_POR_MUNICIPIO,
                        'costo_aproximado' => null,
                        // Sin fecha a propósito: el archivo dice «Pendiente».
                        'verificado_el' => null,
                        'verificado_con' => self::FUENTE_EXCEL.' (hoja «Por municipio», fila sin verificar)',
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

    /**
     * Lo que cambia de un municipio a otro: qué pide cada entidad local.
     *
     * Hoja «Por municipio» del mismo archivo. Armenia NO está aquí: sus ocho
     * fichas salen del documento de la Alcaldía y están verificadas desde el 20
     * de agosto, así que seguirían ganando a esto.
     *
     * Lo que se dejó fuera a propósito, y por qué:
     *
     * - **La columna de costos y vigencias.** El archivo la rotula «a confirmar»
     *   y su propia hoja de instrucciones dice que publicar cifras sin verificar
     *   es un riesgo para el asociado. Es el defecto que ya se limpio una vez.
     * - **Dieciocho de los veinticinco enlaces.** Apuntan a la portada de la
     *   alcaldía, no al trámite (OBS3-10). Solo entran los que `enlaceEsPuntual`
     *   reconoce y además vienen en la celda de su propia entidad.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function guiaPorMunicipioDelExcel(): array
    {
        return [
            // Calarcá
            'calarca' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación que valida la compatibilidad de la actividad comercial (establecimientos nocturnos, expendio de licores, restaurantes, bares) conforme al Plan de Ordenamiento Territorial (POT) del municipio',
                    'checklist' => [
                        'Formulario oficial de solicitud radicado ante la Secretaría de Planeación Municipal',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento',
                        'Paz y salvo del Impuesto Predial o certificación de no declarante',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la obtención del Certificado de Cumplimiento de Normas de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita de inspección técnica dirigida al Comandante del Cuerpo de Bomberos',
                        'Extintores portátiles multipropósito con recarga y mantenimiento vigente (ajustados a m² y nivel de riesgo)',
                        'Señalización de rutas de evacuación, salidas de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia)',
                        'Certificado de instalaciones eléctricas y de gas (si aplica)',
                    ],
                    'enlace_externo' => 'https://tramitescalarca-quindio.gov.co/tramites/',
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de Visita Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Diligenciamiento de la Ficha de Inscripción de Establecimientos Comerciales',
                        'Fotocopia del documento de identidad del representante legal / propietario',
                        'Copia del RUT actualizado y Matrícula Mercantil de la Cámara de Comercio',
                        'Plan de Saneamiento Básico implementado (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Agua Potable)',
                        'Certificados de capacitación en manipulación higiénica de alimentos de los colaboradores (si aplica)',
                    ],
                    'enlace_externo' => 'https://tramitescalarca-quindio.gov.co/tramites/',
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de Calarcá',
                    'checklist' => [
                        'Diligenciamiento del formulario RIT en el portal tributario dentro de los 30 días posteriores al inicio de actividades',
                        'Copia del Registro Único Tributario (RUT) con los códigos CIIU autorizados',
                        'Documento de identidad del representante legal',
                        'Certificado de la Cámara de Comercio',
                    ],
                ],
            ],
            // Circasia
            'circasia' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, documento formal que certifica la compatibilidad de la actividad comercial (establecimientos de comercio, expendio de bebidas alcohólicas, restaurantes) con la reglamentación del Esquema de Ordenamiento Territorial (EOT) del municipio de Circasia',
                    'checklist' => [
                        'Formulario oficial de solicitud de Concepto de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal (Cámara de Comercio de Armenia y del Quindío, no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Cédula de ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal del impuesto predial (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la obtención del Certificado de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Circasia',
                        'Extintores de incendios adecuados al riesgo y área, con recarga y mantenimiento vigente',
                        'Demarcación de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) acorde al aforo',
                        'Certificación de buen estado de instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Calidad del Agua)',
                        'Certificados de capacitación en manipulación de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros de Circasia',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades',
                        'Copia del RUT actualizado con el código CIIU correspondiente a la actividad económica',
                        'Cédula del propietario o representante legal',
                        'Certificado de la Cámara de Comercio de Armenia y del Quindío',
                    ],
                ],
            ],
            // Córdoba
            'cordoba' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación que determina la compatibilidad de la actividad comercial (establecimientos de comercio, expendio de bebidas alcohólicas, restaurantes) con el Esquema de Ordenamiento Territorial (EOT) del municipio de Córdoba',
                    'checklist' => [
                        'Formulario oficial de solicitud de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio de Armenia y del Quindío (no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal por concepto del Impuesto Predial Unificado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la obtención del Certificado de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita de inspección técnica dirigida al Comandante del Cuerpo Voluntario de Bomberos de Córdoba',
                        'Extintores portátiles multipropósito vigentes (tipo ABC/Solkaflam según el área y nivel de riesgo)',
                        'Demarcación de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) proporcional al aforo',
                        'Certificación de buen estado de las instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de la Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Agua Potable)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de Córdoba',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades',
                        'Copia del Registro Único Tributario (RUT) con los códigos CIIU autorizados para la actividad económica',
                        'Fotocopia de la Cédula del titular o representante legal',
                        'Certificado de la Cámara de Comercio de Armenia y del Quindío',
                    ],
                ],
            ],
            // Buenavista
            'buenavista' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación que valida la compatibilidad de la actividad comercial (establecimientos de comercio, bares, restaurantes, expendio de bebidas alcohólicas) conforme al Esquema de Ordenamiento Territorial (EOT) del municipio de Buenavista',
                    'checklist' => [
                        'Formulario oficial de solicitud de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal (Cámara de Comercio de Armenia y del Quindío, no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal del impuesto predial (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la obtención del Certificado de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Buenavista',
                        'Extintores portátiles multipropósito vigentes (tipo ABC/Solkaflam acordes al área y nivel de riesgo)',
                        'Demarcación de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) proporcional al aforo',
                        'Certificación de buen estado de instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Calidad del Agua)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros de Buenavista',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades',
                        'Copia del Registro Único Tributario (RUT) con el código CIIU correspondiente a la actividad económica',
                        'Cédula del propietario o representante legal',
                        'Certificado de la Cámara de Comercio de Armenia y del Quindío',
                    ],
                ],
            ],
            // Filandia
            'filandia' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación formal que valida la compatibilidad de la actividad comercial (establecimientos nocturnos, restaurantes, bares, expendio de bebidas alcohólicas) con la normativa del Esquema de Ordenamiento Territorial (EOT) del municipio de Filandia',
                    'checklist' => [
                        'Formulario oficial de solicitud de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal (Cámara de Comercio de Armenia y del Quindío, vigencia no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal por concepto de Impuesto Predial Unificado',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la emisión del Certificado de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Filandia',
                        'Extintores portátiles multipropósito con recarga y mantenimiento vigente (tipo ABC/Solkaflam según el área y nivel de riesgo)',
                        'Demarcación clara de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) proporcional al aforo',
                        'Certificación de buen estado de instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Calidad del Agua)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros de Filandia',
                    'checklist' => [
                        'Diligenciamiento del formato oficial de inscripción en el Registro de Industria y Comercio dentro de los 30 días hábiles posteriores al inicio de actividades',
                        'Copia del Registro Único Tributario (RUT) con los códigos CIIU correspondientes a la actividad comercial',
                        'Fotocopia de la Cédula del titular o representante legal',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio',
                    ],
                ],
            ],
            // Génova
            'genova' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación formal que valida la compatibilidad de la actividad comercial (establecimientos nocturnos, bares, restaurantes, expendio de bebidas alcohólicas) con la reglamentación del Esquema de Ordenamiento Territorial (EOT) del municipio de Génova',
                    'checklist' => [
                        'Formulario oficial de solicitud de Concepto de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal (Cámara de Comercio de Armenia y del Quindío, no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal por concepto del Impuesto Predial Unificado',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la emisión del Certificado de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Génova',
                        'Extintores portátiles multipropósito vigentes (tipo ABC/Solkaflam acordes al área y nivel de riesgo)',
                        'Demarcación de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) proporcional al aforo',
                        'Certificación de buen estado de instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Calidad del Agua)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de Génova',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades',
                        'Copia del Registro Único Tributario (RUT) con los códigos CIIU autorizados',
                        'Fotocopia de la Cédula del titular o representante legal',
                        'Certificado de la Cámara de Comercio de Armenia y del Quindío',
                    ],
                ],
            ],
            // La Tebaida
            'la-tebaida' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación formal que evalúa y valida la compatibilidad de la actividad comercial (establecimientos nocturnos, restaurantes, bares, expendio de bebidas alcohólicas) con la reglamentación del Plan de Básico de Ordenamiento Territorial (PBOT) del municipio de La Tebaida',
                    'checklist' => [
                        'Formulario oficial de solicitud de Concepto de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio de Armenia y del Quindío (con vigencia no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo del Impuesto Predial Unificado del inmueble donde opera el negocio',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la emisión del Certificado de Cumplimiento de Normas de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita o virtual de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos',
                        'Extintores portátiles multipropósito con mantenimiento y recarga vigente (clasificados según m² y nivel de riesgo)',
                        'Marcación de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) acorde al aforo del lugar',
                        'Certificado de buen estado y seguridad de las instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de la Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Calidad del Agua)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal del establecimiento (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo tributario del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de La Tebaida',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades',
                        'Copia del Registro Único Tributario (RUT) con los códigos CIIU habilitados',
                        'Cédula del propietario o representante legal',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio',
                    ],
                ],
            ],
            // Montenegro
            'montenegro' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación formal que evalúa y verifica la compatibilidad de la actividad comercial (establecimientos de comercio, bares, restaurantes, discotecas, expendio de bebidas alcohólicas) con la reglamentación del Plan de Básico de Ordenamiento Territorial (PBOT) del municipio de Montenegro',
                    'checklist' => [
                        'Formulario oficial de solicitud de Concepto de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio de Armenia y del Quindío (vigencia no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal del Impuesto Predial Unificado correspondiente al inmueble',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la emisión del Certificado de Cumplimiento de Normas de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita o presencial de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Montenegro',
                        'Extintores portátiles multipropósito vigentes (tipo ABC/Solkaflam calculados según área en m² y nivel de riesgo)',
                        'Marcación visible de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) proporcional al aforo del establecimiento',
                        'Certificación técnica de buen estado de las instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de la Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Control de Agua Potable)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo tributario del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de Montenegro',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades comerciales',
                        'Copia del Registro Único Tributario (RUT) con el código CIIU correspondiente a la actividad económica',
                        'Fotocopia de la Cédula del propietario o representante legal',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio',
                    ],
                ],
            ],
            // Pijao
            'pijao' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación formal que evalúa y valida la compatibilidad de la actividad comercial (establecimientos nocturnos, bares, restaurantes, expendio de bebidas alcohólicas) con la reglamentación del Esquema de Ordenamiento Territorial (EOT) del municipio de Pijao',
                    'checklist' => [
                        'Formulario oficial de solicitud de Concepto de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio de Armenia y del Quindío (con vigencia no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo del Impuesto Predial Unificado del inmueble',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la emisión del Certificado de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita o presencial de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Pijao',
                        'Extintores portátiles multipropósito vigentes (tipo ABC/Solkaflam calculados según el área en m² y nivel de riesgo)',
                        'Demarcación de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) acorde al aforo',
                        'Certificación de buen estado de las instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de la Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Agua Potable)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de Pijao',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades',
                        'Copia del Registro Único Tributario (RUT) con los códigos CIIU autorizados para la actividad económica',
                        'Fotocopia de la Cédula del titular o representante legal',
                        'Certificado de la Cámara de Comercio de Armenia y del Quindío',
                    ],
                ],
            ],
            // Quimbaya
            'quimbaya' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación formal que evalúa y verifica la compatibilidad de la actividad comercial (establecimientos nocturnos, bares, restaurantes, discotecas, expendio de bebidas alcohólicas) con la reglamentación del Plan de Básico de Ordenamiento Territorial (PBOT) del municipio de Quimbaya',
                    'checklist' => [
                        'Formulario oficial de solicitud de Concepto de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio de Armenia y del Quindío (vigencia no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal por concepto del Impuesto Predial Unificado',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la emisión del Certificado de Cumplimiento de Normas de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita o presencial de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Quimbaya',
                        'Extintores portátiles multipropósito vigentes (tipo ABC/Solkaflam calculados según el área en m² y nivel de riesgo)',
                        'Demarcación clara de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) proporcional al aforo del establecimiento',
                        'Certificación técnica de buen estado de las instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de la Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Control de Agua Potable)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de Quimbaya',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades comerciales',
                        'Copia del Registro Único Tributario (RUT) con el código CIIU correspondiente a la actividad económica',
                        'Fotocopia de la Cédula del propietario o representante legal',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio',
                    ],
                ],
            ],
            // Salento
            'salento' => [
                [
                    'entidad' => 'Secretaría de Planeación — uso del suelo',
                    'descripcion' => 'Solicitud y expedición del Concepto de Uso del Suelo, certificación formal que evalúa y verifica la compatibilidad de la actividad comercial (establecimientos nocturnos, bares, restaurantes, discotecas, hostales, expendio de bebidas alcohólicas) con la reglamentación del Esquema de Ordenamiento Territorial (EOT) del municipio de Salento',
                    'checklist' => [
                        'Formulario oficial de solicitud de Concepto de Uso de Suelo',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio de Armenia y del Quindío (vigencia no mayor a 30 días)',
                        'Copia del Registro Único Tributario (RUT) actualizado',
                        'Fotocopia de la Cédula de Ciudadanía del propietario o representante legal',
                        'Certificado de Tradición y Libertad del inmueble o contrato de arrendamiento vigente',
                        'Paz y salvo municipal por concepto del Impuesto Predial Unificado',
                    ],
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos — seguridad humana y contra incendios',
                    'descripcion' => 'Inspección Técnica de Seguridad Humana y Protección contra Incendios para la emisión del Certificado de Cumplimiento de Normas de Seguridad Comercial',
                    'checklist' => [
                        'Solicitud escrita o presencial de inspección técnica dirigida a la comandancia del Cuerpo Voluntario de Bomberos de Salento',
                        'Extintores portátiles multipropósito vigentes (tipo ABC/Solkaflam calculados según el área en m² y nivel de riesgo)',
                        'Demarcación clara de rutas de evacuación, señalización de emergencia y luces de socorro operativas',
                        'Plan de Prevención, Preparación y Respuesta ante Emergencias (Plan de Contingencia) proporcional al aforo del establecimiento',
                        'Certificación técnica de buen estado de las instalaciones eléctricas y de gas (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Secretaría de Salud — concepto sanitario',
                    'descripcion' => 'Inscripción del Establecimiento Abierto al Público y programación de la Visita de Inspección Sanitaria para la emisión del Concepto Sanitario Favorable',
                    'checklist' => [
                        'Formulario oficial de inscripción de establecimientos comerciales',
                        'Documento de identidad del titular o representante legal',
                        'Copia del Registro Único Tributario (RUT) y Matrícula Mercantil de la Cámara de Comercio',
                        'Implementación del Plan de Saneamiento Básico (Programa de Limpieza y Desinfección, Control Integral de Plagas, Manejo de Residuos Sólidos y Control de Agua Potable)',
                        'Certificados de capacitación en manipulación higiénica de alimentos del personal contratado (si aplica)',
                    ],
                ],
                [
                    'entidad' => 'Tesorería y Rentas — RIT e Industria y Comercio',
                    'descripcion' => 'Inscripción obligatoria en el Registro de Información Tributaria (RIT) e incorporación al censo del Impuesto de Industria y Comercio (ICA), Avisos y Tableros del Municipio de Salento',
                    'checklist' => [
                        'Diligenciamiento de la solicitud de inscripción RIT dentro de los 30 días hábiles posteriores al inicio de actividades comerciales',
                        'Copia del Registro Único Tributario (RUT) con el código CIIU correspondiente a la actividad económica',
                        'Fotocopia de la Cédula del propietario o representante legal',
                        'Certificado de Existencia y Representación Legal de la Cámara de Comercio',
                    ],
                ],
            ],
        ];
    }
}
