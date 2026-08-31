<?php

namespace App\Models;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Database\Factories\ProveedorFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Proveedor extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<ProveedorFactory> */
    use HasFactory;

    protected $table = 'proveedores';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'categoria_proveedor' => CategoriaProveedor::class,
            'visible_hasta' => 'date',
            'verificado_el' => 'date',
            'acepta_datos' => 'boolean',
            'consentimiento_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<Municipio, $this> */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Dueño de la ficha. Hoy siempre nulo: las fichas las carga el gremio.
     * Existe para el día en que artistas y proveedores tengan cuenta propia.
     *
     * @return BelongsTo<User, $this>
     */
    public function duenio(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Modela la monetización futura: el proveedor paga por permanecer en la
     * base y solo se lista mientras su vigencia siga al día.
     */
    public function scopeVigente(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('visible_hasta')->orWhereDate('visible_hasta', '>=', now()->toDateString());
        });
    }

    public function estaVigente(): bool
    {
        return $this->visible_hasta === null || $this->visible_hasta->gte(now()->startOfDay());
    }

    /**
     * Cada cuánto hay que volver a llamar a un proveedor (OBS3-12).
     *
     * SEIS meses, la mitad que la guía normativa, y la razón es que envejecen
     * distinto. Un trámite de apertura cambia cuando cambia un acuerdo
     * municipal --ritmo anual--; un proveedor cambia de número, de dueño o de
     * oficio cuando le va mal un semestre. La queja del gremio fue literal:
     * «ya no existe, ya no contestan» (R22 04:19).
     *
     * Vive aquí, con su razón al lado, y no en un ajuste que nadie mira: es
     * criterio del gremio, no una norma.
     */
    public const int MESES_HASTA_REVISION = 6;

    /** ¿Alguien confirmó alguna vez que este contacto sirve? */
    public function estaVerificado(): bool
    {
        return $this->verificado_el !== null;
    }

    /**
     * La pila de trabajo de la oficina: lo que nadie verificó nunca y lo que
     * se verificó hace demasiado. Son dos estados distintos para el lector
     * --la vista los distingue-- pero el mismo trabajo pendiente.
     *
     * El borde es estricto: a los seis meses exactos todavía sirve; al día
     * siguiente, no.
     */
    public function necesitaRevision(): bool
    {
        if ($this->verificado_el === null) {
            return true;
        }

        // `subMonthsNoOverflow`, o el borde estricto de arriba deja de serlo:
        // la resta corriente desborda los días 29, 30 y 31 y adelanta el
        // corte hasta dos días, marcando como caducada una ficha que todavía
        // está dentro del plazo. Es el defecto del §28, y aquí se paga igual.
        return $this->verificado_el->copy()->startOfDay()->lt(
            now()->subMonthsNoOverflow(self::MESES_HASTA_REVISION)->startOfDay()
        );
    }

    /**
     * Los que le tocan a la oficina, del más viejo al más nuevo. Sin
     * verificar primero, porque nunca haberlo hecho es peor que hacerlo tarde.
     */
    public function scopeNecesitaRevision(Builder $query): Builder
    {
        return $query
            ->where(function (Builder $q): void {
                $q->whereNull('verificado_el')
                    ->orWhereDate('verificado_el', '<', now()->subMonthsNoOverflow(self::MESES_HASTA_REVISION)->toDateString());
            })
            ->orderByRaw('verificado_el is not null')
            ->orderBy('verificado_el');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'estado', 'categoria_proveedor', 'visible_hasta', 'verificado_el', 'verificado_con'])
            ->logOnlyDirty()
            ->useLogName('proveedor')
            ->setDescriptionForEvent(fn (string $evento): string => "Proveedor {$this->nombre}: {$evento}");
    }
}
