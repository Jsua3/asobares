<?php

namespace App\Filament\Resources\Carteras\Tables;

use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CarterasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asociado.nombre')
                    ->label('Establecimiento')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('asociado.municipio.nombre')
                    ->label('Municipio')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('meses_mora')
                    ->label('Mora')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'success',
                        $state <= 2 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? 'Al día' : "{$state} meses")
                    ->sortable(),
                TextColumn::make('saldo_pendiente')
                    ->label('Saldo pendiente')
                    ->money('COP', locale: 'es_CO')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money('COP', locale: 'es_CO')),
                TextColumn::make('ultimo_pago_at')
                    ->label('Último pago')
                    ->date('d/m/Y')
                    ->placeholder('Sin registro')
                    ->sortable(),
                TextColumn::make('actualizado_at')
                    ->label('Actualizado')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('meses_mora', 'desc')
            ->filters([
                Filter::make('en_mora')
                    ->label('Solo en mora')
                    ->query(fn (Builder $query): Builder => $query->where('meses_mora', '>', 0)),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('Sin estados de cuenta')
            ->emptyStateDescription('Importa el CSV de la contadora para verlos aquí.');
    }
}
