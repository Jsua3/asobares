<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    /** Nombres legibles de los roles internos. */
    private const array ROLES = [
        User::ROL_SUPER_ADMIN => 'Dirección',
        User::ROL_SUBADMIN => 'Secretaría',
        User::ROL_ASOCIADO => 'Empresario',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (User $record): string => $record->email),
                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::ROLES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        User::ROL_SUPER_ADMIN => 'danger',
                        User::ROL_SUBADMIN => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('asociado.nombre')
                    ->label('Establecimiento')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => self::ROLES[$record->name] ?? $record->name),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Sin usuarios')
            ->emptyStateDescription('Las cuentas del equipo y de los empresarios afiliados aparecerán aquí.');
    }
}
