# Marsha Beef – Aplikasi Cetak Label

Panduan untuk agent AI yang mengerjakan repo ini. Instalasi dan operasional ada di `README.md`.

## Ringkasan

Laravel 13 + SQLite untuk mencetak label produk daging (100×75 mm, barcode CODE128) di PT Berkah Marsha
Sejahtera. Dipasang **offline di satu PC pabrik** dulu, harus mudah dinaikkan online nanti. UI berbahasa
Indonesia. Aplikasinya sengaja sederhana: jangan menambah framework atau paket besar tanpa persetujuan
pemilik. Filament, Livewire, dan Laravel Boost sudah diputuskan **tidak dipakai**.

## Perintah

- Tes: `php artisan test` (harus hijau sebelum commit)
- Format: `vendor/bin/pint --dirty`
- Aset: `npm run build`. Folder `public/build` **ikut di-commit** karena PC pabrik tidak memakai Node.
  Wajib build ulang setiap mengubah class Tailwind, CSS, atau JS.
- Jalankan: `php artisan serve`
- Backup database: `php artisan app:backup-db`
- Paket untuk laptop pabrik: `powershell -ExecutionPolicy Bypass -File deploy\buat-paket.ps1`. Skrip
  operator (`*.bat`, `tools\`, `BACA-DULU.txt`) ada di `deploy/` dan hanya boleh berisi karakter ASCII.
  Uji paket dari folder lain dengan PATH tanpa PHP Laragon, dan jangan menyentuh folder Startup asli
  (pakai `MARSHA_STARTUP_DIR` / `MARSHA_DESKTOP_DIR`).
- Server paket dijalankan `php -S` langsung oleh `deploy/tools/jalankan-server.vbs` (folder kerja HARUS
  `public`, router `server.php` mencari `index.php` di folder kerja). Jangan kembali ke `artisan serve`
  (dua proses, lebih lambat). Buka aplikasi selalu lewat `127.0.0.1`, jangan `localhost` (lambat 0,3-2 detik).
- OPcache di paket: dua salinan `php.exe` berbeda bersamaan memicu galat fatal ASLR. Semua pemanggil `php`
  paket (`Mulai.bat`, vbs, `Reset-PIN-Darurat.bat`, `buat-paket.ps1`) harus mengisi `MARSHA_OPCACHE_DIR`
  (dipakai `opcache.file_cache` + `file_cache_fallback` di `php.ini`). Saat mengukur performa, pastikan
  status HTTP tiap request 200 (galat 500 terlihat "cepat") dan tidak ada PHP lain yang sedang jalan.
- Zona waktu aplikasi `Asia/Jakarta` (`APP_TIMEZONE`); jangan kembalikan ke UTC (jam riwayat dan
  tanggal produksi bawaan jadi salah).

## Arsitektur

- `routes/web.php`: `/` adalah dashboard (video + form PIN, `PinController@show`), dan semua rute lain
  di balik middleware `pin` (`RequirePin`, PIN dari `APP_PIN`; kosong di production = aplikasi terkunci).
  PIN benar selalu menuju `/labels/create`. Kunci otomatis setelah `APP_IDLE_MINUTES` tanpa aktivitas:
  server memeriksa lewat `PinSession`, browser lewat `resources/js/idle-lock.js` (`/keepalive`, `/lock`).
- PIN dikelola `App\Support\AccessPin`: hash di tabel `settings` (diganti lewat halaman `/pin`,
  `PinSettingsController`) mengalahkan `APP_PIN` di `.env` (PIN awal). Jangan membaca `config('access.pin')`
  langsung untuk keputusan akses; pakai `AccessPin::isConfigured()` / `verify()`. `app:reset-pin` untuk darurat.
- Nama barang dan nama suhu dipaksa huruf besar: `data-uppercase` di input (`resources/js/uppercase.js`)
  dan `mb_strtoupper` di controller **sebelum** validasi unique.
- `phpunit.xml` mengosongkan `APP_PIN` agar tes tidak terpengaruh isi `.env` lokal.
- `LabelController::store` memakai `App\Support\LabelBarcode` (parsing berat dan pembentukan barcode).
  Barcode **23 digit tetap** (format di README). Jangan mengubah format tanpa memperbarui tes dan README.
- Nomor urut reset per `production_date`, dihitung dengan `withTrashed()` di dalam `DB::transaction`,
  dengan percobaan ulang saat tabrakan unique. SQLite memakai transaksi `IMMEDIATE` dan WAL.
- `layouts/app.blade.php` berisi toast global untuk `session('success')` dan `session('success_del')`.
  Jangan membuat banner sukses sendiri di tiap halaman (pernah menimbulkan notif ganda).
- `labels/print.blade.php`: CSS khusus 100×75 mm; barcode dirender `resources/js/print.js` lalu
  otomatis `window.print()`.
- `public/sw.js`: tidak boleh meng-cache halaman HTML (request navigate) maupun request Range. Naikkan
  `CACHE_NAME` saat aset PWA berubah.

## Aturan

- **Offline-first**: dilarang memuat aset dari CDN atau URL eksternal. Tanpa internet barcode tidak
  tercetak. Pasang lewat npm dan bundel dengan Vite.
- Tailwind v4: pakai nama utilitas v4 (`shrink-0`, `grow`, `rounded-sm`, `backdrop-blur-xs`).
- Setiap perubahan logika barcode atau berat harus disertai tes. Tes memakai SQLite memori.
- Jangan menjalankan `taskkill /IM php.exe`: itu mematikan server Laragon dan server milik pengguna.
  Matikan hanya proses yang kamu jalankan sendiri, dikenali dari port-nya.
- Lingkungan: Windows, Laragon, PHP 8.4. Jangan buka `/labels/{id}/print` di browser pane karena
  `window.print()` membuatnya hang. Untuk melihat tampilan label gunakan Edge headless
  (`msedge --headless --screenshot=...`).
- Commit dan push hanya saat pemilik meminta. Pesan commit bahasa Indonesia dengan awalan
  `feat:`, `fix:`, atau `chore:`.
- Dokumen bisnis internal ada di `Docs/` (di-gitignore). Jangan di-commit.
