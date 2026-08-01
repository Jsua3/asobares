<?php

namespace App\Filament\Resources\Aspirantes;

use App\Filament\Resources\Aspirantes\Pages\EditAspirante;
use App\Filament\Resources\Aspirantes\Pages\ListAspirantes;
use App\Filament\Resources\Aspirantes\Schemas\AspiranteForm;
use App\Filament\Resources\Aspirantes\Tables\AspirantesTable;
use App\Models\Aspirante;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AspiranteResource extends Resource
{
    protected static ?string $model = Aspirante::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|\UnitEnum|null $navigationGroup = 'Bolsas';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'aspirantes';

    protected static ?string $modelLabel = 'Aspirante';

    protected static ?string $pluralModelLabel = 'Aspirantes';

    public static function form(Schema $schema): Schema
    {
        return AspiranteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AspirantesTable::configure($table);
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
            'index' => ListAspirantes::route('/'),
            'edit' => EditAspirante::route('/{record}/edit'),
        ];
    }
}
