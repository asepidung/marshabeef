<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NameUppercaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_name_is_saved_in_uppercase(): void
    {
        $this->post(route('products.store'), ['name' => '  Striploin steak  ']);

        $this->assertSame('STRIPLOIN STEAK', Product::firstOrFail()->name);
    }

    public function test_product_duplicate_is_detected_regardless_of_letter_case(): void
    {
        $this->post(route('products.store'), ['name' => 'STRIPLOIN']);
        $this->post(route('products.store'), ['name' => 'striploin'])->assertSessionHasErrors('name');
        $this->post(route('products.store'), ['name' => 'Striploin'])->assertSessionHasErrors('name');

        $this->assertSame(1, Product::count());
    }

    public function test_type_name_is_saved_in_uppercase(): void
    {
        $this->post(route('types.store'), ['name' => 'chill', 'expired_in_days' => 90])->assertSessionHasNoErrors();
        $this->post(route('types.store'), ['name' => 'Frozen', 'expired_in_days' => 365])->assertSessionHasNoErrors();

        $this->assertSame(['CHILL', 'FROZEN'], Type::orderBy('id')->pluck('name')->all());
    }

    public function test_type_duplicate_is_detected_regardless_of_letter_case(): void
    {
        $this->post(route('types.store'), ['name' => 'CHILL', 'expired_in_days' => 90]);
        $this->post(route('types.store'), ['name' => 'chill', 'expired_in_days' => 90])->assertSessionHasErrors('name');

        $this->assertSame(1, Type::count());
    }

    public function test_type_rename_is_saved_in_uppercase(): void
    {
        $type = Type::create(['name' => 'CHILL', 'expired_in_days' => 90]);

        $this->put(route('types.update', $type), ['name' => 'chiller', 'expired_in_days' => 60])->assertSessionHasNoErrors();

        $this->assertSame('CHILLER', $type->fresh()->name);
    }

    public function test_forms_mark_the_name_inputs_for_uppercase_typing(): void
    {
        Type::create(['name' => 'CHILL', 'expired_in_days' => 90]);

        $this->get(route('products.index'))->assertSee('data-uppercase', false);
        $this->get(route('types.index'))->assertSee('data-uppercase', false);
    }
}
