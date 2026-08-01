<?php

namespace Database\Seeders;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Vacante;
use Illuminate\Database\Seeder;

/**
 * Bolsa de empleo del sector. "Conseguir bartenders acá es lo más difícil."
 */
class VacanteSeeder extends Seeder
{
    public function run(): void
    {
        $asociados = Asociado::pluck('id', 'slug');

        $vacantes = [
            [
                'asociado' => 'nocturno-33',
                'cargo' => 'Bartender',
                'tipo' => TipoVacante::PorTurnos,
                'descripcion' => 'Buscamos bartender con mínimo un año de experiencia en barra de alto volumen. Debe manejar coctelería clásica, control de inventario de barra y trabajo bajo presión en noches de lleno total. Se paga por turno más propinas repartidas.',
                'franja_horaria' => 'Viernes y sábados, 8:00 p. m. – 4:00 a. m.',
                'whatsapp_contacto' => '3151189203',
                'estado' => EstadoPublicacion::Publicado,
            ],
            [
                'asociado' => 'bruma-gastrobar',
                'cargo' => 'Chef de cocina',
                'tipo' => TipoVacante::TiempoCompleto,
                'descripcion' => 'Chef para liderar la cocina: diseño de carta de temporada con producto quindiano, manejo de costos, escandallos y equipo de cuatro personas. Indispensable certificado de manipulación de alimentos vigente.',
                'franja_horaria' => 'Martes a domingo, 10:00 a. m. – 8:00 p. m.',
                'whatsapp_contacto' => '3106648820',
                'estado' => EstadoPublicacion::Publicado,
            ],
            [
                'asociado' => 'terraza-bolivar',
                'cargo' => 'Mesero',
                'tipo' => TipoVacante::PorTurnos,
                'descripcion' => 'Mesero para servicio de terraza. Buena presentación, manejo de bandeja y toma de pedido en tableta. No se requiere experiencia previa: capacitamos. Ideal para estudiantes.',
                'franja_horaria' => 'Miércoles a sábado, 5:00 p. m. – 1:00 a. m.',
                'whatsapp_contacto' => '3178834502',
                'estado' => EstadoPublicacion::Publicado,
            ],
            [
                'asociado' => 'la-cava-del-yipao',
                'cargo' => 'Administrador de establecimiento',
                'tipo' => TipoVacante::TiempoCompleto,
                'descripcion' => 'Administrador con experiencia en el sector nocturno: manejo de personal, cuadre de caja, relación con proveedores y cumplimiento de la normatividad (bomberos, salud, Sayco). Se valora conocimiento de software de punto de venta.',
                'franja_horaria' => 'Lunes a sábado, horario administrativo con noches de cierre',
                'whatsapp_contacto' => '3145520114',
                'estado' => EstadoPublicacion::Publicado,
            ],
            [
                'asociado' => 'sonora-club',
                'cargo' => 'Portero / control de acceso',
                'tipo' => TipoVacante::PorTurnos,
                'descripcion' => 'Personal para control de acceso y requisa en puerta. Debe tener curso de vigilancia vigente y manejo de situaciones de conflicto sin escalarlas. Trabajo en dupla.',
                'franja_horaria' => 'Viernes y sábados, 9:00 p. m. – 3:00 a. m.',
                'whatsapp_contacto' => '3160074455',
                'estado' => EstadoPublicacion::Publicado,
            ],
            [
                'asociado' => 'bruma-gastrobar',
                'cargo' => 'Auxiliar de cocina',
                'tipo' => TipoVacante::TiempoCompleto,
                'descripcion' => 'Apoyo en preparación de mise en place, montaje de platos y aseo de cocina. Se requiere certificado de manipulación de alimentos. Vacante todavía en revisión de la dirección.',
                'franja_horaria' => 'Martes a domingo, 11:00 a. m. – 7:00 p. m.',
                'whatsapp_contacto' => '3106648820',
                // Queda pendiente a propósito: sirve para demostrar que una
                // vacante no publicada NO aparece en /empleo.
                'estado' => EstadoPublicacion::PendienteAprobacion,
            ],
        ];

        foreach ($vacantes as $vacante) {
            $slug = $vacante['asociado'];
            unset($vacante['asociado']);

            Vacante::updateOrCreate(
                ['asociado_id' => $asociados[$slug], 'cargo' => $vacante['cargo']],
                $vacante + ['asociado_id' => $asociados[$slug]]
            );
        }

        $this->registrarAspirantes();
    }

    private function registrarAspirantes(): void
    {
        $aspirantes = [
            ['Duván Alexis Marín', 'duvan.marin@ejemplo.test', '3145598821', 'Bartender', 'Tres años en barra de discoteca en Pereira. Manejo coctelería clásica y flair básico.'],
            ['Yuliana Andrea Correa', 'yuliana.correa@ejemplo.test', '3106612077', 'Mesera', 'Un año en restaurante de hotel. Manejo de bandeja y atención en inglés básico.'],
            ['Cristian Camilo Peña', 'cristian.pena@ejemplo.test', '3122234509', 'Chef', 'Técnico en cocina del SENA, dos años como segundo de cocina en gastrobar.'],
            ['Marisol Henao Ospina', 'marisol.henao@ejemplo.test', '3178840162', 'Administradora', 'Cinco años administrando un bar en Armenia. Manejo de nómina y de visitas de control.'],
            ['Brayan Stiven Loaiza', 'brayan.loaiza@ejemplo.test', '3160091144', 'Auxiliar de cocina', 'Primer empleo. Certificado de manipulación de alimentos vigente.'],
            ['Leidy Johana Ramírez', 'leidy.ramirez@ejemplo.test', '3134461730', 'Bartender', 'Dos años en coctelería de autor. Busco turnos de fin de semana.'],
            ['Andrés Mauricio Toro', 'andres.toro@ejemplo.test', '3197712285', 'Portero', 'Curso de vigilancia vigente y cuatro años en control de acceso.'],
        ];

        foreach ($aspirantes as [$nombre, $correo, $telefono, $cargo, $experiencia]) {
            Aspirante::updateOrCreate(
                ['correo' => $correo],
                [
                    'nombre' => $nombre,
                    'telefono' => $telefono,
                    'cargo_interes' => $cargo,
                    'experiencia' => $experiencia,
                    'acepta_datos' => true,
                    'consentimiento_at' => now()->subDays(random_int(1, 14)),
                ]
            );
        }
    }
}
