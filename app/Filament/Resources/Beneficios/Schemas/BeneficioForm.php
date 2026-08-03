<?php

namespace App\Filament\Resources\Beneficios\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El beneficio')
                    ->description('Se muestra en la página «Afíliate». Es un catálogo: queda vivo al guardar, sin flujo de aprobación.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('orden')
                            ->label('Orden')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Menor número, aparece primero.'),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                        TextInput::make('icono')
                            ->label('Icono')
                            ->required()
                            ->default('heroicon-o-check-badge')
                            ->maxLength(255)
                            ->helperText('Nombre de un icono de Heroicons, p. ej. heroicon-o-check-badge.'),
                    ]),
            ]);
    }
}
