<?php

namespace App\Services;

/**
 * Resumen de una importación de cartera: cuántas filas entraron y qué falló,
 * fila por fila.
 */
class ResultadoDeImportacion
{
    private int $actualizados = 0;

    /** @var list<string> */
    private array $errores = [];

    public function contarActualizado(): void
    {
        $this->actualizados++;
    }

    public function agregarErrorDeFila(int $numeroDeFila, string $mensaje): void
    {
        $this->errores[] = "Fila {$numeroDeFila}: {$mensaje}";
    }

    public function agregarErrorGeneral(string $mensaje): void
    {
        $this->errores[] = $mensaje;
    }

    public function actualizados(): int
    {
        return $this->actualizados;
    }

    /** @return list<string> */
    public function errores(): array
    {
        return $this->errores;
    }

    public function tieneErrores(): bool
    {
        return $this->errores !== [];
    }

    public function resumen(): string
    {
        $partes = ["{$this->actualizados} estados de cuenta actualizados"];

        if ($this->tieneErrores()) {
            $partes[] = count($this->errores).' con problemas';
        }

        return implode(' · ', $partes).'.';
    }
}
