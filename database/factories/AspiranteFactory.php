<?php

namespace Database\Factories;

use App\Enums\CargoDelSector;
use App\Enums\EstadoDeGestion;
use App\Models\Aspirante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Aspirante>
 */
class AspiranteFactory extends Factory
{
    protected $model = Aspirante::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->name(),
            'correo' => fake()->unique()->safeEmail(),
            'telefono' => '31'.fake()->numerify('########'),
            'cargo_interes' => 'Bartender',
            'categoria_cargo' => CargoDelSector::Barra,
            'experiencia' => fake()->sentence(12),
            'estado' => EstadoDeGestion::Nuevo,
            'acepta_datos' => true,
            'consentimiento_at' => now(),
        ];
    }

    public function contactado(): static
    {
        return $this->state(['estado' => EstadoDeGestion::Contactado]);
    }

    /** Perfil cuyo consentimiento venció hace tiempo: la depuración debe barrerlo. */
    public function abandonado(): static
    {
        return $this->state(['consentimiento_at' => now()->subMonths(18)]);
    }
}
