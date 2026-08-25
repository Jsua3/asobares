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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                        // La base real del gremio trae 19 de 41 horarios en
                        // varias lineas, un renglon por franja. Cabian en un
                        // TextInput solo mientras los datos eran inventados.
                        Textarea::make('horario')->label('Horario')->rows(3)
                            ->placeholder("Lunes a viernes de 6:00 p. m. a 2:00 a. m.\nSabado de 4:00 p. m. a 3:00 a. m."),
                        TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(30),
                        TextInput::make('sitio_web')->label('Sitio web')->url()->maxLength(255),
                        TextInput::make('instagram_url')->label('Instagram')->url()->maxLength(255),
                        TextInput::make('google_maps_url')->label('Google Maps / Business')->url()->maxLength(255),
                        TextInput::make('tripadvisor_url')->label('TripAdvisor')->url()->maxLength(255),
                        Textarea::make('genero_musical')->label('Género musical')->rows(3)
                            ->helperText('Un género por renglón. Es por lo que la gente filtra el directorio.')
                            ->placeholder("Salsa\nCrossover"),
                        Textarea::make('servicios')->label('Servicios ofrecidos')->rows(3)
                            ->placeholder('Pista de baile, licores, DJ en vivo, parqueadero'),
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
                        // La librería de medios trae su propio nombrador, así
                        // que no hereda la defensa de `SubidaSegura`: sin esto
                        // la extensión la elegiría quien sube, y un JPEG
                        // llamado «payload.html» quedaría servido como HTML
                        // desde /storage.
                        SpatieMediaLibraryFileUpload::make('galeria')
                            ->label('Galería')
                            ->collection('galeria')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->getUploadedFileNameForStorageUsing(
                                static fn (TemporaryUploadedFile $file): string => Str::ulid()
                                    .'.'.SubidaSegura::extensionPara($file->getMimeType())
                            )
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
                        TextInput::make('documento')->label('NIT o cédula')->maxLength(40)
                            ->helperText('Dato de identificación del titular. No sale al sitio público.'),
                        TextInput::make('correo_interno')->label('Correo interno')->email()->maxLength(255),
                        TextInput::make('telefono_interno')->label('Teléfono interno')->tel()->maxLength(30),
                        DatePicker::make('fecha_afiliacion')->label('Fecha de afiliación')->native(false),
                        DatePicker::make('autorizacion_datos_at')
                            ->label('Autorización de datos (fecha)')
                            ->native(false)
                            ->helperText('Ley 1581/2012: sin esta fecha y su soporte, la ficha no debería publicarse.'),
                        TextInput::make('autorizacion_datos_origen')
                            ->label('Soporte de la autorización')
                            ->maxLength(255)
                            ->placeholder('Formato firmado, acta 04, correo del titular…')
                            ->columnSpanFull(),
                        Textarea::make('notas_internas')->label('Notas internas')->rows(3)->columnSpanFull()
                            ->helperText('Valoración comercial de la oficina. Nunca se publica.'),
                    ]),
            ]);
    }
}
