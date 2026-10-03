@extends('layouts.app')

@section('content')
<div class="glass-panel rounded-2xl overflow-hidden shadow-2xl flex flex-col h-[calc(100vh-10rem)] max-w-5xl mx-auto">
    
    <div class="p-6 md:p-8 flex flex-col md:flex-row justify-between items-center gap-6 bg-white/5 border-b border-white/10 relative z-10">
        <div>
            <h1 class="text-2xl font-bold text-white drop-shadow-md flex items-center">
                <svg class="w-6 h-6 mr-3 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                Master Data Suhu
            </h1>
            <p class="text-white/60 text-sm mt-1">Kelola jenis suhu dan lama simpan (kadaluarsa)</p>
        </div>
        
        <form action="{{ route('types.store') }}" method="POST" class="w-full md:w-auto flex flex-col sm:flex-row gap-3 items-center">
            @csrf
            <input type="text" name="name" placeholder="Nama Suhu (cth: CHILL)" required data-uppercase autocapitalize="characters" style="text-transform: uppercase" class="glass-input rounded-full px-5 py-2.5 text-sm focus:outline-none w-full sm:w-56 transition-all tracking-wide">
            
            <div class="relative w-full sm:w-40 shrink-0">
                <input type="number" name="expired_in_days" placeholder="Lama Simpan" min="1" max="999" required class="glass-input rounded-full pl-5 pr-14 py-2.5 text-sm focus:outline-none w-full transition-all">
                <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white/50 text-xs font-bold tracking-widest">Hari</span>
            </div>
            
            <button type="submit" class="glass-button px-6 py-2.5 rounded-full text-white font-bold text-sm tracking-wide shadow-lg whitespace-nowrap flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Tambah
            </button>
        </form>
    </div>

    @if($errors->any())
        <div class="bg-red-500/20 border-l-4 border-red-500 p-4 m-6 mb-0 rounded-r-lg">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                </div>
                <div class="ml-3">
                    <ul class="list-disc list-inside text-sm text-red-300">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grow overflow-auto p-6 md:p-8 relative z-10">
        <table class="w-full text-left glass-table whitespace-nowrap">
            <thead>
                <tr class="text-white/50 text-xs uppercase tracking-widest font-bold border-b border-white/10">
                    <th class="px-6 py-4">Suhu / Jenis</th>
                    <th class="px-6 py-4">Lama Simpan</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($types as $type)
                    <tr x-data="{ editing: false, editName: '{{ $type->name }}', editDays: '{{ $type->expired_in_days }}' }" class="hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-4">
                            <!-- Tampilan Biasa -->
                            <span x-show="!editing" class="font-bold text-base text-white drop-shadow-sm tracking-widest uppercase">{{ $type->name }}</span>
                            <!-- Form Edit -->
                            <input x-show="editing" type="text" x-model="editName" form="edit-form-{{ $type->id }}" name="name" data-uppercase autocapitalize="characters" class="glass-input rounded-full px-4 py-1.5 text-sm w-full sm:w-48 font-bold tracking-widest uppercase" required x-cloak>
                        </td>
                        <td class="px-6 py-4">
                            <!-- Tampilan Biasa -->
                            <span x-show="!editing" class="bg-indigo-900/60 text-indigo-200 border border-indigo-500/30 font-bold px-3 py-1.5 text-sm rounded-full tracking-wide">
                                {{ $type->expired_in_days }} Hari
                            </span>
                            <!-- Form Edit -->
                            <div x-show="editing" class="relative w-32" x-cloak>
                                <input type="number" x-model="editDays" form="edit-form-{{ $type->id }}" name="expired_in_days" min="1" max="999" class="glass-input rounded-full pl-4 pr-12 py-1.5 text-sm w-full font-bold" required>
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white/50 text-[10px] font-bold uppercase">Hari</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($type->is_active)
                                <span class="bg-green-900/60 text-green-200 border border-green-500/30 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Aktif</span>
                            @else
                                <span class="bg-gray-900/60 text-gray-400 border border-gray-500/30 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <!-- Form Aktual untuk Submit Edit -->
                                <form id="edit-form-{{ $type->id }}" action="{{ route('types.update', $type->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('PUT')
                                </form>
                                
                                <!-- Tombol Aksi Biasa -->
                                <template x-if="!editing">
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editing = true" class="text-blue-400 hover:text-white transition-colors p-2 hover:bg-blue-500/20 rounded-lg" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        
                                        @if($type->hasTransactions())
                                            <form action="{{ route('types.toggle', $type->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="{{ $type->is_active ? 'text-orange-400 hover:bg-orange-500/20' : 'text-green-400 hover:bg-green-500/20' }} hover:text-white transition-colors p-2 rounded-lg" title="{{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    @if($type->is_active)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    @endif
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus suhu ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-white transition-colors p-2 hover:bg-red-500/20 rounded-lg" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </template>
                                
                                <!-- Tombol Aksi Saat Edit -->
                                <template x-if="editing">
                                    <div class="flex items-center gap-2">
                                        <button type="submit" form="edit-form-{{ $type->id }}" class="text-xs bg-green-500/20 text-green-400 hover:bg-green-500 hover:text-white border border-green-500/30 px-3 py-1.5 rounded-full font-bold transition-colors">
                                            Simpan
                                        </button>
                                        <button @click="editing = false; editName = '{{ $type->name }}'; editDays = '{{ $type->expired_in_days }}'" type="button" class="text-xs bg-gray-500/20 text-gray-400 hover:bg-gray-500 hover:text-white border border-gray-500/30 px-3 py-1.5 rounded-full font-bold transition-colors">
                                            Batal
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-white/40">
                            Belum ada data suhu.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
