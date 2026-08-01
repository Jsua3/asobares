<?php

namespace App\Filament\Resources\Artistas;

use App\Filament\Resources\Artistas\Pages\CreateArtista;
use App\Filament\Resources\Artistas\Pages\EditArtista;
use App\Filament\Resources\Artistas\Pages\ListArtistas;
use App\Filament\Resources\Artistas\Schemas\ArtistaForm;
use App\Filament\Resources\Artistas\Tables\ArtistasTable;
use App\Models\Artista;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ArtistaResource extends Resource
{
    protected static ?string $model = Artista::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-musical-note';

    protected static string|\UnitEnum|null $navigationGroup = 'Bolsas';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'artistas';

    protected static ?string $modelLabel = 'Artista';

    protected static ?string $pluralModelLabel = 'Artistas';

    public static function form(Schema $schema): Schema
    {
        return ArtistaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtistasTable::configure($table);
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
            'index' => ListArtistas::route('/'),
            'create' => CreateArtista::route('/create'),
            'edit' => EditArtista::route('/{record}/edit'),
        ];
    }
}
