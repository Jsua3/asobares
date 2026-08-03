<?php

namespace App\Filament\Resources\Artistas\Schemas;

use App\Enums\EstadoPublicacion;
use App\Enums\TipoArtista;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ArtistaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('El artista')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre artístico')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->label('Slug (URL)')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Se usa en la dirección: /artistas/mi-nombre'),
                        Select::make('tipo')
                            ->label('Tipo')
                            ->options(TipoArtista::class)
                            ->default(TipoArtista::Dj)
                            ->required()
                            ->native(false),
                        TextInput::make('genero_musical')
                            ->label('Género musical')
                            ->maxLength(255)
                            ->placeholder('Crossover, salsa, electrónica'),
                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->relationship('municipio', 'nombre')
                            ->searchable()
                            ->preload(),
                        Textarea::make('descripcion')
                            ->label('Reseña')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Contratación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tarifa_desde')
                            ->label('Tarifa desde')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->helperText('En pesos, por presentación. Se muestra como «Desde $X».'),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('instagram_url')
                            ->label('Instagram')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('video_url')
                            ->label('Video de YouTube')
                            ->url()
                            // Solo YouTube: del enlace únicamente se embebe el ID extraído.
                            ->rule('url_youtube')
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->helperText('Pega el enlace del video. Se acepta youtube.com o youtu.be.'),
                    ]),

                Section::make('Foto')
                    ->schema([
                        FileUpload::make('foto')
                            ->label('Foto del artista')
                            ->image()
                            ->disk('public')
                            ->directory('artistas')
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
                            ->helperText(fn (): string => auth()->user()?->can('publicar_artista')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }
}
