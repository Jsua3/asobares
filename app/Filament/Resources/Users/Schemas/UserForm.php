<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    /** Nombres legibles de los roles internos. */
    private const array ROLES = [
        User::ROL_SUPER_ADMIN => 'Dirección (súper admin)',
        User::ROL_SUBADMIN => 'Secretaría (subadmin)',
        User::ROL_ASOCIADO => 'Empresario (asociado)',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('La cuenta')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255)
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'Entrégala como contraseña temporal y pide cambiarla al primer ingreso.'
                                : 'Déjala en blanco para no cambiarla.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Rol y acceso')
                    ->columns(2)
                    ->schema([
                        Select::make('roles')
                            ->label('Rol')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Role $record): string => self::ROLES[$record->name] ?? $record->name)
                            ->multiple()
                            ->maxItems(1)
                            ->preload()
                            ->required()
                            ->helperText('Cada usuario lleva un solo rol. El rol asociado no entra al panel: usa /mi-cuenta.'),
                        Select::make('asociado_id')
                            ->label('Establecimiento vinculado')
                            ->relationship('asociado', 'nombre')
                            ->searchable()
                            ->preload()
                            ->helperText('Solo para el rol asociado: el establecimiento del que es dueño. Sin este vínculo, /mi-cuenta no le funciona.'),
                    ]),
            ]);
    }
}
