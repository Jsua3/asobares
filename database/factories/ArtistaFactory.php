<?php

namespace Database\Factories;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use App\Models\Artista;
use App\Models\Municipio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artista>
 */
class ArtistaFactory extends Factory
{
    protected $model = Artista::class;

    public function definition(): array
    {
        $nombre = 'DJ '.fake()->unique()->firstName();

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.Str::random(4),
            'tipo' => TipoArtista::Dj,
            'genero_musical' => 'Crossover',
            'descripcion' => fake()->paragraph(),
            'tarifa_desde' => 600000,
            'whatsapp' => '31'.fake()->numerify('########'),
            'municipio_id' => Municipio::factory(),
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
}
