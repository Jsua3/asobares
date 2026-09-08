<?php

namespace App\Filament\Widgets;

use App\Models\Transaccion;
use App\Support\FormatoMoneda;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UltimasTransacciones extends TableWidget
{
    protected static ?int $sort = 4;

    /** @var array<string, string> Desglosado: ver `PendientesDeAprobacion`. */
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 'full',
        'xl' => 'full',
    ];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimas transacciones')
            ->query(Transaccion::query()->with('asociado')->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('referencia')->label('Referencia')->copyable(),
                TextColumn::make('concepto')->label('Concepto')->badge(),
                TextColumn::make('asociado.nombre')->label('Asociado')->placeholder('—'),
                TextColumn::make('monto')->label('Monto')->formatStateUsing(fn (mixed $state): string => FormatoMoneda::pesos($state)),
                TextColumn::make('metodo')->label('Método')->badge()->color('gray'),
                TextColumn::make('estado')->label('Estado')->badge(),
                TextColumn::make('created_at')->label('Fecha')->since(),
            ]);
    }

    public static function canView(): bool
    {
        return auth()->user()?->can('ver_transaccion') === true;
    }
}
