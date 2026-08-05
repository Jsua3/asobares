<?php

namespace App\Filament\Resources\Vacantes\Tables;

use App\Enums\CargoDelSector;
use App\Enums\EstadoPublicacion;
use App\Filament\Resources\Postulaciones\PostulacionResource;
use App\Filament\Support\AccionesDeAprobacion;
use App\Models\Vacante;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
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
                    ->description(fn (Vacante $registro): string => $registro->asociado->nombre),
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
                    ->label('Postulaciones')
                    ->counts('postulaciones')
                    ->sortable(),
                TextColumn::make('fecha_limite')
                    ->label('Hasta')
                    ->date()
                    ->placeholder('Sin fecha')
                    ->sortable(),
                TextColumn::make('cerrada_at')
                    ->label('Cerrada')
                    ->since()
                    ->placeholder('Abierta'),
                TextColumn::make('created_at')
                    ->label('Publicada')
                    ->since()
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
                AccionesDeAprobacion::aprobarVacante(),
                AccionesDeAprobacion::devolverConMotivo(),
                self::verPostulaciones(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AccionesDeAprobacion::aprobarVacantesEnLote(),
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
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
            ->url(fn (Vacante $registro): string => PostulacionResource::getUrl('index', [
                'tableFilters' => ['vacante' => ['value' => $registro->getKey()]],
            ]));
    }
}
