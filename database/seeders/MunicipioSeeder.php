<?php

namespace Database\Seeders;

use App\Models\Municipio;
use Illuminate\Database\Seeder;

class MunicipioSeeder extends Seeder
{
    /**
     * Los 12 municipios del Quindío, con su centro aproximado para ubicar los
     * pines del mapa.
     *
     * Los 8 primeros son donde hay afiliados. Los 4 últimos —Buenavista,
     * Córdoba, Génova y Pijao— entraron el 15 de septiembre de 2026 con la guía
     * normativa: el archivo del gremio cubre los doce municipios y sin ellos no
     * hay a qué colgar sus trámites. No tienen afiliados, así que sus
     * coordenadas no las lee nadie hoy; van por completitud y porque
     * `AsociadoSeeder` espera esta forma para todas las filas.
     *
     * Añadir municipios no ensucia ningún filtro: tanto el directorio
     * (`DirectorioController::index`) como la guía (`GuiaController::index`)
     * solo ofrecen los que tienen algo publicado, con `whereHas`. Un municipio
     * sin establecimientos ni fichas existe en la base y no aparece en ningún
     * desplegable.
     *
     * Coordenadas de los cuatro nuevos: Wikipedia en español, consultada el 15
     * de septiembre de 2026 vía su API de `coordinates`. Las 8 anteriores se
     * dejan como estaban.
     *
     * @var array<string, array{slug: string, lat: float, lng: float}>
     */
    public const array MUNICIPIOS = [
        'Armenia' => ['slug' => 'armenia', 'lat' => 4.5339, 'lng' => -75.6811],
        'Salento' => ['slug' => 'salento', 'lat' => 4.6372, 'lng' => -75.5706],
        'Filandia' => ['slug' => 'filandia', 'lat' => 4.6781, 'lng' => -75.6581],
        'Circasia' => ['slug' => 'circasia', 'lat' => 4.6147, 'lng' => -75.6353],
        'Calarcá' => ['slug' => 'calarca', 'lat' => 4.5225, 'lng' => -75.6444],
        'Montenegro' => ['slug' => 'montenegro', 'lat' => 4.5661, 'lng' => -75.7494],
        'Quimbaya' => ['slug' => 'quimbaya', 'lat' => 4.6231, 'lng' => -75.7639],
        'La Tebaida' => ['slug' => 'la-tebaida', 'lat' => 4.4517, 'lng' => -75.7864],
        'Buenavista' => ['slug' => 'buenavista', 'lat' => 4.3597, 'lng' => -75.7392],
        'Córdoba' => ['slug' => 'cordoba', 'lat' => 4.3911, 'lng' => -75.6878],
        'Génova' => ['slug' => 'genova', 'lat' => 4.2067, 'lng' => -75.7906],
        'Pijao' => ['slug' => 'pijao', 'lat' => 4.3328, 'lng' => -75.7056],
    ];

    public function run(): void
    {
        foreach (self::MUNICIPIOS as $nombre => $datos) {
            Municipio::updateOrCreate(['slug' => $datos['slug']], ['nombre' => $nombre]);
        }
    }
}
