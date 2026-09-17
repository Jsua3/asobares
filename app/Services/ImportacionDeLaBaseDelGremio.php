<?php

namespace App\Services;

use App\Models\Asociado;
use Illuminate\Support\Facades\DB;

/**
 * La importación de la base del gremio desde el panel: fichas y, si la
 * dirección lo pide, cuentas de acceso a /mi-cuenta.
 *
 * Todo en una transacción. Si las cuentas revientan a mitad de camino no
 * pueden quedar fichas nuevas sin las cuentas que se pidieron, ni la mitad de
 * las cuentas creadas: el archivo corregido se vuelve a subir entero.
 */
class ImportacionDeLaBaseDelGremio
{
    public function __construct(
        private ImportadorDeAsociados $importador,
        private AltaDeCuentasDeAfiliados $altaDeCuentas,
    ) {}

    /**
     * @return array{carga: ResultadoDeCargaDeAsociados, cuentas: ResultadoDeAltaDeCuentas|null}
     */
    public function importar(string $ruta, string $categoriaPorDefecto, bool $crearCuentas): array
    {
        return DB::transaction(function () use ($ruta, $categoriaPorDefecto, $crearCuentas): array {
            $carga = $this->importador->importar($ruta, $categoriaPorDefecto);

            if (! $crearCuentas) {
                return ['carga' => $carga, 'cuentas' => null];
            }

            $fichas = Asociado::query()
                ->whereKey($carga->fichasTocadas())
                ->orderBy('nombre')
                ->get();

            return ['carga' => $carga, 'cuentas' => $this->altaDeCuentas->crear($fichas)];
        });
    }
}
