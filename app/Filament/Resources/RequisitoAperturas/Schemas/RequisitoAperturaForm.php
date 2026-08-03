<?php

namespace App\Filament\Resources\RequisitoAperturas\Schemas;

use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RequisitoAperturaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El requisito')
                    ->description('Un registro por entidad y municipio: los trámites cambian de un municipio a otro.')
                    ->columns(2)
                    ->schema([
                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->relationship('municipio', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('entidad')
                            ->label('Entidad')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Cuerpo de Bomberos de Armenia'),
                        TextInput::make('costo_aproximado')
                            ->label('Costo aproximado')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->helperText('En pesos. Suma al costo total que se muestra en la guía.'),
                        TextInput::make('orden')
                            ->label('Orden')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Menor número, aparece primero en la guía.'),
                        Textarea::make('descripcion')
                            ->label('Descripción del trámite')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Checklist del trámite')
                    ->description('La lista de pasos o documentos, uno por renglón.')
                    ->schema([
                        Repeater::make('checklist')
                            ->label('Pasos o documentos')
                            ->simple(
                                TextInput::make('item')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Cédula del propietario o del representante legal'),
                            )
                            ->addActionLabel('Agregar paso')
                            ->reorderable(),
                    ]),

                Section::make('Formato descargable y enlaces')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('adjunto')
                            ->label('Formato oficial (PDF)')
                            ->disk('public')
                            ->directory('formatos')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5120)
                            ->helperText('El documento que el usuario descarga desde la guía.'),
                        TextInput::make('adjunto_nombre')
                            ->label('Nombre del formato')
                            ->maxLength(255)
                            ->placeholder('Formato de solicitud de visita — Bomberos Armenia')
                            ->helperText('El nombre limpio con el que se descarga.'),
                        TextInput::make('enlace_externo')
                            ->label('Enlace externo')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Página oficial de la entidad, si existe.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_requisito')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }
}
