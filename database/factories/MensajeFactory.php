<?php

namespace Database\Factories;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use App\Models\Mensaje;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mensaje>
 */
class MensajeFactory extends Factory
{
    protected $model = Mensaje::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo' => TipoMensaje::Contacto,
            'nombre' => fake()->name(),
            'correo' => fake()->safeEmail(),
            'telefono' => fake()->numerify('3#########'),
            'mensaje' => fake()->paragraph(),
            'acepta_datos' => true,
            'consentimiento_at' => now(),
            'estado' => EstadoMensaje::Nuevo,
        ];
    }

    /** Una PQR con su radicado, que es lo único que lleva plazo de ley. */
    public function pqr(): static
    {
        return $this->state(fn (array $atributos): array => [
            'tipo' => TipoMensaje::Pqr,
            'radicado' => 'PQR-'.now()->year.'-'.fake()->unique()->numerify('####'),
        ]);
    }

    public function respondido(): static
    {
        return $this->state(fn (array $atributos): array => [
            'estado' => EstadoMensaje::Respondido,
            'respondido_at' => now(),
        ]);
    }
}
