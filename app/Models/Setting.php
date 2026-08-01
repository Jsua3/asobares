<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Todo el contenido institucional del sitio vive aquí (RNF-09): si un texto
 * aparece en el sitio, se edita desde el panel, no desde una vista.
 */
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $table = 'settings';

    protected $guarded = ['id'];

    private const string CLAVE_CACHE = 'settings.todos';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CLAVE_CACHE));
        static::deleted(fn () => Cache::forget(self::CLAVE_CACHE));
    }

    /**
     * Todos los ajustes indexados por clave, ya convertidos a su tipo.
     *
     * @return array<string, mixed>
     */
    public static function todos(): array
    {
        return Cache::rememberForever(self::CLAVE_CACHE, fn (): array => static::query()
            ->get()
            ->mapWithKeys(fn (Setting $ajuste): array => [$ajuste->clave => $ajuste->valorConvertido()])
            ->all());
    }

    public static function valor(string $clave, mixed $porDefecto = null): mixed
    {
        return static::todos()[$clave] ?? $porDefecto;
    }

    public function valorConvertido(): mixed
    {
        return match ($this->tipo) {
            'numero' => is_numeric($this->valor) ? $this->valor + 0 : 0,
            'booleano' => filter_var($this->valor, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $this->valor, true) ?? [],
            default => $this->valor,
        };
    }
}
