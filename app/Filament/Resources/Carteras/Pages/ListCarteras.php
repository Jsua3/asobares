<?php

namespace App\Filament\Resources\Carteras\Pages;

use App\Filament\Resources\Carteras\CarteraResource;
use App\Models\Cartera;
use App\Services\ImportadorDeCartera;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListCarteras extends ListRecords
{
    protected static string $resource = CarteraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->accionImportar(),
            $this->accionPlantilla(),
        ];
    }

    /** Carga el archivo que envía la contadora. */
    private function accionImportar(): Action
    {
        return Action::make('importar')
            ->label('Importar CSV de la contadora')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->visible(fn (): bool => auth()->user()?->can('importar_cartera') === true)
            ->modalHeading('Importar estado de cuenta')
            ->modalDescription('Columnas esperadas: establecimiento, saldo_pendiente, meses_mora y, opcional, ultimo_pago.')
            ->modalSubmitActionLabel('Importar')
            ->schema([
                FileUpload::make('archivo')
                    ->label('Archivo CSV')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                    ->maxSize(4096)
                    ->storeFiles(false),
            ])
            ->action(function (array $data): void {
                $archivo = $data['archivo'] ?? null;
                $archivo = is_array($archivo) ? reset($archivo) : $archivo;

                if (! $archivo instanceof TemporaryUploadedFile) {
                    Notification::make()->title('No se recibió ningún archivo')->danger()->send();

                    return;
                }

                $resultado = app(ImportadorDeCartera::class)->importar($archivo->getRealPath());

                $notificacion = Notification::make()->title($resultado->resumen())->persistent();

                if ($resultado->tieneErrores()) {
                    // Se muestran hasta 10 errores: alcanza para corregir el archivo
                    // sin convertir la notificación en un muro de texto.
                    $notificacion->warning()->body(implode("\n", array_slice($resultado->errores(), 0, 10)));
                } else {
                    $notificacion->success();
                }

                $notificacion->send();
            });
    }

    /** Deja claro el formato esperado sin tener que leer documentación. */
    private function accionPlantilla(): Action
    {
        return Action::make('plantilla')
            ->label('Descargar plantilla')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->action(fn (): StreamedResponse => response()->streamDownload(function (): void {
                echo "establecimiento,saldo_pendiente,meses_mora,ultimo_pago\n";

                Cartera::with('asociado')->get()->each(function (Cartera $cartera): void {
                    printf(
                        "\"%s\",%s,%d,%s\n",
                        $cartera->asociado->nombre,
                        $cartera->saldo_pendiente,
                        $cartera->meses_mora,
                        $cartera->ultimo_pago_at?->format('Y-m-d') ?? ''
                    );
                });
            }, 'cartera-asobares-quindio.csv', ['Content-Type' => 'text/csv; charset=UTF-8']));
    }
}
