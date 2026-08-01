<?php

namespace App\Models;

use App\Enums\CategoriaNoticia;
use App\Enums\EstadoPublicacion;
use App\Models\Concerns\EsPublicable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Noticia extends Model
{
    /** @use HasFactory<\Database\Factories\NoticiaFactory> */
    use HasFactory;

    use EsPublicable, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'estado', 'categoria', 'publicado_at'])
            ->logOnlyDirty()
            ->useLogName('noticia')
            ->setDescriptionForEvent(fn (string $evento): string => "Noticia {$this->titulo}: {$evento}");
    }
}
