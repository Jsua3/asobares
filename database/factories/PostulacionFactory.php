<?php

namespace Database\Factories;

use App\Enums\EstadoDeGestion;
use App\Models\Postulacion;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Postulacion>
 */
class PostulacionFactory extends Factory
{
    protected $model = Postulacion::class;

    public function definition(): array
    {
        return [
            'vacante_id' => Vacante::factory(),
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => '31'.fake()->numerify('########'),
            'experiencia' => fake()->sentence(12),
            'estado' => EstadoDeGestion::Nuevo,
            'acepta_datos' => true,
            'consentimiento_at' => now(),
        ];
    }

    public function contactada(): static
    {
        return $this->state(['estado' => EstadoDeGestion::Contactado]);
    }
}
