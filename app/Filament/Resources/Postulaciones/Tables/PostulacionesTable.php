<?php

namespace App\Filament\Resources\Postulaciones\Tables;

use App\Enums\EstadoDeGestion;
use App\Models\Postulacion;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostulacionesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Candidato')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Postulacion $registro): string => $registro->correo),
                TextColumn::make('vacante.cargo')
                    ->label('Vacante')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vacante.asociado.nombre')
                    ->label('Establecimiento')
                    ->searchable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Gestión')
                    ->badge()
                    ->sortable(),
                IconColumn::make('acepta_datos')
                    ->label('Datos')
                    ->boolean()
                    ->tooltip('Consentimiento de tratamiento de datos'),
                TextColumn::make('created_at')
                    ->label('Se postuló')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Gestión')
                    ->options(EstadoDeGestion::class),
                SelectFilter::make('vacante')
                    ->label('Vacante')
                    ->relationship('vacante', 'cargo')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                self::gestionar(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Sin postulaciones todavía')
            ->emptyStateDescription('Aquí aparece quien se postule a una vacante desde el sitio público.');
    }

    private static function gestionar(): Action
    {
        return Action::make('gestionar')
            ->label('Cambiar estado')
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->fillForm(fn (Postulacion $registro): array => ['estado' => $registro->estado->value])
            ->schema([
                Select::make('estado')
                    ->label('Estado de gestión')
                    ->options(EstadoDeGestion::class)
                    ->required()
                    ->native(false),
            ])
            ->visible(fn (Postulacion $registro): bool => auth()->user()?->can('update', $registro) === true)
            ->action(function (Postulacion $registro, array $data): void {
                $registro->update(['estado' => $data['estado']]);

                Notification::make()->title('Estado actualizado')->success()->send();
            });
    }
}
