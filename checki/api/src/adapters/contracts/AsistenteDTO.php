<?php

/**
 * Contrato estándar de asistente.
 * Cualquier adaptador produce esto — el core nunca sabe de dónde vino.
 */
class AsistenteDTO
{
    public function __construct(
        public readonly string  $uid_qr,
        public readonly string  $nombre,
        public readonly ?string $email       = null,
        public readonly ?string $empresa     = null,
        public readonly ?string $external_id = null,
        public readonly array   $metadata    = [],
    ) {}

    public function esValido(): bool
    {
        return $this->uid_qr !== '' && $this->nombre !== '';
    }
}
