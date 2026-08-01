<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Arma los formatos oficiales de ejemplo de la guía normativa.
 *
 * Escribe un PDF 1.4 mínimo a mano (catálogo, una página y un flujo de
 * texto con Helvetica) para no depender de librerías ni de descargas.
 */
class GeneradorPdf
{
    private const int ANCHO = 612;

    private const int ALTO = 792;

    /**
     * @param  list<string>  $lineas
     * @return string Ruta relativa dentro del disco `public`.
     */
    public function generar(string $titulo, string $subtitulo, array $lineas, string $carpeta, string $archivo): string
    {
        $ruta = "{$carpeta}/{$archivo}";

        if (Storage::disk('public')->exists($ruta)) {
            return $ruta;
        }

        Storage::disk('public')->put($ruta, $this->construir($titulo, $subtitulo, $lineas));

        return $ruta;
    }

    /** @param  list<string>  $lineas */
    private function construir(string $titulo, string $subtitulo, array $lineas): string
    {
        $contenido = $this->flujoDeTexto($titulo, $subtitulo, $lineas);

        $objetos = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>',
                self::ANCHO,
                self::ALTO
            ),
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            sprintf("<< /Length %d >>\nstream\n%s\nendstream", strlen($contenido), $contenido),
        ];

        $pdf = "%PDF-1.4\n";
        $desplazamientos = [];

        foreach ($objetos as $indice => $objeto) {
            $desplazamientos[] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $indice + 1, $objeto);
        }

        $inicioXref = strlen($pdf);
        $total = count($objetos) + 1;

        $pdf .= "xref\n0 {$total}\n0000000000 65535 f \n";

        foreach ($desplazamientos as $desplazamiento) {
            $pdf .= sprintf("%010d 00000 n \n", $desplazamiento);
        }

        $pdf .= "trailer\n<< /Size {$total} /Root 1 0 R >>\nstartxref\n{$inicioXref}\n%%EOF\n";

        return $pdf;
    }

    /** @param  list<string>  $lineas */
    private function flujoDeTexto(string $titulo, string $subtitulo, array $lineas): string
    {
        $y = self::ALTO - 90;
        $flujo = "BT\n";

        $flujo .= sprintf("/F1 17 Tf\n1 0 0 1 62 %d Tm\n(%s) Tj\n", $y, $this->escapar($titulo));
        $y -= 26;

        $flujo .= sprintf("/F2 10 Tf\n1 0 0 1 62 %d Tm\n(%s) Tj\n", $y, $this->escapar($subtitulo));
        $y -= 34;

        foreach ($lineas as $linea) {
            if ($linea === '') {
                $y -= 10;

                continue;
            }

            $negrita = str_starts_with($linea, '# ');
            $texto = $negrita ? substr($linea, 2) : $linea;

            $flujo .= sprintf(
                "/%s %d Tf\n1 0 0 1 62 %d Tm\n(%s) Tj\n",
                $negrita ? 'F1' : 'F2',
                $negrita ? 12 : 10,
                $y,
                $this->escapar($texto)
            );

            $y -= $negrita ? 22 : 17;
        }

        $flujo .= sprintf(
            "/F2 8 Tf\n1 0 0 1 62 60 Tm\n(%s) Tj\n",
            $this->escapar('Documento de ejemplo generado para el prototipo. Verifique siempre con la entidad competente.')
        );

        return $flujo."ET\n";
    }

    /** Los textos base de PDF van en WinAnsi, no en UTF-8. */
    private function escapar(string $texto): string
    {
        $winAnsi = mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $winAnsi);
    }
}
