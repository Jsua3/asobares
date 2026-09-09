<?php

namespace App\Filament\Resources\Aspirantes\Tables;

use App\Enums\EstadoDeGestion;
use App\Models\Aspirante;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
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
                IconColumn::make('aprobado_el')
                    ->label('En el banco')
                    ->boolean()
                    ->tooltip('Visible para los establecimientos afiliados')
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
                Filter::make('sin_aprobar')
                    ->label('Sin aprobar')
                    ->query(fn (Builder $query): Builder => $query->whereNull('aprobado_el')),
                SelectFilter::make('estado')
                    ->label('Gestión')
                    ->options(EstadoDeGestion::class),
            ])
            ->recordActions([
                EditAction::make()->label('Ver perfil'),
                Action::make('aprobar')
                    ->label('Aprobar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Dejar este perfil en el banco de talento')
                    ->modalDescription('Los establecimientos afiliados verán su nombre, su teléfono y su correo.')
                    ->modalSubmitActionLabel('Sí, aprobar')
                    ->visible(fn (Aspirante $registro): bool => ! $registro->estaAprobado()
                        && auth()->user()?->can('update', $registro) === true)
                    ->action(function (Aspirante $registro): void {
                        $registro->update(['aprobado_el' => now()]);

                        Notification::make()
                            ->title('Perfil aprobado')
                            ->body('Ya aparece en el banco de talento de los afiliados.')
                            ->success()
                            ->send();
                    }),
                Action::make('retirar')
                    ->label('Retirar del banco')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Retirar este perfil del banco')
                    ->modalDescription('Deja de verse para los afiliados. El perfil no se borra.')
                    ->visible(fn (Aspirante $registro): bool => $registro->estaAprobado()
                        && auth()->user()?->can('update', $registro) === true)
                    ->action(function (Aspirante $registro): void {
                        $registro->update(['aprobado_el' => null]);

                        Notification::make()
                            ->title('Perfil retirado del banco')
                            ->success()
                            ->send();
                    }),
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
