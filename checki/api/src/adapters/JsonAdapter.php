<?php

/**
 * Adaptador JSON genérico.
 *
 * Acepta cualquier JSON con mapeo de campos configurable.
 * Soporta JSONs planos, anidados (dot notation) y arrays directos.
 *
 * Uso básico (campos ya en formato estándar):
 *   $adapter = new JsonAdapter();
 *
 * Uso con mapeo explícito:
 *   $adapter = new JsonAdapter(
 *     asistentesMap: [
 *       'uid_qr'  => 'qrData',       // campo en el JSON => campo DTO
 *       'nombre'  => 'profile.name',  // dot notation para campos anidados
 *       'email'   => 'profile.email',
 *     ]
 *   );
 */
class JsonAdapter implements AdapterInterface
{
    public function __construct(
        private readonly array $asistentesMap = [],
        private readonly array $agendaMap     = [],
    ) {}

    public function nombre(): string
    {
        return 'json';
    }

    // ─────────────────────────────────────────────────────────
    // ASISTENTES
    // ─────────────────────────────────────────────────────────

    public function parseAsistentes(string $raw): array
    {
        $data = $this->decode($raw);
        if ($data === null) {
            return [];
        }

        // Soportar { "asistentes": [...] } o directamente [...]
        $items = $this->extraerArray($data, ['asistentes', 'attendees', 'data', 'results']);

        $dtos    = [];
        $errores = [];

        foreach ($items as $i => $item) {
            $uid_qr = $this->get($item, 'uid_qr', $this->asistentesMap);
            $nombre = $this->get($item, 'nombre',  $this->asistentesMap);

            if (empty($uid_qr) || empty($nombre)) {
                $errores[] = "Item $i: uid_qr o nombre vacío — omitido";
                continue;
            }

            // Metadata: todo lo que no pertenece al contrato estándar
            $camposEstandar = ['uid_qr', 'nombre', 'email', 'empresa', 'external_id'];
            $reservados     = array_values($this->asistentesMap) + $camposEstandar;
            $metadata       = [];

            foreach ($item as $k => $v) {
                if (!in_array($k, $reservados, true) && $v !== null && $v !== '') {
                    $metadata[$k] = $v;
                }
            }

            $dtos[] = new AsistenteDTO(
                uid_qr:      (string) $uid_qr,
                nombre:      (string) $nombre,
                email:       $this->getOrNull($item, 'email',       $this->asistentesMap),
                empresa:     $this->getOrNull($item, 'empresa',     $this->asistentesMap),
                external_id: $this->getOrNull($item, 'external_id', $this->asistentesMap),
                metadata:    $metadata,
            );
        }

        if (!empty($errores)) {
            error_log('[JsonAdapter] parseAsistentes — ' . implode(' | ', $errores));
        }

        return $dtos;
    }

    // ─────────────────────────────────────────────────────────
    // AGENDA
    // ─────────────────────────────────────────────────────────

    public function parseAgenda(string $raw): array
    {
        $data = $this->decode($raw);
        if ($data === null) {
            return [];
        }

        $items = $this->extraerArray($data, ['charlas', 'agenda', 'sessions', 'talks', 'data']);

        $dtos    = [];
        $errores = [];
        $orden   = 1;

        foreach ($items as $i => $item) {
            $titulo       = $this->get($item, 'titulo',       $this->agendaMap);
            $salon_nombre = $this->get($item, 'salon_nombre', $this->agendaMap);
            $hora_inicio  = $this->get($item, 'hora_inicio',  $this->agendaMap);
            $hora_fin     = $this->get($item, 'hora_fin',     $this->agendaMap);

            if (empty($titulo) || empty($salon_nombre) || empty($hora_inicio) || empty($hora_fin)) {
                $errores[] = "Item $i: campos obligatorios faltantes — omitido";
                continue;
            }

            $hora_inicio = $this->normalizarDatetime((string) $hora_inicio);
            $hora_fin    = $this->normalizarDatetime((string) $hora_fin);

            if (!$hora_inicio || !$hora_fin) {
                $errores[] = "Item $i: formato de hora inválido — omitido";
                continue;
            }

            $dtos[] = new CharlaDTO(
                titulo:       (string) $titulo,
                salon_nombre: (string) $salon_nombre,
                hora_inicio:  $hora_inicio,
                hora_fin:     $hora_fin,
                ponente:      $this->getOrNull($item, 'ponente', $this->agendaMap),
                orden_en_dia: $orden++,
            );
        }

        if (!empty($errores)) {
            error_log('[JsonAdapter] parseAgenda — ' . implode(' | ', $errores));
        }

        return $dtos;
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS INTERNOS
    // ─────────────────────────────────────────────────────────

    private function decode(string $raw): ?array
    {
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[JsonAdapter] JSON inválido: ' . json_last_error_msg());
            return null;
        }
        return $data;
    }

    private function extraerArray(array $data, array $claves): array
    {
        // Si el root ya es un array indexado, usarlo directamente
        if (isset($data[0])) {
            return $data;
        }

        // Buscar la primera clave conocida que contenga un array
        foreach ($claves as $clave) {
            if (isset($data[$clave]) && is_array($data[$clave])) {
                return $data[$clave];
            }
        }

        return [];
    }

    /**
     * Obtiene un valor del item usando el mapeo o el nombre estándar.
     * Soporta dot notation: 'profile.name' accede a $item['profile']['name'].
     */
    private function get(array $item, string $campo, array $map): mixed
    {
        $clave = $map[$campo] ?? $campo;
        return $this->dotGet($item, $clave);
    }

    private function getOrNull(array $item, string $campo, array $map): ?string
    {
        $val = $this->get($item, $campo, $map);
        if ($val === null || $val === '') {
            return null;
        }
        return (string) $val;
    }

    private function dotGet(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $val  = $data;
        foreach ($keys as $key) {
            if (!is_array($val) || !array_key_exists($key, $val)) {
                return null;
            }
            $val = $val[$key];
        }
        return $val;
    }

    private function normalizarDatetime(string $valor): ?string
    {
        $ts = strtotime($valor);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }
}
