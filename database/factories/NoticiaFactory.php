<?php

namespace Database\Factories;

use App\Enums\CategoriaNoticia;
use App\Enums\EstadoPublicacion;
use App\Models\Noticia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * El modelo importaba esta clase en su `@use HasFactory<…>` y el archivo no
 * existía: `Noticia::factory()` lanzaba
 * `Class "Database\Factories\NoticiaFactory" not found`.
 *
 * @extends Factory<Noticia>
 */
class NoticiaFactory extends Factory
{
    protected $model = Noticia::class;

    /**
     * Nace en Borrador y con `publicado_at` en null, igual que la migración.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titulo = 'Boletín '.fake()->unique()->words(3, true);

        return [
            'titulo' => $titulo,
            'slug' => Str::slug($titulo),
            'extracto' => fake()->sentence(),
            'contenido' => fake()->paragraphs(3, true),
            'imagen' => null,
            'categoria' => CategoriaNoticia::Noticia,
            'publicado_at' => null,
            'estado' => EstadoPublicacion::Borrador,
        ];
    }

    /**
     * Aprobado por la dirección, y nada más. Se deja `publicado_at` en null a
     * propósito: es exactamente el estado en el que «Aprobar y publicar» deja
     * hoy una noticia, y con el que `Noticia::scopeVisible` NO la devuelve.
     * Una prueba que quiera la entrada en `/boletin` tiene que pedir
     * `->visible()` y decirlo.
     */
    public function publicado(): static
    {
        return $this->state(['estado' => EstadoPublicacion::Publicado]);
    }

    /** Las dos compuertas: aprobada y con la fecha ya cumplida. */
    public function visible(): static
    {
        return $this->state([
            'estado' => EstadoPublicacion::Publicado,
            'publicado_at' => now()->subDay(),
        ]);
    }

    /** Programada para más adelante: aprobada, pero todavía fuera del sitio. */
    public function programada(): static
    {
        return $this->state([
            'estado' => EstadoPublicacion::Publicado,
            'publicado_at' => now()->addWeek(),
        ]);
    }
}
