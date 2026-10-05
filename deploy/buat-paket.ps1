<#
  Membuat paket siap pakai (proyek + PHP portabel + skrip) yang tinggal disalin ke laptop lain.

  Contoh:
    powershell -ExecutionPolicy Bypass -File deploy\buat-paket.ps1
    powershell -ExecutionPolicy Bypass -File deploy\buat-paket.ps1 -Output E:\MarshaBeef -Pin 482913

  Hasilnya berisi database KOSONG (hanya struktur tabel), .env production dengan APP_KEY baru,
  dan tidak menyertakan Docs/, .git, node_modules, tes, maupun data dari PC ini.
#>
param(
    [string]$Output = 'D:\WebApps\MarshaBeef-Paket',
    [string]$Php = 'D:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64',
    [string]$Pin = '5585'
)

$ErrorActionPreference = 'Stop'
$repo = Split-Path -Parent $PSScriptRoot
$marker = Join-Path $Output '.marsha-paket'

function Invoke-Native([string]$What, [scriptblock]$Block) {
    & $Block
    if ($LASTEXITCODE -ne 0) { throw "Gagal: $What (kode $LASTEXITCODE)" }
}

function Copy-Tree([string]$From, [string]$To, [string[]]$ExcludeDirs = @(), [string[]]$ExcludeFiles = @()) {
    $args = @($From, $To, '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/NP')
    if ($ExcludeDirs.Count)  { $args += '/XD'; $args += $ExcludeDirs }
    if ($ExcludeFiles.Count) { $args += '/XF'; $args += $ExcludeFiles }
    & robocopy @args | Out-Null
    if ($LASTEXITCODE -ge 8) { throw "robocopy gagal menyalin $From (kode $LASTEXITCODE)" }
    $global:LASTEXITCODE = 0
}

if (-not (Test-Path (Join-Path $Php 'php.exe'))) { throw "php.exe tidak ditemukan di $Php" }
if ($Pin -notmatch '^\d{4,12}$') { throw 'PIN harus berupa 4 sampai 12 angka.' }

if (Test-Path $Output) {
    if (-not (Test-Path $marker)) {
        throw "Folder $Output sudah ada dan bukan hasil skrip ini. Hapus manual atau pilih folder lain (-Output)."
    }
    Remove-Item -LiteralPath $Output -Recurse -Force
}

New-Item -ItemType Directory -Path $Output | Out-Null
Set-Content -Path $marker -Value 'Dibuat oleh deploy\buat-paket.ps1' -Encoding ascii

Write-Host '[1/6] Menyalin proyek...'
Copy-Tree $repo $Output `
    -ExcludeDirs @(
        "$repo\.git", "$repo\node_modules", "$repo\Docs", "$repo\tests", "$repo\deploy",
        "$repo\.idea", "$repo\.vscode", "$repo\.cursor",
        "$repo\storage\logs", "$repo\storage\app\backups",
        "$repo\storage\framework\views", "$repo\storage\framework\cache", "$repo\storage\framework\sessions",
        "$repo\bootstrap\cache"
    ) `
    -ExcludeFiles @('.env', '*.sqlite', '*.sqlite-wal', '*.sqlite-shm', '*.log', '.phpunit.result.cache', 'phpunit.xml', 'package.json', 'package-lock.json', 'vite.config.js')

foreach ($dir in @('storage\logs', 'storage\app\backups', 'storage\app\public', 'storage\framework\views', 'storage\framework\cache\data', 'storage\framework\sessions', 'bootstrap\cache')) {
    New-Item -ItemType Directory -Path (Join-Path $Output $dir) -Force | Out-Null
}

Write-Host '[2/6] Membuang paket pengembangan dari vendor (composer install --no-dev)...'
Push-Location $Output
try {
    Invoke-Native 'composer install --no-dev' { composer install --no-dev --optimize-autoloader --no-interaction --quiet }
} finally { Pop-Location }

Write-Host '[3/6] Menyalin PHP portabel...'
$phpOut = Join-Path $Output 'php'
Copy-Tree $Php $phpOut `
    -ExcludeDirs @("$Php\dev", "$Php\extras") `
    -ExcludeFiles @('php.ini*', '*.pdb', '*.md', 'deplister.exe', 'php-cgi.exe', 'phpdbg.exe', 'php-win.exe', 'phpdbg.exe')

# Pustaka runtime Visual C++ disertakan agar laptop target tidak perlu memasangnya.
foreach ($dll in @('vcruntime140.dll', 'vcruntime140_1.dll', 'msvcp140.dll')) {
    $source = Join-Path $env:SystemRoot "System32\$dll"
    if ((Test-Path $source) -and -not (Test-Path (Join-Path $phpOut $dll))) { Copy-Item $source $phpOut }
}

@'
; php.ini minimal untuk Marsha Beef (dibuat otomatis oleh buat-paket.ps1)
extension_dir = "ext"
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3

date.timezone = Asia/Jakarta
memory_limit = 256M

; Kecepatan: tanpa OPcache setiap halaman mengompilasi ulang ratusan file PHP.
; Server dijalankan lewat "php artisan serve" (CLI), jadi enable_cli wajib menyala.
zend_extension = opcache
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 1
opcache.revalidate_freq = 2
; Windows lambat memeriksa jalur file; cache ini mengurangi akses disk per request.
realpath_cache_size = 4096K
realpath_cache_ttl = 600
display_errors = Off
log_errors = On
'@ | Set-Content -Path (Join-Path $phpOut 'php.ini') -Encoding ascii

$phpExe = Join-Path $phpOut 'php.exe'

Write-Host '[4/6] Menyiapkan .env production dan database kosong...'
$envText = [IO.File]::ReadAllText((Join-Path $repo '.env.example'))
$envText = [regex]::Replace($envText, '(?m)^APP_PIN=[^\r\n]*', "APP_PIN=$Pin")
$envText = [regex]::Replace($envText, '(?m)^APP_URL=[^\r\n]*', 'APP_URL=http://127.0.0.1:8000')
[IO.File]::WriteAllText((Join-Path $Output '.env'), $envText, (New-Object Text.UTF8Encoding($false)))

Push-Location $Output
try {
    Invoke-Native 'key:generate' { & $phpExe artisan key:generate --force --no-interaction | Out-Null }
    Invoke-Native 'migrate'      { & $phpExe artisan migrate --force --no-interaction | Out-Null }
    Invoke-Native 'optimize:clear' { & $phpExe artisan optimize:clear --no-interaction | Out-Null }
} finally { Pop-Location }

Write-Host '[5/6] Menyalin skrip pengelola...'
Copy-Item (Join-Path $PSScriptRoot '*.bat') $Output
Copy-Item (Join-Path $PSScriptRoot 'BACA-DULU.txt') $Output
Copy-Item (Join-Path $PSScriptRoot 'tools') (Join-Path $Output 'tools') -Recurse

Write-Host '[6/6] Selesai.'
$sizeMb = [math]::Round(((Get-ChildItem $Output -Recurse -File -Force | Measure-Object Length -Sum).Sum / 1MB), 0)
Write-Host ''
Write-Host "Paket siap di: $Output  (sekitar $sizeMb MB)"
Write-Host 'Salin SELURUH folder itu ke flashdisk, lalu ke laptop tujuan. Baca BACA-DULU.txt di dalamnya.'
