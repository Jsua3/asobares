<?php

namespace App\Services;

/**
 * Resumen de una carga de la base de asociados del gremio: qué entró, qué se
 * actualizó y qué se rechazó, fila por fila y con el motivo.
 *
 * Mismo criterio que la cartera: una fila mala no aborta el archivo, se
 * reporta con su número para poder corregir el original sin adivinar.
 */
class ResultadoDeCargaDeAsociados
{
    private int $creados = 0;

    private int $actualizados = 0;

    /** @var list<string> */
    private array $errores = [];

    /** @var list<string> */
    private array $avisos = [];

    /**
     * Ids de las fichas creadas o actualizadas en esta carga, como claves para
     * no repetir: el archivo del gremio trae una misma ficha en dos filas.
     *
     * @var array<int, true>
     */
    private array $fichasTocadas = [];

    public function contarCreado(): void
    {
        $this->creados++;
    }

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

    public function agregarAviso(string $mensaje): void
    {
        $this->avisos[] = $mensaje;
    }

    public function anotarFicha(int $id): void
    {
        $this->fichasTocadas[$id] = true;
    }

    /**
     * Las fichas que esta carga creó o actualizó. La importación desde el
     * panel crea cuentas solo para estas: una ficha que no venía en el
     * archivo no recibe una.
     *
     * @return list<int>
     */
    public function fichasTocadas(): array
    {
        return array_keys($this->fichasTocadas);
    }

    public function creados(): int
    {
        return $this->creados;
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

    /** @return list<string> */
    public function avisos(): array
    {
        return $this->avisos;
    }

    public function tieneErrores(): bool
    {
        return $this->errores !== [];
    }

    public function resumen(): string
    {
        $partes = ["{$this->creados} creados", "{$this->actualizados} actualizados"];

        if ($this->tieneErrores()) {
            $partes[] = count($this->errores).' con problemas';
        }

        return implode(' · ', $partes).'.';
    }
}
