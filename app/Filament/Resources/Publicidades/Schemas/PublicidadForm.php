<?php

namespace App\Filament\Resources\Publicidades\Schemas;

use App\Enums\EstadoPublicidad;
use App\Enums\UbicacionPublicidad;
use App\Filament\Forms\Components\SubidaSegura;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PublicidadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del anunciante')
                    ->columns(2)
                    ->schema([
                        TextInput::make('anunciante')
                            ->label('Anunciante')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('nombre_comercial')
                            ->label('Nombre comercial')
                            ->maxLength(255),
                        TextInput::make('contacto')
                            ->label('Contacto')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('telefono')
                            ->label('Telefono')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                    ]),

                Section::make('Pauta')
                    ->columns(2)
                    ->schema([
                        Select::make('ubicacion')
                            ->label('Ubicacion')
                            ->options(UbicacionPublicidad::class)
                            ->native(false)
                            ->required(),
                        TextInput::make('valor')
                            ->label('Valor')
                            ->prefix('$')
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->helperText('Valor administrativo en pesos colombianos. No calcula descuentos, IVA ni facturacion.'),
                        DateTimePicker::make('fecha_inicio')
                            ->label('Inicio de vigencia')
                            ->seconds(false)
                            ->required(),
                        DateTimePicker::make('fecha_fin')
                            ->label('Fin de vigencia')
                            ->seconds(false)
                            ->required()
                            ->afterOrEqual('fecha_inicio'),
                        TextInput::make('url_destino')
                            ->label('URL de destino')
                            ->maxLength(255)
                            ->placeholder('https://...')
                            ->url()
                            ->rules(['regex:/\Ahttps?:\/\//i']),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicidad::class)
                            ->default(EstadoPublicidad::Borrador)
                            ->native(false)
                            ->required()
                            ->disabled(fn (): bool => auth()->user()?->can('publicar_publicidad') !== true)
                            ->helperText(fn (): string => auth()->user()?->can('publicar_publicidad') === true
                                ? 'La direccion puede cambiar el estado o usar las acciones de la tabla.'
                                : 'Puedes redactar la pauta; la publicacion queda para aprobacion.'),
                    ]),

                Section::make('Imagen')
                    ->schema([
                        SubidaSegura::make('imagen')
                            ->label('Pieza grafica')
                            ->imagen()
                            ->directory('publicidades')
                            ->deletable()
                            ->downloadable()
                            ->openable()
                            ->imageEditor()
                            ->helperText('JPG, PNG o WebP, maximo 5 MB. Es obligatoria para publicar.'),
                    ]),

                Section::make('Notas internas')
                    ->schema([
                        Textarea::make('notas')
                            ->label('Notas')
                            ->rows(4)
                            ->maxLength(2000),
                    ]),
            ]);
    }
}
