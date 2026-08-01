<?php

namespace Database\Seeders;

use App\Enums\EstadoInscripcion;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoEvento;
use App\Models\Evento;
use App\Models\Inscripcion;
use Database\Seeders\Support\GeneradorImagen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Solo eventos del gremio: ExpoBar, Congreso Nacional y capacitaciones
 * propias. Nunca eventos de bares individuales.
 */
class EventoSeeder extends Seeder
{
    public function run(GeneradorImagen $imagenes): void
    {
        foreach ($this->eventos() as $datos) {
            $inscritos = $datos['inscritos'] ?? 0;
            unset($datos['inscritos']);

            $datos['slug'] = Str::slug($datos['titulo']);
            $datos['imagen'] = $imagenes->generar("evento-{$datos['titulo']}", 'eventos', 1200, 675);
            $datos['estado'] = EstadoPublicacion::Publicado;

            $evento = Evento::updateOrCreate(['slug' => $datos['slug']], $datos);

            if ($inscritos > 0 && $evento->inscripciones()->count() === 0) {
                $this->inscribir($evento, $inscritos);
            }
        }
    }

    private function inscribir(Evento $evento, int $cantidad): void
    {
        $personas = [
            ['Marcela Ríos Peláez', 'marcela.rios@ejemplo.test', '3145520987', 'Café Cordillera'],
            ['Julián Ortiz Bedoya', 'julian.ortiz@ejemplo.test', '3106647712', 'Bar La Estación 1927'],
            ['Tatiana Gómez Arango', 'tatiana.gomez@ejemplo.test', '3122290043', 'Malabar Cocina y Tragos'],
            ['Ricardo Ibáñez Toro', 'ricardo.ibanez@ejemplo.test', '3178830156', null],
            ['Viviana Cardona Sepúlveda', 'viviana.cardona@ejemplo.test', '3160078829', 'Café de los Andes'],
            ['Esteban Zuluaga Marín', 'esteban.zuluaga@ejemplo.test', '3134470091', 'El Guadual'],
            ['Norma Constanza Ruiz', 'norma.ruiz@ejemplo.test', '3197760334', 'Café del Parque'],
            ['Alberto Franco Henao', 'alberto.franco@ejemplo.test', '3183340562', null],
        ];

        foreach (array_slice($personas, 0, $cantidad) as $indice => [$nombre, $correo, $telefono, $establecimiento]) {
            Inscripcion::create([
                'evento_id' => $evento->id,
                'nombre' => $nombre,
                'correo' => $correo,
                'telefono' => $telefono,
                'establecimiento' => $establecimiento,
                'acepta_datos' => true,
                'consentimiento_at' => now()->subDays(random_int(1, 20)),
                // Los eventos gratuitos se confirman de una; los pagos esperan transacción.
                'estado' => $evento->esGratuito() || $indice % 3 !== 0
                    ? EstadoInscripcion::Confirmada
                    : EstadoInscripcion::Registrada,
            ]);
        }
    }

