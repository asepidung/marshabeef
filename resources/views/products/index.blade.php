@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    
    <div class="md:col-span-1">
        <div class="glass-panel rounded-2xl p-6 transform transition-all hover:translate-y-[-4px] duration-300">
            <h2 class="text-xl font-bold text-white mb-6 border-b border-white/20 pb-3">Tambah Barang Baru</h2>
            
            @if($errors->any())
                <div class="bg-red-500/20 border-l-4 border-red-500 p-3 mb-4 rounded">
                    <ul class="list-disc list-inside text-xs text-red-200">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-white/90 text-sm font-semibold mb-2 uppercase tracking-wide text-xs" for="name">
                        Nama Barang
                    </label>
                    <input class="glass-input rounded-xl w-full py-3 px-4 text-white leading-tight" id="name" name="name" type="text" placeholder="Misal: DAGING SAPI" required autocomplete="off">
                    <p class="text-xs text-white/60 mt-3 font-light">Kode barang akan terisi otomatis (Mulai dari 1001).</p>
                </div>
                
                <div class="flex items-center justify-end mt-4">
                    <button class="glass-button text-white font-bold py-3 px-4 rounded-xl w-full uppercase tracking-wider text-sm" type="submit">
                        Simpan Barang
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="md:col-span-2">
        <div class="glass-panel rounded-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-white/20 flex justify-between items-center bg-white/5">
                <h2 class="text-xl font-bold text-white">Daftar Barang</h2>
                <span class="bg-white/20 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full border border-white/30">{{ $products->count() }} Total</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left glass-table">
                    <thead>
                        <tr class="text-white/70 text-xs uppercase tracking-wider font-semibold">
                            <th class="px-6 py-4">Kode</th>
                            <th class="px-6 py-4">Nama Barang</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="px-6 py-4">
                                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-lg border border-white/30">{{ $product->code }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold tracking-wide">
                                    {{ $product->name }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($product->is_active)
                                        <span class="bg-green-900/60 text-green-200 border border-green-500/30 text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">Aktif</span>
                                    @else
                                        <span class="bg-gray-900/60 text-gray-400 border border-gray-500/30 text-[10px] font-bold px-2 py-1 rounded-full uppercase tracking-wider">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm">
                                    @if($product->hasTransactions())
                                        <form action="{{ route('products.toggle', $product->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-xs font-bold tracking-wider uppercase transition-colors px-3 py-1.5 rounded-lg {{ $product->is_active ? 'text-orange-300 bg-orange-900/30 hover:bg-orange-500/30 border border-orange-500/30' : 'text-green-300 bg-green-900/30 hover:bg-green-500/30 border border-green-500/30' }}">
                                                {{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus barang ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-pink-300 hover:text-pink-100 font-bold tracking-wider uppercase text-xs transition-colors bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg">Hapus</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-white/50 text-sm font-light">
                                    Belum ada data barang. Silakan tambah di form samping.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
