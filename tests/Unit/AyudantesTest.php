<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AyudantesTest extends TestCase
{
    /** @return list<array{0: float|int|null, 1: string}> */
    public static function montos(): array
    {
        return [
            [0, '$0'],
            [50000, '$50.000'],
            [1250000, '$1.250.000'],
            [2104124.0, '$2.104.124'],
            [null, '$0'],
        ];
    }

    #[DataProvider('montos')]
    public function test_formatea_pesos_colombianos(float|int|null $monto, string $esperado): void
    {
        $this->assertSame($esperado, pesos($monto));
    }

    /** @return list<array{0: ?string, 1: ?string}> */
    public static function numeros(): array
    {
        return [
            ['3215549513', 'https://wa.me/573215549513'],
            ['321 554 9513', 'https://wa.me/573215549513'],
            ['573215549513', 'https://wa.me/573215549513'],
            ['+57 321 5549513', 'https://wa.me/573215549513'],
            [null, null],
            ['', null],
            ['sin-numeros', null],
        ];
    }

    #[DataProvider('numeros')]
    public function test_arma_el_enlace_de_whatsapp(?string $numero, ?string $esperado): void
    {
        $this->assertSame($esperado, enlaceWhatsapp($numero));
    }

    public function test_el_mensaje_precargado_va_codificado(): void
    {
        $this->assertSame(
            'https://wa.me/573215549513?text=Hola%2C%20%C2%BFc%C3%B3mo%20est%C3%A1n%3F',
            enlaceWhatsapp('3215549513', 'Hola, ¿cómo están?')
        );
    }
}
