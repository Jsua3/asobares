<?php

namespace App\Models;

use App\Enums\Alcance;
use Database\Factories\BeneficioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Beneficio extends Model
{
    /** @use HasFactory<BeneficioFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'beneficios';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'alcance' => Alcance::class,
        ];
    }

    /**
     * El municipio solo significa algo en un beneficio municipal, así que el
     * modelo lo suelta cuando el alcance deja de serlo.
     *
     * Va aquí y no en el formulario a propósito: el formulario cubre a quien
     * teclea, y esta fila la puede cambiar también un sembrador, una consola o
     * una importación. Una fila con alcance nacional y municipio puesto le
     * mentiría a cualquier consulta que filtre por municipio, y nadie la vería
     * porque el sitio no pinta ese dato cuando el alcance no es municipal.
     */
    protected static function booted(): void
    {
        static::saving(function (Beneficio $beneficio): void {
            if ($beneficio->alcance?->exigeMunicipio() !== true) {
                $beneficio->municipio_id = null;
            }
        });
    }

    /** @return BelongsTo<Municipio, $this> */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Lo que el sitio público escribe encima del beneficio, o nada.
     *
     * Nada en dos casos: sin clasificar --el gremio todavía no dijo de quién
     * es-- y municipal sin municipio, que no debería existir pero si existiera
     * la etiqueta sería «Municipal» a secas, que no informa de nada.
     */
    public function etiquetaDeAlcance(): ?string
    {
        return match ($this->alcance) {
            null => null,
            Alcance::Municipal => $this->municipio?->nombre,
            default => $this->alcance->getLabel(),
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'orden', 'alcance', 'municipio_id'])
            ->logOnlyDirty()
            ->useLogName('beneficio')
            ->setDescriptionForEvent(fn (string $evento): string => "Beneficio {$this->titulo}: {$evento}");
    }
}
