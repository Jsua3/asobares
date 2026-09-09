<?php

namespace App\Models;

use App\Enums\EstadoPublicidad;
use App\Enums\UbicacionPublicidad;
use Database\Factories\PublicidadFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Publicidad extends Model
{
    /** @use HasFactory<PublicidadFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'publicidades';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicidad::class,
            'ubicacion' => UbicacionPublicidad::class,
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'valor' => 'decimal:2',
            'aprobado_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Publicidad $publicidad): void {
            $publicidad->validarDatosCriticos();

            if ($publicidad->estado !== EstadoPublicidad::Publicada) {
                return;
            }

            $publicidad->validarPublicacion();

            $usuario = auth()->user();

            if ($usuario !== null && $usuario->can('publicar', $publicidad)) {
                $publicidad->aprobado_por ??= $usuario->getKey();
                $publicidad->aprobado_at ??= now();
            }
        });
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function scopeVigente(Builder $query, ?Carbon $momento = null): Builder
    {
        $momento ??= now();

        return $query
            ->where('estado', EstadoPublicidad::Publicada)
            ->where('fecha_inicio', '<=', $momento)
            ->where('fecha_fin', '>=', $momento);
    }

    public function scopeEnUbicacion(Builder $query, UbicacionPublicidad|string $ubicacion): Builder
    {
        $valor = $ubicacion instanceof UbicacionPublicidad ? $ubicacion->value : $ubicacion;

        return $query->where('ubicacion', $valor);
    }

    public function scopePublicaEn(Builder $query, UbicacionPublicidad $ubicacion, ?Carbon $momento = null): Builder
    {
        return $query
            ->vigente($momento)
            ->enUbicacion($ubicacion)
            ->orderBy('fecha_inicio')
            ->orderBy('id');
    }

    public function visiblePublicamente(?Carbon $momento = null): bool
    {
        $momento ??= now();

        return $this->estado === EstadoPublicidad::Publicada
            && filled($this->imagen)
            && $this->fecha_inicio !== null
            && $this->fecha_fin !== null
            && $this->fecha_inicio->lte($momento)
            && $this->fecha_fin->gte($momento);
    }

    public function puedePublicarse(): bool
    {
        return filled($this->imagen)
            && $this->fecha_inicio !== null
            && $this->fecha_fin !== null
            && $this->fecha_fin->gte($this->fecha_inicio);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'anunciante',
                'nombre_comercial',
                'contacto',
                'email',
                'telefono',
                'imagen',
                'url_destino',
                'ubicacion',
                'fecha_inicio',
                'fecha_fin',
                'valor',
                'estado',
                'aprobado_por',
                'aprobado_at',
                'notas',
            ])
            ->logOnlyDirty()
            ->useLogName('publicidad')
            ->setDescriptionForEvent(fn (string $evento): string => "Publicidad {$this->anunciante}: {$evento}");
    }

    private function validarDatosCriticos(): void
    {
        $errores = [];

        if (filled($this->url_destino)) {
            $esquema = parse_url((string) $this->url_destino, PHP_URL_SCHEME);

            if (! in_array(strtolower((string) $esquema), ['http', 'https'], true)) {
                $errores['url_destino'] = 'La URL de destino debe iniciar con http:// o https://.';
            }
        }

        if ((float) $this->valor < 0) {
            $errores['valor'] = 'El valor de la pauta no puede ser negativo.';
        }

        if ($this->fecha_inicio !== null && $this->fecha_fin !== null && $this->fecha_fin->lt($this->fecha_inicio)) {
            $errores['fecha_fin'] = 'La fecha final debe ser posterior o igual a la fecha inicial.';
        }

        if ($errores !== []) {
            throw ValidationException::withMessages($errores);
        }
    }

    private function validarPublicacion(): void
    {
        if ($this->puedePublicarse()) {
            return;
        }

        throw ValidationException::withMessages([
            'estado' => 'Para publicar la pauta debe tener imagen y fechas validas.',
        ]);
    }
}
