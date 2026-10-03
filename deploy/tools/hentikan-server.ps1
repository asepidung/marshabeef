$root = Split-Path -Parent $PSScriptRoot
$phpExe = [IO.Path]::GetFullPath((Join-Path $root 'php\php.exe'))

# Hanya proses php.exe milik paket ini yang dihentikan.
$procs = Get-CimInstance Win32_Process -Filter "Name='php.exe'" |
    Where-Object { $_.ExecutablePath -and ($_.ExecutablePath -ieq $phpExe) }

if (-not $procs) {
    Write-Host 'Server tidak sedang berjalan.'
    exit 0
}

$procs | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }
Write-Host 'Server dihentikan.'
