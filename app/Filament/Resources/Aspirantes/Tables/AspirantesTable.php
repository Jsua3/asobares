<?php

namespace App\Filament\Resources\Aspirantes\Tables;

use App\Enums\EstadoDeGestion;
use App\Models\Aspirante;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AspirantesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Aspirante')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Aspirante $record): string => $record->correo),
                TextColumn::make('cargo_interes')
                    ->label('Cargo de interés')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('categoria_cargo')
                    ->label('Área')
                    ->badge()
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Gestión')
                    ->badge()
                    ->sortable(),
                IconColumn::make('acepta_datos')
                    ->label('Datos')
                    ->boolean()
                    ->tooltip('Consentimiento de tratamiento de datos'),
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('ultima_semana')
                    ->label('Últimos 7 días')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subWeek())),
                SelectFilter::make('estado')
                    ->label('Gestión')
                    ->options(EstadoDeGestion::class),
            ])
            ->recordActions([
                EditAction::make()->label('Ver perfil'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Sin aspirantes todavía')
            ->emptyStateDescription('Los perfiles que se registren en la bolsa de empleo aparecerán aquí.');
    }
}
