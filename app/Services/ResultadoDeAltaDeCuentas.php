<?php

namespace App\Services;

/**
 * Qué cuentas de afiliado creó una importación, cuántas fichas ya tenían la
 * suya y qué fichas se quedaron sin cuenta, con el motivo. No lleva correos ni
 * contraseñas: lo lee la dirección en una notificación del panel.
 *
 * Las que ya tenían cuenta solo se cuentan. No piden nada al gremio, y al
 * volver a importar el archivo corregido son casi todas: nombradas una por una
 * empujarían fuera del aviso las fichas que sí hay que corregir.
 */
class ResultadoDeAltaDeCuentas
{
    private int $creadas = 0;

    private int $yaTenianCuenta = 0;

    /** @var list<string> */
    private array $sinCuenta = [];

    public function contarCreada(): void
    {
        $this->creadas++;
    }

    public function contarYaTenia(): void
    {
        $this->yaTenianCuenta++;
    }

    public function agregarSinCuenta(string $establecimiento, string $motivo): void
    {
        $this->sinCuenta[] = "«{$establecimiento}»: {$motivo}";
    }

    public function creadas(): int
    {
        return $this->creadas;
    }

    public function yaTenianCuenta(): int
    {
        return $this->yaTenianCuenta;
    }

    /** @return list<string> */
    public function sinCuenta(): array
    {
        return $this->sinCuenta;
    }

    public function resumen(): string
    {
        $tramos = [$this->creadas === 1 ? '1 cuenta creada' : "{$this->creadas} cuentas creadas"];

        if ($this->yaTenianCuenta > 0) {
            $tramos[] = $this->yaTenianCuenta === 1
                ? '1 ficha ya tenía cuenta'
                : "{$this->yaTenianCuenta} fichas ya tenían cuenta";
        }

        $sin = count($this->sinCuenta);

        if ($sin > 0) {
            $tramos[] = $sin === 1 ? '1 ficha sin cuenta' : "{$sin} fichas sin cuenta";
        }

        return implode(' · ', $tramos).'.';
    }
}
