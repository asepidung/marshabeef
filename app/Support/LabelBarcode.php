<?php

namespace App\Support;

use InvalidArgumentException;
use RangeException;

class LabelBarcode
{
    public const LENGTH = 23;

    public const MAX_WEIGHT = 999.99;

    public const MAX_PCS = 99;

    public const MAX_COUNTER = 9999;

    public const MAX_TYPE_ID = 9;

    public const MAX_PRODUCT_CODE = 9999;

    /** @return array{0: float, 1: int} [berat, pcs] — pcs 0 berarti tidak diisi */
    public static function parseWeightInput(string $input): array
    {
        $normalized = str_replace(',', '.', preg_replace('/\s+/', '', $input));

        if (! preg_match('#^(\d{1,3}(?:\.\d{1,2})?)(?:/(\d{1,2}))?$#', $normalized, $m)) {
            throw new InvalidArgumentException(self::weightMessage());
        }

        $weight = (float) $m[1];
        $pcs = isset($m[2]) ? (int) $m[2] : 0;

        if ($weight < 0.01 || (isset($m[2]) && $pcs < 1)) {
            throw new InvalidArgumentException(self::weightMessage());
        }

        return [$weight, $pcs];
    }

    public static function build(
        \DateTimeInterface $date,
        int $productCode,
        int $typeId,
        float $weight,
        int $pcs,
        int $counter,
    ): string {
        if ($productCode < 1 || $productCode > self::MAX_PRODUCT_CODE) {
            throw new InvalidArgumentException('Kode barang di luar batas 4 digit barcode.');
        }
        if ($typeId < 1 || $typeId > self::MAX_TYPE_ID) {
            throw new InvalidArgumentException('ID jenis suhu di luar batas 1 digit barcode.');
        }
        if ($weight < 0.01 || $weight > self::MAX_WEIGHT) {
            throw new InvalidArgumentException(self::weightMessage());
        }
        if ($pcs < 0 || $pcs > self::MAX_PCS) {
            throw new InvalidArgumentException(self::weightMessage());
        }
        if ($counter < 1 || $counter > self::MAX_COUNTER) {
            throw new RangeException('Batas '.self::MAX_COUNTER.' label per hari sudah tercapai.');
        }

        $barcode = '1'
            .$date->format('ymd')
            .str_pad((string) $productCode, 4, '0', STR_PAD_LEFT)
            .$typeId
            .str_pad((string) (int) round($weight * 100), 5, '0', STR_PAD_LEFT)
            .str_pad((string) $pcs, 2, '0', STR_PAD_LEFT)
            .str_pad((string) $counter, 4, '0', STR_PAD_LEFT);

        if (strlen($barcode) !== self::LENGTH) {
            throw new \LogicException('Panjang barcode tidak sesuai: '.$barcode);
        }

        return $barcode;
    }

    private static function weightMessage(): string
    {
        return 'Format berat tidak valid. Contoh: 22.35 atau 22.35/6 (berat 0.01–999.99 kg, maks 2 desimal; pcs 1–99).';
    }
}
