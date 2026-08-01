<?php

namespace App\Filament\Resources\Asociados\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AsociadosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('categoria.id')
                    ->searchable(),
                TextColumn::make('municipio.id')
                    ->searchable(),
                TextColumn::make('direccion')
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->searchable(),
                TextColumn::make('instagram_url')
                    ->searchable(),
                TextColumn::make('sitio_web')
                    ->searchable(),
                TextColumn::make('google_maps_url')
                    ->searchable(),
                TextColumn::make('tripadvisor_url')
                    ->searchable(),
                TextColumn::make('horario')
                    ->searchable(),
                TextColumn::make('lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('foto_portada')
                    ->searchable(),
                IconColumn::make('destacado')
                    ->boolean(),
                TextColumn::make('estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('representante')
                    ->searchable(),
                TextColumn::make('correo_interno')
                    ->searchable(),
                TextColumn::make('telefono_interno')
                    ->searchable(),
                TextColumn::make('fecha_afiliacion')
                    ->date()
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
                //
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
