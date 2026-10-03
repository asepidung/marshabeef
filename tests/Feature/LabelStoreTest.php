<?php

namespace Tests\Feature;

use App\Models\Label;
use App\Models\Product;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelStoreTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private Type $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create(['name' => 'STRIPLOIN', 'code' => '1002']);
        $this->type = Type::create(['name' => 'CHILL', 'expired_in_days' => 90]);
    }

    private function store(string $weight, string $date = '2026-10-02', array $extra = [])
    {
        return $this->post(route('labels.store'), array_merge([
            'product_id' => $this->product->id,
            'type_id' => $this->type->id,
            'production_date' => $date,
            'weight_input' => $weight,
        ], $extra));
    }

    public function test_barcode_is_built_from_the_submitted_label(): void
    {
        $this->store('22.15/4')->assertRedirect(route('labels.create'));

        $label = Label::firstOrFail();
        $this->assertSame('12610021002102215040001', $label->barcode);
        $this->assertSame(22.15, (float) $label->weight);
        $this->assertSame(4, $label->qty_pcs);
    }

    public function test_comma_is_read_as_decimal_point_not_truncated(): void
    {
        $this->store('22,22')->assertSessionHasNoErrors();

        $this->assertSame(22.22, (float) Label::firstOrFail()->weight);
    }

    public function test_counter_increments_within_a_day_and_resets_the_next_day(): void
    {
        $this->store('10.00');
        $this->store('11.00');
        $this->store('12.00', '2026-10-03');

        $this->assertSame(
            [1, 2],
            Label::whereDate('production_date', '2026-10-02')->orderBy('id')->pluck('counter')->all()
        );
        $this->assertSame(1, Label::whereDate('production_date', '2026-10-03')->value('counter'));
    }

    public function test_counter_is_not_limited_to_one_year(): void
    {
        $this->store('10.00', '2026-01-01');
        $this->store('10.00', '2026-12-31');

        $this->assertSame([1, 1], Label::orderBy('id')->pluck('counter')->all());
    }

    public function test_deleted_label_number_is_not_reused_and_does_not_error(): void
    {
        $this->store('22.35');
        $this->store('22.35');
        Label::orderByDesc('id')->first()->delete();

        $this->store('22.35')->assertSessionHasNoErrors()->assertRedirect(route('labels.create'));

        $this->assertSame([1, 2, 3], Label::withTrashed()->orderBy('id')->pluck('counter')->all());
        $this->assertSame(3, Label::withTrashed()->distinct()->count('barcode'));
    }

    public function test_every_barcode_has_the_same_length(): void
    {
        $this->store('0.01');
        $this->store('999.99/99');
        $this->store('22.35');

        foreach (Label::all() as $label) {
            $this->assertSame(23, strlen($label->barcode));
        }
    }

    public function test_invalid_weight_is_rejected_and_nothing_is_saved(): void
    {
        foreach (['abc', '22.355', '1000', '0', '22.35/100', '22.35/0'] as $input) {
            $this->store($input)->assertSessionHasErrors('weight_input');
        }

        $this->assertSame(0, Label::count());
    }

    public function test_type_id_above_nine_is_rejected_instead_of_changing_barcode_length(): void
    {
        Type::forceCreate(['id' => 10, 'name' => 'EXTRA', 'expired_in_days' => 30]);

        $this->post(route('labels.store'), [
            'product_id' => $this->product->id,
            'type_id' => 10,
            'production_date' => '2026-10-02',
            'weight_input' => '22.35',
        ])->assertSessionHasErrors();

        $this->assertSame(0, Label::count());
    }

    public function test_cannot_create_more_than_nine_types(): void
    {
        foreach (range(2, 9) as $i) {
            $this->post(route('types.store'), ['name' => "SUHU $i", 'expired_in_days' => 30])->assertSessionHasNoErrors();
        }
        $this->assertSame(9, Type::count());

        $this->post(route('types.store'), ['name' => 'SUHU 10', 'expired_in_days' => 30])->assertSessionHasErrors();

        $this->assertSame(9, Type::count());
    }
}
