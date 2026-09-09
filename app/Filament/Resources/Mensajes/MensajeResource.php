<?php

namespace App\Filament\Resources\Mensajes;

use App\Enums\EstadoMensaje;
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

    /**
     * Cuántos mensajes esperan respuesta, en el menú (Acta 08, A-04).
     *
     * Antes del 9 de septiembre de 2026 la única señal de que había algo en la
     * bandeja era una tarjeta del tablero: quien entraba al panel a publicar una
     * noticia no se enteraba de que había una PQR corriendo su plazo legal.
     */
    public static function getNavigationBadge(): ?string
    {
        $sinResponder = Mensaje::query()->where('estado', '!=', EstadoMensaje::Respondido)->count();

        return $sinResponder > 0 ? (string) $sinResponder : null;
    }

    /**
     * Rojo cuando alguna PQR ya se pasó de los quince días hábiles de ley; ámbar
     * mientras solo haya cosas por atender. La diferencia importa: una es «hay
     * trabajo» y la otra es «se incumplió un término».
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return Mensaje::pqrVencidas()->isNotEmpty() ? 'danger' : 'warning';
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
