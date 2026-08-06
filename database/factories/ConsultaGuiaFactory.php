<?php

namespace Database\Factories;

use App\Models\ConsultaGuia;
use App\Models\Municipio;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConsultaGuia> */
class ConsultaGuiaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'municipio_id' => Municipio::factory(),
            'requisito_apertura_id' => null,
            'created_at' => fake()->dateTimeBetween('-18 months', 'now'),
        ];
    }
}
