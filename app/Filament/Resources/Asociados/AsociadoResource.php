<?php

namespace App\Filament\Resources\Asociados;

use App\Filament\Resources\Asociados\Pages\CreateAsociado;
use App\Filament\Resources\Asociados\Pages\EditAsociado;
use App\Filament\Resources\Asociados\Pages\ListAsociados;
use App\Filament\Resources\Asociados\Schemas\AsociadoForm;
use App\Filament\Resources\Asociados\Tables\AsociadosTable;
use App\Models\Asociado;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AsociadoResource extends Resource
{
    protected static ?string $model = Asociado::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'asociados';

    protected static ?string $modelLabel = 'Asociado';

    protected static ?string $pluralModelLabel = 'Asociados';

    public static function form(Schema $schema): Schema
    {
        return AsociadoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AsociadosTable::configure($table);
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
            'index' => ListAsociados::route('/'),
            'create' => CreateAsociado::route('/create'),
            'edit' => EditAsociado::route('/{record}/edit'),
        ];
    }
}
