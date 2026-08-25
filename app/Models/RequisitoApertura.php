<?php

namespace App\Models;

use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Database\Factories\RequisitoAperturaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Producto insignia del sitio: la guía normativa, que difiere por municipio.
 */
class RequisitoApertura extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<RequisitoAperturaFactory> */
    use HasFactory;

    protected $table = 'requisitos_apertura';

    protected $guarded = ['id'];

    /**
     * Cuánto dura la tranquilidad de una verificación.
     *
     * Doce meses porque los trámites de apertura se mueven al ritmo de los
     * acuerdos municipales y de las tarifas anuales —la matrícula mercantil se
     * renueva antes del 31 de marzo de cada año—, así que un año es el ciclo
     * natural en el que algo cambia sin que nadie avise. No es una norma: es
     * criterio del gremio, y por eso vive aquí con su razón al lado y no en un
     * ajuste que nadie va a mirar.
     */
    public const int MESES_HASTA_REVISION = 12;

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'checklist' => 'array',
            'costo_aproximado' => 'decimal:2',
            'verificado_el' => 'date',
            'vigente_hasta' => 'date',
        ];
    }

    /** @return BelongsTo<Municipio, $this> */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function tieneAdjunto(): bool
    {
        return filled($this->adjunto);
    }

    public function tieneCosto(): bool
    {
        return $this->costo_aproximado !== null && (float) $this->costo_aproximado > 0;
    }

    public function estaVerificado(): bool
    {
        return $this->verificado_el !== null;
    }

    /**
     * La pila de trabajo de la oficina: lo que nadie verificó nunca y lo que
     * se verificó hace demasiado. Son dos estados distintos para el lector
     * —la vista los distingue— pero el mismo trabajo pendiente.
     *
     * El borde es estricto: a los doce meses exactos todavía sirve; al día
     * siguiente, no.
     */
    public function necesitaRevision(): bool
    {
        if ($this->verificado_el === null) {
            return true;
        }

        return $this->verificado_el->startOfDay()->lt(
            now()->subMonths(self::MESES_HASTA_REVISION)->startOfDay()
        );
    }

    /** Un decreto con fecha de muerte. Lo normal es que un trámite no la tenga. */
    public function esTransitorio(): bool
    {
        return $this->vigente_hasta !== null;
    }

    /** «Vigente hasta el 30 de noviembre» incluye el 30: la comparación es estricta contra ayer. */
    public function haCaducado(): bool
    {
        return $this->vigente_hasta !== null
            && $this->vigente_hasta->startOfDay()->lt(now()->startOfDay());
    }

    /**
     * Lo que el sitio puede mostrar hoy: lo permanente y lo que aún no vence.
     *
     * Se compone con `publicado()` en vez de meterse dentro de él, porque el
     * panel y el observer usan `publicado()` y ahí un decreto vencido sí tiene
     * que seguir viéndose: alguien tiene que poder renovarlo.
     *
     * El cierre que agrupa el `orWhere` es cinturón y tirantes, no la guarda
     * que este proyecto creyó al principio: Eloquent ya aísla las condiciones
     * de un scope local en su propio grupo —`Builder::callScope()` cuenta los
     * `where` antes y después y llama a `addNewWheresWithinGroup()`—, así que
     * `publicado()->vigente()` sale agrupado con o sin él.
     *
     * Se conserva porque el peligro es real fuera del scope: estas mismas dos
     * líneas escritas en un controlador, en un `whereRaw` o tras un `toBase()`
     * sí sueltan el `orWhere` y anulan el filtro de publicación, y entonces la
     * guía sirve borradores. El cierre hace que copiar el bloque sea seguro.
     */
    public function scopeVigente(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull('vigente_hasta')
            ->orWhere('vigente_hasta', '>=', now()->toDateString()));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'entidad', 'estado', 'municipio_id', 'costo_aproximado',
                // Cambiar una fecha de verificación es afirmar autoridad sobre
                // información legal: tiene que quedar quién y cuándo.
                'verificado_el', 'verificado_con', 'vigente_hasta',
            ])
            ->logOnlyDirty()
            ->useLogName('requisito')
            ->setDescriptionForEvent(fn (string $evento): string => "Requisito {$this->entidad}: {$evento}");
    }
}
