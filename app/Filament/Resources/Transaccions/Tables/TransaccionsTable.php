<?php

namespace App\Filament\Resources\Transaccions\Tables;

use App\Enums\ConceptoTransaccion;
use App\Enums\EstadoTransaccion;
use App\Enums\MetodoPago;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Solo lectura: las transacciones las escribe la pasarela, no una persona.
 */
class TransaccionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referencia')
                    ->label('Referencia')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Referencia copiada')
                    ->fontFamily('mono'),
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->badge()
                    ->sortable(),
                TextColumn::make('asociado.nombre')
                    ->label('Asociado')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('inscripcion.evento.titulo')
                    ->label('Evento')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->money('COP', locale: 'es_CO')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')->money('COP', locale: 'es_CO')),
                TextColumn::make('metodo')
                    ->label('Método')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')->label('Estado')->options(EstadoTransaccion::class),
                SelectFilter::make('concepto')->label('Concepto')->options(ConceptoTransaccion::class),
                SelectFilter::make('metodo')->label('Método')->options(MetodoPago::class),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateHeading('Todavía no hay transacciones')
            ->emptyStateDescription('Aquí quedará el registro de cada pago que pase por la pasarela.');
    }
}
