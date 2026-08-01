<?php

namespace App\Models;

use Database\Factories\BeneficioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Beneficio extends Model
{
    /** @use HasFactory<BeneficioFactory> */
    use HasFactory;

    use LogsActivity;

    protected $table = 'beneficios';

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'orden'])
            ->logOnlyDirty()
            ->useLogName('beneficio')
            ->setDescriptionForEvent(fn (string $evento): string => "Beneficio {$this->titulo}: {$evento}");
    }
}
