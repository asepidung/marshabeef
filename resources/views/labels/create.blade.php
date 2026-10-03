@extends('layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row gap-6 max-w-full">
    
    <!-- Bagian Kiri: Sidebar Form -->
    <div class="w-full lg:w-2/5 xl:w-1/3">
        <div class="glass-panel rounded-2xl p-5 md:p-6 sticky top-24">
            <div class="border-b border-white/20 pb-3 mb-5">
                <h2 class="text-xl font-bold text-white drop-shadow-md tracking-wide flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    BUAT LABEL
                </h2>
            </div>
            
            <form action="{{ route('labels.store') }}" method="POST">
                @csrf
                
                <div class="flex flex-col gap-4 mb-4">
                    <!-- Product -->
                    <div class="group">
                        <select class="glass-input rounded-lg w-full py-2.5 px-3 text-sm leading-tight transition-all duration-300 font-semibold uppercase tracking-wider" 
                                id="product_id" name="product_id" required autofocus tabindex="1">
                            <option value="">-- PILIH BARANG --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id', session('last_product_id')) == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Type -->
                    <div class="group">
                        <select class="glass-input rounded-lg w-full py-2.5 px-3 text-sm leading-tight transition-all duration-300 font-semibold uppercase tracking-wider" 
                                id="type_id" name="type_id" required tabindex="-1">
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" {{ old('type_id', session('last_type_id', '1')) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Production Date -->
                    <div class="group relative">
                        <input class="glass-input rounded-lg w-full py-2.5 px-3 text-sm leading-tight transition-all duration-300 font-semibold cursor-pointer" 
                               id="production_date" name="production_date" type="date" value="{{ old('production_date', session('last_production_date', date('Y-m-d'))) }}" 
                               required onclick="try{this.showPicker()}catch(e){}" tabindex="-1">
                    </div>
                    
                    <!-- Expired Checkbox -->
                    <div class="group">
                        <label class="flex items-center space-x-3 cursor-pointer p-2 px-1 transition-all duration-300">
                            <input type="checkbox" name="use_expired" value="1" {{ old('use_expired', session('last_use_expired')) ? 'checked' : '' }} class="w-4 h-4 text-pink-500 rounded border-white/20 bg-black/30 focus:ring-pink-500 focus:ring-offset-gray-900" tabindex="-1">
                            <span class="text-white/90 font-semibold tracking-wide text-xs">Pakai Expired</span>
                        </label>
                    </div>

                    <!-- Weight and Pcs -->
                    <div class="group relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-white/50 text-xs font-semibold">Berat/Pcs</span>
                        </div>
                        <input class="glass-input rounded-lg w-full py-2.5 pl-20 pr-3 font-bold text-lg transition-all duration-300" 
                               id="weight_input" name="weight_input" type="text" placeholder="" 
                               required autocomplete="off" tabindex="2">
                        @error('weight_input')
                            <span class="text-red-400 text-xs mt-1 block px-2">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    @error('barcode')
                        <div class="text-red-300 text-xs bg-red-900/30 border border-red-500/30 rounded-lg px-3 py-2">{{ $message }}</div>
                    @enderror

                    <div class="mt-4">
                        <button class="glass-button text-white font-bold py-3 w-full rounded-lg shadow-lg tracking-widest text-sm" type="submit" tabindex="3">
                            CETAK & SIMPAN LABEL
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bagian Kanan: Table History -->
    <div class="w-full lg:w-3/5 xl:w-2/3">
        <div class="glass-panel rounded-2xl overflow-hidden flex flex-col h-full min-h-[500px]">
            <div class="px-4 py-3 border-b border-white/10 flex justify-between items-center bg-black/20">
                <div class="flex items-center">
                    <h2 class="text-sm font-bold text-white tracking-widest uppercase">Riwayat Pencetakan Terakhir</h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-indigo-500/20 text-indigo-200 text-xs font-bold px-3 py-1 rounded-full border border-indigo-500/30">Total: {{ number_format($labels->total(), 0, ',', '.') }}</span>
                </div>
            </div>
            
            <div class="overflow-x-auto flex-grow">
                <table class="w-full text-left glass-table whitespace-nowrap">
                    <thead>
                        <tr class="text-white/60 text-[10px] uppercase tracking-wider font-semibold border-b border-white/10 bg-white/5">
                            <th class="px-4 py-2">Barcode</th>
                            <th class="px-4 py-2">Barang</th>
                            <th class="px-4 py-2 text-center">Berat</th>
                            <th class="px-4 py-2 text-center">Suhu</th>
                            <th class="px-4 py-2 text-center">Pcs</th>
                            <th class="px-4 py-2 text-center">Waktu</th>
                            <th class="px-4 py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($labels as $label)
                            <tr class="text-xs {{ $label->trashed() ? 'opacity-40 grayscale pointer-events-none' : 'hover:bg-white/5' }} transition-colors">
                                <td class="px-4 py-2 font-bold tracking-wider {{ $label->trashed() ? 'text-white/50 line-through' : 'text-white drop-shadow' }}">
                                    {{ $label->barcode }}
                                </td>
                                <td class="px-4 py-2 font-bold {{ $label->trashed() ? 'text-yellow-400/50' : 'text-yellow-400' }}">
                                    {{ $label->product->name }}
                                </td>
                                <td class="px-4 py-2 font-semibold text-center {{ $label->trashed() ? 'text-white/50' : 'text-white' }}">
                                    {{ number_format($label->weight, 2) }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="bg-indigo-900/60 text-indigo-200 border border-indigo-500/30 text-[9px] font-bold px-2 py-0.5 rounded-md tracking-wider uppercase">
                                        {{ $label->type->name ?? 'UNKNOWN' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 font-semibold text-center {{ $label->trashed() ? 'text-white/50' : 'text-white' }}">
                                    {{ $label->qty_pcs > 0 ? $label->qty_pcs : '-' }}
                                </td>
                                <td class="px-4 py-2 font-light text-center {{ $label->trashed() ? 'text-white/40' : 'text-white/70' }}">
                                    {{ $label->created_at->format('H:i:s') }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    @if(!$label->trashed())
                                        <form action="{{ route('labels.destroy', $label->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus label ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-200 transition-colors p-1 hover:bg-red-500/20 rounded-lg inline-flex items-center justify-center" title="Hapus Label">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[9px] text-red-300 font-bold px-1.5 py-0.5 border border-red-500/30 bg-red-900/30 rounded uppercase tracking-wider">Dihapus</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-20 text-center text-white/30 text-sm">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        Belum ada data label yang dicetak.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($labels->hasPages())
            <div class="px-6 py-4 border-t border-white/10 bg-black/20">
                {{ $labels->links('pagination::tailwind') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if(session('print_url'))
<script>
    window.onload = function() {
        window.open("{{ session('print_url') }}", "_blank");
    };
</script>
@endif
@endsection