    /** @return list<array<string, mixed>> */
    private function eventos(): array
    {
        return [
            // --- Próximos ---
            [
                'titulo' => 'Capacitación: manipulación de alimentos para bares',
                'tipo' => TipoEvento::Capacitacion,
                'descripcion' => "Certificación en manipulación de alimentos dictada con la Secretaría de Salud, pensada para las cocinas pequeñas de bares y gastrobares. Cubre almacenamiento en frío, cadena de temperatura, control de plagas y el papeleo exacto que revisa el inspector cuando llega sin avisar.\n\nIncluye certificado individual para cada asistente. Cupo limitado a 40 personas.",
                'lugar' => 'Auditorio de la Cámara de Comercio de Armenia y del Quindío',
                'fecha_inicio' => now()->addDays(18)->setTime(8, 0),
                'fecha_fin' => now()->addDays(18)->setTime(12, 0),
                'cupos' => 40,
                'precio' => 0,
                'permite_inscripcion' => true,
                'inscritos' => 5,
            ],
            [
                'titulo' => 'ExpoBar Quindío 2026',
                'tipo' => TipoEvento::Evento,
                'descripcion' => "La feria del sector en el Eje Cafetero: muestra comercial de proveedores, competencia de coctelería, rueda de negocios y panel de normatividad con las secretarías.\n\nLa entrada da acceso a la muestra comercial completa y al panel de la tarde. Los afiliados al día tienen tarifa preferencial.",
                'lugar' => 'Centro de Convenciones de Armenia',
                'fecha_inicio' => now()->addDays(45)->setTime(10, 0),
                'fecha_fin' => now()->addDays(46)->setTime(20, 0),
                'cupos' => 300,
                'precio' => 30000,
                'permite_inscripcion' => true,
                'inscritos' => 3,
            ],
            [
                'titulo' => 'Congreso Nacional Asobares 2026',
                'tipo' => TipoEvento::Evento,
                'descripcion' => 'El encuentro anual de la Asociación de Bares de Colombia. El capítulo Quindío asiste con delegación propia. La inscripción se gestiona directamente en la plataforma de la Nacional.',
                'lugar' => 'Cartagena de Indias',
                'fecha_inicio' => now()->addDays(80)->setTime(9, 0),
                'fecha_fin' => now()->addDays(82)->setTime(18, 0),
                'cupos' => null,
                'precio' => 0,
                'permite_inscripcion' => false,
                'enlace_externo' => 'https://asobares.org/congreso-nacional',
            ],

            // --- Pasados ---
            [
                'titulo' => 'Mercado Nocturno — segunda edición',
                'tipo' => TipoEvento::Evento,
                'descripcion' => "Mercado campesino sobre la Avenida Bolívar, dentro de la línea de Cultura del capítulo. Música en vivo, rincón cultural, exposición de bares, stand de bebidas y muestra de licoreras.\n\nUn evento semestral que apoya a empresarios del Quindío y acerca la vida nocturna a públicos que normalmente no entran a un bar.",
                'lugar' => 'Avenida Bolívar, Armenia',
                'fecha_inicio' => now()->subDays(35)->setTime(18, 0),
                'fecha_fin' => now()->subDays(35)->setTime(23, 0),
                'cupos' => null,
                'precio' => 0,
                'permite_inscripcion' => false,
            ],
            [
                'titulo' => 'Socialización del Sello Púrpura',
                'tipo' => TipoEvento::Capacitacion,
                'descripcion' => "Presentación de la estrategia del Sello Púrpura a los equipos de los establecimientos afiliados, dentro de la línea de Seguridad del capítulo.\n\nEn el baño de mujeres de cada establecimiento se instala un sticker con la ruta que se activa ante una alerta de maltrato verbal o sexual, y las líneas de atención correspondientes. Cada establecimiento socializa la estrategia con sus colaboradores.",
                'lugar' => 'Sede ASOBARES Quindío, piso 3',
                'fecha_inicio' => now()->subDays(70)->setTime(8, 0),
                'fecha_fin' => now()->subDays(70)->setTime(12, 0),
                'cupos' => 30,
                'precio' => 0,
                'permite_inscripcion' => false,
            ],
            [
                'titulo' => 'Lanzamiento del Observatorio Económico Nocturno',
                'tipo' => TipoEvento::Evento,
                'descripcion' => 'Presentación pública de las primeras cifras del Observatorio: participación de la economía nocturna en el empleo de Armenia, ingresos medios del sector e informalidad.',
                'lugar' => 'Cámara de Comercio de Armenia y del Quindío',
                'fecha_inicio' => now()->subDays(120)->setTime(9, 0),
                'fecha_fin' => now()->subDays(120)->setTime(11, 30),
                'cupos' => 120,
                'precio' => 0,
                'permite_inscripcion' => false,
            ],
        ];
    }
}
