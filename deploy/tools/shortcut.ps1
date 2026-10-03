param(
    [Parameter(Mandatory = $true)]
    [ValidateSet('Install', 'Remove')]
    [string]$Action
)

$root = Split-Path -Parent $PSScriptRoot

# Lokasi bisa diganti lewat variabel lingkungan (dipakai saat pengujian).
$startupDir = if ($env:MARSHA_STARTUP_DIR) { $env:MARSHA_STARTUP_DIR } else { Join-Path $env:APPDATA 'Microsoft\Windows\Start Menu\Programs\Startup' }
$desktopDir = if ($env:MARSHA_DESKTOP_DIR) { $env:MARSHA_DESKTOP_DIR } else { [Environment]::GetFolderPath('Desktop') }

$serverLink = Join-Path $startupDir 'Marsha Beef Server.lnk'
$desktopLink = Join-Path $desktopDir 'Marsha Beef.lnk'

if ($Action -eq 'Remove') {
    foreach ($link in @($serverLink, $desktopLink)) {
        if (Test-Path $link) { Remove-Item -LiteralPath $link -Force }
    }
    Write-Host 'AutoStart dimatikan. Server tidak lagi menyala otomatis saat laptop dinyalakan.'
    exit 0
}

New-Item -ItemType Directory -Path $startupDir -Force | Out-Null
$shell = New-Object -ComObject WScript.Shell
$target = Join-Path $root 'Mulai.bat'
$icon = Join-Path $PSScriptRoot 'marsha.ico'

# 1. Menyalakan server otomatis saat pengguna login ke Windows (tanpa membuka browser).
$link = $shell.CreateShortcut($serverLink)
$link.TargetPath = $target
$link.Arguments = '/server'
$link.WorkingDirectory = $root
$link.WindowStyle = 7
$link.IconLocation = $icon
$link.Save()

# 2. Ikon di Desktop untuk membuka aplikasi.
if (Test-Path $desktopDir) {
    $link = $shell.CreateShortcut($desktopLink)
    $link.TargetPath = $target
    $link.WorkingDirectory = $root
    $link.WindowStyle = 7
    $link.IconLocation = $icon
    $link.Save()
}

Write-Host 'AutoStart aktif.'
Write-Host ' - Server akan menyala otomatis setiap laptop dinyalakan dan pengguna masuk ke Windows.'
Write-Host ' - Ikon "Marsha Beef" sudah dibuat di Desktop untuk membuka aplikasi.'
