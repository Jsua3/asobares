<?php

namespace App\Filament\Resources\Mensajes;

use App\Filament\Resources\Mensajes\Pages\EditMensaje;
use App\Filament\Resources\Mensajes\Pages\ListMensajes;
use App\Filament\Resources\Mensajes\Schemas\MensajeForm;
use App\Filament\Resources\Mensajes\Tables\MensajesTable;
use App\Models\Mensaje;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class MensajeResource extends Resource
{
    protected static ?string $model = Mensaje::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox';

    protected static string|\UnitEnum|null $navigationGroup = 'Bandejas';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'mensajes';

    protected static ?string $modelLabel = 'Mensaje';

    protected static ?string $pluralModelLabel = 'Mensajes y PQR';

    public static function form(Schema $schema): Schema
    {
        return MensajeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MensajesTable::configure($table);
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
            'index' => ListMensajes::route('/'),
            'edit' => EditMensaje::route('/{record}/edit'),
        ];
    }
}
