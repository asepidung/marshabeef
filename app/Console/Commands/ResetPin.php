<?php

namespace App\Console\Commands;

use App\Support\AccessPin;
use Illuminate\Console\Command;

class ResetPin extends Command
{
    protected $signature = 'app:reset-pin
        {pin? : PIN baru (4-12 angka). Kosongkan untuk kembali memakai APP_PIN dari file .env}';

    protected $description = 'Atur ulang PIN aplikasi (untuk pemilik, mis. saat PIN lupa)';

    public function handle(): int
    {
        $pin = $this->argument('pin');

        if ($pin !== null) {
            if (! AccessPin::isValidFormat((string) $pin)) {
                $this->error('PIN harus berupa 4 sampai 12 angka.');

                return self::FAILURE;
            }

            AccessPin::change((string) $pin);
            $this->info('PIN diganti sesuai yang Anda masukkan.');

            return self::SUCCESS;
        }

        AccessPin::reset();

        if (AccessPin::envPin() === '') {
            $this->warn('PIN di database dihapus, tetapi APP_PIN di .env kosong. Isi APP_PIN atau jalankan lagi dengan PIN baru.');

            return self::SUCCESS;
        }

        $this->info('PIN dikembalikan ke PIN awal (APP_PIN di file .env).');

        return self::SUCCESS;
    }
}
