<?php

namespace App\Filament\Resources\Proveedors\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use App\Models\Proveedor;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProveedorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('municipio'))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Proveedor')
                    ->searchable(['nombre', 'slug', 'whatsapp', 'correo'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Proveedor $registro): ?string => $registro->municipio?->nombre)
                    ->wrap()
                    ->width('18rem'),
                TextColumn::make('categoria_proveedor')
                    ->label('Servicio')
                    ->badge()
                    ->searchable()
                    ->visibleFrom('md'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('visible_hasta')
                    ->label('Vigencia')
                    ->date('d/m/Y')
                    ->placeholder('Sin fecha')
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    AccionesDeAprobacion::aprobarFichaDeBolsa(fn (): string => route('proveedores.index')),
                    AccionesDeAprobacion::devolver(),
                    EditAction::make()->label('Editar'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Acciones del proveedor'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                BulkActionGroup::make([
                    AccionesDeAprobacion::aprobarFichasEnLote(
                        'publicar_proveedor',
                        fn (): string => route('proveedores.index')
                    ),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
