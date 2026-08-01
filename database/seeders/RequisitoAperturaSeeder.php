<?php

namespace Database\Seeders;

use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Database\Seeders\Support\GeneradorPdf;
use Illuminate\Database\Seeder;

/**
 * El producto insignia: la guía normativa por municipio.
 *
 * "Es el punto donde caen siempre los negocios y los cierran." Los costos y
 * algunos trámites cambian de un municipio a otro a propósito: esa diferencia
 * es justamente lo que ningún otro gremio documenta.
 */
class RequisitoAperturaSeeder extends Seeder
{
    public function run(GeneradorPdf $pdf): void
    {
        $formatoBomberos = $pdf->generar(
            'Solicitud de visita técnica — Cuerpo de Bomberos',
            'Formato de ejemplo · ASOBARES Capítulo Quindío',
            [
                '# Datos del establecimiento',
                'Razón social: ______________________________________________',
                'NIT o cédula: ______________________________________________',
                'Nombre comercial: __________________________________________',
                'Dirección: _________________________________________________',
                'Área del local (m2): _______   Aforo solicitado: ___________',
                '',
                '# Datos del solicitante',
                'Nombre completo: ___________________________________________',
                'Calidad en que actúa: propietario / representante legal',
                'Teléfono: ______________  Correo: __________________________',
                '',
                '# Documentos que se adjuntan',
                '(  ) Certificado de matrícula mercantil vigente',
                '(  ) Certificado de uso de suelos',
                '(  ) Plano o croquis del local con salidas de emergencia',
                '(  ) Certificado de recarga de extintores',
                '(  ) Constancia de mantenimiento eléctrico',
                '',
                '# Declaración',
                'Declaro que la información consignada es veraz y autorizo la',
                'visita técnica en el horario que disponga la entidad.',
                '',
                'Firma: __________________________   Fecha: _______________',
            ],
            'formatos',
            'formato-solicitud-visita-bomberos.pdf'
        );

        $formatoPolicia = $pdf->generar(
            'Registro de establecimiento abierto al público',
            'Formato de ejemplo · ASOBARES Capítulo Quindío',
            [
                '# Identificación del establecimiento',
                'Nombre comercial: __________________________________________',
                'Dirección: _________________________________________________',
                'Actividad principal: bar / gastrobar / discoteca / café',
                'Horario de funcionamiento solicitado: ______________________',
                '',
                '# Responsable',
                'Nombre: ____________________________________________________',
                'Documento: _________________  Teléfono: ____________________',
                '',
                '# Compromisos del establecimiento',
                '(  ) Respetar el horario autorizado por la autoridad local',
                '(  ) Prohibir el ingreso de menores de edad',
                '(  ) Mantener niveles de ruido dentro de la norma',
                '(  ) Contar con personal de seguridad identificado',
                '(  ) Exhibir de forma visible los documentos de funcionamiento',
                '',
                'Firma del responsable: ______________  Fecha: _____________',
            ],
            'formatos',
            'formato-registro-policia.pdf'
        );

        foreach ($this->guiaPorMunicipio($formatoBomberos, $formatoPolicia) as $slug => $requisitos) {
            $municipio = Municipio::where('slug', $slug)->firstOrFail();

            foreach ($requisitos as $orden => $requisito) {
                RequisitoApertura::updateOrCreate(
                    ['municipio_id' => $municipio->id, 'entidad' => $requisito['entidad']],
                    $requisito + ['municipio_id' => $municipio->id, 'orden' => $orden + 1]
                );
            }
        }
    }

