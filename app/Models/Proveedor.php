<?php

namespace App\Models;

use App\Enums\CategoriaProveedor;
use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Proveedor extends Model
{
    /** @use HasFactory<\Database\Factories\ProveedorFactory> */
    use HasFactory;

    use EsPublicable, LogsActivity;

    protected $table = 'proveedores';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'categoria_proveedor' => CategoriaProveedor::class,
            'visible_hasta' => 'date',
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
     * Modela la monetizaciÃ³n futura: el proveedor paga por permanecer en la
     * base y solo se lista mientras su vigencia siga al dÃ­a.
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'estado', 'categoria_proveedor', 'visible_hasta'])
            ->logOnlyDirty()
            ->useLogName('proveedor')
            ->setDescriptionForEvent(fn (string $evento): string => "Proveedor {$this->nombre}: {$evento}");
    }
}
