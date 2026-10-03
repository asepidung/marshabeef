param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('Install', 'Remove')]
    [string]$Action
)

$root = Split-Path -Parent $PSScriptRoot

# Lokasi bisa diganti lewat variabel lingkungan (dipakai saat pengujian).
$startupDir = if ($env:MARSHA_STARTUP_DIR) { $env:MARSHA_STARTUP_DIR } else { Join-Path $env:APPDATA 'Microsoft\Windows\Start Menu\Programs\Startup' }
$desktopDir = if ($env:MARSHA_DESKTOP_DIR) { $env:MARSHA_DESKTOP_DIR } else { [Environment]::GetFolderPath('Desktop') }

$serverLink = [IO.Path]::Combine($startupDir, 'Marsha Beef Server.lnk')
$desktopLink = [IO.Path]::Combine($desktopDir, 'Marsha Beef.lnk')

if ($Action -eq 'Remove') {
    foreach ($link in @($serverLink, $desktopLink)) {
        if (Test-Path -LiteralPath $link) { [IO.File]::Delete($link) }
    }
    Write-Host 'AutoStart dimatikan. Server tidak lagi menyala otomatis saat laptop dinyalakan.'
    exit 0
}

$shell = New-Object -ComObject WScript.Shell
$target = Join-Path $root 'Mulai.bat'
$icon = Join-Path $PSScriptRoot 'marsha.ico'

# Membuat shortcut lalu memastikan filenya benar-benar ada. Mengembalikan $null jika berhasil, atau pesan galat.
function New-Link([string]$path, [string]$arguments) {
    try {
        $dir = Split-Path -Parent $path
        if (-not (Test-Path -LiteralPath $dir)) { New-Item -ItemType Directory -Path $dir -Force -ErrorAction Stop | Out-Null }

        $link = $shell.CreateShortcut($path)
        $link.TargetPath = $target
        $link.Arguments = $arguments
        $link.WorkingDirectory = $root
        $link.WindowStyle = 7
        if (Test-Path -LiteralPath $icon) { $link.IconLocation = $icon }
        $link.Save()

        if (-not (Test-Path -LiteralPath $path)) { return 'file shortcut tidak terbentuk' }
        return $null
    } catch {
        return $_.Exception.Message
    }
}

$serverError = New-Link $serverLink '/server'
$desktopError = New-Link $desktopLink ''

Write-Host ''
if ($serverError) {
    Write-Host 'GAGAL mengaktifkan AutoStart:' -ForegroundColor Red
    Write-Host "  $serverError"
    Write-Host "  Cara manual: tekan tombol Windows + R, ketik  shell:startup  lalu Enter,"
    Write-Host '  kemudian salin shortcut Mulai.bat ke folder yang terbuka.'
} else {
    Write-Host 'AutoStart AKTIF. Server menyala otomatis saat laptop dinyalakan dan masuk ke Windows.'
    Write-Host "  Lokasi: $serverLink"
}

Write-Host ''
if ($desktopError) {
    Write-Host 'GAGAL membuat ikon di Desktop:' -ForegroundColor Red
    Write-Host "  $desktopError"
    Write-Host '  Cara manual: klik kanan file Mulai.bat > Send to > Desktop (create shortcut).'
} else {
    Write-Host 'Ikon "Marsha Beef" dibuat di Desktop.'
    Write-Host "  Lokasi: $desktopLink"
}
