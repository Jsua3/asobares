<?php

namespace App\Filament\Resources\Municipios;

use App\Filament\Resources\Municipios\Pages\CreateMunicipio;
use App\Filament\Resources\Municipios\Pages\EditMunicipio;
use App\Filament\Resources\Municipios\Pages\ListMunicipios;
use App\Filament\Resources\Municipios\Schemas\MunicipioForm;
use App\Filament\Resources\Municipios\Tables\MunicipiosTable;
use App\Models\Municipio;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MunicipioResource extends Resource
{
    protected static ?string $model = Municipio::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'municipios';

    protected static ?string $modelLabel = 'Municipio';

    protected static ?string $pluralModelLabel = 'Municipios';

    public static function form(Schema $schema): Schema
    {
        return MunicipioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MunicipiosTable::configure($table);
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
            'index' => ListMunicipios::route('/'),
            'create' => CreateMunicipio::route('/create'),
            'edit' => EditMunicipio::route('/{record}/edit'),
        ];
    }
}
