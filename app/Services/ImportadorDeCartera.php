<?php

namespace App\Services;

use App\Models\Asociado;
use App\Models\Cartera;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use League\Csv\Reader;
use Throwable;

/**
 * Carga el archivo de cartera que envía la contadora.
 *
 * Nunca aborta por una fila mala: procesa todo lo válido y devuelve el
 * detalle de lo que falló, con número de fila, para poder corregir el
 * archivo sin adivinar.
 */
class ImportadorDeCartera
{
    /** @var list<string> */
    public const array COLUMNAS_REQUERIDAS = ['establecimiento', 'saldo_pendiente', 'meses_mora'];

    /** @var list<string> */
    public const array COLUMNAS_OPCIONALES = ['ultimo_pago'];

    public function importar(string $rutaAbsoluta): ResultadoDeImportacion
    {
        $resultado = new ResultadoDeImportacion;

        try {
            $lector = Reader::createFromPath($rutaAbsoluta, 'r');
            $lector->setHeaderOffset(0);
            $encabezados = array_map($this->normalizar(...), $lector->getHeader());
        } catch (Throwable $error) {
            $resultado->agregarErrorGeneral('No se pudo leer el archivo: '.$error->getMessage());

            return $resultado;
        }

        $faltantes = array_diff(self::COLUMNAS_REQUERIDAS, $encabezados);

        if ($faltantes !== []) {
            $resultado->agregarErrorGeneral(
                'Al archivo le faltan estas columnas: '.implode(', ', $faltantes).
                '. Se esperan: '.implode(', ', [...self::COLUMNAS_REQUERIDAS, ...self::COLUMNAS_OPCIONALES]).'.'
            );

            return $resultado;
        }

        // Índice por slug para no consultar la base en cada fila.
        $asociados = Asociado::pluck('id', 'slug');

        // Las filas malas se reportan sin abortar el resto, pero el archivo se
        // aplica como un bloque: si el proceso se cae a mitad de camino, no
        // pueden quedar la mitad de las carteras actualizadas y la otra no.
        DB::transaction(function () use ($lector, $asociados, $resultado): void {
            foreach ($lector->getRecords() as $numero => $fila) {
                // getRecords numera desde la fila siguiente al encabezado.
                $this->procesarFila($this->normalizarFila($fila), (int) $numero, $asociados, $resultado);
            }
        });

        return $resultado;
    }

    /**
     * @param  array<string, string>  $fila
     * @param  Collection<string, int>  $asociados
     */
    private function procesarFila(array $fila, int $numero, $asociados, ResultadoDeImportacion $resultado): void
    {
        $nombre = trim($fila['establecimiento'] ?? '');

        if ($nombre === '') {
            $resultado->agregarErrorDeFila($numero, 'La columna «establecimiento» viene vacía.');

            return;
        }

        $slug = Str::slug($nombre);
        $asociadoId = $asociados[$slug] ?? null;

        if ($asociadoId === null) {
            $resultado->agregarErrorDeFila($numero, "No existe un asociado llamado «{$nombre}».");

            return;
        }

        if (trim($fila['saldo_pendiente'] ?? '') === '') {
            $resultado->agregarErrorDeFila($numero, "«{$nombre}»: el saldo pendiente viene vacío. Si no debe nada, escribe 0.");

            return;
        }

        $saldo = $this->aNumero($fila['saldo_pendiente'] ?? '');

        if ($saldo === null || $saldo < 0) {
            $resultado->agregarErrorDeFila($numero, "«{$nombre}»: el saldo pendiente no es un número válido.");

            return;
        }

        if (trim($fila['meses_mora'] ?? '') === '') {
            $resultado->agregarErrorDeFila($numero, "«{$nombre}»: los meses de mora vienen vacíos. Si está al día, escribe 0.");

            return;
        }

        $meses = $this->aNumero($fila['meses_mora'] ?? '');

        if ($meses === null || $meses < 0 || $meses != (int) $meses) {
            $resultado->agregarErrorDeFila($numero, "«{$nombre}»: los meses de mora deben ser un entero de 0 en adelante.");

            return;
        }

        $ultimoPago = $this->aFecha($fila['ultimo_pago'] ?? '');

        if ($ultimoPago === false) {
            $resultado->agregarErrorDeFila($numero, "«{$nombre}»: la fecha de último pago no se entiende (usa AAAA-MM-DD o DD/MM/AAAA).");

            return;
        }

        Cartera::updateOrCreate(
            ['asociado_id' => $asociadoId],
            [
                'saldo_pendiente' => $saldo,
                'meses_mora' => (int) $meses,
                'ultimo_pago_at' => $ultimoPago?->toDateString(),
                'actualizado_at' => now(),
            ]
        );

        $resultado->contarActualizado();
    }

    /** @param  array<string, string|null>  $fila */
    private function normalizarFila(array $fila): array
    {
        $normalizada = [];

        foreach ($fila as $columna => $valor) {
            $normalizada[$this->normalizar((string) $columna)] = (string) ($valor ?? '');
        }

        return $normalizada;
    }

    /** Tolera mayúsculas, tildes y espacios en los encabezados del contador. */
    private function normalizar(string $texto): string
    {
        return Str::of($texto)->trim()->lower()->ascii()->replace([' ', '-'], '_')->toString();
    }

    /**
     * Interpreta una cifra de dinero venga como venga del computador de la
     * contadora: «1.250.000», «1.250.000,50», «1250.75» o «1,250,000.75».
     *
     * La regla es una sola: el separador decimal es el que está más a la
     * derecha, y solo cuenta como decimal si le siguen una o dos cifras. Todo
     * lo demás son separadores de miles y se descarta. Así «1.250.000» son un
     * millón doscientos cincuenta mil pesos y «1250.75» son mil doscientos
     * cincuenta con setenta y cinco.
     *
     * Antes se borraban TODOS los puntos, de modo que un archivo exportado en
     * formato inglés multiplicaba cada saldo por cien.
     *
     * Devuelve null cuando no hay número, incluida la celda vacía: dejar la
     * deuda de alguien en cero tiene que ser una decisión escrita, no el
     * efecto de una casilla en blanco.
     */
    private function aNumero(string $valor): ?float
    {
        $limpio = preg_replace('/[^\d,.\-]/u', '', trim($valor)) ?? '';

        if ($limpio === '') {
            return null;
        }

        $posicionDecimal = max(
            ($coma = strrpos($limpio, ',')) === false ? -1 : $coma,
            ($punto = strrpos($limpio, '.')) === false ? -1 : $punto,
        );

        $cifrasDecimales = $posicionDecimal >= 0 ? strlen($limpio) - $posicionDecimal - 1 : 0;

        if ($posicionDecimal >= 0 && $cifrasDecimales >= 1 && $cifrasDecimales <= 2) {
            $limpio = str_replace([',', '.'], '', substr($limpio, 0, $posicionDecimal))
                .'.'.substr($limpio, $posicionDecimal + 1);
        } else {
            $limpio = str_replace([',', '.'], '', $limpio);
        }

        return is_numeric($limpio) ? (float) $limpio : null;
    }

    /** @return CarbonImmutable|null|false `false` significa formato inválido. */
    private function aFecha(string $valor): CarbonImmutable|null|false
    {
        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'] as $formato) {
            try {
                return CarbonImmutable::createFromFormat($formato, $valor)->startOfDay();
            } catch (Throwable) {
                continue;
            }
        }

        return false;
    }
}
