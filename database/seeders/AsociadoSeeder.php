<?php

namespace Database\Seeders;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use Database\Seeders\Support\GeneradorImagen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * 24 establecimientos ficticios repartidos por municipio y categoría.
 * Los datos institucionales del gremio son reales; estos NO.
 */
class AsociadoSeeder extends Seeder
{
    public function run(GeneradorImagen $imagenes): void
    {
        $municipios = Municipio::pluck('id', 'slug');
        $categorias = Categoria::pluck('id', 'slug');

        foreach ($this->establecimientos() as $indice => $datos) {
            $coordenadas = $this->coordenadasConJitter($datos['municipio'], $indice);

            $asociado = Asociado::updateOrCreate(
                ['slug' => Str::slug($datos['nombre'])],
                [
                    'nombre' => $datos['nombre'],
                    'categoria_id' => $categorias[$datos['categoria']],
                    'municipio_id' => $municipios[$datos['municipio']],
                    'descripcion' => $datos['descripcion'],
                    'direccion' => $datos['direccion'],
                    'whatsapp' => $datos['whatsapp'],
                    'instagram_url' => 'https://instagram.com/'.Str::slug($datos['nombre'], ''),
                    'sitio_web' => $datos['sitio_web'] ?? null,
                    'google_maps_url' => $datos['perfiles'] ?? false
                        ? 'https://maps.google.com/?q='.urlencode($datos['nombre'].' '.$datos['municipio'])
                        : null,
                    'tripadvisor_url' => $datos['perfiles'] ?? false
                        ? 'https://www.tripadvisor.co/Restaurant_Review-'.Str::slug($datos['nombre'])
                        : null,
                    'horario' => $datos['horario'],
                    'lat' => $coordenadas['lat'],
                    'lng' => $coordenadas['lng'],
                    'foto_portada' => $imagenes->generar("asociado-{$datos['nombre']}", 'asociados', 1200, 900),
                    'destacado' => $datos['destacado'] ?? false,
                    'estado' => EstadoPublicacion::Publicado,

                    // Campos internos: existen en el panel, nunca en el sitio.
                    'representante' => $datos['representante'],
                    'correo_interno' => Str::slug($datos['nombre']).'@ejemplo.test',
                    'telefono_interno' => $datos['whatsapp'],
                    'fecha_afiliacion' => now()->subMonths(random_int(2, 22))->toDateString(),
                    'notas_internas' => $datos['nota_interna'] ?? 'Sin novedades.',
                ]
            );

            // Galería solo para los destacados: mantiene la semilla ágil.
            if (($datos['destacado'] ?? false) && $asociado->getMedia('galeria')->isEmpty()) {
                foreach (range(1, 3) as $numero) {
                    $ruta = $imagenes->generar("galeria-{$datos['nombre']}-{$numero}", 'galeria', 1200, 900);
                    $asociado->addMedia(storage_path("app/public/{$ruta}"))
                        ->preservingOriginal()
                        ->toMediaCollection('galeria');
                }
            }
        }
    }

