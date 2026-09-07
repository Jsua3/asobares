<?php

namespace App\Filament\Resources\Mensajes\Tables;

use App\Enums\EstadoMensaje;
use App\Enums\TipoMensaje;
use App\Models\Mensaje;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MensajesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Remitente')
                    ->searchable(['nombre', 'correo', 'radicado', 'mensaje'])
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (Mensaje $registro): string => collect([
                        $registro->radicado,
                        $registro->correo,
                    ])->filter()->implode(' · '))
                    ->wrap()
                    ->width('16rem'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('mensaje')
                    ->label('Resumen')
                    ->limit(48)
                    ->visibleFrom('md'),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Recibido')
                    ->since()
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('tipo')->label('Tipo')->options(TipoMensaje::class),
                SelectFilter::make('estado')->label('Estado')->options(EstadoMensaje::class),
            ])
            ->recordActions([
                ViewAction::make()->label('Ver'),
                ActionGroup::make([
                    self::marcarRespondido(),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->tooltip('Más acciones'),
            ])
            ->recordActionsColumnLabel('Acciones')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ])
            ->emptyStateHeading('Bandeja vacía')
            ->emptyStateDescription('Los mensajes que lleguen por los formularios del sitio aparecerán aquí.');
    }

    /** Cierra el caso dejando constancia de qué se respondió. */
    private static function marcarRespondido(): Action
    {
        return Action::make('responder')
            ->label('Marcar respondido')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Mensaje $registro): bool => $registro->estado !== EstadoMensaje::Respondido
                && auth()->user()?->can('update', $registro) === true)
            ->modalHeading(fn (Mensaje $registro): string => $registro->esPqr()
                ? "Cerrar {$registro->radicado}"
                : 'Marcar como respondido')
            ->schema([
                Textarea::make('nota_respuesta')
                    ->label('¿Qué se respondió?')
                    ->rows(4)
                    ->required()
                    ->helperText('Queda como constancia interna del trámite.'),
            ])
            ->action(function (Mensaje $registro, array $data): void {
                $registro->update([
                    'estado' => EstadoMensaje::Respondido,
                    'nota_respuesta' => $data['nota_respuesta'],
                    'respondido_at' => now(),
                ]);

                Notification::make()->title('Mensaje marcado como respondido')->success()->send();
            });
    }
}
