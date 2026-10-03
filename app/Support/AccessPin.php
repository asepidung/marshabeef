<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * PIN akses aplikasi. PIN yang diganti lewat aplikasi disimpan sebagai hash di database
 * dan mengalahkan APP_PIN di .env (yang berfungsi sebagai PIN awal).
 */
class AccessPin
{
    private const KEY = 'pin_hash';

    public static function envPin(): string
    {
        return (string) config('access.pin');
    }

    public static function storedHash(): ?string
    {
        try {
            $hash = DB::table('settings')->where('key', self::KEY)->value('value');
        } catch (QueryException) {
            // Tabel belum ada (database lama sebelum migrasi): anggap belum ada PIN tersimpan.
            return null;
        }

        return $hash === null || $hash === '' ? null : (string) $hash;
    }

    public static function isConfigured(): bool
    {
        return self::storedHash() !== null || self::envPin() !== '';
    }

    public static function verify(string $pin): bool
    {
        $hash = self::storedHash();

        if ($hash !== null) {
            return Hash::check($pin, $hash);
        }

        $env = self::envPin();

        return $env !== '' && hash_equals($env, $pin);
    }

    public static function isValidFormat(string $pin): bool
    {
        return (bool) preg_match('/^\d{4,12}$/', $pin);
    }

    public static function change(string $pin): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => self::KEY],
            ['value' => Hash::make($pin), 'updated_at' => now()],
        );
    }

    /** Hapus PIN dari database sehingga kembali memakai APP_PIN di .env. */
    public static function reset(): void
    {
        DB::table('settings')->where('key', self::KEY)->delete();
    }
}
