<?php

namespace App\Filament\Resources\Noticias\Schemas;

use App\Enums\CategoriaNoticia;
use App\Enums\EstadoPublicacion;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoticiaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('extracto')
                    ->columnSpanFull(),
                Textarea::make('contenido')
                    ->columnSpanFull(),
                TextInput::make('imagen'),
                Select::make('categoria')
                    ->options(CategoriaNoticia::class)
                    ->default('noticia')
                    ->required(),
                DateTimePicker::make('publicado_at'),
                Select::make('estado')
                    ->options(EstadoPublicacion::class)
                    ->default('borrador')
                    ->required(),
            ]);
    }
}
