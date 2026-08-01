<?php

namespace Database\Factories;

use App\Models\Municipio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Municipio>
 */
class MunicipioFactory extends Factory
{
    protected $model = Municipio::class;

    public function definition(): array
    {
        $nombre = fake()->unique()->city();

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.Str::random(4),
        ];
    }
}
