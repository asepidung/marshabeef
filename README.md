# Marsha Beef – Aplikasi Cetak Label

Aplikasi cetak label produk daging untuk PT Berkah Marsha Sejahtera. Dibangun dengan Laravel 13 dan
SQLite, bisa berjalan **100% offline** di satu PC (semua aset sudah dibundel lokal), dan bisa dinaikkan
online nanti.

Fitur: master barang, master suhu (CHILL/FROZEN + lama simpan), cetak label 100×75 mm lengkap dengan
barcode CODE128, riwayat cetak, PIN akses, dan PWA (bisa di-install dari Chrome/Edge).

## Kebutuhan

- PHP 8.3 atau lebih baru dengan ekstensi `pdo_sqlite`, `sqlite3`, `mbstring`, `gd` (Laragon sudah lengkap)
- Composer (hanya untuk instalasi pertama)
- Node.js **tidak diperlukan** untuk menjalankan aplikasi. Hasil build aset sudah ada di `public/build`.

## Instalasi di PC pabrik

```bash
composer install --no-dev --optimize-autoloader
copy .env.example .env
php artisan key:generate
php artisan migrate --force
```

File database `database/database.sqlite` dibuat otomatis oleh perintah `migrate`.

Lalu buka file `.env` dan isi:

| Variabel | Isi |
|---|---|
| `APP_PIN` | PIN yang dipakai operator (angka, misalnya 6 digit). **Wajib**, tanpa ini aplikasi terkunci |
| `APP_ENV` | `production` (sudah bawaan) |
| `APP_DEBUG` | `false` (sudah bawaan) |

Jika PC pabrik tidak punya internet saat instalasi, jalankan `composer install` di PC lain lalu salin
seluruh folder proyek (termasuk `vendor`) ke PC pabrik, kemudian jalankan `php artisan optimize:clear`
di PC pabrik. Langkah terakhir itu wajib: tanpa ini cache view dari PC asal bisa membuat halaman error.

### Menjalankan

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Buka `http://127.0.0.1:8000`. Agar otomatis menyala saat PC hidup, buat shortcut/Task Scheduler yang
menjalankan perintah di atas dari folder proyek. Di Chrome atau Edge, klik ikon **Install** di address bar
untuk memasangnya sebagai aplikasi.

Jika perlu diakses dari PC lain di jaringan kantor, ganti `--host=0.0.0.0`. Catatan: PWA hanya bisa
di-install lewat `localhost` atau HTTPS, bukan lewat alamat IP biasa.

## Format barcode (23 digit, tetap)

```
1 | yymmdd | KKKK | S | BBBBB | PP | NNNN
```

| Bagian | Arti | Batas |
|---|---|---|
| `1` | Awalan tetap | |
| `yymmdd` | Tanggal produksi | |
| `KKKK` | Kode barang | 1001–9999 |
| `S` | ID jenis suhu | 1–9 (maksimal 9 jenis suhu) |
| `BBBBB` | Berat dalam gram/10 (berat × 100) | 0,01–999,99 kg |
| `PP` | Jumlah pcs | 0–99 |
| `NNNN` | Nomor urut, **reset setiap hari produksi** | 1–9999 per hari |

Nama barang dan nama suhu **selalu huruf besar**: otomatis diubah saat mengetik, dan dipaksa lagi di
server (sehingga `striploin` dan `STRIPLOIN` dianggap duplikat).

Input berat di form: `22.35` atau `22,35` (koma dibaca sebagai desimal), atau dengan jumlah pcs
`22.35/6`. Maksimal 2 desimal. Input di luar batas ditolak dengan pesan, tidak pernah dibulatkan diam-diam.

Nomor urut label yang sudah dihapus tidak dipakai ulang.

## Backup database

Seluruh data ada di satu file: `database/database.sqlite`. Jangan disalin langsung saat aplikasi jalan,
pakai perintah ini (aman walau aplikasi sedang dipakai):

```bash
php artisan app:backup-db --dir=E:\BackupMarsha --keep=30
```

`--dir` opsional (bawaan `storage/app/backups`), `--keep` jumlah backup terbaru yang disimpan.
Untuk backup harian otomatis di Windows (setiap hari 23:00):

```bash
schtasks /Create /SC DAILY /ST 23:00 /TN "Backup Marsha Beef" /TR "cmd /c cd /d D:\path\ke\marshabeef && php artisan app:backup-db --dir=E:\BackupMarsha"
```

Untuk memulihkan: hentikan aplikasi, salin file backup ke `database/database.sqlite`, jalankan lagi.

## Keamanan

- PIN dimasukkan di **halaman depan (dashboard)**. Setelah PIN benar, operator langsung masuk ke halaman
  Cetak Label. Semua halaman lain memerlukan PIN dari `APP_PIN`.
- **Kunci otomatis:** jika tidak ada klik, sentuhan, atau keyboard selama `APP_IDLE_MINUTES` (bawaan 30
  menit), aplikasi kembali ke dashboard dan meminta PIN lagi. Dicek di browser dan juga di server.
- Percobaan PIN dibatasi 5 kali per menit per komputer.
- Folder `database/` berada di luar `public/`, jadi file database tidak bisa diunduh lewat web **selama
  document root web server diarahkan ke folder `public`** (otomatis jika memakai `php artisan serve`).
- PIN ini cukup untuk pemakaian lokal di satu PC. **Sebelum dinaikkan online** ganti dengan login
  pengguna sungguhan, aktifkan HTTPS, dan pertimbangkan MySQL/PostgreSQL.

## Naik ke online (checklist)

1. Hosting dengan HTTPS, document root ke `public/`.
2. `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` sesuai domain, `APP_PIN` terisi.
3. Ganti PIN dengan autentikasi pengguna (nama + kata sandi) dan batasi siapa yang boleh menghapus data.
4. Pindahkan database ke MySQL/PostgreSQL jika banyak pengguna bersamaan, atur backup dari penyedia hosting.
5. Jalankan `php artisan config:cache route:cache view:cache`.

## Pengembangan

```bash
composer install
npm install
copy .env.example .env   # lalu ubah APP_ENV=local dan APP_DEBUG=true
php artisan key:generate
php artisan migrate
npm run dev              # atau npm run build
php artisan serve
php artisan test
```

Setelah mengubah CSS/JS atau class Tailwind di view, jalankan `npm run build` dan commit isi
`public/build` karena PC pabrik tidak memakai Node.
