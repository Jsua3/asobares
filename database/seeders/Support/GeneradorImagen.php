<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;
use Random\Engine\Mt19937;
use Random\Randomizer;
use RuntimeException;

/**
 * Genera portadas de relleno para el demo sin depender de URLs externas.
 *
 * El fondo es transparente a propósito: la que se ve por detrás es la
 * superficie de la tarjeta, así que la misma imagen sirve en tema claro y en
 * oscuro sin generar dos versiones. Encima van unas diagonales en gris neutro
 * —legible sobre ambas superficies— y el monograma de la marca.
 *
 * El nombre del negocio se rotula en HTML sobre la tarjeta, no dentro del PNG,
 * para que se lea nítido en cualquier tamaño.
 */
class GeneradorImagen
{
    /**
     * Gris equidistante de las dos superficies de tarjeta: da 3,7:1 tanto
     * sobre #FFFFFF del tema claro como sobre #121011 del oscuro, así que las
     * diagonales se ven igual de presentes en los dos.
     */
    private const int GRIS = 0x747474;

    /** 0 = opaco, 127 = invisible. */
    private const int ALFA_DIAGONALES = 86;

    private const string MONOGRAMA = 'img/monograma-asobares.png';

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

        Storage::disk('public')->put($ruta, $this->componer($semilla, $ancho, $alto));

        return $ruta;
    }

    /**
     * Vuelve a dibujar una portada ya existente con el diseño actual. La usa
     * el mantenimiento del demo para refrescar las imágenes sembradas sin
     * tener que rehacer la base de datos.
     */
    public function regenerar(string $semilla, string $ruta, int $ancho = 1200, int $alto = 900): void
    {
        Storage::disk('public')->put($ruta, $this->componer($semilla, $ancho, $alto));
    }

    private function componer(string $semilla, int $ancho, int $alto): string
    {
        $lienzo = imagecreatetruecolor($ancho, $alto);

        // Sin mezcla y guardando alfa, el relleno inicial deja el lienzo
        // realmente transparente en vez de negro.
        imagealphablending($lienzo, false);
        imagesavealpha($lienzo, true);
        imagefill($lienzo, 0, 0, imagecolorallocatealpha($lienzo, 0, 0, 0, 127));
        imagealphablending($lienzo, true);

        // Semilla determinista: el mismo negocio siempre da la misma portada,
        // pero dos negocios distintos no salen calcados.
        $aleatorio = new Randomizer(new Mt19937(crc32($semilla)));

        $this->dibujarDiagonales($lienzo, $ancho, $alto, $aleatorio);
        $this->dibujarMonograma($lienzo, $ancho, $alto);

        ob_start();
        imagepng($lienzo, null, 6);

        return (string) ob_get_clean();
    }

    /** Diagonales grises: dan textura y ritmo sin tapar el monograma. */
    private function dibujarDiagonales(\GdImage $lienzo, int $ancho, int $alto, Randomizer $aleatorio): void
    {
        $color = $this->color($lienzo, self::GRIS, self::ALFA_DIAGONALES);

        // La separación y el grosor se escalan con el alto para que una imagen
        // de 270 px y otra de 900 px se vean con la misma trama.
        $escala = $alto / 900;
        $separacion = (int) max(24, $aleatorio->getInt(78, 104) * $escala);
        $grosor = (int) max(6, $aleatorio->getInt(20, 30) * $escala);

        for ($x = -$alto; $x < $ancho; $x += $separacion) {
            imagefilledpolygon(
                $lienzo,
                [$x, $alto, $x + $grosor, $alto, $x + $grosor + $alto, 0, $x + $alto, 0],
                $color
            );
        }
    }

    /**
     * Monograma centrado y por encima del eje: la franja de abajo la tapa el
     * degradado con el que la tarjeta rotula el nombre del establecimiento.
     */
    private function dibujarMonograma(\GdImage $lienzo, int $ancho, int $alto): void
    {
        $origen = public_path(self::MONOGRAMA);

        if (! is_file($origen)) {
            throw new RuntimeException("Falta el monograma de marca en {$origen}.");
        }

        $monograma = imagecreatefrompng($origen);

        if ($monograma === false) {
            throw new RuntimeException("No se pudo leer el monograma de marca en {$origen}.");
        }

        imagealphablending($monograma, true);
        imagesavealpha($monograma, true);

        $altoDestino = (int) ($alto * 0.30);
        $anchoDestino = (int) ($altoDestino * imagesx($monograma) / imagesy($monograma));

        imagecopyresampled(
            $lienzo,
            $monograma,
            (int) (($ancho - $anchoDestino) / 2),
            (int) ($alto * 0.42 - $altoDestino / 2),
            0,
            0,
            $anchoDestino,
            $altoDestino,
            imagesx($monograma),
            imagesy($monograma)
        );
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
