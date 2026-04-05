<?php

/**
 * POST /admin/api/importar-preview
 *
 * Parsea el archivo sin insertar nada.
 * Devuelve una preview para que el operador confirme antes de importar.
 *
 * Response:
 * {
 *   tipo, total, muestra: [...primeros 5 DTOs],
 *   columnas_detectadas: { uid_qr: "qr_code", nombre: "full_name", ... },
 *   errores_validacion: ["fila 3: uid_qr vacío", ...],
 *   advertencias: ["X filas omitidas", ...]
 * }
 */

$tipo   = $_POST['tipo']   ?? '';
$fuente = $_POST['fuente'] ?? 'csv';

if (!in_array($tipo, ['asistentes', 'agenda'], true)) {
    Response::error(400, 'tipo debe ser "asistentes" o "agenda"');
}

// Leer contenido
$raw = '';
if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
    $raw = file_get_contents($_FILES['archivo']['tmp_name']);
} else {
    $raw = file_get_contents('php://input');
}

if (empty($raw)) {
    Response::error(400, 'No se recibió contenido');
}

// Parsear con el adaptador
$adapter = match ($fuente) {
    'json'  => new JsonAdapter(),
    default => new CsvAdapter(delimiter: 'auto'),
};

if ($tipo === 'asistentes') {
    $dtos = $adapter->parseAsistentes($raw);
} else {
    $dtos = $adapter->parseAgenda($raw);
}

// Validar y construir preview
$errores     = [];
$advertencias = [];
$validos     = 0;
$muestra     = [];

foreach ($dtos as $i => $dto) {
    if (!$dto->esValido()) {
        $errores[] = "Fila " . ($i + 2) . ": datos insuficientes (uid_qr o nombre vacío)";
        continue;
    }
    $validos++;
    if (count($muestra) < 5) {
        $muestra[] = (array)$dto;
    }
}

$omitidos = count($dtos) - $validos;
if ($omitidos > 0) {
    $advertencias[] = "$omitidos filas omitidas por datos inválidos";
}

// Columnas detectadas (solo para CSV)
$columnas_detectadas = null;
if ($fuente === 'csv' && !empty($raw)) {
    $columnas_detectadas = detectarColumnasCSV($raw, $tipo);
}

// Verificar duplicados en el lote (mismo uid_qr más de una vez)
if ($tipo === 'asistentes') {
    $uids = array_map(fn($d) => $d->uid_qr, $dtos);
    $duplicados = array_filter(array_count_values($uids), fn($c) => $c > 1);
    if (!empty($duplicados)) {
        foreach (array_keys($duplicados) as $uid) {
            $advertencias[] = "UID duplicado en el archivo: $uid (se usará la última ocurrencia)";
        }
    }
}

Response::json([
    'tipo'                => $tipo,
    'total_parseados'     => count($dtos),
    'total_validos'       => $validos,
    'muestra'             => $muestra,
    'columnas_detectadas' => $columnas_detectadas,
    'errores_validacion'  => $errores,
    'advertencias'        => $advertencias,
    'listo_para_importar' => empty($errores) && $validos > 0,
]);

// ─── Helper ───────────────────────────────────────────────
function detectarColumnasCSV(string $raw, string $tipo): array
{
    $raw    = ltrim($raw, "\xEF\xBB\xBF");
    $linea  = strtok(str_replace("\r\n", "\n", $raw), "\n");
    $counts = [','  => substr_count($linea, ','), ';' => substr_count($linea, ';'), "\t" => substr_count($linea, "\t")];
    arsort($counts);
    $del    = array_key_first($counts);
    $header = array_map(
        fn($h) => strtolower(trim(str_replace([' ', '-'], '_', $h))),
        str_getcsv($linea, $del)
    );

    $aliasMap = $tipo === 'asistentes'
        ? ['uid_qr' => ['uid_qr','qr','qr_code','codigo_qr','badge','qrdata','ticket'],
           'nombre' => ['nombre','name','full_name','nombre_completo'],
           'email'  => ['email','correo','mail'],
           'empresa'=> ['empresa','company','organizacion'],
           'external_id' => ['external_id','id','ref','registro']]
        : ['titulo'      => ['titulo','title','charla','sesion'],
           'salon_nombre'=> ['salon','sala','room','venue'],
           'hora_inicio' => ['hora_inicio','inicio','start','start_time'],
           'hora_fin'    => ['hora_fin','fin','end','end_time'],
           'ponente'     => ['ponente','speaker','expositor'],
           'fecha'       => ['fecha','date','dia']];

    $resultado = [];
    foreach ($aliasMap as $campo => $candidatos) {
        foreach ($candidatos as $alias) {
            if (in_array($alias, $header, true)) {
                $resultado[$campo] = $alias;
                break;
            }
        }
        if (!isset($resultado[$campo])) {
            $resultado[$campo] = null; // no detectado
        }
    }
    return $resultado;
}
