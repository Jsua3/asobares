<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    protected $model = Categoria::class;

    public function definition(): array
    {
        $nombre = fake()->unique()->word();

        return [
            'nombre' => Str::ucfirst($nombre),
            'slug' => Str::slug($nombre).'-'.Str::random(4),
        ];
    }
}
