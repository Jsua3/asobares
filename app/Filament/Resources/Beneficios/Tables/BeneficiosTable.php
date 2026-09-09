<?php

namespace App\Filament\Resources\Beneficios\Tables;

use App\Enums\Alcance;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BeneficiosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->searchable(),
                TextColumn::make('icono')
                    ->searchable(),
                TextColumn::make('orden')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('alcance')
                    ->label('Alcance')
                    ->badge()
                    ->placeholder('Sin clasificar')
                    ->sortable(),
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->placeholder('—')
                    ->sortable(),
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
                SelectFilter::make('alcance')
                    ->label('Alcance')
                    ->options(Alcance::class),
                // La pila de trabajo de la oficina: qué falta por clasificar.
                Filter::make('sin_clasificar')
                    ->label('Sin clasificar')
                    ->query(fn (Builder $query): Builder => $query->whereNull('alcance')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
