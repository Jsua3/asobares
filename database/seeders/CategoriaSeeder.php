<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /** @var array<string, string> */
    public const array CATEGORIAS = [
        'Bar' => 'bar',
        'Gastrobar' => 'gastrobar',
        'Café' => 'cafe',
        'Discoteca' => 'discoteca',
        'Restaurante bar' => 'restaurante-bar',
        'Rooftop' => 'rooftop',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIAS as $nombre => $slug) {
            Categoria::updateOrCreate(['slug' => $slug], ['nombre' => $nombre]);
        }
    }
}
