<?php

namespace App\Filament\Resources\Aliados\Schemas;

use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AliadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El aliado')
                    ->description('Aliados con convenio, distintos de los proveedores que pagan por aparecer.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('url')
                            ->label('Sitio web')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('orden')
                            ->label('Orden')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Menor número, aparece primero en el carrusel.'),
                        Toggle::make('activo')
                            ->label('Convenio activo')
                            ->helperText('Apágalo para retirarlo del sitio sin borrar el registro.')
                            ->inline(false),
                        Textarea::make('descripcion')
                            ->label('Descripción pública')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Convenio para afiliados')
                    ->description('Contenido privado: solo lo ven los empresarios afiliados en /mi-cuenta.')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Textarea::make('detalle_convenio')
                            ->label('Condiciones del convenio')
                            ->rows(4)
                            ->maxLength(2000)
                            ->placeholder('15 % de descuento en pedidos superiores a $500.000, entrega en Armenia.'),
                    ]),

                Section::make('Logo')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo del aliado')
                            ->image()
                            ->disk('public')
                            ->directory('aliados')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('JPG, PNG o WebP, máximo 5 MB.'),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_aliado')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }
}
