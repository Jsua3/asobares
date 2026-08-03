<?php

namespace App\Filament\Resources\Eventos\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoEvento;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El evento')
                    ->description('Solo eventos del gremio: ExpoBar, congresos, capacitaciones.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Se usa en la dirección: /eventos/mi-evento'),
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TipoEvento::class)
                            ->default(TipoEvento::Evento)
                            ->required()
                            ->native(false),
                        TextInput::make('lugar')
                            ->label('Lugar')
                            ->maxLength(255)
                            ->placeholder('Centro de Convenciones, Armenia'),
                        DateTimePicker::make('fecha_inicio')
                            ->label('Empieza')
                            ->required()
                            ->native(false),
                        DateTimePicker::make('fecha_fin')
                            ->label('Termina')
                            ->native(false)
                            ->helperText('Déjalo vacío si es de un solo momento.'),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Inscripción y aforo')
                    ->columns(2)
                    ->schema([
                        Toggle::make('permite_inscripcion')
                            ->label('Permite inscripción en línea')
                            ->helperText('Apágalo si la inscripción se hace por fuera del sitio.'),
                        TextInput::make('enlace_externo')
                            ->label('Enlace externo')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Para eventos de la Nacional cuya inscripción es afuera.'),
                        TextInput::make('cupos')
                            ->label('Cupos')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Déjalo vacío si no hay límite.'),
                        TextInput::make('precio')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('$')
                            ->helperText('En pesos. 0 = gratuito; con precio, la inscripción se confirma al aprobarse el pago.'),
                    ]),

                Section::make('Imagen')
                    ->schema([
                        FileUpload::make('imagen')
                            ->label('Imagen del evento')
                            ->image()
                            ->disk('public')
                            ->directory('eventos')
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
                            ->helperText(fn (): string => auth()->user()?->can('publicar_evento')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }
}
