<?php

namespace Database\Factories;

use App\Enums\EstadoPublicacion;
use App\Models\Aliado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * El modelo importaba esta clase en su `@use HasFactory<…>` y el archivo no
 * existía: `Aliado::factory()` lanzaba `Class "Database\Factories\AliadoFactory"
 * not found`, y con él se caía todo el conjunto de datos de
 * `AutorizacionDeBorradoTest`, que descubre las políticas por reflexión.
 *
 * @extends Factory<Aliado>
 */
class AliadoFactory extends Factory
{
    protected $model = Aliado::class;

    /**
     * Nace en Borrador, igual que `EventoFactory` y `AsociadoFactory`: salir al
     * sitio público tiene que ser una decisión escrita en el caso de prueba.
     *
     * `activo` sí nace en true, y a propósito: es el valor por defecto de la
     * columna (`create_aliados_table`, línea 23). La fábrica describe el
     * esquema, no el formulario del panel —que hoy no declara `->default()` y
     * por eso crea aliados apagados—; si un día los dos coinciden, esta línea
     * lo seguirá diciendo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'logo' => null,
            'url' => fake()->url(),
            'descripcion' => fake()->sentence(),
            'detalle_convenio' => null,
            'orden' => 0,
            'estado' => EstadoPublicacion::Borrador,
            'activo' => true,
        ];
    }

    public function publicado(): static
    {
        return $this->state(['estado' => EstadoPublicacion::Publicado]);
    }

    /**
     * Las dos compuertas a la vez, que es lo que `Aliado::scopeVisible` exige
     * para pintar el logo en el carrusel de la portada. Aprobar sin esto deja
     * el convenio invisible, que es el defecto que documenta PANEL-02.
     */
    public function visible(): static
    {
        return $this->state([
            'estado' => EstadoPublicacion::Publicado,
            'activo' => true,
        ]);
    }

    /** El detalle del convenio sólo lo ven los asociados con sesión iniciada. */
    public function conConvenioPrivado(): static
    {
        return $this->state(['detalle_convenio' => fake()->paragraph()]);
    }
}
