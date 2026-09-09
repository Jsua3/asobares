<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\CifrasDelGremio;
use Illuminate\Database\Seeder;

/**
 * Todo el contenido institucional del sitio (RNF-09). Si un texto se ve en
 * el sitio público, se edita aquí desde el panel, nunca en una vista Blade.
 */
class SettingSeeder extends Seeder
{
    /**
     * Ajustes que existieron, ya no los lee nadie, y hay que quitar de la base.
     *
     * Retirar una clave de `ajustes()` no la borra de `settings`, y el panel arma
     * su formulario **desde la base**: sin esta lista, un ajuste jubilado le
     * seguiría apareciendo a la oficina en producción, ofreciéndose para editar y
     * sin cambiar nada al guardarse.
     *
     * Explícita y no «todo lo que no esté en `ajustes()`» a propósito: un borrado
     * por diferencia sobre datos reales es un modo de fallo demasiado caro para
     * ahorrarse tres líneas.
     *
     * @var list<string>
     */
    private const JUBILADOS = [
        // 9 sep 2026. `hero_resumen_corto` hace ese trabajo desde el rediseño del
        // hero; este párrafo se quedó sembrado y ninguna vista lo pintaba.
        'hero_subtitulo',

        // 9 sep 2026. Ninguna vista lo pintaba, así que el sitio nunca dijo «60»
        // --el expediente afirmaba que sí (D-18)--. Y publicar una cifra de
        // afiliados que la base no sostiene (48 filas) va contra la regla de que
        // en producción solo entra lo que salga de un documento oficial. El día
        // que el gremio fije la cifra, entra con su fuente y su vista.
        'cifra_afiliados',
    ];

