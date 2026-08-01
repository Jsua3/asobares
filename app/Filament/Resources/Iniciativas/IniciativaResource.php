<?php

namespace App\Filament\Resources\Iniciativas;

use App\Filament\Resources\Iniciativas\Pages\CreateIniciativa;
use App\Filament\Resources\Iniciativas\Pages\EditIniciativa;
use App\Filament\Resources\Iniciativas\Pages\ListIniciativas;
use App\Filament\Resources\Iniciativas\Schemas\IniciativaForm;
use App\Filament\Resources\Iniciativas\Tables\IniciativasTable;
use App\Models\Iniciativa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class IniciativaResource extends Resource
{
    protected static ?string $model = Iniciativa::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rocket-launch';

    protected static string|UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'iniciativas';

    protected static ?string $modelLabel = 'Iniciativa';

    protected static ?string $pluralModelLabel = 'Iniciativas del gremio';

    public static function form(Schema $schema): Schema
    {
        return IniciativaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IniciativasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIniciativas::route('/'),
            'create' => CreateIniciativa::route('/create'),
            'edit' => EditIniciativa::route('/{record}/edit'),
        ];
    }
}
