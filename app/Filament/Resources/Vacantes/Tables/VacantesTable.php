<?php

namespace App\Filament\Resources\Vacantes\Tables;

use App\Enums\CargoDelSector;
use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Postulaciones\PostulacionResource;
use App\Filament\Support\AccionesDeAprobacion;
use App\Models\Vacante;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * El panel modera la bolsa de empleo: aprueba, devuelve y consulta.
 * Editar la vacante es del establecimiento que la publicó.
 */
class VacantesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Vacante $registro): string => $registro->asociado->nombre)
                    ->wrap()
                    ->width('18rem'),
                TextColumn::make('categoria_cargo')
                    ->label('Área')
                    ->badge()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('postulaciones_count')
                    ->label('Post.')
                    ->counts('postulaciones')
                    ->sortable(),
                TextColumn::make('fecha_limite')
                    ->label('Vigencia')
                    ->state(fn (Vacante $registro): string => $registro->cerrada_at !== null
                        ? 'Cerrada '.$registro->cerrada_at->diffForHumans()
                        : 'Abierta'.($registro->fecha_limite ? ' hasta '.$registro->fecha_limite->format('d/m/Y') : ' sin fecha límite'))
                    ->description(fn (Vacante $registro): string => 'Publicada '.$registro->created_at->diffForHumans())
                    ->wrap()
                    ->width('13rem')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),
                SelectFilter::make('categoria_cargo')
                    ->label('Área')
                    ->options(CargoDelSector::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    AccionesDeAprobacion::aprobarVacante(),
                    AccionesDeAprobacion::devolverConMotivo(),
                    AccionesDeAprobacion::dejarDePublicarVacante(),
                    self::verPostulaciones(),
                    DeleteAction::make()
                        ->label('Eliminar')
                        ->visible(fn (Vacante $registro): bool => auth()->user()?->can('delete', $registro) === true),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Acciones de la vacante'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                BulkActionGroup::make([
                    AccionesDeAprobacion::aprobarVacantesEnLote(),
                    DeleteBulkAction::make()->label('Eliminar seleccionadas'),
                ])->label('Acciones'),
            ])
            ->emptyStateHeading('Sin vacantes todavía')
            ->emptyStateDescription('Las publican los establecimientos asociados desde su cuenta.');
    }

    /** Los datos de los candidatos viven en su propia bandeja, no aquí. */
    private static function verPostulaciones(): Action
    {
        return Action::make('postulaciones')
            ->label('Ver postulaciones')
            ->icon('heroicon-o-user-group')
            ->color('gray')
            ->visible(fn (): bool => auth()->user()?->can('ver_postulacion') === true
                || auth()->user()?->esSuperAdmin() === true
                || auth()->user()?->esSubadmin() === true)
            ->url(fn (Vacante $registro): string => PostulacionResource::getUrl('index', [
                'tableFilters' => ['vacante' => ['value' => $registro->getKey()]],
            ]));
    }
}
