<?php

namespace App\Filament\Resources\Inscripcions\Tables;

use App\Enums\EstadoInscripcion;
use App\Models\Inscripcion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InscripcionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Inscrito')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Inscripcion $record): string => $record->correo),
                TextColumn::make('evento.titulo')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('establecimiento')
                    ->label('Establecimiento')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('transaccion.referencia')
                    ->label('Pago')
                    ->placeholder('Sin cobro')
                    ->fontFamily('mono')
                    ->copyable(),
                TextColumn::make('created_at')
                    ->label('Inscrito el')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoInscripcion::class),
                SelectFilter::make('evento')
                    ->label('Evento')
                    ->relationship('evento', 'titulo')
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()->label('Ver'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Sin inscripciones todavía')
            ->emptyStateDescription('Las inscripciones a eventos hechas desde el sitio aparecerán aquí.');
    }
}
