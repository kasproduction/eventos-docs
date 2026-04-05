<?php

/**
 * Adaptador CSV universal.
 *
 * Funciona con cualquier CSV porque el mapeo de columnas es configurable.
 * Si no se pasa mapeo, intenta detectar las columnas automáticamente
 * buscando nombres comunes en el header.
 *
 * Uso básico (auto-detect):
 *   $adapter = new CsvAdapter();
 *   $dtos = $adapter->parseAsistentes($csvString);
 *
 * Uso con mapeo explícito (cuando el CSV tiene nombres de columna propios):
 *   $adapter = new CsvAdapter(
 *     asistentesMap: ['uid_qr' => 'codigo_qr', 'nombre' => 'nombre_completo', ...]
 *   );
 */
class CsvAdapter implements AdapterInterface
{
    // Nombres alternativos que se reconocen en auto-detect
    private const ALIAS_ASISTENTES = [
        'uid_qr'      => ['uid_qr', 'qr', 'qr_code', 'codigo_qr', 'badge', 'qrdata', 'ticket'],
        'nombre'      => ['nombre', 'name', 'full_name', 'nombre_completo', 'attendee', 'asistente'],
        'email'       => ['email', 'correo', 'mail', 'e_mail'],
        'empresa'     => ['empresa', 'company', 'organizacion', 'organization', 'compania'],
        'external_id' => ['external_id', 'id', 'external', 'ref', 'registro', 'ticket_id'],
    ];

    private const ALIAS_AGENDA = [
        'titulo'       => ['titulo', 'title', 'charla', 'sesion', 'session', 'nombre', 'name'],
        'salon_nombre' => ['salon', 'sala', 'room', 'venue', 'salon_nombre', 'location'],
        'hora_inicio'  => ['hora_inicio', 'inicio', 'start', 'start_time', 'hora_inicio', 'from'],
        'hora_fin'     => ['hora_fin', 'fin', 'end', 'end_time', 'to'],
        'ponente'      => ['ponente', 'speaker', 'expositor', 'presenter'],
        'fecha'        => ['fecha', 'date', 'dia', 'day'],
    ];

    public function __construct(
        private readonly array  $asistentesMap = [],  // campo_dto => columna_csv
        private readonly array  $agendaMap     = [],
        private readonly string $delimiter     = ',',
        private readonly string $encoding      = 'UTF-8',
    ) {}

    public function nombre(): string
    {
        return 'csv';
    }

    // ─────────────────────────────────────────────────────────
    // ASISTENTES
    // ─────────────────────────────────────────────────────────

    public function parseAsistentes(string $raw): array
    {
        $rows   = $this->parseCsv($raw);
        if (empty($rows)) {
            return [];
        }

        $header = array_shift($rows);
        $header = $this->normalizeHeader($header);
        $map    = $this->resolveMap($header, self::ALIAS_ASISTENTES, $this->asistentesMap);

        $dtos    = [];
        $errores = [];

        foreach ($rows as $i => $row) {
            if ($this->rowVacia($row)) {
                continue;
            }

            $row = array_pad($row, count($header), '');
            $col = array_combine($header, $row);

            $uid_qr = trim($col[$map['uid_qr']] ?? '');
            $nombre = trim($col[$map['nombre']] ?? '');

            if ($uid_qr === '' || $nombre === '') {
                $errores[] = "Fila " . ($i + 2) . ": uid_qr o nombre vacío — omitida";
                continue;
            }

            // Metadata: todo lo que no está en el contrato estándar
            $camposEstandar = array_values($map);
            $metadata = [];
            foreach ($col as $k => $v) {
                if (!in_array($k, $camposEstandar, true) && $v !== '') {
                    $metadata[$k] = $v;
                }
            }

            $dtos[] = new AsistenteDTO(
                uid_qr:       $uid_qr,
                nombre:       $nombre,
                email:        $this->nullIfEmpty($col[$map['email']]       ?? ''),
                empresa:      $this->nullIfEmpty($col[$map['empresa']]     ?? ''),
                external_id:  $this->nullIfEmpty($col[$map['external_id']] ?? ''),
                metadata:     $metadata,
            );
        }

        if (!empty($errores)) {
            error_log('[CsvAdapter] parseAsistentes — ' . implode(' | ', $errores));
        }

        return $dtos;
    }

    // ─────────────────────────────────────────────────────────
    // AGENDA
    // ─────────────────────────────────────────────────────────

