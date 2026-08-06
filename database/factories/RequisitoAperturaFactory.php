<?php

namespace Database\Factories;

use App\Models\Municipio;
use App\Models\RequisitoApertura;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RequisitoApertura> */
class RequisitoAperturaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'municipio_id' => Municipio::factory(),
            'entidad' => fake()->company(),
            'descripcion' => fake()->paragraph(),
            'checklist' => ['Documento 1', 'Documento 2'],
            'enlace_externo' => fake()->url(),
            'adjunto' => null,
            'adjunto_nombre' => null,
            'costo_aproximado' => fake()->randomFloat(2, 0, 500000),
            'orden' => 0,
            'estado' => 'borrador',
        ];
    }

    public function publicado(): self
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'publicado',
        ]);
    }
}
