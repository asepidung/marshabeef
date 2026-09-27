<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Product;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            'product_id'      => 'required|exists:products,id',
            'production_date' => 'required|date',
            'type_id'         => 'required|exists:types,id',
            'weight_input'    => 'required|string',
        ]);

        // Parse weight_input (e.g. "22.22/6" or "22.22")
        $weightInput = $request->weight_input;
        $weight   = 0;
        $qty_pcs  = 0;

        if (strpos($weightInput, '/') !== false) {
            $parts   = explode('/', $weightInput);
            $weight  = (float) $parts[0];
            $qty_pcs = (int) ($parts[1] ?? 0);
        } else {
            $weight  = (float) $weightInput;
            $qty_pcs = 0;
        }

        if ($weight <= 0) {
            return back()->withInput()->withErrors(['weight_input' => 'Format berat tidak valid.']);
        }

        $date    = Carbon::parse($request->production_date);
        $product = Product::find($request->product_id);
        $type    = Type::find($request->type_id);

        // Calculate expired date
        $expiredDate = clone $date;
        $expiredDate->addDays($type->expired_in_days);

        $printExpired = $request->has('use_expired');

        // --- FIX: Gunakan DB Transaction + lockForUpdate untuk cegah race condition ---
        $label = DB::transaction(function () use (
            $date, $product, $type, $expiredDate, $printExpired, $weight, $qty_pcs, $request
        ) {
            $yearStart = $date->copy()->startOfYear();
            $yearEnd   = $date->copy()->endOfYear();

            $currentCount = Label::whereBetween('production_date', [$yearStart, $yearEnd])
                ->lockForUpdate()
                ->max('counter');
            $counter = $currentCount ? $currentCount + 1 : 1;

            $prefix    = "1";
            $dateStr   = $date->format('ymd');
            $prodCode  = str_pad($product->code, 4, '0', STR_PAD_LEFT);
            $typeStr   = $request->type_id;
            $weightStr = str_pad(round($weight * 100), 5, '0', STR_PAD_LEFT);
            $pcsStr    = str_pad($qty_pcs, 2, '0', STR_PAD_LEFT);
            $counterStr = str_pad($counter, 4, '0', STR_PAD_LEFT);

            $barcode = $prefix . $dateStr . $prodCode . $typeStr . $weightStr . $pcsStr . $counterStr;

            return Label::create([
                'product_id'      => $request->product_id,
                'production_date' => $request->production_date,
                'type_id'         => $request->type_id,
                'expired_date'    => $expiredDate,
                'print_expired'   => $printExpired,
                'weight'          => $weight,
                'qty_pcs'         => $qty_pcs,
                'counter'         => $counter,
                'barcode'         => $barcode,
            ]);
        });

        // Simpan default state di session
        session([
            'last_product_id'      => $request->product_id,
            'last_type_id'         => $request->type_id,
            'last_production_date' => $request->production_date,
            'last_use_expired'     => $request->has('use_expired'),
        ]);

        return redirect()->route('labels.create')->with([
            'success'   => 'Label berhasil disimpan dan dicetak!',
            'print_url' => route('labels.print', $label->id)
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
