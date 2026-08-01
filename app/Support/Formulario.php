<?php

namespace App\Support;

/**
 * Constantes compartidas por los formularios públicos.
 *
 * Vive en una clase y no en el trait porque PHP no permite leer una
 * constante de trait directamente, y las vistas Blade necesitan leerla.
 */
final class Formulario
{
    /** Campo oculto que una persona nunca llena y un bot sí. */
    public const string CAMPO_TRAMPA = 'sitio_web_confirmacion';
}
