<?php

namespace App\Filament\Resources\Eventos\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EventosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->badge()
                    ->searchable(),
                TextColumn::make('lugar')
                    ->searchable(),
                TextColumn::make('fecha_inicio')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('fecha_fin')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('imagen')
                    ->searchable(),
                TextColumn::make('cupos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('precio')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('permite_inscripcion')
                    ->boolean(),
                TextColumn::make('enlace_externo')
                    ->searchable(),
                TextColumn::make('estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
            ])
            ->recordActions([
                ...AccionesDeAprobacion::paraFila(),
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
