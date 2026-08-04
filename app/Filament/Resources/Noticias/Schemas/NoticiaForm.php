<?php

namespace App\Filament\Resources\Noticias\Schemas;

use App\Enums\CategoriaNoticia;
use App\Enums\EstadoPublicacion;
use App\Filament\Forms\Components\SubidaSegura;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NoticiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('La entrada')
                    ->description('La materia prima llega ~mensualmente de Asobares Colombia.')
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
                            ->helperText('Se usa en la dirección: /boletin/mi-entrada'),
                        Select::make('categoria')
                            ->label('Categoría')
                            ->options(CategoriaNoticia::class)
                            ->default(CategoriaNoticia::Noticia)
                            ->required()
                            ->native(false),
                        DateTimePicker::make('publicado_at')
                            ->label('Fecha de publicación')
                            ->native(false)
                            ->helperText('La fecha que se muestra en el boletín.'),
                        Textarea::make('extracto')
                            ->label('Extracto')
                            ->rows(2)
                            ->maxLength(300)
                            ->helperText('El resumen que se lee en la tarjeta del boletín.')
                            ->columnSpanFull(),
                        Textarea::make('contenido')
                            ->label('Contenido')
                            ->rows(10)
                            ->columnSpanFull(),
                    ]),

                Section::make('Imagen')
                    ->schema([
                        SubidaSegura::make('imagen')
                            ->label('Imagen de la entrada')
                            ->imagen()
                            ->directory('boletin')
                            ->helperText('JPG, PNG o WebP, máximo 5 MB.'),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoPublicacion::class)
                            ->default(EstadoPublicacion::Borrador)
                            ->required()
                            ->helperText(fn (): string => auth()->user()?->can('publicar_noticia')
                                ? 'Puedes publicar directamente.'
                                : 'Al guardar, quedará pendiente de aprobación de la dirección.'),
                    ]),
            ]);
    }
}
