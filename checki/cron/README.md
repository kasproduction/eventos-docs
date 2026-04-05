# Cron Jobs — Sistema Checkin/Checkout

## Scripts disponibles

### `fin_jornada.php`
Ejecutar al terminar cada día del evento.
- Auto-checkout de todos los que siguen "dentro"
- Recalcula asistencia completa del día

```bash
php cron/fin_jornada.php --evento=1
php cron/fin_jornada.php --evento=1 --dia=3   # solo día específico
```

### `calcular_asistencia.php`
Recalcula asistencia. Útil después de cambios de agenda.

```bash
php cron/calcular_asistencia.php --evento=1
php cron/calcular_asistencia.php --evento=1 --dia=2
php cron/calcular_asistencia.php --evento=1 --solo-si-requerido
```

---

### `backup_bd.php`
Genera un volcado comprimido (`.sql.gz`) de la BD y lo guarda localmente.
Si hay un USB montado en `/media/usb/checkin_backups`, copia ahí también.
Rota automáticamente: conserva los últimos 48 backups (48h a razón de 1/hora).

```bash
php cron/backup_bd.php                         # backup a /backups/ del proyecto
php cron/backup_bd.php --dir=/mis/backups      # directorio personalizado
```

**Ajustes en el script** (primeras líneas de configuración):
- `$BACKUP_DIR` → ruta local donde guardar (default: `<proyecto>/backups`)
- `$USB_DIR`    → punto de montaje del USB en Linux
- `$RETENER`    → cuántos backups conservar (default: 48)

---

## Configuración de cron (Linux)

```cron
# Backup cada hora
0 * * * * php /var/www/html/cron/backup_bd.php >> /var/log/checkin_backup.log 2>&1

# Fin de jornada a las 23:00 todos los días
0 23 * * * php /var/www/html/cron/fin_jornada.php --evento=1 >> /var/log/checkin_fin_jornada.log 2>&1

# Detectar cambios de agenda cada 5 minutos y recalcular si es necesario
*/5 * * * * php /var/www/html/cron/calcular_asistencia.php --evento=1 --solo-si-requerido >> /var/log/checkin_calculo.log 2>&1
```

## Ejecutar manualmente desde el panel admin

Panel → Reportes → tab "Recalcular" → botón "Recalcular ahora"
