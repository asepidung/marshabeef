<?php

namespace Tests\Unit;

use App\Support\LabelBarcode;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RangeException;

class LabelBarcodeTest extends TestCase
{
    #[DataProvider('validWeights')]
    public function test_parses_valid_weight_input(string $input, float $weight, int $pcs): void
    {
        $this->assertSame([$weight, $pcs], LabelBarcode::parseWeightInput($input));
    }

    public static function validWeights(): array
    {
        return [
            'titik' => ['22.35', 22.35, 0],
            'koma jadi titik' => ['22,35', 22.35, 0],
            'bulat' => ['22', 22.0, 0],
            'dengan pcs' => ['22.35/6', 22.35, 6],
            'koma dengan pcs' => ['22,35/6', 22.35, 6],
            'spasi diabaikan' => [' 22,35 / 6 ', 22.35, 6],
            'batas atas' => ['999.99/99', 999.99, 99],
            'batas bawah' => ['0.01', 0.01, 0],
        ];
    }

    #[DataProvider('invalidWeights')]
    public function test_rejects_invalid_weight_input(string $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        LabelBarcode::parseWeightInput($input);
    }

    public static function invalidWeights(): array
    {
        return [
            'huruf' => ['abc'],
            'kosong' => [''],
            'tiga desimal' => ['22.355'],
            'ribuan eropa' => ['1.234,5'],
            'empat digit' => ['1000'],
            'nol' => ['0'],
            'negatif' => ['-5'],
            'pcs nol' => ['22.35/0'],
            'pcs tiga digit' => ['22.35/100'],
            'pcs kosong' => ['22.35/'],
            'dua garis miring' => ['22.35/6/2'],
        ];
    }

    public function test_builds_the_same_barcode_as_existing_labels(): void
    {
        // Label #31 di database: 2026-10-02, barang 1002, suhu 1, 22.15 kg, 4 pcs, urutan 31
        $barcode = LabelBarcode::build(Carbon::parse('2026-10-02'), 1002, 1, 22.15, 4, 31);

        $this->assertSame('12610021002102215040031', $barcode);
    }

    public function test_barcode_is_always_fixed_length(): void
    {
        foreach ([[1001, 1, 0.01, 0, 1], [9999, 9, 999.99, 99, 9999], [1234, 2, 22.35, 0, 57]] as [$code, $type, $w, $pcs, $n]) {
            $barcode = LabelBarcode::build(Carbon::parse('2026-01-05'), $code, $type, $w, $pcs, $n);
            $this->assertSame(LabelBarcode::LENGTH, strlen($barcode));
            $this->assertMatchesRegularExpression('/^\d{23}$/', $barcode);
        }
    }

    public function test_rejects_values_that_would_change_barcode_length(): void
    {
        $date = Carbon::parse('2026-10-02');

        foreach ([
            'barang 5 digit' => [10000, 1, 22.35, 0, 1],
            'suhu 2 digit' => [1002, 10, 22.35, 0, 1],
            'berat 1000 kg' => [1002, 1, 1000.0, 0, 1],
            'pcs 100' => [1002, 1, 22.35, 100, 1],
        ] as $case => [$code, $type, $w, $pcs, $n]) {
            try {
                LabelBarcode::build($date, $code, $type, $w, $pcs, $n);
                $this->fail("Seharusnya ditolak: $case");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_rejects_counter_beyond_four_digits(): void
    {
        $this->expectException(RangeException::class);
        LabelBarcode::build(Carbon::parse('2026-10-02'), 1002, 1, 22.35, 0, 10000);
    }
}
