<?php

namespace App\Models;

use App\Enums\EstadoIniciativa;
use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Database\Factories\IniciativaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Lo que el gremio ya tiene en marcha: Vibrarte, Bares Verdes, Blindando tu
 * Negocio, Noche Segura y Competitiva, Diplomado en Gerencia de Bares.
 */
class Iniciativa extends Model
{
    use EsPublicable, LogsActivity;

    /** @use HasFactory<IniciativaFactory> */
    use HasFactory;

    protected $table = 'iniciativas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoPublicacion::class,
            'estado_iniciativa' => EstadoIniciativa::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'estado', 'estado_iniciativa', 'orden'])
            ->logOnlyDirty()
            ->useLogName('iniciativa')
            ->setDescriptionForEvent(fn (string $evento): string => "Iniciativa {$this->nombre}: {$evento}");
    }
}
