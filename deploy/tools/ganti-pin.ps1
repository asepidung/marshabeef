$root = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $root '.env'

if (-not (Test-Path $envFile)) {
    Write-Host 'File .env tidak ditemukan. PIN tidak diubah.'
    exit 1
}

function Read-Pin([string]$prompt) {
    $secure = Read-Host $prompt -AsSecureString
    $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
    try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr) }
    finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr) }
}

Write-Host 'GANTI PIN MARSHA BEEF'
Write-Host ''

# MARSHA_NEW_PIN hanya untuk pengujian otomatis; pemakaian normal bertanya lewat layar.
if ($env:MARSHA_NEW_PIN) {
    $pin = $env:MARSHA_NEW_PIN
    $confirm = $pin
} else {
    $pin = Read-Pin 'Masukkan PIN baru (4 sampai 12 angka)'
    $confirm = $null
}

if ($pin -notmatch '^\d{4,12}$') {
    Write-Host 'PIN harus berupa 4 sampai 12 angka. PIN tidak diubah.'
    exit 1
}

if ($null -eq $confirm) { $confirm = Read-Pin 'Ulangi PIN baru' }

if ($confirm -ne $pin) {
    Write-Host 'Kedua PIN tidak sama. PIN tidak diubah.'
    exit 1
}

$text = [IO.File]::ReadAllText($envFile)

if ($text -match '(?m)^APP_PIN=[^\r\n]*') {
    $text = [regex]::Replace($text, '(?m)^APP_PIN=[^\r\n]*', "APP_PIN=$pin")
} else {
    $text = $text.TrimEnd() + "`r`nAPP_PIN=$pin`r`n"
}

[IO.File]::WriteAllText($envFile, $text, (New-Object Text.UTF8Encoding($false)))

Write-Host ''
Write-Host 'PIN berhasil diganti. PIN baru langsung berlaku.'
