<?php

/**
 * Contrato que todo adaptador debe implementar.
 *
 * Recibe datos crudos (string con CSV, JSON, etc.)
 * y devuelve arrays del contrato estándar.
 * El core nunca llama al adaptador directamente — lo hace ImportService.
 */
interface AdapterInterface
{
    /**
     * @return AsistenteDTO[]
     */
    public function parseAsistentes(string $raw): array;

    /**
     * @return CharlaDTO[]
     */
    public function parseAgenda(string $raw): array;

    /**
     * Nombre del adaptador para sync_log y mensajes de error.
     */
    public function nombre(): string;
}
