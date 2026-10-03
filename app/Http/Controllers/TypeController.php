<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Support\LabelBarcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RangeException;

class TypeController extends Controller
{
    public function index()
    {
        $types = Type::orderBy('id')->get();

        return view('types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->merge(['name' => mb_strtoupper(trim((string) $request->input('name')))]);

        $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
            'expired_in_days' => 'required|integer|min:1|max:999',
        ], [
            'name.unique' => 'Nama suhu sudah ada.',
        ]);

        // ID jenis suhu masuk barcode sebagai 1 digit, jadi ID maksimal 9.
        try {
            DB::transaction(function () use ($request) {
                $type = Type::create([
                    'name' => strtoupper($request->name),
                    'expired_in_days' => $request->expired_in_days,
                ]);

                if ($type->id > LabelBarcode::MAX_TYPE_ID) {
                    throw new RangeException(
                        'Jenis suhu sudah mencapai batas maksimal '.LabelBarcode::MAX_TYPE_ID.' (batas 1 digit pada barcode).'
                    );
                }
            });
        } catch (RangeException $e) {
            return back()->withInput()->withErrors([$e->getMessage()]);
        }

        return back()->with('success', 'Suhu berhasil ditambahkan!');
    }

    public function update(Request $request, Type $type)
    {
        $request->merge(['name' => mb_strtoupper(trim((string) $request->input('name')))]);

        $request->validate([
            'name' => 'required|string|max:255|unique:types,name,'.$type->id,
            'expired_in_days' => 'required|integer|min:1|max:999',
        ], [
            'name.unique' => 'Nama suhu sudah ada.',
        ]);

        $type->update([
            'name' => strtoupper($request->name),
            'expired_in_days' => $request->expired_in_days,
        ]);

        return back()->with('success', 'Suhu berhasil diupdate!');
    }

    public function toggleActive(Type $type)
    {
        $type->update(['is_active' => ! $type->is_active]);

        return back()->with('success', 'Status Suhu berhasil diubah!');
    }

    public function destroy(Type $type)
    {
        if ($type->hasTransactions()) {
            return back()->withErrors(['Suhu tidak bisa dihapus karena sudah dipakai pada label. Nonaktifkan saja.']);
        }

        $type->delete();

        return back()->with('success', 'Suhu berhasil dihapus!');
    }
}
