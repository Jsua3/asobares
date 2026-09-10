<?php

namespace App\Filament\Resources\Publicidades\Tables;

use App\Enums\EstadoPublicidad;
use App\Enums\UbicacionPublicidad;
use App\Models\Publicidad;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class PublicidadesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('imagen')
                    ->label('Pieza')
                    ->disk(config('almacenamiento.publico'))
                    ->height(42)
                    ->width(74),
                TextColumn::make('anunciante')
                    ->label('Anunciante')
                    ->searchable(['anunciante', 'nombre_comercial', 'contacto', 'email', 'telefono'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Publicidad $registro): ?string => $registro->nombre_comercial),
                TextColumn::make('ubicacion')
                    ->label('Ubicacion')
                    ->badge()
                    ->sortable(),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->formatStateUsing(fn ($state): string => '$'.number_format((float) $state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('fecha_inicio')
                    ->label('Vigencia')
                    ->state(fn (Publicidad $registro): string => $registro->fecha_inicio->format('d/m/Y').' - '.$registro->fecha_fin->format('d/m/Y'))
                    ->description(fn (Publicidad $registro): string => $registro->visiblePublicamente() ? 'Visible ahora' : 'No visible publicamente')
                    ->sortable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(EstadoPublicidad::class),
                SelectFilter::make('ubicacion')
                    ->label('Ubicacion')
                    ->options(UbicacionPublicidad::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    self::vistaPrevia(),
                    self::marcarPendientePago(),
                    self::marcarPagada(),
                    self::enviarAprobacion(),
                    self::publicar(),
                    self::rechazar(),
                    self::retirarPublicacion(),
                    EditAction::make()->label('Editar'),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Acciones de publicidad'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar seleccionadas'),
                ])->label('Acciones'),
            ])
            ->emptyStateHeading('Sin publicidad interna')
            ->emptyStateDescription('Las pautas pagadas para Inicio y Directorio apareceran aqui.');
    }

    private static function vistaPrevia(): Action
    {
        return Action::make('vista_previa')
            ->label('Vista previa')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->modalContent(fn (Publicidad $record) => view('filament.publicidad-preview', ['publicidad' => $record]));
    }

    private static function marcarPendientePago(): Action
    {
        return Action::make('marcar_pendiente_pago')
            ->label('Marcar pendiente de pago')
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->visible(fn (Publicidad $record): bool => $record->estado !== EstadoPublicidad::PendientePago)
            ->action(fn (Publicidad $record): bool => $record->update(['estado' => EstadoPublicidad::PendientePago]));
    }

    private static function marcarPagada(): Action
    {
        return Action::make('marcar_pagada')
            ->label('Marcar pagada')
            ->icon('heroicon-o-banknotes')
            ->color('info')
            ->visible(fn (Publicidad $record): bool => $record->estado === EstadoPublicidad::PendientePago)
            ->action(fn (Publicidad $record): bool => $record->update(['estado' => EstadoPublicidad::Pagada]));
    }

    private static function enviarAprobacion(): Action
    {
        return Action::make('enviar_aprobacion')
            ->label('Enviar a aprobacion')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->visible(fn (Publicidad $record): bool => in_array($record->estado, [
                EstadoPublicidad::Borrador,
                EstadoPublicidad::Pagada,
                EstadoPublicidad::Rechazada,
            ], true))
            ->action(fn (Publicidad $record): bool => $record->update(['estado' => EstadoPublicidad::PendienteAprobacion]));
    }

    private static function publicar(): Action
    {
        return Action::make('publicar')
            ->label('Publicar')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (Publicidad $record): bool => auth()->user()?->can('publicar', $record) === true
                && $record->puedePublicarse())
            ->action(function (Publicidad $record): void {
                if (! $record->puedePublicarse()) {
                    throw ValidationException::withMessages([
                        'estado' => 'Solo una pauta pagada, con imagen y fechas validas, puede publicarse.',
                    ]);
                }

                $record->update([
                    'estado' => EstadoPublicidad::Publicada,
                    'aprobado_por' => auth()->id(),
                    'aprobado_at' => now(),
                ]);

                Notification::make()->title('Publicidad publicada')->success()->send();
            });
    }

    private static function rechazar(): Action
    {
        return Action::make('rechazar')
            ->label('Rechazar')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Publicidad $record): bool => auth()->user()?->can('publicar', $record) === true
                && $record->estado !== EstadoPublicidad::Rechazada)
            ->action(fn (Publicidad $record): bool => $record->update([
                'estado' => EstadoPublicidad::Rechazada,
                'aprobado_por' => null,
                'aprobado_at' => null,
            ]));
    }

    private static function retirarPublicacion(): Action
    {
        return Action::make('retirar_publicacion')
            ->label('Retirar publicacion')
            ->icon('heroicon-o-eye-slash')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Publicidad $record): bool => auth()->user()?->can('publicar', $record) === true
                && $record->estado === EstadoPublicidad::Publicada)
            ->action(fn (Publicidad $record): bool => $record->update([
                'estado' => EstadoPublicidad::Borrador,
                'aprobado_por' => null,
                'aprobado_at' => null,
            ]));
    }
}
