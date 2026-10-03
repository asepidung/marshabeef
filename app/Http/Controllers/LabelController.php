<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Product;
use App\Models\Type;
use App\Support\LabelBarcode;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RangeException;

class LabelController extends Controller
{
    public function index()
    {
        $labels = Label::with(['product', 'type'])->orderBy('id', 'desc')->paginate(50);

        return view('labels.index', compact('labels'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $types = Type::where('is_active', true)->orderBy('id')->get();
        // Sertakan data yang di soft-delete
        $labels = Label::with(['product', 'type'])->withTrashed()->orderBy('id', 'desc')->paginate(10);

        return view('labels.create', compact('products', 'types', 'labels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'production_date' => 'required|date',
            'type_id' => 'required|exists:types,id',
            'weight_input' => 'required|string|max:20',
        ]);

        try {
            [$weight, $qty_pcs] = LabelBarcode::parseWeightInput($request->weight_input);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['weight_input' => $e->getMessage()]);
        }

        $date = Carbon::parse($request->production_date);
        $product = Product::find($request->product_id);
        $type = Type::find($request->type_id);

        $expiredDate = $date->copy()->addDays($type->expired_in_days);
        $printExpired = $request->has('use_expired');

        // Nomor urut reset per hari produksi. withTrashed() supaya nomor label
        // yang sudah dihapus tidak dipakai ulang (barcode unik mencakup baris terhapus).
        $makeLabel = function () use ($date, $product, $type, $expiredDate, $printExpired, $weight, $qty_pcs, $request) {
            $currentCount = Label::withTrashed()
                ->whereDate('production_date', $date->toDateString())
                ->lockForUpdate()
                ->max('counter');
            $counter = ((int) $currentCount) + 1;

            $barcode = LabelBarcode::build($date, (int) $product->code, $type->id, $weight, $qty_pcs, $counter);

            return Label::create([
                'product_id' => $product->id,
                'production_date' => $request->production_date,
                'type_id' => $type->id,
                'expired_date' => $expiredDate,
                'print_expired' => $printExpired,
                'weight' => $weight,
                'qty_pcs' => $qty_pcs,
                'counter' => $counter,
                'barcode' => $barcode,
            ]);
        };

        try {
            $label = null;
            for ($attempt = 1; $label === null; $attempt++) {
                try {
                    $label = DB::transaction($makeLabel);
                } catch (UniqueConstraintViolationException $e) {
                    if ($attempt >= 3) {
                        throw $e;
                    }
                }
            }
        } catch (InvalidArgumentException|RangeException $e) {
            return back()->withInput()->withErrors(['barcode' => $e->getMessage()]);
        }

        // Simpan default state di session
        session([
            'last_product_id' => $request->product_id,
            'last_type_id' => $request->type_id,
            'last_production_date' => $request->production_date,
            'last_use_expired' => $request->has('use_expired'),
        ]);

        return redirect()->route('labels.create')->with([
            'success' => 'Label berhasil disimpan dan dicetak!',
            'print_url' => route('labels.print', $label->id),
        ]);
    }

    public function print(Label $label)
    {
        return view('labels.print', compact('label'));
    }

    public function destroy(Label $label)
    {
        $label->delete(); // Soft delete

        return back()->with('success_del', 'Label berhasil dihapus.');
    }
}
