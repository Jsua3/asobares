<?php

namespace App\Filament\Resources\Asociados\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AsociadosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto_portada')
                    ->label('Portada')
                    ->disk('public')
                    ->height(40)
                    ->width(60),
                TextColumn::make('nombre')
                    ->label('Establecimiento')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record): ?string => $record->direccion),
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                IconColumn::make('destacado')
                    ->label('Destacado')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('representante')
                    ->label('Representante')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fecha_afiliacion')
                    ->label('Afiliado desde')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nombre')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
                SelectFilter::make('municipio')
                    ->label('Municipio')
                    ->relationship('municipio', 'nombre')
                    ->preload(),
                SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre')
                    ->preload(),
                TernaryFilter::make('destacado')
                    ->label('Destacado'),
            ])
            ->recordActions([
                ...AccionesDeAprobacion::paraFila(),
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                AccionesDeAprobacion::aprobarEnLote('publicar_asociado'),
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Todavía no hay establecimientos')
            ->emptyStateDescription('Los asociados que registres aparecerán aquí.');
    }
}
