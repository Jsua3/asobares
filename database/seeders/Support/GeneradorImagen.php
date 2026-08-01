<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Genera portadas de relleno para el demo sin depender de URLs externas.
 *
 * Son composiciones geométricas en la paleta de marca: el nombre del negocio
 * se rotula en HTML sobre la tarjeta, no dentro del PNG, para que se lea
 * nítido en cualquier tamaño.
 */
class GeneradorImagen
{
    private const int FONDO = 0x0C0A0B;

    /** Tonos cálidos emparentados con el rojo de marca #EE4036. */
    private const array ACENTOS = [0xEE4036, 0xF27065, 0xB71F17, 0xDA2A20, 0x7D1E1A];

    /**
     * Crea un PNG determinista a partir de la semilla y devuelve su ruta
     * relativa dentro del disco `public`.
     */
    public function generar(string $semilla, string $carpeta, int $ancho = 1200, int $alto = 900): string
    {
        $ruta = "{$carpeta}/".md5($semilla).'.png';

        if (Storage::disk('public')->exists($ruta)) {
            return $ruta;
        }

        $lienzo = imagecreatetruecolor($ancho, $alto);
        imagefill($lienzo, 0, 0, $this->color($lienzo, self::FONDO));

        // Semilla determinista: el mismo negocio siempre da la misma portada.
        $aleatorio = new Randomizer(new Mt19937(crc32($semilla)));

        $this->dibujarResplandor($lienzo, $ancho, $alto, $aleatorio);
        $this->dibujarBandas($lienzo, $ancho, $alto, $aleatorio);
        $this->dibujarCirculo($lienzo, $ancho, $alto, $aleatorio);
        $this->dibujarVineta($lienzo, $ancho, $alto);

        ob_start();
        imagepng($lienzo, null, 6);
        $binario = (string) ob_get_clean();
        imagedestroy($lienzo);

        Storage::disk('public')->put($ruta, $binario);

        return $ruta;
    }

    /**
     * Resplandor rojo difuso en la parte superior, como el hero del sitio.
     *
     * Se apilan elipses casi transparentes de mayor a menor: la opacidad se
     * acumula hacia el centro y produce una caída suave, sin bordes duros.
     */
    private function dibujarResplandor(\GdImage $lienzo, int $ancho, int $alto, Randomizer $aleatorio): void
    {
        $acento = self::ACENTOS[$aleatorio->getInt(0, count(self::ACENTOS) - 1)];
        $centroX = $aleatorio->getInt((int) ($ancho * 0.3), (int) ($ancho * 0.7));
        $radio = (int) ($ancho * 0.42);

        // El centro queda por encima del borde superior: solo entra al cuadro
        // la cola del resplandor, que es lo que da el aire de noche.
        for ($paso = $radio; $paso > 0; $paso -= 10) {
            imagefilledellipse(
                $lienzo,
                $centroX,
                (int) ($alto * -0.08),
                $paso * 2,
                (int) ($paso * 1.35),
                $this->color($lienzo, $acento, 123)
            );
        }
    }

    /** Bandas diagonales tenues que dan textura sin ensuciar. */
    private function dibujarBandas(\GdImage $lienzo, int $ancho, int $alto, Randomizer $aleatorio): void
    {
        $separacion = $aleatorio->getInt(48, 80);

        for ($x = -$alto; $x < $ancho; $x += $separacion) {
            $puntos = [$x, $alto, $x + 18, $alto, $x + 18 + $alto, 0, $x + $alto, 0];
            imagefilledpolygon($lienzo, $puntos, $this->color($lienzo, 0xFFFFFF, 124));
        }
    }

    private function dibujarCirculo(\GdImage $lienzo, int $ancho, int $alto, Randomizer $aleatorio): void
    {
        $acento = self::ACENTOS[$aleatorio->getInt(0, count(self::ACENTOS) - 1)];
        $diametro = (int) ($alto * 0.42);

        imagesetthickness($lienzo, 5);
        imageellipse(
            $lienzo,
            $aleatorio->getInt((int) ($ancho * 0.35), (int) ($ancho * 0.65)),
            (int) ($alto * 0.58),
            $diametro,
            $diametro,
            $this->color($lienzo, $acento, 88)
        );
    }

    /**
     * Oscurece la franja inferior para que el nombre del negocio, que se
     * rotula en HTML sobre la tarjeta, siempre contraste.
     */
    private function dibujarVineta(\GdImage $lienzo, int $ancho, int $alto): void
    {
        $altura = (int) ($alto * 0.5);

        for ($y = 0; $y < $altura; $y++) {
            // y = 0 es la fila de abajo: ahí el negro es opaco y se desvanece hacia arriba.
            $alfa = (int) (127 * ($y / $altura) ** 0.6);
            imagefilledrectangle($lienzo, 0, $alto - $y, $ancho, $alto - $y, $this->color($lienzo, 0x000000, $alfa));
        }
    }

    /** @param int<0, 127> $alfa 0 = opaco, 127 = transparente. */
    private function color(\GdImage $lienzo, int $hex, int $alfa = 0): int
    {
        return imagecolorallocatealpha(
            $lienzo,
            ($hex >> 16) & 0xFF,
            ($hex >> 8) & 0xFF,
            $hex & 0xFF,
            $alfa
        );
    }
}
