<?php

namespace App\Filament\Resources\Eventos;

use App\Filament\Resources\Eventos\Pages\CreateEvento;
use App\Filament\Resources\Eventos\Pages\EditEvento;
use App\Filament\Resources\Eventos\Pages\ListEventos;
use App\Filament\Resources\Eventos\Schemas\EventoForm;
use App\Filament\Resources\Eventos\Tables\EventosTable;
use App\Models\Evento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Contenido';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'eventos';

    protected static ?string $modelLabel = 'Evento o capacitación';

    protected static ?string $pluralModelLabel = 'Eventos y capacitaciones';

    public static function form(Schema $schema): Schema
    {
        return EventoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventosTable::configure($table);
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
            'index' => ListEventos::route('/'),
            'create' => CreateEvento::route('/create'),
            'edit' => EditEvento::route('/{record}/edit'),
        ];
    }
}
