<?php

namespace App\Filament\Resources\Vacantes\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VacantesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asociado.id')
                    ->searchable(),
                TextColumn::make('cargo')
                    ->searchable(),
                TextColumn::make('tipo')
                    ->badge()
                    ->searchable(),
                TextColumn::make('franja_horaria')
                    ->searchable(),
                TextColumn::make('whatsapp_contacto')
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
