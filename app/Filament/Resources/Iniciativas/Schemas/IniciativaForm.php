<?php

namespace App\Filament\Resources\Iniciativas\Schemas;

use App\Enums\EstadoIniciativa;
use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class IniciativaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('La iniciativa')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(120)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(140)
                            ->unique(ignoreRecord: true),
                        TextInput::make('resumen')
                            ->label('En una frase')
                            ->required()
                            ->maxLength(180)
                            ->helperText('Es lo que se lee en la tarjeta del sitio.')
                            ->columnSpanFull(),
                        Textarea::make('descripcion')
                            ->label('Descripción completa')
                            ->rows(5)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Estado y contexto')
                    ->columns(2)
                    ->schema([
                        Select::make('estado_iniciativa')
                            ->label('¿En qué punto va?')
                            ->options(EstadoIniciativa::class)
                            ->default(EstadoIniciativa::Formulacion)
                            ->required()
                            ->helperText('Formulación → Escalando → En ejecución.'),
                        Select::make('linea')
                            ->label('Línea de trabajo')
                            ->options([
                                'Seguridad' => 'Seguridad',
                                'Cultura' => 'Cultura',
                                'Sostenibilidad' => 'Sostenibilidad',
                            ])
                            ->native(false),
                        TextInput::make('lugar')
                            ->label('Dónde')
                            ->maxLength(120)
                            ->placeholder('Carrera 14, Armenia'),
                        TextInput::make('orden')
                            ->label('Orden')
                            ->numeric()
                            ->default(0)
                            ->helperText('Menor número, aparece primero.'),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_iniciativa')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }
}