    public function run(): void
    {
        Setting::query()->whereIn('clave', self::JUBILADOS)->delete();

        foreach ($this->ajustes() as $ajuste) {
            // Las cifras del gremio las escribe la oficina, no este archivo:
            // se crean si faltan y no se vuelven a tocar. Con `updateOrCreate`
            // un resembrado —para añadir un texto nuevo, por ejemplo— las
            // devolvía a vacío y la franja desaparecía sin aviso. D-14 sigue
            // abierta para el resto, que sí se sobrescribe a propósito.
            if ($ajuste['grupo'] === CifrasDelGremio::GRUPO) {
                Setting::firstOrCreate(['clave' => $ajuste['clave']], $ajuste);

                continue;
            }

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
            $this->texto('hero_frase_corta', 'Gremio, ciudad y noche en una sola voz.', 'inicio', 'Frase corta del hero'),
            $this->texto('hero_resumen_corto', 'Representamos la vida nocturna del Quindío con criterio, cultura y territorio.', 'inicio', 'Resumen corto del hero'),
            $this->texto('hero_video_rotulo', 'Video institucional', 'inicio', 'Rótulo del video del hero'),
            $this->texto('hero_video_titulo', 'ASOBARES Capítulo Quindío', 'inicio', 'Título del video del hero'),
            $this->texto('hero_video_detalle', 'Una mirada breve al gremio que mueve la noche, la cultura y el territorio.', 'inicio', 'Detalle del video del hero'),
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
            // ⚠️ Prometía «costos y los formatos oficiales listos para
            // descargar», y la guía ya no tiene ni lo uno ni lo otro: los
            // costos eran inventados y los formatos eran PDF rotulados
            // «Formato de ejemplo», y ambos se retiraron. Una tarjeta de
            // portada que promete lo que la página siguiente no da es la misma
            // clase de defecto que el «ya» del WhatsApp (OBS3-14).
            $this->largo('portada_guia_texto', 'Los requisitos reales para abrir un establecimiento, con la lista de lo que pide cada entidad, a quién se le pide y qué documento sale de ahí.', 'inicio', 'Portada · texto de la tarjeta de la guía'),
            $this->largo('portada_empleo_texto', 'Bartenders, chefs, meseros y administradores para la vida nocturna del Quindío. Publican solo los establecimientos asociados.', 'inicio', 'Portada · texto de la tarjeta de empleo'),
            $this->texto('portada_destacados_texto', 'Algunos de los establecimientos afiliados al gremio.', 'inicio', 'Portada · pie de los destacados'),
            $this->texto('portada_eventos_titulo', 'Próximos eventos del gremio', 'inicio', 'Portada · título de eventos'),
            $this->texto('portada_aliados_titulo', 'Aliados del capítulo', 'inicio', 'Portada · título de aliados'),
            // OBS3-04: las dos bandas de aliados llevan rótulo propio.
            $this->texto('portada_aliados_institucionales', 'Respaldo institucional', 'inicio', 'Portada · rótulo de aliados institucionales'),
            $this->texto('portada_aliados_comerciales', 'Convenios para afiliados', 'inicio', 'Portada · rótulo de aliados comerciales'),
            $this->texto('portada_videos_rotulo', 'ASOBARES en movimiento', 'inicio', 'Portada · rótulo de videos'),
            $this->texto('portada_videos_titulo', 'Historias cortas para sentir el gremio.', 'inicio', 'Portada · título de videos'),
            $this->largo('portada_videos_intro', 'Una banda audiovisual para mostrar recorridos, eventos, testimonios y momentos de la noche quindiana con un tono sobrio, local y cercano.', 'inicio', 'Portada · introducción de videos'),
            $this->texto('portada_videos_cta', 'Ver agenda del gremio', 'inicio', 'Portada · enlace de videos'),
            $this->texto('portada_video_1_titulo', 'La noche se mueve', 'inicio', 'Portada · video principal'),
            $this->texto('portada_video_1_detalle', 'Recorridos, eventos y voces del sector', 'inicio', 'Portada · detalle del video principal'),
            $this->texto('portada_video_2_titulo', 'Rutas del gremio', 'inicio', 'Portada · video secundario 1'),
            $this->texto('portada_video_2_detalle', 'Establecimientos, cocina, barra y cultura local', 'inicio', 'Portada · detalle del video secundario 1'),
            $this->texto('portada_video_3_titulo', 'Agenda viva', 'inicio', 'Portada · video secundario 2'),
            $this->texto('portada_video_3_detalle', 'Encuentros, formación y noches memorables', 'inicio', 'Portada · detalle del video secundario 2'),
            $this->texto('portada_videos_proxima_rotulo', 'Próxima pieza', 'inicio', 'Portada · rótulo de próxima pieza'),
            $this->largo('portada_videos_proxima_texto', 'Clips de afiliados, activaciones y memoria del capítulo.', 'inicio', 'Portada · texto de próxima pieza'),

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

            // --- El gremio en cifras (D-25, Acta 05): las teclea la oficina ---
            $this->texto(CifrasDelGremio::CLAVE_TITULO, 'El gremio en cifras', 'inicio', 'Portada · título de la franja de cifras del gremio'),
            ...$this->cifrasDelGremio(),

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
            // OBS3-14. El directivo pregunto «¿ese tiene respuesta?» y pidio
            // automatizarlo (R21 11:15-11:21). Automatizar WhatsApp no es
            // codigo de esta plataforma --es WhatsApp Business, del gremio--,
            // asi que lo que si esta en nuestra mano es no prometerlo. El
            // texto es editable para que el gremio ponga su horario real el
            // dia que lo tenga, o lo cambie entero si automatiza de verdad.
            $this->texto('contacto_whatsapp_aviso', 'Te responde una persona del equipo, no un contestador automático.', 'contacto', 'Aviso bajo el WhatsApp'),
            $this->texto('contacto_whatsapp_visible', '321 5549513', 'contacto', 'WhatsApp para mostrar'),
            $this->texto('contacto_instagram', 'asobaresquindio', 'contacto', 'Usuario de Instagram'),
            $this->texto('contacto_direccion', 'Piso 3, Cámara de Comercio de Armenia y del Quindío', 'contacto', 'Dirección'),
            $this->texto('contacto_ciudad', 'Armenia, Quindío', 'contacto', 'Ciudad'),
            $this->texto('contacto_lat', '4.5378', 'contacto', 'Latitud de la oficina'),
            $this->texto('contacto_lng', '-75.6757', 'contacto', 'Longitud de la oficina'),
            $this->texto('contacto_correo_destino', 'asobaresquindio@asobares.org', 'contacto', 'Correo que recibe los formularios'),
            $this->texto('contacto_titulo_pagina', 'Hablemos', 'contacto', 'Título de la página de contacto'),
            $this->largo('contacto_subtitulo', 'Contacto general, PQR, propuestas de alianza o solicitud para entrar a la bolsa de proveedores.', 'contacto', 'Introducción de contacto'),
            $this->texto('contacto_formulario_titulo', 'Escríbenos', 'contacto', 'Título del formulario de contacto'),
            $this->texto('contacto_oficina_titulo', 'La oficina', 'contacto', 'Título de datos de oficina'),

            // --- Directorio ---
            $this->texto('directorio_titulo', 'Directorio de establecimientos', 'directorio', 'Título del directorio'),
            $this->largo('directorio_intro', 'Bares, gastrobares, cafés y discotecas afiliados en el Quindío.', 'directorio', 'Introducción del directorio'),

            // --- Guía normativa ---
            // OBS3-10. Dos rotulos porque el enlace no siempre cumple lo mismo.
            $this->texto('guia_enlace_puntual', 'Ir al trámite', 'guia', 'Guía · enlace que abre el trámite exacto'),
            $this->texto('guia_enlace_portada', 'Sitio de la entidad', 'guia', 'Guía · enlace que solo abre el portal'),
            $this->texto('guia_titulo', 'Abre tu negocio sin que te lo cierren', 'guia', 'Título de la guía'),
            // Misma corrección que en `portada_guia_texto`: prometía «cuánto
            // cuesta y qué formato tienes que descargar», y la guía no tiene
            // costos ni formatos. Los tendrá cuando el gremio los cargue; ese
            // día se edita esta línea desde el panel, que es donde vive.
            $this->largo('guia_intro', 'La normatividad cambia de un municipio a otro. Escoge el tuyo y revisa, entidad por entidad, qué te van a pedir y ante quién se tramita.', 'guia', 'Introducción de la guía'),
            $this->largo('guia_descargo', 'Esta guía es orientativa y se actualiza con la información que cada entidad entrega al gremio. Los requisitos, costos y formatos pueden cambiar sin aviso: verifica siempre directamente con la entidad competente antes de iniciar tu trámite.', 'guia', 'Texto de descargo'),
            $this->largo('guia_selector_ayuda', 'Estamos levantando la guía municipio por municipio con la información que cada entidad entrega al gremio. Si falta el tuyo, escríbenos.', 'guia', 'Ayuda bajo el selector de municipio'),
            $this->texto('guia_cta_titulo', '¿Dudas con algún trámite?', 'guia', 'Título del llamado final'),
            $this->largo('guia_cta_texto', 'La orientación jurídica es gratuita para los afiliados, pero si estás empezando y todavía no haces parte del gremio, escríbenos igual: para eso existe esta guía.', 'guia', 'Texto del llamado final'),

            // --- Bolsa de empleo ---
            $this->texto('empleo_titulo', 'Bolsa de empleo del sector', 'empleo', 'Título'),
            $this->largo('empleo_intro', 'Bartenders, chefs, meseros y administradores para la vida nocturna del Quindío. Conseguir un buen bartender acá es lo más difícil; por eso el muro existe.', 'empleo', 'Introducción'),
            $this->texto('empleo_aviso', 'Solo los establecimientos asociados publican vacantes en este muro.', 'empleo', 'Aviso del muro'),
            $this->texto('empleo_cta_perfil', 'Déjanos tu perfil', 'empleo', 'Botón hacia el formulario de aspirante'),
            $this->texto('empleo_cta_vacantes', 'Ver vacantes', 'empleo', 'Botón hacia el listado de vacantes'),
            $this->texto('empleo_vacantes_titulo', 'Vacantes abiertas', 'empleo', 'Título del listado público de vacantes'),
            $this->texto('empleo_perfil_titulo', 'Déjanos tu perfil', 'empleo', 'Título del formulario de aspirante'),
            $this->largo('empleo_perfil_texto', 'Cuando un establecimiento asociado busque tu cargo, te contactamos. No necesitas cuenta.', 'empleo', 'Texto del formulario de aspirante'),
            $this->largo('empleo_perfil_experiencia_placeholder', 'Cuéntanos en pocas líneas dónde has trabajado y qué sabes hacer.', 'empleo', 'Placeholder de experiencia del aspirante'),
            $this->texto('empleo_perfil_experiencia_ayuda', 'Con dos o tres frases es suficiente.', 'empleo', 'Ayuda de experiencia del aspirante'),
            $this->largo('empleo_perfil_privacidad', 'Tu perfil quedará visible para los establecimientos afiliados a ASOBARES Capítulo Quindío, que podrán contactarte directamente para ofrecerte trabajo.', 'empleo', 'Aviso de visibilidad del perfil'),

            // --- Artistas y proveedores ---
            $this->texto('artistas_titulo', 'Directorio de artistas', 'artistas', 'Título de artistas'),
            $this->largo('artistas_intro', 'DJs, bandas y solistas de la región. Son las once de la noche, se te cayó el DJ y necesitas uno: aquí está su género, su contacto y un video para escucharlo antes de llamar.', 'artistas', 'Introducción de artistas'),
            // OBS3-08. El acta ofrecía dos redacciones --«a convenir» o
            // «según el evento»--, así que la decide el gremio desde el panel.
            $this->texto('artistas_tarifa_leyenda', 'A convenir', 'artistas', 'Artistas · leyenda en lugar de la tarifa'),
            $this->texto('artistas_bloque_titulo', '¿Eres DJ, banda o solista?', 'artistas', 'Título del bloque de inscripción'),
            $this->largo('artistas_bloque_texto', 'Inscríbete gratis en la bolsa de artistas del gremio y aparece cuando un establecimiento busque música para su noche.', 'artistas', 'Texto del bloque de inscripción'),
            $this->texto('artistas_bloque_cta', 'Inscribirme en la bolsa', 'artistas', 'Botón del bloque de inscripción'),
            // OBS3-12. Los tres estados de la verificacion de un proveedor.
            $this->texto('proveedores_verificado', 'Contacto verificado el', 'proveedores', 'Proveedores · rótulo de verificado'),
            $this->texto('proveedores_verificacion_vieja', 'Sin confirmar desde', 'proveedores', 'Proveedores · rótulo de verificación vencida'),
            $this->texto('proveedores_sin_verificar', 'El gremio no ha confirmado este contacto', 'proveedores', 'Proveedores · rótulo de sin verificar'),
            $this->texto('proveedores_titulo', 'Bolsa de proveedores', 'proveedores', 'Título de proveedores'),
            $this->largo('proveedores_intro', 'Hielo, licores, alimentos, aseo, seguridad y mantenimiento. ¿Quién te arregla la campana de extracción un sábado? Aquí.', 'proveedores', 'Introducción de proveedores'),
            $this->texto('proveedores_beneficio_titulo', 'Un beneficio de estar afiliado', 'proveedores', 'Título del beneficio público'),
            $this->largo('proveedores_beneficio_texto', 'La secretaría verifica cada proveedor y anota la fecha de la última revisión, para que nadie llame a un número que ya no responde. El listado con nombres, WhatsApp y correos es para los establecimientos afiliados: aquí solo se ve de qué está hecho.', 'proveedores', 'Explicación del beneficio público'),
            $this->texto('proveedores_afiliado_titulo', 'Ya estás afiliado', 'proveedores', 'Título para afiliados'),
            $this->largo('proveedores_afiliado_texto', 'Entra al directorio completo con los contactos de cada proveedor.', 'proveedores', 'Texto para afiliados'),
            $this->texto('proveedores_afiliado_cta', 'Ver el directorio', 'proveedores', 'Botón para afiliados'),
            $this->texto('proveedores_no_afiliado_titulo', '¿Quieres los contactos?', 'proveedores', 'Título para visitantes no afiliados'),
            $this->largo('proveedores_no_afiliado_texto', 'El directorio con nombres, WhatsApp y correos es para los establecimientos afiliados a ASOBARES Capítulo Quindío.', 'proveedores', 'Texto para visitantes no afiliados'),
            $this->texto('proveedores_no_afiliado_cta', 'Afiliar mi establecimiento', 'proveedores', 'Botón de afiliación para proveedores'),
            $this->texto('proveedores_no_afiliado_login_cta', 'Ya soy afiliado', 'proveedores', 'Botón de ingreso para afiliados'),
            $this->largo('proveedores_inscripcion_texto', '¿Le vendes al sector nocturno del Quindío?', 'proveedores', 'Texto de inscripción de proveedor'),
            $this->texto('proveedores_inscripcion_cta', 'Inscríbete en la bolsa', 'proveedores', 'Botón de inscripción de proveedor'),

            // --- Eventos ---
            $this->texto('eventos_titulo', 'Eventos y capacitaciones', 'eventos', 'Título de eventos'),
            $this->largo('eventos_intro', 'Solo eventos del gremio: ferias, foros y formación para los establecimientos del Quindío.', 'eventos', 'Introducción de eventos'),
            $this->texto('eventos_vacios_proximos', 'No hay eventos programados por ahora', 'eventos', 'Mensaje sin próximos eventos'),
            $this->texto('eventos_vacios_pasados', 'Todavía no hay eventos pasados', 'eventos', 'Mensaje sin eventos pasados'),
            $this->texto('eventos_vacios_texto', 'Publicamos aquí la agenda del gremio.', 'eventos', 'Texto del mensaje sin eventos'),

            // --- Boletín ---
            $this->texto('boletin_titulo', 'Boletín del gremio', 'boletin', 'Título del boletín'),
            $this->largo('boletin_intro', 'Publicamos poco y publicamos cuando hay algo que decir: cifras del Observatorio Económico que envía la Nacional, decisiones que afectan al sector y los proyectos en los que está trabajando el capítulo.', 'boletin', 'Introducción del boletín'),
            $this->texto('boletin_vacio_titulo', 'Todavía no hay publicaciones', 'boletin', 'Mensaje sin publicaciones'),
            $this->texto('boletin_vacio_texto', 'El boletín se publica alrededor de una vez al mes.', 'boletin', 'Frecuencia del boletín'),

            // --- Afiliación ---
            $this->texto('afiliate_titulo', 'Afíliate al gremio', 'afiliacion', 'Título'),
            $this->largo('afiliate_intro', 'Tu establecimiento deja de estar solo frente a una visita de control, una norma nueva o una tarifa de derechos de autor. Déjanos tus datos y te contactamos.', 'afiliacion', 'Introducción'),
            $this->largo('afiliate_como_funciona', "Nos escribes por el formulario o por WhatsApp.\nAgendamos una visita a tu establecimiento para conocerte.\nFirmas la afiliación y defines qué información tuya se publica en el directorio.\nQuedas dentro: representación, convenios, formación y orientación jurídica.", 'afiliacion', 'Cómo funciona'),
            $this->texto('afiliate_beneficios_titulo', 'Lo que incluye la afiliación', 'afiliacion', 'Título de beneficios'),
            $this->largo('afiliate_beneficios_intro', 'Representación, orientación y beneficios concretos para que tu establecimiento no camine solo.', 'afiliacion', 'Introducción de beneficios'),
            $this->texto('afiliate_formulario_titulo', 'Déjanos tus datos', 'afiliacion', 'Título del formulario'),
            $this->largo('afiliate_formulario_texto', 'Te contactamos para agendar la visita a tu establecimiento.', 'afiliacion', 'Texto de apoyo del formulario'),
            $this->texto('afiliate_whatsapp_cta', 'Escribirnos por WhatsApp', 'afiliacion', 'Botón de WhatsApp tras enviar solicitud'),
            $this->texto('afiliate_contacto_texto', '¿Prefieres hablar directo?', 'afiliacion', 'Texto del contacto directo'),
            $this->texto('afiliate_contacto_cta', 'Escríbenos por WhatsApp al', 'afiliacion', 'Enlace del contacto directo'),

            // --- Mi cuenta ---
            $this->texto('mi_cuenta_pago_al_dia_titulo', 'Estás al día', 'mi_cuenta', 'Título cuando el asociado está al día'),
            $this->largo('mi_cuenta_pago_al_dia_texto', 'No tienes saldos pendientes con el capítulo.', 'mi_cuenta', 'Texto cuando el asociado está al día'),
            $this->texto('mi_cuenta_pago_metodos', 'PSE o tarjeta', 'mi_cuenta', 'Texto bajo el botón de pago'),
            $this->largo('mi_cuenta_pago_ayuda', 'Si no coincide con tus registros, escríbenos a', 'mi_cuenta', 'Ayuda bajo estado de cuenta'),
            $this->texto('mi_cuenta_convenios_titulo', 'Convenios vigentes', 'mi_cuenta', 'Título de convenios'),
            $this->largo('mi_cuenta_convenios_texto', 'El detalle de cada convenio es información privada de los afiliados. No aparece en el sitio público.', 'mi_cuenta', 'Texto de convenios'),
            $this->texto('mi_cuenta_convenios_vacio_titulo', 'Todavía no hay convenios publicados', 'mi_cuenta', 'Título sin convenios'),
            $this->largo('mi_cuenta_convenios_vacio_texto', 'Cuando el gremio publique un aliado con condiciones para afiliados, aparece aquí.', 'mi_cuenta', 'Texto sin convenios'),
            $this->texto('mi_cuenta_convenio_condiciones_rotulo', 'Condiciones del convenio', 'mi_cuenta', 'Rótulo de condiciones del convenio'),
            $this->texto('mi_cuenta_convenio_sin_condiciones', 'Este aliado todavía no tiene condiciones comerciales publicadas.', 'mi_cuenta', 'Texto de convenio sin condiciones'),
            $this->texto('mi_cuenta_convenio_enlace', 'Sitio del aliado', 'mi_cuenta', 'Enlace del aliado'),

            // --- SEO por página ---
            $this->texto('seo_directorio_titulo', 'Directorio de establecimientos — ASOBARES Quindío', 'seo', 'SEO · título del directorio'),
            $this->largo('seo_directorio_descripcion', 'Bares, gastrobares, cafés y discotecas afiliados al gremio en Armenia, Salento, Filandia y todo el Quindío.', 'seo', 'SEO · descripción del directorio'),
            $this->texto('seo_contacto_titulo', 'Contacto y PQR — ASOBARES Quindío', 'seo', 'SEO · título de contacto'),
            $this->largo('seo_contacto_descripcion', 'Escríbenos: contacto general, peticiones, quejas y reclamos, propuestas de alianza o solicitud para entrar a la bolsa de proveedores.', 'seo', 'SEO · descripción de contacto'),
            $this->texto('seo_afiliate_titulo', 'Afíliate al gremio — ASOBARES Quindío', 'seo', 'SEO · título de afiliación'),
            $this->largo('seo_afiliate_descripcion', 'Afíliate a ASOBARES Capítulo Quindío y accede a representación, convenios, formación y orientación para tu establecimiento.', 'seo', 'SEO · descripción de afiliación'),
            $this->texto('seo_guia_titulo', 'Abre tu negocio — ASOBARES Quindío', 'seo', 'SEO · título de la guía normativa'),
            $this->largo('seo_guia_descripcion', 'Requisitos para abrir un bar, gastrobar o café en el Quindío: qué pide cada entidad y ante quién se tramita, municipio por municipio.', 'seo', 'SEO · descripción de la guía normativa'),
            $this->texto('seo_empleo_titulo', 'Bolsa de empleo — ASOBARES Quindío', 'seo', 'SEO · título de empleo'),
            $this->largo('seo_empleo_descripcion', 'Vacantes de bartender, chef, mesero y administrador en bares y gastrobares del Quindío. Publican solo los establecimientos asociados.', 'seo', 'SEO · descripción de empleo'),
            $this->texto('seo_artistas_titulo', 'Directorio de artistas — ASOBARES Quindío', 'seo', 'SEO · título de artistas'),
            $this->largo('seo_artistas_descripcion', 'DJs, bandas y solistas del Quindío: género musical, contacto directo y video para escucharlos.', 'seo', 'SEO · descripción de artistas'),
            $this->texto('seo_proveedores_titulo', 'Bolsa de proveedores — ASOBARES Quindío', 'seo', 'SEO · título de proveedores'),
            $this->largo('seo_proveedores_descripcion', 'Bolsa de proveedores verificados para bares y gastrobares del Quindío: un beneficio para los establecimientos afiliados a ASOBARES.', 'seo', 'SEO · descripción de proveedores'),
            $this->texto('seo_eventos_titulo', 'Eventos y capacitaciones — ASOBARES Quindío', 'seo', 'SEO · título de eventos'),
            $this->largo('seo_eventos_descripcion', 'ExpoBar, foros, congresos y capacitaciones del gremio de la vida nocturna del Quindío.', 'seo', 'SEO · descripción de eventos'),
            $this->texto('seo_boletin_titulo', 'Boletín del gremio — ASOBARES Quindío', 'seo', 'SEO · título de boletín'),
            $this->largo('seo_boletin_descripcion', 'Noticias, cifras y decisiones del gremio de la vida nocturna del Quindío.', 'seo', 'SEO · descripción de boletín'),

            // --- Legal ---
            $this->texto('politica_responsable', 'Asociación de Bares de Colombia — Capítulo Quindío', 'legal', 'Responsable del tratamiento'),
            $this->texto('politica_actualizacion', '1 de agosto de 2026', 'legal', 'Fecha de última actualización'),
        ];
    }

    /**
     * Las cuatro ranuras nacen vacías a propósito: la franja no se pinta
     * hasta que la oficina teclee la primera cifra, y lo que teclee no lo
     * pisa un resembrado (ver `run()`).
     *
     * @return list<array{clave: string, valor: string, tipo: string, grupo: string, etiqueta: string}>
     */
    private function cifrasDelGremio(): array
    {
        $ajustes = [];

        foreach (range(1, CifrasDelGremio::RANURAS) as $ranura) {
            $ajustes[] = $this->texto(CifrasDelGremio::clave($ranura), '', CifrasDelGremio::GRUPO, "Cifra {$ranura} · el número (p. ej. 48 o 92,4 %)");
            $ajustes[] = $this->texto(CifrasDelGremio::claveDetalle($ranura), '', CifrasDelGremio::GRUPO, "Cifra {$ranura} · qué es (p. ej. establecimientos afiliados al día)");
        }

        return $ajustes;
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
