@extends('layouts.app')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="glass-panel rounded-2xl p-6 md:p-8 w-full max-w-md">
        <div class="border-b border-white/20 pb-3 mb-5">
            <h1 class="text-xl font-bold tracking-wide flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                GANTI PIN
            </h1>
            <p class="text-white/60 text-sm mt-1">PIN terdiri dari 4 sampai 12 angka.</p>
        </div>

        <form action="{{ route('pin.update') }}" method="POST" class="flex flex-col gap-4">
            @csrf

            @if($requiresCurrent)
            <div>
                <label for="current_pin" class="block text-xs font-semibold tracking-wider text-white/70 mb-1">PIN SAAT INI</label>
                <input type="password" id="current_pin" name="current_pin" inputmode="numeric" autocomplete="off" required autofocus
                       class="glass-input rounded-lg w-full py-2.5 px-4 text-lg tracking-[0.3em] font-bold">
                @error('current_pin')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            @endif

            <div>
                <label for="new_pin" class="block text-xs font-semibold tracking-wider text-white/70 mb-1">PIN BARU</label>
                <input type="password" id="new_pin" name="new_pin" inputmode="numeric" autocomplete="off" required
                       @unless($requiresCurrent) autofocus @endunless
                       class="glass-input rounded-lg w-full py-2.5 px-4 text-lg tracking-[0.3em] font-bold">
                @error('new_pin')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="new_pin_confirmation" class="block text-xs font-semibold tracking-wider text-white/70 mb-1">ULANGI PIN BARU</label>
                <input type="password" id="new_pin_confirmation" name="new_pin_confirmation" inputmode="numeric" autocomplete="off" required
                       class="glass-input rounded-lg w-full py-2.5 px-4 text-lg tracking-[0.3em] font-bold">
            </div>

            <button type="submit" class="glass-button text-white font-bold py-3 w-full rounded-lg shadow-lg tracking-widest text-sm mt-2">
                SIMPAN PIN
            </button>
        </form>
    </div>
</div>
@endsection