    /**
     * Dispersa los pines alrededor del centro del municipio para que el mapa
     * no los apile en un solo punto.
     *
     * @return array{lat: float, lng: float}
     */
    private function coordenadasConJitter(string $municipio, int $indice): array
    {
        $centro = collect(MunicipioSeeder::MUNICIPIOS)
            ->first(fn (array $datos): bool => $datos['slug'] === $municipio);

        $desplazamiento = fn (int $semilla): float => ((crc32((string) $semilla) % 200) - 100) / 20000;

        return [
            'lat' => round($centro['lat'] + $desplazamiento($indice * 7 + 1), 7),
            'lng' => round($centro['lng'] + $desplazamiento($indice * 13 + 5), 7),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function establecimientos(): array
    {
        return [
            // --- Armenia ---
            [
                'nombre' => 'La Cava del Yipao', 'categoria' => 'bar', 'municipio' => 'armenia', 'destacado' => true, 'perfiles' => true,
                'descripcion' => 'Bar de barra larga y carta de destilados en el centro de Armenia. Coctelería de autor con aguardiente y café de la región, y una vitrina de vinilos que suena de jueves a sábado.',
                'direccion' => 'Calle 20 # 14-32, Centro', 'whatsapp' => '3145520114', 'horario' => 'Jue a sáb, 6:00 p. m. – 2:00 a. m.',
                'representante' => 'Andrés Felipe Loaiza', 'sitio_web' => 'https://ejemplo.test/la-cava-del-yipao',
                'nota_interna' => 'Afiliado fundador. Presta el local para reuniones del capítulo.',
            ],
            [
                'nombre' => 'Bruma Gastrobar', 'categoria' => 'gastrobar', 'municipio' => 'armenia', 'destacado' => true, 'perfiles' => true,
                'descripcion' => 'Cocina de producto quindiano con carbón y humo. Plátano, trucha y cerdo de la región en platos para compartir, acompañados de cervezas artesanales del Eje.',
                'direccion' => 'Avenida Bolívar # 19-45', 'whatsapp' => '3106648820', 'horario' => 'Mar a dom, 12:00 m. – 12:00 a. m.',
                'representante' => 'Daniela Restrepo Ochoa',
            ],
            [
                'nombre' => 'Café Cordillera', 'categoria' => 'cafe', 'municipio' => 'armenia',
                'descripcion' => 'Tostión propia y métodos de filtrado a la vista. De día es oficina de medio Armenia; de noche, catas y música en vivo sin amplificar.',
                'direccion' => 'Carrera 14 # 4-18, La Castellana', 'whatsapp' => '3122298741', 'horario' => 'Lun a sáb, 7:00 a. m. – 9:00 p. m.',
                'representante' => 'Mariana Ospina Vélez',
            ],
            [
                'nombre' => 'Terraza Bolívar', 'categoria' => 'rooftop', 'municipio' => 'armenia', 'destacado' => true, 'perfiles' => true,
                'descripcion' => 'Rooftop en el piso 9 con vista a la cordillera. Coctelería clásica, DJ residente los viernes y la mejor puesta de sol de la ciudad.',
                'direccion' => 'Carrera 13 # 10-55, piso 9', 'whatsapp' => '3178834502', 'horario' => 'Mié a sáb, 5:00 p. m. – 2:00 a. m.',
                'representante' => 'Camilo Andrés Grisales', 'sitio_web' => 'https://ejemplo.test/terraza-bolivar',
                'nota_interna' => 'Interesado en la bolsa de artistas para DJs de fin de semana.',
            ],
            [
                'nombre' => 'Sonora Club', 'categoria' => 'discoteca', 'municipio' => 'armenia', 'perfiles' => true,
                'descripcion' => 'Salsa brava y música tropical con orquesta en vivo el último sábado de cada mes. Pista amplia y dos barras.',
                'direccion' => 'Calle 21 Norte # 12-08', 'whatsapp' => '3160074455', 'horario' => 'Vie y sáb, 9:00 p. m. – 3:00 a. m.',
                'representante' => 'Yeison Alberto Cardona',
            ],
            [
                'nombre' => 'El Guadual', 'categoria' => 'restaurante-bar', 'municipio' => 'armenia',
                'descripcion' => 'Restaurante bar en estructura de guadua, con cocina de leña y carta de cervezas artesanales. Almuerzos entre semana y música en vivo los sábados.',
                'direccion' => 'Kilómetro 2 vía Armenia – Circasia', 'whatsapp' => '3134478899', 'horario' => 'Mar a dom, 11:00 a. m. – 11:00 p. m.',
                'representante' => 'Luz Adriana Marín',
            ],
            [
                'nombre' => 'Bar La Estación 1927', 'categoria' => 'bar', 'municipio' => 'armenia',
                'descripcion' => 'Bar de barrio con memoria ferroviaria: fotografías del viejo tren del Quindío, mesas de billar y cerveza fría a precio honesto.',
                'direccion' => 'Calle 18 # 16-24', 'whatsapp' => '3197765012', 'horario' => 'Lun a sáb, 4:00 p. m. – 1:00 a. m.',
                'representante' => 'Jhon Fredy Betancourt',
            ],
            [
                'nombre' => 'Malabar Cocina y Tragos', 'categoria' => 'gastrobar', 'municipio' => 'armenia', 'perfiles' => true,
                'descripcion' => 'Gastrobar de cocina de fusión con toques asiáticos y coctelería de bitters caseros. Barra de doce puestos frente al bartender.',
                'direccion' => 'Carrera 15 # 8-30', 'whatsapp' => '3183341276', 'horario' => 'Mar a sáb, 5:00 p. m. – 1:00 a. m.',
                'representante' => 'Sara Isabel Quintero',
            ],
            [
                'nombre' => 'Nocturno 33', 'categoria' => 'discoteca', 'municipio' => 'armenia', 'destacado' => true, 'perfiles' => true,
                'descripcion' => 'Electrónica y house con cabina central y sonido calibrado. Programación de DJs invitados dos veces al mes.',
                'direccion' => 'Avenida Centenario # 33-19', 'whatsapp' => '3151189203', 'horario' => 'Vie y sáb, 10:00 p. m. – 4:00 a. m.',
                'representante' => 'Kevin Steven Ramírez',
                'nota_interna' => 'Publica vacantes con frecuencia. Buen caso para la bolsa de empleo.',
            ],
            [
                'nombre' => 'Café de los Andes', 'categoria' => 'cafe', 'municipio' => 'armenia',
                'descripcion' => 'Cafetería de especialidad con repostería propia y una pequeña librería de intercambio.',
                'direccion' => 'Carrera 16 # 12-07', 'whatsapp' => '3115523398', 'horario' => 'Lun a sáb, 8:00 a. m. – 8:00 p. m.',
                'representante' => 'Paula Andrea Gil',
            ],

            // --- Salento ---
            [
                'nombre' => 'Mirador de Cocora', 'categoria' => 'restaurante-bar', 'municipio' => 'salento', 'destacado' => true, 'perfiles' => true,
                'descripcion' => 'Restaurante bar con vista al valle. Trucha de la región, canelazo y una terraza que se llena al atardecer.',
                'direccion' => 'Vía al Valle de Cocora, km 4', 'whatsapp' => '3144412200', 'horario' => 'Todos los días, 10:00 a. m. – 10:00 p. m.',
                'representante' => 'Gustavo Adolfo Herrera', 'sitio_web' => 'https://ejemplo.test/mirador-de-cocora',
            ],
            [
                'nombre' => 'Bar El Arriero', 'categoria' => 'bar', 'municipio' => 'salento',
                'descripcion' => 'Bar tradicional en la calle Real. Tejo, aguardiente y conversación hasta que cierre.',
                'direccion' => 'Calle Real # 6-12', 'whatsapp' => '3128876410', 'horario' => 'Jue a dom, 3:00 p. m. – 12:00 a. m.',
                'representante' => 'Óscar Iván Salazar',
            ],
            [
                'nombre' => 'Café Palma de Cera', 'categoria' => 'cafe', 'municipio' => 'salento',
                'descripcion' => 'Café de finca propia con tour de tostión los fines de semana. Terraza con vista a la palma de cera.',
                'direccion' => 'Carrera 5 # 4-40', 'whatsapp' => '3169903371', 'horario' => 'Todos los días, 7:00 a. m. – 7:00 p. m.',
                'representante' => 'Claudia Milena Arias',
            ],

            // --- Filandia ---
            [
                'nombre' => 'Colina Iluminada', 'categoria' => 'rooftop', 'municipio' => 'filandia', 'destacado' => true, 'perfiles' => true,
                'descripcion' => 'Rooftop sobre el parque principal, con vista de 360° al paisaje cultural cafetero. Coctelería de frutas locales.',
                'direccion' => 'Carrera 6 # 6-15, piso 3', 'whatsapp' => '3172234498', 'horario' => 'Jue a dom, 4:00 p. m. – 12:00 a. m.',
                'representante' => 'Natalia Andrea Ceballos',
            ],
            [
                'nombre' => 'Bar Balcón del Quindío', 'categoria' => 'bar', 'municipio' => 'filandia',
                'descripcion' => 'Bar de balcón colonial con carta corta de cervezas artesanales y música de la casa.',
                'direccion' => 'Calle 7 # 5-28', 'whatsapp' => '3105567129', 'horario' => 'Vie a dom, 5:00 p. m. – 1:00 a. m.',
                'representante' => 'Sebastián Molina Ruiz',
            ],

            // --- Circasia ---
            [
                'nombre' => 'La Herradura', 'categoria' => 'bar', 'municipio' => 'circasia',
                'descripcion' => 'Bar campestre a las afueras, con parqueadero amplio y música popular los fines de semana.',
                'direccion' => 'Vía Circasia – Filandia, km 2', 'whatsapp' => '3187741155', 'horario' => 'Vie a dom, 2:00 p. m. – 2:00 a. m.',
                'representante' => 'Diego Alejandro Botero',
            ],
            [
                'nombre' => 'Café Circasia', 'categoria' => 'cafe', 'municipio' => 'circasia',
                'descripcion' => 'Café de pueblo frente al parque, con pastelería casera y wifi que sí funciona.',
                'direccion' => 'Carrera 14 # 9-03', 'whatsapp' => '3141120087', 'horario' => 'Lun a sáb, 7:00 a. m. – 8:00 p. m.',
                'representante' => 'Ángela María Duque',
            ],

            // --- Calarcá ---
            [
                'nombre' => 'Gastrobar Los Faroles', 'categoria' => 'gastrobar', 'municipio' => 'calarca',
                'descripcion' => 'Gastrobar en casona restaurada, con patio interior y cocina de autor sobre recetas de la abuela.',
                'direccion' => 'Calle 39 # 24-11', 'whatsapp' => '3159982240', 'horario' => 'Mié a dom, 5:00 p. m. – 12:00 a. m.',
                'representante' => 'Juan Carlos Villegas',
            ],
            [
                'nombre' => 'Bar Puerto Espejo', 'categoria' => 'bar', 'municipio' => 'calarca',
                'descripcion' => 'Bar de rock y blues con tocatas de bandas locales los viernes.',
                'direccion' => 'Carrera 25 # 38-40', 'whatsapp' => '3132218806', 'horario' => 'Jue a sáb, 6:00 p. m. – 2:00 a. m.',
                'representante' => 'Mauricio Ospina Cárdenas',
            ],

            // --- Montenegro ---
            [
                'nombre' => 'Discoteca Palma Nights', 'categoria' => 'discoteca', 'municipio' => 'montenegro',
                'descripcion' => 'Discoteca de crossover con dos ambientes y pista de baile amplia. La parada obligada de los fines de semana en Montenegro.',
                'direccion' => 'Calle 20 # 6-70', 'whatsapp' => '3196630112', 'horario' => 'Vie y sáb, 9:00 p. m. – 3:00 a. m.',
                'representante' => 'Wilmar Andrés Ruiz',
            ],
            [
                'nombre' => 'Café del Parque', 'categoria' => 'cafe', 'municipio' => 'montenegro',
                'descripcion' => 'Café tradicional frente al parque principal, abierto desde 1998.',
                'direccion' => 'Carrera 5 # 19-22', 'whatsapp' => '3117789004', 'horario' => 'Lun a dom, 6:30 a. m. – 8:00 p. m.',
                'representante' => 'Rosa Elena Jaramillo',
            ],

            // --- Quimbaya ---
            [
                'nombre' => 'Bar Luces de Quimbaya', 'categoria' => 'bar', 'municipio' => 'quimbaya',
                'descripcion' => 'Bar que se llena en temporada de faroles. Carta de cervezas y picadas para compartir.',
                'direccion' => 'Calle 15 # 6-33', 'whatsapp' => '3183307742', 'horario' => 'Jue a dom, 4:00 p. m. – 1:00 a. m.',
                'representante' => 'Fernando Castaño Ruiz',
            ],
            [
                'nombre' => 'Gastrobar Alambique', 'categoria' => 'gastrobar', 'municipio' => 'quimbaya',
                'descripcion' => 'Destilados artesanales y cocina de fuego lento en una antigua trilladora de café.',
                'direccion' => 'Carrera 7 # 12-50', 'whatsapp' => '3121165590', 'horario' => 'Mié a sáb, 5:00 p. m. – 12:00 a. m.',
                'representante' => 'Laura Cristina Mejía',
            ],

            // --- La Tebaida ---
            [
                'nombre' => 'Restaurante Bar El Tren', 'categoria' => 'restaurante-bar', 'municipio' => 'la-tebaida',
                'descripcion' => 'Cocina casera de mediodía y bar de noche, junto a la antigua estación del tren.',
                'direccion' => 'Calle 12 # 8-19', 'whatsapp' => '3154470028', 'horario' => 'Lun a sáb, 11:00 a. m. – 11:00 p. m.',
                'representante' => 'Hernán Darío Trejos',
            ],
        ];
    }
}
