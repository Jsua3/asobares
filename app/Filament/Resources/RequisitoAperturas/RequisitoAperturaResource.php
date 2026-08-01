<?php

namespace App\Filament\Resources\RequisitoAperturas;

use App\Filament\Resources\RequisitoAperturas\Pages\CreateRequisitoApertura;
use App\Filament\Resources\RequisitoAperturas\Pages\EditRequisitoApertura;
use App\Filament\Resources\RequisitoAperturas\Pages\ListRequisitoAperturas;
use App\Filament\Resources\RequisitoAperturas\Schemas\RequisitoAperturaForm;
use App\Filament\Resources\RequisitoAperturas\Tables\RequisitoAperturasTable;
use App\Models\RequisitoApertura;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RequisitoAperturaResource extends Resource
{
    protected static ?string $model = RequisitoApertura::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'requisitos';

    protected static ?string $modelLabel = 'Requisito de apertura';

    protected static ?string $pluralModelLabel = 'Guía normativa';

    public static function form(Schema $schema): Schema
    {
        return RequisitoAperturaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RequisitoAperturasTable::configure($table);
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
            'index' => ListRequisitoAperturas::route('/'),
            'create' => CreateRequisitoApertura::route('/create'),
            'edit' => EditRequisitoApertura::route('/{record}/edit'),
        ];
    }
}
