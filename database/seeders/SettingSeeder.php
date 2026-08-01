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
            // --- Identidad ---
            $this->texto('sitio_nombre', 'ASOBARES Capítulo Quindío', 'identidad', 'Nombre del sitio'),
            $this->texto('sitio_eslogan', 'El gremio de la vida nocturna del Quindío', 'identidad', 'Eslogan'),
            $this->largo('sitio_descripcion', 'Asociación de Bares de Colombia, Capítulo Quindío. Representamos a bares, gastrobares, cafés y discotecas del departamento ante las instituciones, y acompañamos a quien quiere abrir su establecimiento.', 'identidad', 'Descripción para buscadores'),

            // --- Inicio ---
            $this->texto('hero_titulo', 'La noche del Quindío tiene quien la represente', 'inicio', 'Título del hero'),
            $this->largo('hero_subtitulo', 'Somos el gremio que se sienta en la mesa con las instituciones y el que te explica, paso a paso, cómo abrir tu establecimiento sin que te lo cierren.', 'inicio', 'Subtítulo del hero'),
            $this->texto('hero_cta_directorio', 'Explora la noche', 'inicio', 'Botón hacia el directorio'),
            $this->texto('hero_cta_afiliate', 'Afíliate', 'inicio', 'Botón hacia afiliación'),
            $this->texto('cta_final_titulo', '¿Tu establecimiento todavía no es parte del gremio?', 'inicio', 'Título del cierre'),
            $this->largo('cta_final_texto', 'Afiliarte toma una conversación. Representación ante las instituciones, descuentos en derechos de autor, formación y orientación jurídica sin costo.', 'inicio', 'Texto del cierre'),

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
            $this->largo('quienes_historia', 'ASOBARES Capítulo Quindío nació el 14 de agosto de 2024 en Armenia como el capítulo regional de Asobares Colombia. Reunimos a bares, gastrobares, cafés y discotecas del departamento alrededor de una idea simple: la vida nocturna es una industria que genera empleo, paga impuestos y merece ser tratada como tal.', 'institucional', 'Historia'),
            $this->largo('quienes_mision', 'Representar al sector de la vida nocturna del Quindío ante las instituciones públicas y privadas, para proponer como gremio: participar en las decisiones sobre horarios, ruido, orden público y formalización antes de que se tomen, y no reclamar después.', 'institucional', 'Misión'),
            $this->largo('quienes_que_hacemos', 'Gestionamos con las Secretarías de Salud, Gobierno y Planeación. Negociamos tarifas de derechos de autor. Formamos a los equipos de nuestros afiliados. Y construimos la guía normativa por municipio que hoy no tiene ningún otro gremio del país.', 'institucional', 'Qué hacemos'),
            $this->texto('quienes_presidente', 'Jorge Iván Botero Ángel', 'institucional', 'Presidente'),
            $this->texto('quienes_directora', 'Natalia Gutiérrez', 'institucional', 'Directora ejecutiva'),
            $this->texto('quienes_fundacion', '14 de agosto de 2024', 'institucional', 'Fecha de fundación'),
            $this->largo('quienes_programas', "Armenia 24 Horas — la propuesta para una ciudad con vida económica nocturna regulada y segura.\nForo Quindío Nocturno — el espacio donde el sector, la academia y las instituciones discuten el futuro de la industria.", 'institucional', 'Programas'),
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
            $this->texto('guia_titulo', 'Abre tu negocio sin que te lo cierren', 'guia', 'Título de la guía'),
            $this->largo('guia_intro', 'La normatividad cambia de un municipio a otro. Escoge el tuyo y revisa, entidad por entidad, qué te van a pedir, cuánto cuesta y qué formato tienes que descargar y llevar diligenciado.', 'guia', 'Introducción de la guía'),
            $this->largo('guia_descargo', 'Esta guía es orientativa y se actualiza con la información que cada entidad entrega al gremio. Los requisitos, costos y formatos pueden cambiar sin aviso: verifica siempre directamente con la entidad competente antes de iniciar tu trámite.', 'guia', 'Texto de descargo'),

            // --- Bolsa de empleo ---
            $this->texto('empleo_titulo', 'Bolsa de empleo del sector', 'empleo', 'Título'),
            $this->largo('empleo_intro', 'Bartenders, chefs, meseros y administradores para la vida nocturna del Quindío. Conseguir un buen bartender acá es lo más difícil; por eso el muro existe.', 'empleo', 'Introducción'),
            $this->texto('empleo_aviso', 'Solo los establecimientos asociados publican vacantes en este muro.', 'empleo', 'Aviso del muro'),

            // --- Artistas y proveedores ---
            $this->texto('artistas_titulo', 'Directorio de artistas', 'modulos', 'Título de artistas'),
            $this->largo('artistas_intro', 'DJs, bandas y solistas de la región. Son las once de la noche, se te cayó el DJ y necesitas uno: aquí está su género, su tarifa desde, su contacto y un video para escucharlo antes de llamar.', 'modulos', 'Introducción de artistas'),
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
