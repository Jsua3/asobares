<?php

namespace Database\Factories;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoEvento;
use App\Models\Evento;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * El modelo importaba esta clase y la documentaba en su `@use HasFactory<…>`
 * desde el primer día, pero el archivo no existía: `Evento::factory()` lanzaba
 * `Class "Database\Factories\EventoFactory" not found`. Por eso los seis tests
 * que tocan eventos levantan sus datos con `Evento::create([...])` a mano, a
 * diez líneas por evento.
 *
 * @extends Factory<Evento>
 */
class EventoFactory extends Factory
{
    protected $model = Evento::class;

    /**
     * Nace en Borrador y no en Publicado, igual que `AsociadoFactory`: que un
     * registro salga al sitio público tiene que ser una decisión escrita en el
     * caso de prueba (`->publicado()`), nunca el descuido de no decir nada.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titulo = 'Foro '.fake()->unique()->words(3, true);

        return [
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'tipo' => TipoEvento::Evento,
            'descripcion' => fake()->paragraph(),
            'lugar' => 'Armenia, Quindío',
            'fecha_inicio' => now()->addDays(10)->setTime(9, 0),
            'fecha_fin' => null,
            'cupos' => null,
            'precio' => 0,
            'permite_inscripcion' => true,
            'estado' => EstadoPublicacion::Borrador,
        ];
    }

    public function publicado(): static
    {
        return $this->state(['estado' => EstadoPublicacion::Publicado]);
    }

    /** Ancla el evento a un día concreto: es lo que pide cada caso del calendario. */
    public function elDia(CarbonInterface $dia, int $hora = 9): static
    {
        return $this->state([
            'fecha_inicio' => $dia->copy()->setTime($hora, 0),
            'fecha_fin' => null,
        ]);
    }

    /**
     * ExpoBar dura dos días y el Congreso Nacional tres (`EventoSeeder`): el
     * evento de varios días no es una hipótesis de laboratorio, es el evento
     * más grande que publica el gremio.
     */
    public function deVariosDias(CarbonInterface $desde, int $dias): static
    {
        return $this->state([
            'fecha_inicio' => $desde->copy()->setTime(9, 0),
            'fecha_fin' => $desde->copy()->addDays($dias - 1)->setTime(18, 0),
        ]);
    }
}
