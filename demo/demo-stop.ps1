# EventOS - apaga los servicios que abrio DEMO-START.bat / DEMO-START-PROD.bat.
# Los encuentra por puerto (Windows Terminal no expone titulo de ventana),
# mata el arbol completo desde el "cmd /k" raiz para que la pestana se cierre.
# Laragon (Apache/MySQL/Redis) se deja corriendo a proposito.
#
# Next 16 en modo DEV escribe .next\dev sin parar; un kill forzado lo deja a
# medias ("Unexpected end of JSON input" fantasma en SSR). Por eso, si la webapp
# estaba en dev, al final se borra .next\dev. Cuesta un arranque frio la
# proxima vez, pero nunca una cache rota. En modo PROD (next start) no aplica.

$services = @(
    @{ Name = 'Webapp Next'; Port = 3000 },
    @{ Name = 'Socket';      Port = 3001 },
    @{ Name = 'Kiosko Vite'; Port = 5173 },
    @{ Name = 'Expo';        Port = 8081 }
)
$webDevCache = 'C:\laragon\www\eventos-web\.next\dev'

function Get-ListenerPid([int]$port) {
    $c = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($c) { return [int]$c.OwningProcess }
    return $null
}

function Get-RootCmd([int]$pid_) {
    # sube por la cadena de padres hasta el "cmd /k" que abrio el START
    $cur = Get-CimInstance Win32_Process -Filter "ProcessId=$pid_" -ErrorAction SilentlyContinue
    $root = $null
    while ($cur) {
        if ($cur.Name -ieq 'cmd.exe') { $root = $cur }
        $cur = Get-CimInstance Win32_Process -Filter "ProcessId=$($cur.ParentProcessId)" -ErrorAction SilentlyContinue
        if ($cur -and $cur.Name -notin @('cmd.exe','node.exe','npm.cmd','pnpm.cmd','npx.cmd','conhost.exe')) { break }
    }
    if ($root) { return [int]$root.ProcessId }
    return $pid_
}

$webWasDev = $false

foreach ($s in $services) {
    $pid_ = Get-ListenerPid $s.Port
    if (-not $pid_) { Write-Host ("  {0,-12} :{1}  no estaba corriendo" -f $s.Name, $s.Port); continue }

    if ($s.Port -eq 3000) {
        # junta las lineas de comando del listener y sus padres: "next dev" vs "next start"
        $chain = @(); $cur = Get-CimInstance Win32_Process -Filter "ProcessId=$pid_" -ErrorAction SilentlyContinue
        $hops = 0
        while ($cur -and $hops -lt 5) {
            $chain += $cur.CommandLine
            $cur = Get-CimInstance Win32_Process -Filter "ProcessId=$($cur.ParentProcessId)" -ErrorAction SilentlyContinue
            $hops++
        }
        $joined = ($chain -join ' ')
        if ($joined -match '(\s|")dev(\s|"|$)' -and $joined -notmatch '(\s|")start(\s|"|$)') { $webWasDev = $true }
    }

    $root = Get-RootCmd $pid_
    Write-Host ("  {0,-12} :{1}  cerrando (pid {2})..." -f $s.Name, $s.Port, $pid_) -NoNewline
    & taskkill /F /T /PID $root 2>&1 | Out-Null
    $left = Get-ListenerPid $s.Port
    if ($left) { & taskkill /F /T /PID $left 2>&1 | Out-Null }
    Write-Host " cerrado"
}

if ($webWasDev -and (Test-Path $webDevCache)) {
    Write-Host "  Webapp estaba en DEV: limpiando .next\dev para que no quede cache rota..." -NoNewline
    Start-Sleep -Seconds 2
    Remove-Item -Recurse -Force $webDevCache -ErrorAction SilentlyContinue
    Write-Host " ok"
}

Write-Host ""
Write-Host "  Listo. Laragon sigue arriba (Apache + MySQL + Redis)."
