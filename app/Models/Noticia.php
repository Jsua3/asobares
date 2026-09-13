<?php

namespace App\Models;

use App\Enums\CategoriaNoticia;
use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Database\Factories\NoticiaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class Noticia extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<NoticiaFactory> */
    use HasFactory;

    protected $table = 'noticias';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'categoria' => CategoriaNoticia::class,
            'publicado_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Publicada y con fecha ya cumplida. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->publicado()
            ->whereNotNull('publicado_at')
            ->where('publicado_at', '<=', now())
            ->orderByDesc('publicado_at');
    }

    /**
     * El cuerpo de la noticia listo para imprimirse sin escapar.
     *
     * El panel lo edita en un Textarea de texto plano, pero la ficha lo pinta
     * como HTML, así que dos párrafos separados por una línea en blanco salían
     * como un solo bloque (SEG-04). Si el contenido no trae etiquetas, cada
     * bloque separado por líneas en blanco pasa a ser un `<p>` y los saltos
     * simples se conservan como `<br>`. Si trae HTML (lo sembrado, o lo que
     * llegue de un editor enriquecido) se deja como estaba. En los dos casos
     * el resultado pasa por el saneado: la conversión va antes, nunca después.
     */
    public function contenidoSaneado(): string
    {
        $contenido = (string) $this->contenido;

        if (! self::traeEtiquetasHtml($contenido)) {
            $contenido = self::parrafosDesdeTextoPlano($contenido);
        }

        return (new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->allowSafeElements()
                ->allowRelativeLinks()
                ->forceHttpsUrls()
        ))->sanitize($contenido);
    }

    /**
     * Una etiqueta de verdad, de apertura o de cierre. Un «<3», un «a < b» o
     * un correo entre ángulos no cuentan: son texto y se escapan como tal.
     */
    private static function traeEtiquetasHtml(string $contenido): bool
    {
        return preg_match('/<\/?[a-z][a-z0-9-]*(\s[^>]*)?\/?>/i', $contenido) === 1;
    }

    /**
     * Parte el texto por líneas en blanco, escapa cada bloque y lo envuelve en
     * `<p>`. `\R` cubre el CRLF con el que el navegador envía un Textarea.
     */
    private static function parrafosDesdeTextoPlano(string $texto): string
    {
        $bloques = preg_split('/\R[ \t]*(?:\R[ \t]*)+/u', trim($texto)) ?: [];

        return collect($bloques)
            ->map(fn (string $bloque): string => trim($bloque))
            ->filter(fn (string $bloque): bool => $bloque !== '')
            ->map(fn (string $bloque): string => '<p>'.preg_replace('/[ \t]*\R[ \t]*/u', '<br>', e($bloque)).'</p>')
            ->implode("\n");
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'estado', 'categoria', 'publicado_at'])
            ->logOnlyDirty()
            ->useLogName('noticia')
            ->setDescriptionForEvent(fn (string $evento): string => "Noticia {$this->titulo}: {$evento}");
    }
}