    public function parseAgenda(string $raw): array
    {
        $rows = $this->parseCsv($raw);
        if (empty($rows)) {
            return [];
        }

        $header = array_shift($rows);
        $header = $this->normalizeHeader($header);
        $map    = $this->resolveMap($header, self::ALIAS_AGENDA, $this->agendaMap);

        $dtos    = [];
        $errores = [];
        $orden   = 1;

        foreach ($rows as $i => $row) {
            if ($this->rowVacia($row)) {
                continue;
            }

            $row = array_pad($row, count($header), '');
            $col = array_combine($header, $row);

            $titulo       = trim($col[$map['titulo']]       ?? '');
            $salon_nombre = trim($col[$map['salon_nombre']] ?? '');
            $hora_inicio  = trim($col[$map['hora_inicio']]  ?? '');
            $hora_fin     = trim($col[$map['hora_fin']]     ?? '');
            $fecha        = trim($col[$map['fecha']]        ?? '');

            if ($titulo === '' || $salon_nombre === '' || $hora_inicio === '' || $hora_fin === '') {
                $errores[] = "Fila " . ($i + 2) . ": campos obligatorios faltantes — omitida";
                continue;
            }

            // Si la hora viene sin fecha, combinarla con la columna fecha
            $hora_inicio = $this->normalizarDatetime($hora_inicio, $fecha);
            $hora_fin    = $this->normalizarDatetime($hora_fin,    $fecha);

            if (!$hora_inicio || !$hora_fin) {
                $errores[] = "Fila " . ($i + 2) . ": formato de hora inválido — omitida";
                continue;
            }

            $dtos[] = new CharlaDTO(
                titulo:       $titulo,
                salon_nombre: $salon_nombre,
                hora_inicio:  $hora_inicio,
                hora_fin:     $hora_fin,
                ponente:      $this->nullIfEmpty($col[$map['ponente']] ?? ''),
                orden_en_dia: $orden++,
            );
        }

        if (!empty($errores)) {
            error_log('[CsvAdapter] parseAgenda — ' . implode(' | ', $errores));
        }

        return $dtos;
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS INTERNOS
    // ─────────────────────────────────────────────────────────

    private function parseCsv(string $raw): array
    {
        // Eliminar BOM UTF-8
        $raw = ltrim($raw, "\xEF\xBB\xBF");

        // Convertir encoding si es necesario
        if ($this->encoding !== 'UTF-8') {
            $raw = mb_convert_encoding($raw, 'UTF-8', $this->encoding);
        }

        // Detectar delimitador si es auto
        $delimiter = $this->delimiter === 'auto'
            ? $this->detectarDelimitador($raw)
            : $this->delimiter;

        $rows = [];
        foreach (explode("\n", str_replace("\r\n", "\n", $raw)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $rows[] = str_getcsv($line, $delimiter);
        }

        return $rows;
    }

    private function normalizeHeader(array $header): array
    {
        return array_map(
            fn($h) => strtolower(trim(str_replace([' ', '-'], '_', $h))),
            $header
        );
    }

    /**
     * Construye el mapa campo_dto => columna_csv.
     * Prioridad: mapeo explícito > auto-detect > vacío (se omitirá).
     */
    private function resolveMap(array $header, array $aliases, array $explicito): array
    {
        $map = [];

        foreach ($aliases as $campo => $candidatos) {
            // Mapeo explícito tiene prioridad
            if (isset($explicito[$campo])) {
                $map[$campo] = strtolower($explicito[$campo]);
                continue;
            }

            // Auto-detect: buscar primer alias que exista en el header
            foreach ($candidatos as $alias) {
                if (in_array($alias, $header, true)) {
                    $map[$campo] = $alias;
                    break;
                }
            }

            // Si no se encontró, dejar vacío (los campos obligatorios fallarán en validación)
            if (!isset($map[$campo])) {
                $map[$campo] = '';
            }
        }

        return $map;
    }

    private function normalizarDatetime(string $valor, string $fecha): ?string
    {
        // Ya es datetime completo: '2025-03-10 08:00:00' o '2025-03-10T08:00:00'
        if (preg_match('/\d{4}-\d{2}-\d{2}[\sT]\d{2}:\d{2}/', $valor)) {
            $ts = strtotime($valor);
            return $ts ? date('Y-m-d H:i:s', $ts) : null;
        }

        // Solo hora: '08:00' o '08:00:00' — combinar con $fecha
        if (preg_match('/^\d{1,2}:\d{2}/', $valor) && $fecha !== '') {
            $ts = strtotime($fecha . ' ' . $valor);
            return $ts ? date('Y-m-d H:i:s', $ts) : null;
        }

        return null;
    }

    private function detectarDelimitador(string $raw): string
    {
        $linea = strtok($raw, "\n");
        $counts = [
            ','  => substr_count($linea, ','),
            ';'  => substr_count($linea, ';'),
            "\t" => substr_count($linea, "\t"),
        ];
        arsort($counts);
        return array_key_first($counts);
    }

    private function rowVacia(array $row): bool
    {
        return count(array_filter($row, fn($v) => trim($v) !== '')) === 0;
    }

    private function nullIfEmpty(string $val): ?string
    {
        $v = trim($val);
        return $v !== '' ? $v : null;
    }
}
