<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('id', 'desc')->get();
        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
        ], [
            'name.unique' => 'Nama barang sudah ada.',
        ]);

        $lastProduct = Product::orderBy('id', 'desc')->first();
        $code = $lastProduct ? (intval($lastProduct->code) + 1) : 1001;

        Product::create([
            'name' => strtoupper($request->name),
            'code' => (string) $code,
        ]);

        return redirect()->route('products.index')->with('success', 'Barang baru berhasil ditambahkan.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        return back()->with('success', 'Status Barang berhasil diubah!');
    }

    public function destroy(Product $product)
    {
        if ($product->hasTransactions()) {
            return back()->withErrors(['name' => 'Barang tidak bisa dihapus karena sudah digunakan dalam transaksi cetak label. Silakan nonaktifkan saja.']);
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Barang berhasil dihapus.');
    }
}
