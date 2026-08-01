<?php

namespace App\Support;

/**
 * Extrae el ID de un video de YouTube a partir de una URL.
 *
 * Vive fuera del modelo porque es parsing puro de texto: así se puede
 * probar exhaustivamente sin base de datos, y queda claro que del enlace
 * que alguien escriba en el panel solo se embebe el ID validado.
 */
final class VideoDeYoutube
{
    /**
     * Delimitador `~` a propósito: el `#` de las anclas de URL forma parte de
     * los patrones y rompería un delimitador `#`.
     *
     * @var list<string>
     */
    private const array PATRONES = [
        '~^https?://(?:www\.)?youtube\.com/watch\?(?:[^#]*&)?v=([A-Za-z0-9_-]{11})(?:[&#]|$)~',
        '~^https?://(?:www\.)?youtube\.com/embed/([A-Za-z0-9_-]{11})(?:[?/#]|$)~',
        '~^https?://(?:www\.)?youtube\.com/shorts/([A-Za-z0-9_-]{11})(?:[?/#]|$)~',
        '~^https?://youtu\.be/([A-Za-z0-9_-]{11})(?:[?/#]|$)~',
    ];

    public static function id(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        foreach (self::PATRONES as $patron) {
            if (preg_match($patron, trim($url), $coincidencias) === 1) {
                return $coincidencias[1];
            }
        }

        return null;
    }

    public static function esValida(?string $url): bool
    {
        return self::id($url) !== null;
    }
}
