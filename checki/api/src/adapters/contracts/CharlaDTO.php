<?php

/**
 * Contrato estándar de charla.
 * hora_inicio / hora_fin deben llegar en formato 'Y-m-d H:i:s'.
 */
class CharlaDTO
{
    public function __construct(
        public readonly string  $titulo,
        public readonly string  $salon_nombre,
        public readonly string  $hora_inicio,
        public readonly string  $hora_fin,
        public readonly ?string $ponente      = null,
        public readonly int     $orden_en_dia = 0,
    ) {}

    public function esValido(): bool
    {
        return $this->titulo       !== ''
            && $this->salon_nombre !== ''
            && $this->hora_inicio  !== ''
            && $this->hora_fin     !== '';
    }
}
