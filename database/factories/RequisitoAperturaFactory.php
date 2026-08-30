<?php

namespace Database\Factories;

use App\Enums\EstadoPublicacion;
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
            'estado' => EstadoPublicacion::Borrador,
        ];
    }

    public function publicado(): self
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoPublicacion::Publicado,
        ]);
    }

    public function verificado(?string $fecha = null): self
    {
        return $this->state(fn (array $attributes) => [
            'verificado_el' => $fecha ?? now()->toDateString(),
            'verificado_con' => 'Documento oficial entregado por la entidad',
        ]);
    }

    /** Un decreto transitorio: caduca, pero todavía no. */
    public function transitorio(?string $hasta = null): self
    {
        return $this->state(fn (array $attributes) => [
            'vigente_hasta' => $hasta ?? now()->addMonth()->toDateString(),
        ]);
    }

    public function caducado(): self
    {
        return $this->state(fn (array $attributes) => [
            'vigente_hasta' => now()->subDay()->toDateString(),
        ]);
    }
}
