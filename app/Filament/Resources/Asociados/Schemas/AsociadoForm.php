<?php

namespace App\Filament\Resources\Asociados\Schemas;

use App\Enums\EstadoPublicacion;
use App\Filament\Forms\Components\SubidaSegura;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AsociadoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificación')
                    ->description('Lo que verá cualquier persona que entre al directorio.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del establecimiento')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Se usa en la dirección: /directorio/mi-establecimiento'),
                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->relationship('categoria', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->relationship('municipio', 'nombre')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('descripcion')
                            ->label('Reseña')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Contacto público')
                    ->description('El propietario decide qué datos suyos se publican: deja en blanco lo que no quiera mostrar.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('direccion')->label('Dirección')->maxLength(255),
                        TextInput::make('horario')->label('Horario')->maxLength(255)
                            ->placeholder('Jue a sáb, 6:00 p. m. – 2:00 a. m.'),
                        TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(30),
                        TextInput::make('sitio_web')->label('Sitio web')->url()->maxLength(255),
                        TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(255),
                        TextInput::make('google_maps_url')->label('Google Maps / Business')->url()->maxLength(255),
                        TextInput::make('tripadvisor_url')->label('TripAdvisor')->url()->maxLength(255),
                    ]),

                Section::make('Ubicación en el mapa')
                    ->description('Coordenadas para el pin del directorio. Puedes copiarlas de Google Maps.')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('lat')->label('Latitud')->numeric()->minValue(-90)->maxValue(90),
                        TextInput::make('lng')->label('Longitud')->numeric()->minValue(-180)->maxValue(180),
                    ]),

                Section::make('Imágenes')
                    ->schema([
                        SubidaSegura::make('foto_portada')
                            ->label('Foto de portada')
                            ->imagen()
                            ->directory('asociados'),
                        SpatieMediaLibraryFileUpload::make('galeria')
                            ->label('Galería')
                            ->collection('galeria')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxSize(5120)
                            ->helperText('Se convierten a WebP automáticamente.'),
                    ]),

                Section::make('Publicación')
                    ->columns(2)
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_asociado')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, el contenido quedará pendiente de aprobación de la dirección.'),
                        Toggle::make('destacado')
                            ->label('Destacar en el inicio')
                            ->helperText('Aparece en la franja de destacados de la página principal.'),
                    ]),

                Section::make('Datos internos del gremio')
                    ->description('Uso exclusivo de la oficina. Nada de esta sección sale al sitio público.')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('representante')->label('Representante')->maxLength(255),
                        TextInput::make('correo_interno')->label('Correo interno')->email()->maxLength(255),
                        TextInput::make('telefono_interno')->label('Teléfono interno')->tel()->maxLength(30),
                        DatePicker::make('fecha_afiliacion')->label('Fecha de afiliación')->native(false),
                        Textarea::make('notas_internas')->label('Notas internas')->rows(3)->columnSpanFull(),
                    ]),
            ]);
    }
}
