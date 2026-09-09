<?php

namespace Database\Factories;

use App\Enums\EstadoPublicidad;
use App\Enums\UbicacionPublicidad;
use App\Models\Publicidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publicidad>
 */
class PublicidadFactory extends Factory
{
    protected $model = Publicidad::class;

    public function definition(): array
    {
        return [
            'anunciante' => fake()->company(),
            'nombre_comercial' => fake()->company(),
            'contacto' => fake()->name(),
            'email' => fake()->safeEmail(),
            'telefono' => '31'.fake()->numerify('########'),
            'imagen' => 'publicidades/pauta.jpg',
            'url_destino' => 'https://example.com',
            'ubicacion' => UbicacionPublicidad::Inicio,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addWeek(),
            'valor' => 500000,
            'estado' => EstadoPublicidad::Borrador,
            'notas' => null,
        ];
    }

    public function publicadaVigente(): static
    {
        return $this->state([
            'estado' => EstadoPublicidad::Publicada,
            'fecha_inicio' => now()->subDay(),
            'fecha_fin' => now()->addWeek(),
        ]);
    }

    public function vencida(): static
    {
        return $this->state([
            'estado' => EstadoPublicidad::Publicada,
            'fecha_inicio' => now()->subWeeks(2),
            'fecha_fin' => now()->subDay(),
        ]);
    }

    public function enInicio(): static
    {
        return $this->state(['ubicacion' => UbicacionPublicidad::Inicio]);
    }

    public function enDirectorio(): static
    {
        return $this->state(['ubicacion' => UbicacionPublicidad::Directorio]);
    }
}
