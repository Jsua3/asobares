<?php

namespace Database\Seeders;

use App\Enums\EstadoIniciativa;
use App\Enums\EstadoPublicacion;
use App\Models\Iniciativa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Las 5 iniciativas que el capítulo presenta como su portafolio vigente,
 * tomadas del TED gremial: «lo que Asobares ya tiene en marcha».
 */
class IniciativaSeeder extends Seeder
{
    public function run(): void
    {
        $iniciativas = [
            [
                'nombre' => 'Vibrarte',
                'resumen' => 'Corredor peatonal seguro e iluminado, activo 24 horas.',
                'descripcion' => 'Un corredor sobre la carrera 14 pensado como proyecto de ciudad: señalización, iluminación y presencia institucional para que la gente camine tranquila a cualquier hora. Es el primer distrito de experiencia del departamento y el modelo que el gremio quiere replicar en otros municipios.',
                'estado_iniciativa' => EstadoIniciativa::EnEjecucion,
                'linea' => 'Cultura',
                'lugar' => 'Carrera 14, Armenia',
            ],
            [
                'nombre' => 'Bares Verdes',
                'resumen' => 'Certificación en sostenibilidad y responsabilidad social.',
                'descripcion' => 'Acompañamiento para que los establecimientos reduzcan su huella ambiental: separación de residuos, eficiencia energética y compra a productores locales. Quien cumple los criterios recibe la certificación del gremio.',
                'estado_iniciativa' => EstadoIniciativa::EnEjecucion,
                'linea' => 'Sostenibilidad',
            ],
            [
                'nombre' => 'Blindando tu Negocio',
                'resumen' => 'Seguridad y revisión constante en los establecimientos.',
                'descripcion' => 'Revisión periódica de las condiciones de seguridad de cada local —extintores, salidas, instalación eléctrica, control de acceso— antes de que llegue una visita de control. La idea es simple: que el inspector no encuentre nada que el gremio no haya revisado primero.',
                'estado_iniciativa' => EstadoIniciativa::Escalando,
                'linea' => 'Seguridad',
            ],
            [
                'nombre' => 'Noche Segura y Competitiva',
                'resumen' => 'Brigadas de emergencia y profesionalización del personal.',
                'descripcion' => 'Conformación de brigadas de emergencia y formación del personal de servicio y seguridad, arrancando por Circasia y Calarcá. Busca que los municipios pequeños tengan el mismo estándar de operación que Armenia.',
                'estado_iniciativa' => EstadoIniciativa::Formulacion,
                'linea' => 'Seguridad',
                'lugar' => 'Circasia y Calarcá',
            ],
            [
                'nombre' => 'Diplomado en Gerencia de Bares',
                'resumen' => 'Formación gerencial formal para empresarios del sector.',
                'descripcion' => 'Un programa formal para que el dueño de establecimiento deje de aprender a los golpes: costos, normatividad, manejo de personal y planeación. Pensado para el dueño pequeño, que suele ser también el bartender y el administrador.',
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
