<?php

namespace App\Models;

use App\Enums\EstadoSolicitudAfiliacion;
use Database\Factories\SolicitudAfiliacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SolicitudAfiliacion extends Model
{
    /** @use HasFactory<SolicitudAfiliacionFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'solicitudes_afiliacion';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'estado' => EstadoSolicitudAfiliacion::class,
            'acepta_datos' => 'boolean',
            'consentimiento_at' => 'datetime',
            'visita_programada_at' => 'datetime',
            'resuelto_at' => 'datetime',
            'aprobado_at' => 'datetime',
            'rechazado_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Municipio, $this> */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /** @return BelongsTo<Categoria, $this> */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** @return BelongsTo<Asociado, $this> */
    public function asociado(): BelongsTo
    {
        return $this->belongsTo(Asociado::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    /** @return BelongsTo<User, $this> */
    public function rechazador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rechazado_por');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['establecimiento_nombre', 'solicitante_nombre', 'estado', 'municipio_id', 'categoria_id'])
            ->logOnlyDirty()
            ->useLogName('solicitud_afiliacion')
            ->setDescriptionForEvent(fn (string $evento): string => "Solicitud de afiliación {$this->establecimiento_nombre}: {$evento}");
    }
}
