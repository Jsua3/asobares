<?php

namespace App\Filament\Resources\Iniciativas\Tables;

use App\Enums\EstadoIniciativa;
use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use App\Models\Iniciativa;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IniciativasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orden')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Iniciativa')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Iniciativa $record): ?string => $record->resumen),
                TextColumn::make('estado_iniciativa')
                    ->label('¿En qué punto va?')
                    ->badge()
                    ->sortable(),
                TextColumn::make('linea')
                    ->label('Línea')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lugar')
                    ->label('Dónde')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estado')
                    ->label('Publicación')
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('orden')
            ->reorderable('orden')
            ->filters([
                SelectFilter::make('estado_iniciativa')
                    ->label('¿En qué punto va?')
                    ->options(EstadoIniciativa::class),
                SelectFilter::make('estado')
                    ->label('Publicación')
                    ->options(EstadoPublicacion::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ...AccionesDeAprobacion::paraFila(),
                    EditAction::make()->label('Editar'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Acciones de la iniciativa'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                AccionesDeAprobacion::aprobarEnLote('publicar_iniciativa'),
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Todavía no hay iniciativas')
            ->emptyStateDescription('Aquí va el portafolio de lo que el gremio tiene en marcha.');
    }
}
