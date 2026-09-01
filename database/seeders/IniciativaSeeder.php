<?php

namespace Database\Seeders;

use App\Enums\EstadoIniciativa;
use App\Enums\EstadoPublicacion;
use App\Models\Iniciativa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Las 5 iniciativas que el capítulo presenta como su portafolio vigente.
 *
 * Fuente: `material/TED GREMIAL - ASOBARES QUINDIO.pdf`, lámina «LAS 5
 * INICIATIVAS MÁS IMPORTANTES — lo que Asobares ya tiene en marcha para
 * cumplir el sueño». De ahí salen los cinco nombres, los cinco resúmenes y
 * los cinco estados, uno por uno. (El PDF es un escaneo sin capa de texto:
 * se leyó extrayendo las imágenes de página incrustadas.)
 *
 * ⚠️ **Los `descripcion` largos que había aquí eran ampliación editorial, no
 * del TED.** Afirmaban cosas que la lámina no dice --que Vibrarte es «el
 * primer distrito de experiencia del departamento», qué criterios exactos
 * certifica Bares Verdes, qué revisa Blindando tu Negocio-- y en un esquema
 * que no marca la procedencia de cada fila eso se vuelve indistinguible de un
 * dato del gremio. Ahora cada descripción se queda dentro de lo que dicen el
 * TED y, para «Blindando tu Negocio», el documento de la jornada con la
 * Alcaldía. Ampliarlas es trabajo del gremio desde el panel.
 *
 * La lámina rotula la quinta como «Diplomado Gerencia de Bares»; aquí lleva
 * la preposición porque el slug ya está publicado y cambiarlo dejaría la
 * iniciativa vieja huérfana en cualquier base ya sembrada.
 */
class IniciativaSeeder extends Seeder
{
    public function run(): void
    {
        $iniciativas = [
            [
                'nombre' => 'Vibrarte',
                'resumen' => 'Corredor peatonal seguro e iluminado, activo 24 horas.',
                'descripcion' => 'Un corredor peatonal sobre la carrera 14, seguro e iluminado y activo las 24 horas. Responde a la visión que el gremio presentó para el sector: cada municipio, un distrito de experiencia, con señalización, iluminación y seguridad como proyecto de ciudad.',
                'estado_iniciativa' => EstadoIniciativa::EnEjecucion,
                'linea' => 'Cultura',
                'lugar' => 'Carrera 14, Armenia',
            ],
            [
                'nombre' => 'Bares Verdes',
                'resumen' => 'Certificación en sostenibilidad y responsabilidad social.',
                'descripcion' => 'Certificación en sostenibilidad y responsabilidad social para los establecimientos del capítulo.',
                'estado_iniciativa' => EstadoIniciativa::EnEjecucion,
                'linea' => 'Sostenibilidad',
            ],
            [
                'nombre' => 'Blindando tu Negocio',
                'resumen' => 'Seguridad y revisión constante en los establecimientos.',
                // Lo entrecomillado sale del documento de la jornada
                // «BLINDEMOS TU NEGOCIO ARMENIA», hecho con la Alcaldía.
                'descripcion' => 'Seguridad y revisión constante en los establecimientos, en articulación con la Alcaldía de Armenia y las autoridades competentes. El propósito es preventivo y educativo: que cada empresario conozca sus deberes y derechos antes de las jornadas de control. El objetivo no es sancionar, sino sensibilizar para que el comercio nocturno sea ejemplo de orden, seguridad y cumplimiento legal.',
                'estado_iniciativa' => EstadoIniciativa::Escalando,
                'linea' => 'Seguridad',
            ],
            [
                'nombre' => 'Noche Segura y Competitiva',
                'resumen' => 'Brigadas de emergencia y profesionalización del personal.',
                'descripcion' => 'Brigadas de emergencia y profesionalización del personal, arrancando por Circasia y Calarcá.',
                'estado_iniciativa' => EstadoIniciativa::Formulacion,
                'linea' => 'Seguridad',
                'lugar' => 'Circasia y Calarcá',
            ],
            [
                'nombre' => 'Diplomado en Gerencia de Bares',
                'resumen' => 'Formación gerencial formal para empresarios del sector.',
                'descripcion' => 'Formación gerencial formal para los empresarios del sector.',
                'estado_iniciativa' => EstadoIniciativa::Formulacion,
                'linea' => 'Cultura',
            ],
        ];

        foreach ($iniciativas as $orden => $iniciativa) {
            $iniciativa['slug'] = Str::slug($iniciativa['nombre']);
            $iniciativa['orden'] = $orden + 1;
            $iniciativa['estado'] = EstadoPublicacion::Publicado;

            Iniciativa::updateOrCreate(['slug' => $iniciativa['slug']], $iniciativa);
        }
    }
}
