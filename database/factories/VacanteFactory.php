<?php

namespace Database\Factories;

use App\Enums\CargoDelSector;
use App\Enums\EstadoPublicacion;
use App\Enums\TipoVacante;
use App\Models\Asociado;
use App\Models\Vacante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacante>
 */
class VacanteFactory extends Factory
{
    protected $model = Vacante::class;

    public function definition(): array
    {
        return [
            'asociado_id' => Asociado::factory(),
            'cargo' => fake()->randomElement(['Bartender', 'Mesero', 'Chef de cocina', 'Administrador']),
            'categoria_cargo' => CargoDelSector::Barra,
            'tipo' => TipoVacante::PorTurnos,
            'descripcion' => fake()->paragraph(),
            'franja_horaria' => 'Vie y sáb, 6:00 p. m. – 2:00 a. m.',
            'whatsapp_contacto' => '31'.fake()->numerify('########'),
            'estado' => EstadoPublicacion::Borrador,
        ];
    }

    public function publicado(): static
    {
        return $this->state(['estado' => EstadoPublicacion::Publicado]);
    }

    public function pendiente(): static
    {
        return $this->state(['estado' => EstadoPublicacion::PendienteAprobacion]);
    }

    public function cerrada(): static
    {
        return $this->state(['cerrada_at' => now()->subDay()]);
    }

    public function vencida(): static
    {
        return $this->state(['fecha_limite' => now()->subDay()->toDateString()]);
    }

    public function momentanea(): static
    {
        return $this->state([
            'tipo' => TipoVacante::Momentaneo,
            'fecha_limite' => now()->addWeek()->toDateString(),
        ]);
    }
}
