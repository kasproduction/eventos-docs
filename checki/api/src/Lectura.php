<?php

// ─── Parsear input ────────────────────────────────────────
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['uid_qr']) || empty($input['totem_id'])) {
    Response::error(400, 'uid_qr y totem_id son requeridos');
}

$uid_qr          = trim((string) $input['uid_qr']);
$totem_id        = (int) $input['totem_id'];
$timestamp_totem = $input['timestamp_totem'] ?? null;

$db = DB::get();

// $now ya no se usa en SQL — se usa NOW(3) de MySQL directamente
// para evitar desfase de zona horaria entre PHP y MySQL.

try {

    // ──────────────────────────────────────────────────────
    // PASO 01 — Identificar asistente por uid_qr  →  O(1)
    // ──────────────────────────────────────────────────────
    $stmt = $db->prepare("
        SELECT id, nombre, evento_id
        FROM   asistentes
        WHERE  uid_qr = ?
          AND  activo = 1
        LIMIT 1
    ");
    $stmt->execute([$uid_qr]);
    $asistente = $stmt->fetch();

    if (!$asistente) {
        // QR desconocido — loguear sin exponer info
        Response::error(200, 'QR no válido');
    }

    $asistente_id = (int) $asistente['id'];
    $evento_id    = (int) $asistente['evento_id'];

    // ──────────────────────────────────────────────────────
    // PASO 02 — Identificar salón por totem_id  →  índice
    // ──────────────────────────────────────────────────────
    $stmt = $db->prepare("
        SELECT id, salon_id
        FROM   totems
        WHERE  id     = ?
          AND  activo = 1
        LIMIT 1
    ");
    $stmt->execute([$totem_id]);
    $totem = $stmt->fetch();

    if (!$totem) {
        Response::error(400, 'Tótem no registrado');
    }

    $salon_id = (int) $totem['salon_id'];

    // ──────────────────────────────────────────────────────
    // PASO 03 — Estado actual + determinar tipo
    //  (se hace ANTES del debounce para poder filtrar por tipo)
    // ──────────────────────────────────────────────────────
    $stmt = $db->prepare("
        SELECT salon_id, estado, ultimo_movimiento_id
        FROM   estado_asistentes
        WHERE  asistente_id = ?
    ");
    $stmt->execute([$asistente_id]);
    $estados = $stmt->fetchAll();

    $estado_en_este_salon        = 'fuera';
    $salon_donde_esta_dentro     = null;
    $ultimo_mov_id_salon_anterior = null;

    foreach ($estados as $e) {
        if ((int)$e['salon_id'] === $salon_id) {
            $estado_en_este_salon = $e['estado'];
        } elseif ($e['estado'] === 'dentro') {
            $salon_donde_esta_dentro      = (int)$e['salon_id'];
            $ultimo_mov_id_salon_anterior = (int)$e['ultimo_movimiento_id'];
        }
    }

    $tipo = ($estado_en_este_salon === 'fuera') ? 'checkin' : 'checkout';

    // ──────────────────────────────────────────────────────
    // PASO 04 — Debounce: mismo UID+salón+tipo en <N segundos
    //  Solo bloquea si el MISMO tipo se repite rápido
    //  (doble-scan accidental del lector HID).
    //  Permite checkin → checkout inmediato sin bloqueo.
    // ──────────────────────────────────────────────────────
    $stmt = $db->prepare("
        SELECT id
        FROM   movimientos
        WHERE  asistente_id = ?
          AND  salon_id     = ?
          AND  tipo         = ?
          AND  timestamp    >= NOW(3) - INTERVAL " . (int)DEBOUNCE_SEGUNDOS . " SECOND
        LIMIT 1
    ");
    $stmt->execute([$asistente_id, $salon_id, $tipo]);

    if ($stmt->fetch()) {
        // Ignorar silenciosamente — tótem ya tiene su feedback del scan anterior
        exit;
    }

    // ──────────────────────────────────────────────────────
    // Abrir transacción — todo o nada
    // Usamos GET_LOCK para evitar race condition si el mismo
    // QR llega a dos tótems en el mismo instante (escenario 11)
    // ──────────────────────────────────────────────────────
    $lock_key = "scan_{$asistente_id}";
    $db->query("SELECT GET_LOCK('{$lock_key}', 2)");

    $db->beginTransaction();

    try {

        // ──────────────────────────────────────────────────
        // PASO 06 — Auto-checkout si está dentro en otro salón
        // ──────────────────────────────────────────────────
        if ($tipo === 'checkin' && $salon_donde_esta_dentro !== null) {

            // Recuperar el totem_id real del salón anterior
            $stmt_prev = $db->prepare("SELECT totem_id FROM movimientos WHERE id = ? LIMIT 1");
            $stmt_prev->execute([$ultimo_mov_id_salon_anterior]);
            $prev_mov = $stmt_prev->fetch();
            $totem_id_anterior = $prev_mov ? (int)$prev_mov['totem_id'] : $totem_id;

            $stmt = $db->prepare("
                INSERT INTO movimientos
                    (evento_id, asistente_id, totem_id, salon_id,
                     tipo,       timestamp,   metodo,              flags)
                VALUES
                    (?,          ?,            ?,        ?,
                     'checkout', NOW(3),       'auto_cambio_salon', 'cambio_salon')
            ");
            $stmt->execute([
                $evento_id, $asistente_id, $totem_id_anterior,
                $salon_donde_esta_dentro,
            ]);
            $auto_mov_id = (int) $db->lastInsertId();

            $stmt = $db->prepare("
                UPDATE estado_asistentes
                SET    estado               = 'fuera',
                       ultimo_movimiento_id = ?,
                       updated_at          = NOW(3)
                WHERE  asistente_id = ?
                  AND  salon_id     = ?
            ");
            $stmt->execute([$auto_mov_id, $asistente_id, $salon_donde_esta_dentro]);
        }

        // ──────────────────────────────────────────────────
        // PASO 07 — Charla activa en este salón ahora
        // ──────────────────────────────────────────────────
        $stmt = $db->prepare("
            SELECT c.id, c.titulo
            FROM   charlas c
            JOIN   dias_evento d ON d.id = c.dia_evento_id
            WHERE  c.salon_id   = ?
              AND  c.cancelada  = 0
              AND  c.hora_inicio <= NOW()
              AND  c.hora_fin   >= NOW()
            LIMIT 1
        ");
        $stmt->execute([$salon_id]);
        $charla = $stmt->fetch();

        $charla_id     = $charla ? (int)$charla['id']    : null;
        $charla_titulo = $charla ? $charla['titulo']      : null;

        // Flags
        $flags = [];
        if (!$charla_id) {
            $flags[] = 'fuera_horario';
        }
        $flags_str = $flags ? implode(',', $flags) : null;

        // ──────────────────────────────────────────────────
        // PASO 08 — INSERT en movimientos
        // ──────────────────────────────────────────────────
        $stmt = $db->prepare("
            INSERT INTO movimientos
                (evento_id, asistente_id, totem_id, salon_id,
                 tipo, timestamp, timestamp_totem, metodo, flags)
            VALUES
                (?, ?, ?, ?,
                 ?, NOW(3), ?, 'qr_lector', ?)
        ");
        $stmt->execute([
            $evento_id, $asistente_id, $totem_id, $salon_id,
            $tipo, $timestamp_totem, $flags_str,
        ]);
        $movimiento_id = (int) $db->lastInsertId();

        // ──────────────────────────────────────────────────
        // PASO 09 — UPSERT en estado_asistentes
        // ──────────────────────────────────────────────────
        $nuevo_estado = ($tipo === 'checkin') ? 'dentro' : 'fuera';

        $stmt = $db->prepare("
            INSERT INTO estado_asistentes
                (asistente_id, salon_id, estado, ultimo_movimiento_id, updated_at)
            VALUES
                (?, ?, ?, ?, NOW(3))
            ON DUPLICATE KEY UPDATE
                estado               = VALUES(estado),
                ultimo_movimiento_id = VALUES(ultimo_movimiento_id),
                updated_at          = NOW(3)
        ");
        $stmt->execute([$asistente_id, $salon_id, $nuevo_estado, $movimiento_id]);

        $db->commit();

    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    } finally {
        $db->query("SELECT RELEASE_LOCK('{$lock_key}')");
    }

    // ──────────────────────────────────────────────────────
    // PASO 10 — Responder al tótem
    // ──────────────────────────────────────────────────────
    if ($tipo === 'checkin') {

        Response::json([
            'tipo'    => 'checkin',
            'nombre'  => $asistente['nombre'],
            'charla'  => $charla_titulo,
            'color'   => 'verde',
            'mensaje' => 'Bienvenido',
        ]);

    } else {

        // Calcular minutos que estuvo en sala (para mostrar en pantalla)
        $stmt = $db->prepare("
            SELECT TIMESTAMPDIFF(MINUTE, timestamp, NOW()) AS minutos
            FROM   movimientos
            WHERE  asistente_id = ?
              AND  salon_id     = ?
              AND  tipo         = 'checkin'
            ORDER  BY timestamp DESC
            LIMIT 1
        ");
        $stmt->execute([$asistente_id, $salon_id]);
        $row     = $stmt->fetch();
        $minutos = $row ? (int)$row['minutos'] : 0;

        Response::json([
            'tipo'    => 'checkout',
            'nombre'  => $asistente['nombre'],
            'minutos' => $minutos,
            'color'   => 'verde',
            'mensaje' => 'Hasta luego',
        ]);
    }

} catch (Throwable $e) {
    // No exponer detalles al tótem
    error_log('[Lectura] ' . $e->getMessage() . ' | uid=' . $uid_qr . ' totem=' . $totem_id);
    Response::error(500, 'Error interno del servidor');
}
