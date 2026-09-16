<?php

namespace App\Services;

/**
 * Qué cuentas de afiliado creó una importación y qué fichas se quedaron sin
 * la suya, con el motivo. No lleva correos ni contraseñas: lo lee la
 * dirección en una notificación del panel.
 */
class ResultadoDeAltaDeCuentas
{
    private int $creadas = 0;

    /** @var list<string> */
    private array $sinCuenta = [];

    public function contarCreada(): void
    {
        $this->creadas++;
    }

    public function agregarSinCuenta(string $establecimiento, string $motivo): void
    {
        $this->sinCuenta[] = "«{$establecimiento}»: {$motivo}";
    }

    public function creadas(): int
    {
        return $this->creadas;
    }

    /** @return list<string> */
    public function sinCuenta(): array
    {
        return $this->sinCuenta;
    }

    public function resumen(): string
    {
        $creadas = $this->creadas === 1 ? '1 cuenta creada' : "{$this->creadas} cuentas creadas";
        $sin = count($this->sinCuenta);

        if ($sin === 0) {
            return "{$creadas}.";
        }

        return "{$creadas} · {$sin} ".($sin === 1 ? 'ficha sin cuenta' : 'fichas sin cuenta').'.';
    }
}
