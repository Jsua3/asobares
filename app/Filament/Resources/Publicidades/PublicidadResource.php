<?php

namespace App\Filament\Resources\Publicidades;

use App\Filament\Resources\Publicidades\Pages\CreatePublicidad;
use App\Filament\Resources\Publicidades\Pages\EditPublicidad;
use App\Filament\Resources\Publicidades\Pages\ListPublicidades;
use App\Filament\Resources\Publicidades\Schemas\PublicidadForm;
use App\Filament\Resources\Publicidades\Tables\PublicidadesTable;
use App\Models\Publicidad;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PublicidadResource extends Resource
{
    protected static ?string $model = Publicidad::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Gremio';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'publicidad';

    protected static ?string $modelLabel = 'Publicidad';

    protected static ?string $pluralModelLabel = 'Publicidad interna';

    public static function form(Schema $schema): Schema
    {
        return PublicidadForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicidadesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublicidades::route('/'),
            'create' => CreatePublicidad::route('/create'),
            'edit' => EditPublicidad::route('/{record}/edit'),
        ];
    }
}
