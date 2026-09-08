<?php

namespace Database\Factories;

use App\Enums\EstadoSolicitudAfiliacion;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\SolicitudAfiliacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudAfiliacion>
 */
class SolicitudAfiliacionFactory extends Factory
{
    protected $model = SolicitudAfiliacion::class;

    public function definition(): array
    {
        return [
            'solicitante_nombre' => fake()->name(),
            'solicitante_identificacion' => fake()->numerify('##########'),
            'solicitante_telefono' => fake()->numerify('3#########'),
            'solicitante_correo' => fake()->unique()->safeEmail(),
            'solicitante_cargo' => 'Propietario',
            'establecimiento_nombre' => fake()->company(),
            'razon_social' => fake()->company().' S.A.S.',
            'nit' => fake()->numerify('#########-#'),
            'municipio_id' => Municipio::factory(),
            'direccion' => fake()->streetAddress(),
            'establecimiento_telefono' => fake()->numerify('3#########'),
            'establecimiento_correo' => fake()->unique()->companyEmail(),
            'categoria_id' => Categoria::factory(),
            'descripcion' => fake()->paragraph(),
            'acepta_datos' => true,
            'consentimiento_at' => now(),
            'estado' => EstadoSolicitudAfiliacion::Pendiente,
        ];
    }
}
