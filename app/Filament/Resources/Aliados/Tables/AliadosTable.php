<?php

namespace App\Filament\Resources\Aliados\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use App\Models\Aliado;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AliadosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('municipio'))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Aliado')
                    ->searchable(['nombre', 'descripcion', 'detalle_convenio', 'url'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Aliado $registro): ?string => $registro->municipio?->nombre)
                    ->wrap()
                    ->width('16rem'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('detalle_convenio')
                    ->label('Convenio')
                    ->limit(48)
                    ->placeholder('Sin convenio escrito')
                    ->visibleFrom('lg'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                IconColumn::make('activo')
                    ->label('Activo')
                    ->boolean()
                    ->visibleFrom('md'),
            ])
            ->defaultSort('orden')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ...AccionesDeAprobacion::paraFila(),
                    EditAction::make()->label('Editar'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Acciones del aliado'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
