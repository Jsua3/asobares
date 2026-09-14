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
     * como HTML, así que sin conversión dos párrafos separados por una línea
     * en blanco saldrían como un solo bloque. Si el contenido no trae
     * etiquetas, cada bloque separado por líneas en blanco pasa a ser un `<p>`
     * y los saltos simples se conservan como `<br>`. Si trae HTML (lo
     * sembrado, o lo que llegue de un editor enriquecido) se deja como estaba.
     * En los dos casos el resultado pasa por el saneado: la conversión va
     * antes, nunca después.
     */
    public function contenidoSaneado(): string
    {
        // Un byte UTF-8 roto hace que las expresiones con /u devuelvan false,
        // y la noticia saldría vacía sin avisar: se sustituye antes de mirar.
        $contenido = mb_scrub((string) $this->contenido, 'UTF-8');

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
     * Una etiqueta HTML de verdad, de apertura o de cierre, de las que escriben
     * un editor o el sembrador. Un «<3», un «a < b», un correo entre ángulos o
     * un marcador como «<de 8 a 12>» o «<nombre del contacto>» no cuentan: son
     * texto. Tomarlos por etiqueta haría que el saneado se comiera todo lo que
     * viene detrás. El enlace solo cuenta con `href`, para que «<a lo sumo
     * cinco>» siga siendo texto.
     */
    private static function traeEtiquetasHtml(string $contenido): bool
    {
        return preg_match(
            '/<\/?(?:a(?=\s[^>]*\bhref\b|>)|p|br|hr|strong|em|b|i|u|s|small|mark|span|div|ul|ol|li|h[1-6]|blockquote|code|pre|table|thead|tbody|tr|th|td|figure|figcaption|img)(?:\s[^>]*)?\/?>/i',
            $contenido
        ) === 1;
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
