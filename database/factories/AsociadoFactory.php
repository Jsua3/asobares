<?php

namespace Database\Factories;

use App\Enums\EstadoPublicacion;
use App\Models\Asociado;
use App\Models\Categoria;
use App\Models\Municipio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Asociado>
 */
class AsociadoFactory extends Factory
{
    protected $model = Asociado::class;

    public function definition(): array
    {
        $nombre = 'Bar '.fake()->unique()->company();

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.Str::random(4),
            'categoria_id' => Categoria::factory(),
            'municipio_id' => Municipio::factory(),
            'descripcion' => fake()->paragraph(),
            'direccion' => fake()->streetAddress(),
            'whatsapp' => '31'.fake()->numerify('########'),
            'horario' => 'Jue a sáb, 6:00 p. m. – 2:00 a. m.',
            'lat' => fake()->latitude(4.4, 4.7),
            'lng' => fake()->longitude(-75.8, -75.5),
            'destacado' => false,
            'estado' => EstadoPublicacion::Borrador,
            'representante' => fake()->name(),
            'correo_interno' => fake()->unique()->safeEmail(),
        ];
    }

    public function publicado(): static
    {
        return $this->state(['estado' => EstadoPublicacion::Publicado]);
    }

    public function destacado(): static
    {
        return $this->state(['destacado' => true, 'estado' => EstadoPublicacion::Publicado]);
    }
}
