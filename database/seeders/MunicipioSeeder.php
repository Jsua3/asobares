<?php

namespace Database\Seeders;

use App\Models\Municipio;
use Illuminate\Database\Seeder;

class MunicipioSeeder extends Seeder
{
    /**
     * Los 8 municipios donde hoy hay afiliados, con su centro aproximado
     * para ubicar los pines del mapa.
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
    ];

    public function run(): void
    {
        foreach (self::MUNICIPIOS as $nombre => $datos) {
            Municipio::updateOrCreate(['slug' => $datos['slug']], ['nombre' => $nombre]);
        }
    }
}
