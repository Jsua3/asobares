<?php

namespace App\Filament\Resources\Noticias;

use App\Filament\Resources\Noticias\Pages\CreateNoticia;
use App\Filament\Resources\Noticias\Pages\EditNoticia;
use App\Filament\Resources\Noticias\Pages\ListNoticias;
use App\Filament\Resources\Noticias\Schemas\NoticiaForm;
use App\Filament\Resources\Noticias\Tables\NoticiasTable;
use App\Models\Noticia;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NoticiaResource extends Resource
{
    protected static ?string $model = Noticia::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'boletin';

    protected static ?string $modelLabel = 'Entrada del boletín';

    protected static ?string $pluralModelLabel = 'Boletín';

    public static function form(Schema $schema): Schema
    {
        return NoticiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoticiasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNoticias::route('/'),
            'create' => CreateNoticia::route('/create'),
            'edit' => EditNoticia::route('/{record}/edit'),
        ];
    }
}
