<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Cargos estándar del solicitante en /afiliate.
 *
 * El valor final se guarda en `solicitud_afiliacion.solicitante_cargo`.
 * Si la persona elige Otro, se persiste el texto que escriba, no la palabra
 * «Otro». Las solicitudes históricas con cargo libre siguen legibles tal cual.
 */
enum CargoDelSolicitante: string implements HasLabel
{
    case Propietario = 'Propietario/a';
    case RepresentanteLegal = 'Representante legal';
    case Administrador = 'Administrador/a';
    case Gerente = 'Gerente';
    case Encargado = 'Encargado/a';
    case Otro = 'Otro';

    public function getLabel(): string
    {
        return $this->value;
    }

    /** @return array<string, string> */
    public static function opcionesParaFormulario(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $cargo): array => [$cargo->value => $cargo->getLabel()])
            ->all();
    }

    public function esOtro(): bool
    {
        return $this === self::Otro;
    }
}
