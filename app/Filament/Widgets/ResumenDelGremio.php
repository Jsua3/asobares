<?php

namespace App\Filament\Widgets;

use App\Enums\EstadoInscripcion;
use App\Enums\EstadoMensaje;
use App\Models\Asociado;
use App\Models\Aspirante;
use App\Models\Cartera;
use App\Models\Inscripcion;
use App\Models\Mensaje;
use App\Models\Transaccion;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenDelGremio extends StatsOverviewWidget
{
    protected ?string $heading = 'El gremio hoy';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $enMora = Cartera::enMora()->count();
        $saldoTotal = (float) Cartera::enMora()->sum('saldo_pendiente');
        $recaudadoDelMes = (float) Transaccion::aprobada()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('monto');

        return [
            Stat::make('Asociados publicados', Asociado::publicado()->count())
                ->description(Asociado::publicado()->distinct('municipio_id')->count('municipio_id').' municipios con presencia')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('success'),

            Stat::make('Afiliados en mora', $enMora)
                ->description($this->pesos($saldoTotal).' por recaudar')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($enMora > 0 ? 'danger' : 'success'),

            Stat::make('Recaudado este mes', $this->pesos($recaudadoDelMes))
                ->description(Transaccion::aprobada()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count().' transacciones aprobadas')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('Mensajes nuevos', Mensaje::where('estado', EstadoMensaje::Nuevo)->count())
                ->description(Mensaje::whereNotNull('radicado')->where('estado', '!=', EstadoMensaje::Respondido)->count().' PQR sin responder')
                ->descriptionIcon('heroicon-o-inbox')
                ->color('warning'),

            Stat::make('Aspirantes de la semana', Aspirante::where('created_at', '>=', now()->subWeek())->count())
                ->description('Perfiles nuevos en la bolsa de empleo')
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('info'),

            Stat::make('Inscripciones de la semana', Inscripcion::where('created_at', '>=', now()->subWeek())->count())
                ->description(Inscripcion::where('estado', EstadoInscripcion::Confirmada)->count().' confirmadas en total')
                ->descriptionIcon('heroicon-o-ticket')
                ->color('info'),
        ];
    }

    private function pesos(float $monto): string
    {
        return '$'.number_format($monto, 0, ',', '.');
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('ver_asociado') === true;
    }
}
