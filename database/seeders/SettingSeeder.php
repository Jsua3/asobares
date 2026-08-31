<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Todo el contenido institucional del sitio (RNF-09). Si un texto se ve en
 * el sitio público, se edita aquí desde el panel, nunca en una vista Blade.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->ajustes() as $ajuste) {
            Setting::updateOrCreate(['clave' => $ajuste['clave']], $ajuste);
        }
    }

    /** @return list<array{clave: string, valor: string, tipo: string, grupo: string, etiqueta: string}> */
    private function ajustes(): array
    {
        return [
            // --- Identidad (lema oficial del capítulo) ---
            $this->texto('sitio_nombre', 'ASOBARES Capítulo Quindío', 'identidad', 'Nombre del sitio'),
            $this->texto('sitio_eslogan', 'La noche construye territorio', 'identidad', 'Lema del capítulo'),
            $this->texto('sitio_descripcion_corta', 'El gremio que representa, fortalece y dinamiza el sector nocturno, gastronómico y de entretenimiento del Quindío.', 'identidad', 'Descripción corta'),
            $this->largo('sitio_descripcion', 'Asociación de Bares de Colombia, Capítulo Quindío. Representamos a bares, gastrobares, cafés y discotecas del departamento ante las instituciones, y acompañamos a quien quiere abrir su establecimiento.', 'identidad', 'Descripción para buscadores'),

            // --- Inicio ---
            $this->texto('hero_titulo', 'La noche construye territorio', 'inicio', 'Título del hero'),
            $this->largo('hero_subtitulo', 'Somos el gremio que se sienta en la mesa con las instituciones y el que te explica, paso a paso, cómo abrir tu establecimiento sin que te lo cierren. Trabajamos por la dignificación de la vida nocturna del Quindío.', 'inicio', 'Subtítulo del hero'),
            $this->texto('hero_cta_directorio', 'Explora la noche', 'inicio', 'Botón hacia el directorio'),
            $this->texto('hero_cta_afiliate', 'Afíliate', 'inicio', 'Botón hacia afiliación'),
            $this->texto('cta_final_titulo', '¿Tu establecimiento todavía no es parte del gremio?', 'inicio', 'Título del cierre'),
            $this->largo('cta_final_texto', 'Afiliarte toma una conversación. Representación ante las instituciones, descuentos en derechos de autor, formación y orientación jurídica sin costo.', 'inicio', 'Texto del cierre'),

            /*
             * Los títulos de cada sección de la portada. Estaban cableados en
             * `publico/inicio.blade.php` mientras el resto del contenido ya
             * salía de aquí, así que la afirmación que se le hizo al gremio en
             * la revisión del 28 de agosto —«toda la página es completamente
             * editable», R22 02:53— era falsa justo en lo que estaban mirando.
             *
             * Van con prefijo `portada_` porque `guia_titulo` y `empleo_titulo`
             * ya existen para las páginas de la guía y de la bolsa: son otros
             * textos y no deben compartir clave con las tarjetas del inicio.
             */
            $this->texto('portada_cifras_titulo', 'La noche en cifras · Observatorio Económico', 'inicio', 'Portada · título de la franja de cifras'),
            $this->texto('portada_guia_titulo', 'Abre tu negocio', 'inicio', 'Portada · título de la tarjeta de la guía'),
            $this->texto('portada_empleo_titulo', 'Bolsa de empleo', 'inicio', 'Portada · título de la tarjeta de empleo'),
            $this->texto('portada_destacados_titulo', 'La noche del Quindío', 'inicio', 'Portada · título de los establecimientos destacados'),
            // OBS3-01: «Lo que gana tu establecimiento» le sonó al directivo
            // «como si estuviéramos vendiendo una lotería» (R22 03:05).
            $this->texto('portada_beneficios_titulo', 'Beneficios de pertenecer al gremio', 'inicio', 'Portada · título de beneficios'),
            $this->texto('portada_beneficios_intro', 'Cinco beneficios concretos por estar afiliado al capítulo.', 'inicio', 'Portada · entradilla de beneficios'),
            $this->largo('portada_guia_texto', 'Los requisitos reales para abrir un establecimiento, municipio por municipio, con checklist, costos y los formatos oficiales listos para descargar.', 'inicio', 'Portada · texto de la tarjeta de la guía'),
            $this->largo('portada_empleo_texto', 'Bartenders, chefs, meseros y administradores para la vida nocturna del Quindío. Publican solo los establecimientos asociados.', 'inicio', 'Portada · texto de la tarjeta de empleo'),
            $this->texto('portada_destacados_texto', 'Algunos de los establecimientos afiliados al gremio.', 'inicio', 'Portada · pie de los destacados'),
            $this->texto('portada_eventos_titulo', 'Próximos eventos del gremio', 'inicio', 'Portada · título de eventos'),
            $this->texto('portada_aliados_titulo', 'Aliados del capítulo', 'inicio', 'Portada · título de aliados'),
            // OBS3-04: las dos bandas de aliados llevan rótulo propio.
            $this->texto('portada_aliados_institucionales', 'Respaldo institucional', 'inicio', 'Portada · rótulo de aliados institucionales'),
            $this->texto('portada_aliados_comerciales', 'Convenios para afiliados', 'inicio', 'Portada · rótulo de aliados comerciales'),

            // --- Manifiesto (discurso del TED gremial) ---
            $this->texto('manifiesto_apertura', 'Nos conocen por la rumba. Pero hoy venimos a hablarles del territorio.', 'manifiesto', 'Frase de apertura'),
            $this->texto('manifiesto_cierre_titulo', 'Asobares no representa bares. Representa el Quindío que se vive de noche.', 'manifiesto', 'Frase de cierre'),
            $this->texto('manifiesto_cierre_firma', 'Construyendo un Quindío nocturno', 'manifiesto', 'Firma del cierre'),
            $this->texto('vision_titulo', 'En 10 años, el Quindío no tendrá bares. Tendrá momentos memorables.', 'manifiesto', 'Visión a 10 años'),
            $this->texto('vision_nota', 'No solo tragos: una experiencia.', 'manifiesto', 'Nota al margen de la visión'),
            $this->largo('vision_detalle', "Cada municipio, un distrito de experiencia — con señalización, iluminación y seguridad como proyecto de ciudad.\nCada empresario, un anfitrión formal, de clase mundial.", 'manifiesto', 'Detalle de la visión'),

            // Las dos barreras que el gremio nombra. Formato: Titular | Explicación.
            $this->largo('barreras', implode("\n", [
                'La informalidad nos compite en la calle | El consumo y la venta informal en el espacio público erosionan la competencia leal y deterioran la imagen del sector formal.',
                '17 años operando bajo un POT vencido | El Plan de Ordenamiento Territorial de Armenia es del 2009–2023. Sin usos de suelo claros para el sector, no hay dónde formalizar ni crecer.',
            ]), 'manifiesto', 'Barreras del sector (Titular | Explicación)'),

            $this->texto('iniciativas_titulo', 'Las iniciativas más importantes', 'manifiesto', 'Título de iniciativas'),
            $this->texto('iniciativas_intro', 'Lo que el gremio ya tiene en marcha para cumplir ese sueño.', 'manifiesto', 'Introducción de iniciativas'),

            // --- Cifras del Observatorio Económico (marzo 2026) ---
            $this->texto('cifra_empleo', '12,65 %', 'cifras', 'Participación en el empleo de Armenia'),
            $this->texto('cifra_empleo_detalle', 'del empleo de Armenia lo genera la economía nocturna', 'cifras', 'Detalle de la cifra de empleo'),
            $this->texto('cifra_ingreso', '$2.104.124', 'cifras', 'Ingreso medio mensual del sector'),
            $this->texto('cifra_ingreso_detalle', 'es el ingreso medio mensual en el sector', 'cifras', 'Detalle del ingreso'),
            $this->texto('cifra_informalidad', '72,82 %', 'cifras', 'Informalidad'),
            $this->texto('cifra_informalidad_detalle', 'de informalidad: el reto que el gremio quiere cerrar', 'cifras', 'Detalle de informalidad'),
            $this->texto('cifra_jovenes', '35,28 %', 'cifras', 'Trabajadores de 28 años o menos'),
            $this->texto('cifra_jovenes_detalle', 'de los trabajadores tiene 28 años o menos', 'cifras', 'Detalle de juventud'),
            $this->texto('cifra_afiliados', '60', 'cifras', 'Establecimientos afiliados'),

            // --- Quiénes somos ---
            // OBS3-11. Los quince textos de «Quiénes somos» que estaban
            // cableados. Si Natalia va a entregar la redaccion propia del
            // capitulo, la pagina tiene que aceptarla sin tocar codigo.
            $this->texto('quienes_titulo_historia', 'Cómo nació el capítulo', 'institucional', 'Quiénes somos · título de la historia'),
            $this->texto('quienes_titulo_que_hacemos', 'Qué hace el gremio', 'institucional', 'Quiénes somos · título de qué hace el gremio'),
            $this->texto('quienes_titulo_barreras', 'Lo que hoy nos frena', 'institucional', 'Quiénes somos · título de las barreras'),
            $this->texto('quienes_titulo_lineas', 'Nuestras líneas de trabajo', 'institucional', 'Quiénes somos · título de las líneas de trabajo'),
            $this->texto('quienes_titulo_armenia', 'Armenia Nocturna', 'institucional', 'Quiénes somos · título de la estrategia de Armenia'),
            $this->texto('quienes_titulo_direccion', 'La dirección', 'institucional', 'Quiénes somos · título de la dirección'),
            $this->texto('quienes_titulo_beneficios', 'Beneficios del afiliado', 'institucional', 'Quiénes somos · título de beneficios'),
            $this->texto('quienes_titulo_nacional', 'Somos el capítulo regional de Asobares Colombia', 'institucional', 'Quiénes somos · título del respaldo nacional'),
            $this->texto('quienes_rotulo_vision', 'Visión del sector en el Quindío', 'institucional', 'Quiénes somos · rótulo de la visión'),
            $this->largo('quienes_barreras_pie', 'Son los dos cuellos de botella que el gremio lleva a cada mesa con las instituciones.', 'institucional', 'Quiénes somos · pie de las barreras'),
            $this->largo('quienes_iniciativas_pie', 'Formulación › Escalando › En ejecución. El estado de cada iniciativa se actualiza desde el panel.', 'institucional', 'Quiénes somos · pie de las iniciativas'),
            $this->largo('quienes_lineas_intro', 'Todo lo que hace el capítulo se organiza alrededor de estos tres ejes.', 'institucional', 'Quiénes somos · entradilla de las líneas'),
            $this->texto('quienes_rotulo_programas', 'Programas', 'institucional', 'Quiénes somos · rótulo de programas'),
            $this->texto('quienes_cargo_presidente', 'Presidente', 'institucional', 'Quiénes somos · cargo del presidente'),
            $this->texto('quienes_cargo_directora', 'Directora ejecutiva', 'institucional', 'Quiénes somos · cargo de la directora'),
            $this->largo('quienes_historia', 'Somos una organización gremial que nace en Bogotá y llega al Quindío con la necesidad de afianzar la relación TURISMO – NOCHE. El capítulo se fundó el 14 de agosto de 2024 en Armenia y reúne a bares, gastrobares, cafés y discotecas del departamento alrededor de una idea simple: la vida nocturna es una industria que genera empleo, paga impuestos y merece ser tratada como tal.', 'institucional', 'Historia'),
            $this->largo('quienes_mision', 'Representar al sector de la vida nocturna del Quindío ante las instituciones públicas y privadas, para proponer como gremio: participar en las decisiones sobre horarios, ruido, orden público y formalización antes de que se tomen, y no reclamar después.', 'institucional', 'Misión'),
            $this->largo('quienes_que_hacemos', 'Trabajamos por una vida nocturna más diversa y por la dignificación del sector, para consolidarnos como el corazón nocturno del Eje Cafetero. Gestionamos con las Secretarías de Salud, Gobierno y Planeación; negociamos tarifas de derechos de autor; formamos a los equipos de nuestros afiliados; y construimos la guía normativa por municipio que hoy no tiene ningún otro gremio del país.', 'institucional', 'Qué hacemos'),
            $this->texto('quienes_vision', 'Hacia la transformación del Quindío en un paraíso nocturno seguro', 'institucional', 'Visión'),
            $this->texto('quienes_presidente', 'Jorge Iván Botero Ángel', 'institucional', 'Presidente'),
            $this->texto('quienes_directora', 'Natalia Gutiérrez', 'institucional', 'Directora ejecutiva'),
            $this->texto('quienes_fundacion', '14 de agosto de 2024', 'institucional', 'Fecha de fundación'),

            // Las tres líneas del plan de acción del capítulo. Formato por línea:
            // Nombre | Descripción | Programas separados por punto y coma.
            $this->largo('quienes_lineas', implode("\n", [
                'Seguridad | Promover esparcimiento seguro en el Quindío, articulados con las secretarías de gobierno de cada municipio. En 2024, el 73 % de los visitantes del departamento decidió hospedarse aquí: la seguridad y la convivencia son parte del atractivo turístico. | Sello Púrpura; Sello Seguro; Campañas de socialización',
                'Cultura | Incentivar espacios donde la cultura nocturna sea la protagonista. La identidad local se refleja en la música, la gastronomía y los cócteles: eso atrae turistas, estimula a bartenders y chefs, y apoya a artistas y productores de la región. | Ruta Coctelera «Quindío en copas»; Mercado Nocturno; Karaoke bajo las estrellas; Rock al Bosque; Navidad al Aire Libre',
                'Sostenibilidad | Reducir la huella ambiental del sector y apoyar a productores y proveedores locales, adelantándonos a la regulación en vez de reaccionar a ella. | Bares Verdes; Reciclaje Nocturno; Eventos Eco-Friendly',
            ]), 'institucional', 'Líneas de trabajo (Nombre | Descripción | Programas)'),

            $this->largo('quienes_estrategia_armenia', 'Armenia Nocturna es la propuesta del capítulo para la ciudad: articular a la Secretaría de Gobierno y a la de Desarrollo Económico alrededor de una vida económica nocturna regulada, segura y reconocida como industria.', 'institucional', 'Estrategia Armenia Nocturna'),
            $this->largo('quienes_programas_nacionales', "Tardeo en la ciudad\nMi destino, tu noche\nLa ruta del coctel\nPregunta por Ángela", 'institucional', 'Programas de la Nacional que aterriza el capítulo'),
            $this->texto('url_nacional', 'https://asobares.org', 'institucional', 'Sitio de Asobares Nacional'),

            // --- Contacto ---
            $this->texto('contacto_correo', 'asobaresquindio@asobares.org', 'contacto', 'Correo'),
            $this->texto('contacto_whatsapp', '573215549513', 'contacto', 'WhatsApp (formato internacional)'),
            $this->texto('contacto_whatsapp_visible', '321 5549513', 'contacto', 'WhatsApp para mostrar'),
            $this->texto('contacto_instagram', 'asobaresquindio', 'contacto', 'Usuario de Instagram'),
            $this->texto('contacto_direccion', 'Piso 3, Cámara de Comercio de Armenia y del Quindío', 'contacto', 'Dirección'),
            $this->texto('contacto_ciudad', 'Armenia, Quindío', 'contacto', 'Ciudad'),
            $this->texto('contacto_lat', '4.5378', 'contacto', 'Latitud de la oficina'),
            $this->texto('contacto_lng', '-75.6757', 'contacto', 'Longitud de la oficina'),
            $this->texto('contacto_correo_destino', 'asobaresquindio@asobares.org', 'contacto', 'Correo que recibe los formularios'),

            // --- Guía normativa ---
            // OBS3-10. Dos rotulos porque el enlace no siempre cumple lo mismo.
            $this->texto('guia_enlace_puntual', 'Ir al trámite', 'guia', 'Guía · enlace que abre el trámite exacto'),
            $this->texto('guia_enlace_portada', 'Sitio de la entidad', 'guia', 'Guía · enlace que solo abre el portal'),
            $this->texto('guia_titulo', 'Abre tu negocio sin que te lo cierren', 'guia', 'Título de la guía'),
            $this->largo('guia_intro', 'La normatividad cambia de un municipio a otro. Escoge el tuyo y revisa, entidad por entidad, qué te van a pedir, cuánto cuesta y qué formato tienes que descargar y llevar diligenciado.', 'guia', 'Introducción de la guía'),
            $this->largo('guia_descargo', 'Esta guía es orientativa y se actualiza con la información que cada entidad entrega al gremio. Los requisitos, costos y formatos pueden cambiar sin aviso: verifica siempre directamente con la entidad competente antes de iniciar tu trámite.', 'guia', 'Texto de descargo'),

            // --- Bolsa de empleo ---
            $this->texto('empleo_titulo', 'Bolsa de empleo del sector', 'empleo', 'Título'),
            $this->largo('empleo_intro', 'Bartenders, chefs, meseros y administradores para la vida nocturna del Quindío. Conseguir un buen bartender acá es lo más difícil; por eso el muro existe.', 'empleo', 'Introducción'),
            $this->texto('empleo_aviso', 'Solo los establecimientos asociados publican vacantes en este muro.', 'empleo', 'Aviso del muro'),

            // --- Artistas y proveedores ---
            $this->texto('artistas_titulo', 'Directorio de artistas', 'modulos', 'Título de artistas'),
            $this->largo('artistas_intro', 'DJs, bandas y solistas de la región. Son las once de la noche, se te cayó el DJ y necesitas uno: aquí está su género, su contacto y un video para escucharlo antes de llamar.', 'modulos', 'Introducción de artistas'),
            // OBS3-08. El acta ofrecía dos redacciones --«a convenir» o
            // «según el evento»--, así que la decide el gremio desde el panel.
            $this->texto('artistas_tarifa_leyenda', 'A convenir', 'modulos', 'Artistas · leyenda en lugar de la tarifa'),
            $this->texto('proveedores_titulo', 'Bolsa de proveedores', 'modulos', 'Título de proveedores'),
            $this->largo('proveedores_intro', 'Hielo, licores, alimentos, aseo, seguridad y mantenimiento. ¿Quién te arregla la campana de extracción un sábado? Aquí.', 'modulos', 'Introducción de proveedores'),

            // --- Boletín ---
            $this->texto('boletin_titulo', 'Boletín del gremio', 'boletin', 'Título del boletín'),
            $this->largo('boletin_intro', 'Publicamos poco y publicamos cuando hay algo que decir: cifras del Observatorio Económico que envía la Nacional, decisiones que afectan al sector y los proyectos en los que está trabajando el capítulo.', 'boletin', 'Introducción del boletín'),

            // --- Afiliación ---
            $this->texto('afiliate_titulo', 'Afíliate al gremio', 'afiliacion', 'Título'),
            $this->largo('afiliate_intro', 'Tu establecimiento deja de estar solo frente a una visita de control, una norma nueva o una tarifa de derechos de autor. Déjanos tus datos y te contactamos.', 'afiliacion', 'Introducción'),
            $this->largo('afiliate_como_funciona', "Nos escribes por el formulario o por WhatsApp.\nAgendamos una visita a tu establecimiento para conocerte.\nFirmas la afiliación y defines qué información tuya se publica en el directorio.\nQuedas dentro: representación, convenios, formación y orientación jurídica.", 'afiliacion', 'Cómo funciona'),

            // --- Legal ---
            $this->texto('politica_responsable', 'Asociación de Bares de Colombia — Capítulo Quindío', 'legal', 'Responsable del tratamiento'),
            $this->texto('politica_actualizacion', '1 de agosto de 2026', 'legal', 'Fecha de última actualización'),
        ];
    }

    /** @return array{clave: string, valor: string, tipo: string, grupo: string, etiqueta: string} */
    private function texto(string $clave, string $valor, string $grupo, string $etiqueta): array
    {
        return compact('clave', 'valor', 'grupo', 'etiqueta') + ['tipo' => 'string'];
    }

    /** @return array{clave: string, valor: string, tipo: string, grupo: string, etiqueta: string} */
    private function largo(string $clave, string $valor, string $grupo, string $etiqueta): array
    {
        return compact('clave', 'valor', 'grupo', 'etiqueta') + ['tipo' => 'text'];
    }
}
