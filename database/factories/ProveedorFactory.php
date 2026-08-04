<?php

namespace Database\Factories;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Models\Municipio;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        $nombre = fake()->unique()->company();

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.Str::random(4),
            'categoria_proveedor' => CategoriaProveedor::Hielo,
            'descripcion' => fake()->paragraph(),
            'whatsapp' => '31'.fake()->numerify('########'),
            'correo' => fake()->unique()->companyEmail(),
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

    public function vencido(): static
    {
        return $this->state(['visible_hasta' => now()->subDay()->toDateString()]);
    }
}
