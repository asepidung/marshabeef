@echo off
cd /d "%~dp0"
set "PHP=%~dp0php\php.exe"
if not exist "%PHP%" set "PHP=php"

echo RESET PIN DARURAT (untuk pemilik)
echo.
echo Gunakan ini HANYA jika PIN lupa dan tidak bisa masuk ke aplikasi.
echo PIN akan dikembalikan ke PIN awal (yang tertulis di file .env pada baris APP_PIN).
echo Setelah masuk, segera ganti PIN lewat menu "Ganti PIN" di aplikasi.
echo.
choice /c YN /m "Lanjutkan"
if errorlevel 2 exit /b 0

rem Jaring pengaman OPcache (lihat php\php.ini).
set "MARSHA_OPCACHE_DIR=%~dp0storage\framework\opcache"
if not exist "%MARSHA_OPCACHE_DIR%" mkdir "%MARSHA_OPCACHE_DIR%"

rem Buang cache config supaya APP_PIN terbaru di .env yang dipakai.
"%PHP%" artisan config:clear >nul 2>&1
"%PHP%" artisan app:reset-pin
echo.
pause
