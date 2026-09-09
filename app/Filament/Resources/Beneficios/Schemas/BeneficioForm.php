<?php

namespace App\Filament\Resources\Beneficios\Schemas;

use App\Enums\Alcance;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeneficioForm
{
    /**
     * `$get` devuelve el valor crudo del formulario o el enum ya casteado según
     * desde dónde se hidrate el esquema, así que se comprueban los dos. Es el
     * mismo cuidado que lleva `AliadoForm` con `TipoAliado`.
     */
    private static function esMunicipal(mixed $alcance): bool
    {
        return $alcance === Alcance::Municipal->value || $alcance === Alcance::Municipal;
    }

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

                Section::make('De quién es')
                    ->description('Se puede dejar en blanco. Sin clasificar, el beneficio sale en el sitio como hasta ahora, sin decir de quién es: el sistema no lo adivina.')
                    ->columns(2)
                    ->schema([
                        Select::make('alcance')
                            ->label('Alcance')
                            ->options(Alcance::class)
                            ->live()
                            ->helperText(fn (): string => collect(Alcance::cases())
                                ->map(fn (Alcance $alcance): string => $alcance->getLabel().': '.$alcance->descripcion())
                                ->implode(' · ')),

                        // El municipio solo aparece --y solo se exige-- cuando
                        // el alcance es municipal. El otro lado del invariante
                        // lo cierra el modelo: al dejar de ser municipal, la
                        // fila suelta el municipio aunque nadie toque este
                        // campo.
                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->relationship('municipio', 'nombre')
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get): bool => static::esMunicipal($get('alcance')))
                            ->required(fn (callable $get): bool => static::esMunicipal($get('alcance')))
                            ->helperText('Un beneficio municipal sin municipio no se puede mostrar a nadie.'),
                    ]),
            ]);
    }
}
