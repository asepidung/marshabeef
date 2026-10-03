@extends('layouts.app')

@section('content')
<div class="glass-panel rounded-2xl overflow-hidden">
    <div class="px-6 py-5 border-b border-white/20 flex justify-between items-center bg-white/5">
        <h2 class="text-xl font-bold text-white tracking-wide">Riwayat Cetak Label</h2>
        <span class="bg-white/20 backdrop-blur-xs text-white text-xs font-bold px-3 py-1.5 rounded-full border border-white/30">{{ $labels->total() }} Total</span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left glass-table">
            <thead>
                <tr class="text-white/70 text-xs uppercase tracking-wider font-semibold">
                    <th class="px-6 py-4">Waktu Cetak</th>
                    <th class="px-6 py-4">Barcode</th>
                    <th class="px-6 py-4">Barang</th>
                    <th class="px-6 py-4 text-center">Tipe</th>
                    <th class="px-6 py-4 text-center">Berat</th>
                    <th class="px-6 py-4 text-center">Pcs</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($labels as $label)
                    <tr>
                        <td class="px-6 py-4 text-sm font-light whitespace-nowrap">
                            {{ $label->created_at->format('d-m-Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold tracking-widest text-indigo-200 whitespace-nowrap drop-shadow-md">
                            {{ $label->barcode }}
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold tracking-wide">
                            {{ $label->product->name }} ({{ $label->product->code }})
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            <span class="bg-indigo-500/30 text-indigo-100 border border-indigo-300/30 text-xs font-bold px-3 py-1 rounded-lg">
                                {{ $label->type->name ?? 'UNKNOWN' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-center">
                            {{ number_format($label->weight, 2) }} kg
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-center">
                            {{ $label->qty_pcs }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('labels.print', $label->id) }}" target="_blank" class="text-white hover:text-indigo-200 font-bold uppercase tracking-wider text-xs transition-colors bg-white/10 hover:bg-white/20 border border-white/20 px-4 py-2 rounded-lg inline-block">Cetak Ulang</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-white/50 text-sm font-light">
                            Belum ada riwayat pencetakan label.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($labels->hasPages())
    <div class="px-6 py-4 border-t border-white/20 bg-white/5">
        {{ $labels->links() }}
    </div>
    @endif
</div>
@endsection
