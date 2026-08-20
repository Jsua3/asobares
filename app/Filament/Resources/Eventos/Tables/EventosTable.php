<?php

namespace App\Filament\Resources\Eventos\Tables;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoEvento;
use App\Filament\Support\AccionesDeAprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagen')
                    ->label('Imagen')
                    ->disk(config('almacenamiento.publico'))
                    ->height(40)
                    ->width(60),
                TextColumn::make('titulo')
                    ->label('Evento')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record): ?string => $record->lugar),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),
                TextColumn::make('fecha_inicio')
                    ->label('Fecha')
                    ->dateTime('d/m/Y h:i a')
                    ->sortable(),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state): string => (float) $state === 0.0
                        ? 'Gratuito'
                        : '$'.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('cupos')
                    ->label('Cupos')
                    ->numeric()
                    ->placeholder('Sin límite')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('permite_inscripcion')
                    ->label('Inscripción')
                    ->boolean(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('fecha_inicio', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options(TipoEvento::class),
            ])
            ->recordActions([
                ...AccionesDeAprobacion::paraFila(),
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                AccionesDeAprobacion::aprobarEnLote('publicar_evento'),
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Todavía no hay eventos')
            ->emptyStateDescription('Los eventos y capacitaciones del gremio aparecerán aquí.');
    }
}
