<?php

namespace Database\Factories;

use App\Enums\EstadoIniciativa;
use App\Enums\EstadoPublicacion;
use App\Models\Iniciativa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Iniciativa>
 */
class IniciativaFactory extends Factory
{
    protected $model = Iniciativa::class;

    public function definition(): array
    {
        $nombre = Str::ucfirst(fake()->unique()->words(2, true));

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.Str::random(4),
            'resumen' => fake()->sentence(),
            'descripcion' => fake()->paragraph(),
            'estado_iniciativa' => fake()->randomElement(EstadoIniciativa::cases()),
            'linea' => fake()->randomElement(['Seguridad', 'Cultura', 'Sostenibilidad']),
            'orden' => fake()->numberBetween(1, 10),
            'estado' => EstadoPublicacion::Borrador,
        ];
    }

    public function publicado(): static
    {
        return $this->state(['estado' => EstadoPublicacion::Publicado]);
    }
}
