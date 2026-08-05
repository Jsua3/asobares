<?php

namespace App\Filament\Resources\Proveedors\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProveedorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('categoria_proveedor')
                    ->badge()
                    ->searchable(),
                TextColumn::make('whatsapp')
                    ->searchable(),
                TextColumn::make('correo')
                    ->searchable(),
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->searchable(),
                TextColumn::make('visible_hasta')
                    ->date()
                    ->sortable(),
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
                AccionesDeAprobacion::aprobarFichaDeBolsa(fn (): string => route('proveedores.index')),
                AccionesDeAprobacion::devolver(),
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AccionesDeAprobacion::aprobarEnLote('publicar_proveedor'),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