    /** @return array<string, list<array<string, mixed>>> */
    private function guiaPorMunicipio(string $formatoBomberos, string $formatoPolicia): array
    {
        return [
            'armenia' => [
                [
                    'entidad' => 'Cámara de Comercio de Armenia y del Quindío',
                    'descripcion' => 'La matrícula mercantil es el primer paso: sin ella no puedes tramitar nada más. Se renueva cada año antes del 31 de marzo.',
                    'checklist' => [
                        'Formulario RUES diligenciado (persona natural o jurídica)',
                        'Cédula del propietario o del representante legal',
                        'RUT expedido por la DIAN',
                        'Consulta previa de homonimia del nombre comercial',
                        'Pago del derecho de matrícula según los activos declarados',
                    ],
                    'enlace_externo' => 'https://camaraarmenia.org.co',
                    'costo_aproximado' => 180000,
                ],
                [
                    'entidad' => 'Alcaldía de Armenia — Uso de suelos',
                    'descripcion' => 'El certificado de uso de suelos dice si en esa dirección se puede operar un establecimiento nocturno. Consúltalo ANTES de firmar el arriendo: es el error más caro y más común.',
                    'checklist' => [
                        'Solicitud de concepto de uso de suelo por dirección exacta',
                        'Certificado de matrícula mercantil',
                        'Croquis de ubicación del inmueble',
                        'Verificar la distancia mínima a colegios e iglesias',
                    ],
                    'enlace_externo' => 'https://armenia.gov.co',
                    'costo_aproximado' => 45000,
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos de Armenia',
                    'descripcion' => 'Concepto técnico de seguridad humana. Revisan extintores, señalización, salidas de emergencia y la instalación eléctrica. Descarga el formato, diligéncialo y radícalo para que agenden la visita.',
                    'checklist' => [
                        'Carta de solicitud de visita técnica diligenciada',
                        'Extintores vigentes y con recarga certificada',
                        'Señalización de rutas de evacuación y salidas',
                        'Botiquín de primeros auxilios dotado',
                        'Certificado de mantenimiento de la instalación eléctrica',
                        'Plan de emergencia y contingencia del establecimiento',
                    ],
                    'adjunto' => $formatoBomberos,
                    'adjunto_nombre' => 'Formato de solicitud de visita — Bomberos Armenia',
                    'costo_aproximado' => 120000,
                ],
                [
                    'entidad' => 'Sayco y Acinpro',
                    'descripcion' => 'Si suena música en tu establecimiento, causa derechos de autor y derechos conexos. Los afiliados a ASOBARES tienen tarifa preferencial negociada por el gremio.',
                    'checklist' => [
                        'Formulario de declaración del establecimiento',
                        'Indicar área del local y aforo real',
                        'Declarar el tipo de música y si hay ejecución en vivo',
                        'Presentar el carné de afiliación a ASOBARES para el descuento',
                    ],
                    'enlace_externo' => 'https://www.sayco.org',
                    'costo_aproximado' => 380000,
                ],
                [
                    'entidad' => 'Secretaría de Salud de Armenia',
                    'descripcion' => 'Concepto sanitario favorable. Obligatorio si manipulas alimentos o bebidas, es decir: prácticamente siempre.',
                    'checklist' => [
                        'Certificados de manipulación de alimentos de todo el personal',
                        'Concepto sanitario del establecimiento',
                        'Contrato vigente de control de plagas',
                        'Certificado de lavado y desinfección de tanques de agua',
                        'Plan de manejo de residuos sólidos',
                    ],
                    'costo_aproximado' => null,
                ],
                [
                    'entidad' => 'Policía Nacional — Control de horarios',
                    'descripcion' => 'Registro del establecimiento y verificación del horario autorizado. Es la entidad que más visita de noche: ten los papeles a la mano y visibles.',
                    'checklist' => [
                        'Formato de registro del establecimiento diligenciado',
                        'Aviso visible de prohibición de ingreso a menores de edad',
                        'Horario autorizado exhibido en la entrada',
                        'Documentos de funcionamiento disponibles en el local',
                        'Personal de seguridad debidamente identificado',
                    ],
                    'adjunto' => $formatoPolicia,
                    'adjunto_nombre' => 'Formato de registro de establecimiento — Policía',
                    'costo_aproximado' => null,
                ],
            ],

            'salento' => [
                [
                    'entidad' => 'Cámara de Comercio de Armenia y del Quindío',
                    'descripcion' => 'Salento se atiende desde la seccional de Armenia. Mismo trámite de matrícula mercantil, con atención presencial los martes en el municipio.',
                    'checklist' => [
                        'Formulario RUES diligenciado',
                        'Cédula del propietario o representante legal',
                        'RUT expedido por la DIAN',
                        'Consulta previa de homonimia del nombre comercial',
                    ],
                    'enlace_externo' => 'https://camaraarmenia.org.co',
                    'costo_aproximado' => 180000,
                ],
                [
                    'entidad' => 'Alcaldía de Salento — Uso de suelos',
                    'descripcion' => 'Salento es Paisaje Cultural Cafetero: el centro histórico tiene restricciones adicionales de fachada, aviso y niveles de ruido que no aplican en otros municipios.',
                    'checklist' => [
                        'Solicitud de concepto de uso de suelo',
                        'Concepto de la oficina de Planeación sobre intervención de fachada',
                        'Aprobación del diseño del aviso comercial',
                        'Verificar restricción de ruido en el perímetro del centro histórico',
                    ],
                    'costo_aproximado' => 38000,
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos de Salento',
                    'descripcion' => 'En Salento el concepto de bomberos cuesta cerca de $100.000 y la visita se agenda con menos frecuencia: solicítala con tiempo.',
                    'checklist' => [
                        'Carta de solicitud de visita técnica',
                        'Extintores vigentes con recarga certificada',
                        'Señalización de evacuación',
                        'Certificado de la instalación eléctrica',
                        'Consideraciones especiales para construcciones en bahareque y madera',
                    ],
                    'adjunto' => $formatoBomberos,
                    'adjunto_nombre' => 'Formato de solicitud de visita — Bomberos Salento',
                    'costo_aproximado' => 100000,
                ],
                [
                    'entidad' => 'Sayco y Acinpro',
                    'descripcion' => 'Tarifa nacional según área y aforo, con descuento para afiliados al gremio.',
                    'checklist' => [
                        'Formulario de declaración del establecimiento',
                        'Área del local y aforo real',
                        'Declarar si hay música en vivo',
                        'Carné de afiliación a ASOBARES para el descuento',
                    ],
                    'enlace_externo' => 'https://www.sayco.org',
                    'costo_aproximado' => 290000,
                ],
                [
                    'entidad' => 'Secretaría de Salud del Quindío',
                    'descripcion' => 'En municipios sin secretaría propia, el concepto sanitario lo emite la Secretaría departamental.',
                    'checklist' => [
                        'Certificados de manipulación de alimentos del personal',
                        'Concepto sanitario del establecimiento',
                        'Control de plagas vigente',
                        'Certificado de potabilidad del agua',
                    ],
                    'costo_aproximado' => null,
                ],
                [
                    'entidad' => 'Policía Nacional — Control de horarios',
                    'descripcion' => 'El horario en Salento es más restrictivo que en Armenia por la vocación turística del municipio. Confirma el horario vigente antes de programar tu operación.',
                    'checklist' => [
                        'Formato de registro del establecimiento',
                        'Aviso de prohibición de ingreso a menores',
                        'Horario autorizado exhibido en la entrada',
                        'Documentos de funcionamiento en el local',
                    ],
                    'adjunto' => $formatoPolicia,
                    'adjunto_nombre' => 'Formato de registro de establecimiento — Policía',
                    'costo_aproximado' => null,
                ],
            ],

            'filandia' => [
                [
                    'entidad' => 'Cámara de Comercio de Armenia y del Quindío',
                    'descripcion' => 'Matrícula mercantil para Filandia, tramitada en la seccional de Armenia o en las jornadas móviles del municipio.',
                    'checklist' => [
                        'Formulario RUES diligenciado',
                        'Cédula del propietario o representante legal',
                        'RUT expedido por la DIAN',
                        'Consulta de homonimia',
                    ],
                    'enlace_externo' => 'https://camaraarmenia.org.co',
                    'costo_aproximado' => 180000,
                ],
                [
                    'entidad' => 'Alcaldía de Filandia — Uso de suelos',
                    'descripcion' => 'El centro histórico de Filandia también está protegido. Los balcones y fachadas tienen norma propia y el aviso comercial requiere aprobación.',
                    'checklist' => [
                        'Solicitud de concepto de uso de suelo',
                        'Aprobación de intervención de fachada si aplica',
                        'Diseño del aviso comercial aprobado por Planeación',
                        'Verificación de aforo permitido para el inmueble',
                    ],
                    'costo_aproximado' => 35000,
                ],
                [
                    'entidad' => 'Cuerpo de Bomberos de Filandia',
                    'descripcion' => 'Concepto técnico de seguridad humana, con énfasis en las construcciones tradicionales de madera del centro.',
                    'checklist' => [
                        'Carta de solicitud de visita técnica',
                        'Extintores vigentes',
                        'Señalización de evacuación',
                        'Certificado eléctrico',
                        'Medidas reforzadas para inmuebles en madera',
                    ],
                    'adjunto' => $formatoBomberos,
                    'adjunto_nombre' => 'Formato de solicitud de visita — Bomberos Filandia',
                    'costo_aproximado' => 95000,
                ],
                [
                    'entidad' => 'Sayco y Acinpro',
                    'descripcion' => 'Declaración y pago de derechos de autor y conexos según área y aforo.',
                    'checklist' => [
                        'Formulario de declaración',
                        'Área y aforo del establecimiento',
                        'Declarar música en vivo si aplica',
                        'Carné de afiliación a ASOBARES',
                    ],
                    'enlace_externo' => 'https://www.sayco.org',
                    'costo_aproximado' => 270000,
                ],
                [
                    'entidad' => 'Secretaría de Salud del Quindío',
                    'descripcion' => 'Concepto sanitario emitido por la Secretaría departamental.',
                    'checklist' => [
                        'Certificados de manipulación de alimentos',
                        'Concepto sanitario',
                        'Control de plagas vigente',
                        'Manejo de residuos sólidos',
                    ],
                    'costo_aproximado' => null,
                ],
                [
                    'entidad' => 'Policía Nacional — Control de horarios',
                    'descripcion' => 'Registro y verificación de horarios. Filandia aplica restricciones especiales en temporada alta y festivos.',
                    'checklist' => [
                        'Formato de registro del establecimiento',
                        'Aviso de prohibición de ingreso a menores',
                        'Horario autorizado visible',
                        'Documentos de funcionamiento en el local',
                    ],
                    'adjunto' => $formatoPolicia,
                    'adjunto_nombre' => 'Formato de registro de establecimiento — Policía',
                    'costo_aproximado' => null,
                ],
            ],
        ];
    }
}
