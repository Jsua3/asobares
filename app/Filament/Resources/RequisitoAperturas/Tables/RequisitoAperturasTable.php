<?php

namespace App\Filament\Resources\RequisitoAperturas\Tables;

use App\Enums\EstadoPublicacion;
use App\Filament\Support\AccionesDeAprobacion;
use App\Models\RequisitoApertura;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RequisitoAperturasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('municipio.nombre')
                    ->label('Municipio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entidad')
                    ->searchable(),
                TextColumn::make('enlace_externo')
                    ->searchable(),
                TextColumn::make('adjunto')
                    ->searchable(),
                TextColumn::make('adjunto_nombre')
                    ->searchable(),
                TextColumn::make('costo_aproximado')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('orden')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estado')
                    ->badge()
                    ->searchable(),
                TextColumn::make('verificado_el')
                    ->label('Verificado')
                    ->date('d/m/Y')
                    ->placeholder('Sin verificar')
                    ->badge()
                    ->color(fn (RequisitoApertura $record): string => match (true) {
                        ! $record->estaVerificado() => 'gray',
                        $record->necesitaRevision() => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                TextColumn::make('vigente_hasta')
                    ->label('Vigente hasta')
                    ->date('d/m/Y')
                    ->placeholder('Permanente')
                    ->color(fn (RequisitoApertura $record): string => $record->haCaducado() ? 'danger' : 'gray')
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
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicacion::class),

                Filter::make('necesita_revision')
                    ->label('Necesita revisión')
                    // Junta las dos mitades de la pila de trabajo: lo que nadie
                    // verificó nunca y lo que se verificó hace más de un año.
                    // El borde coincide con RequisitoApertura::necesitaRevision().
                    ->query(fn (Builder $query): Builder => $query->where(fn (Builder $q) => $q
                        ->whereNull('verificado_el')
                        ->orWhere(
                            'verificado_el',
                            '<',
                            now()->subMonths(RequisitoApertura::MESES_HASTA_REVISION)->toDateString()
                        ))),

                Filter::make('caducados')
                    ->label('Caducados')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('vigente_hasta')
                        ->where('vigente_hasta', '<', now()->toDateString())),
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
