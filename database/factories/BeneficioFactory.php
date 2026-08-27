<?php

namespace Database\Factories;

use App\Models\Beneficio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * El modelo importaba esta clase en su `@use HasFactory<…>` y el archivo no
 * existía: `Beneficio::factory()` lanzaba
 * `Class "Database\Factories\BeneficioFactory" not found`.
 *
 * @extends Factory<Beneficio>
 */
class BeneficioFactory extends Factory
{
    protected $model = Beneficio::class;

    /**
     * `Beneficio` no usa `EsPublicable`: no tiene `estado`, así que no hay nada
     * que aprobar. Lo único que decide si sale en la portada es `orden`.
     *
     * El icono se deja en el valor por defecto de la columna a propósito: es un
     * nombre de Heroicon que la portada renderiza sin comprobar que exista
     * (`inicio.blade.php`, línea 124), y un nombre inventado por la fábrica
     * haría reventar con 500 cualquier prueba que cargue `/`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => fake()->unique()->sentence(3),
            'descripcion' => fake()->paragraph(),
            'icono' => 'heroicon-o-check-badge',
            'orden' => 0,
        ];
    }
}
