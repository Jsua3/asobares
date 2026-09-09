<?php

namespace App\Models;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use Carbon\CarbonImmutable;
use Database\Factories\MensajeFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mensaje extends Model
{
    /** @use HasFactory<MensajeFactory> */
    use HasFactory;

    protected $table = 'mensajes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tipo' => TipoMensaje::class,
            'estado' => EstadoMensaje::class,
            'acepta_datos' => 'boolean',
            'consentimiento_at' => 'datetime',
            'respondido_at' => 'datetime',
        ];
    }

    /**
     * Lo que la ley da para responder una PQR: quince días hábiles
     * (Ley 1755 de 2015, artículo 14, para las peticiones generales).
     */
    public const int DIAS_HABILES_DE_PLAZO = 15;

    public function esPqr(): bool
    {
        return $this->tipo === TipoMensaje::Pqr;
    }

    /**
     * Cuándo vence el plazo legal de esta PQR, o `null` si no corre ninguno.
     *
     * No corre en dos casos: cuando no es PQR --un mensaje de contacto no tiene
     * término de ley-- y cuando ya se respondió, que es lo que para el reloj.
     *
     * ⚠️ **Se cuentan días de semana, no días hábiles de verdad: los festivos
     * colombianos no se descuentan** porque su calendario no está en el
     * proyecto y meterlo a medias sería peor que no tenerlo. El error va del
     * lado seguro --sin festivos la fecha sale ANTES que la legal, nunca
     * después--, así que el panel avisa con margen. Si algún día hace falta la
     * fecha exacta (para responderle a la SIC, por ejemplo), esto hay que
     * cambiarlo por un calendario de verdad y no por una aproximación mejor.
     */
    public function venceEl(): ?CarbonImmutable
    {
        if (! $this->esPqr() || $this->estado === EstadoMensaje::Respondido) {
            return null;
        }

        return CarbonImmutable::parse($this->created_at)
            ->startOfDay()
            ->addWeekdays(self::DIAS_HABILES_DE_PLAZO);
    }

    /** Si hoy ya pasó el plazo de una PQR que sigue sin responder. */
    public function plazoVencido(): bool
    {
        $vence = $this->venceEl();

        return $vence !== null && $vence->isPast();
    }

    /**
     * Las PQR abiertas que ya se pasaron de plazo.
     *
     * Se resuelve en PHP y no en SQL a propósito: el cálculo de días hábiles no
     * se puede expresar en una consulta portable entre SQLite y PostgreSQL, y
     * esta bandeja son decenas de filas, no millones. El día que lo sean, la
     * fecha de vencimiento se guarda en una columna al radicar.
     *
     * @return Collection<int, Mensaje>
     */
    public static function pqrVencidas(): Collection
    {
        return static::query()
            ->whereNotNull('radicado')
            ->where('estado', '!=', EstadoMensaje::Respondido)
            ->get()
            ->filter(fn (Mensaje $mensaje): bool => $mensaje->plazoVencido());
    }

    /**
     * Consecutivo anual de PQR: PQR-2026-0001, PQR-2026-0002...
     *
     * Se bloquea la tabla durante el cálculo para que dos envíos simultáneos
     * no reciban el mismo radicado.
     */
    public static function generarRadicado(?int $anio = null): string
    {
        $anio ??= now()->year;
        $prefijo = "PQR-{$anio}-";

        return DB::transaction(function () use ($prefijo): string {
            // Este `like` se queda sensible a mayúsculas a propósito, y por eso
            // no se convirtió a `whereLike(caseSensitive: false)` como el
            // buscador del directorio.
            //
            // El cambio de semántica entre SQLite (insensible) y PostgreSQL
            // (sensible) no puede morder aquí: `radicado` no lo escribe nadie.
            // Lo produce siempre este mismo método con el prefijo en
            // mayúsculas, sus dos únicos llamadores son `ContactoController` y
            // `MensajeSeeder`, y el campo del panel está `->disabled()`
            // (MensajeForm.php:33). Verificado además contra PostgreSQL 17:
            // el consecutivo sale igual en los dos motores.
            //
            // Y si algún día se ensuciara, insensible sería la respuesta
            // equivocada: ampliaría el barrido a radicados de otro prefijo y
            // el consecutivo dejaría de ser el del año.
            $ultimo = static::query()
                ->where('radicado', 'like', "{$prefijo}%")
                ->lockForUpdate()
                ->orderByDesc('radicado')
                ->value('radicado');

            $consecutivo = $ultimo === null ? 1 : ((int) substr($ultimo, strlen($prefijo))) + 1;

            return $prefijo.str_pad((string) $consecutivo, 4, '0', STR_PAD_LEFT);
        });
    }
}
