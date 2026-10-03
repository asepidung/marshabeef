@extends('layouts.app')

@section('hide_nav', '1')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="glass-panel rounded-2xl p-8 w-full max-w-sm text-center">
        <img src="{{ asset('img/icons/icon-192.png') }}" alt="Marsha Beef" class="w-24 h-24 mx-auto mb-4">
        <h1 class="text-xl font-bold tracking-widest mb-1">MARSHA BEEF</h1>
        <p class="text-white/60 text-sm mb-6">Masukkan PIN untuk melanjutkan</p>

        @if($misconfigured)
            <div class="text-yellow-200 text-xs bg-yellow-900/30 border border-yellow-500/30 rounded-lg px-3 py-3 text-left">
                PIN belum diatur. Isi <code class="font-bold">APP_PIN</code> di file <code class="font-bold">.env</code>,
                lalu muat ulang halaman ini.
            </div>
        @else
            <form action="{{ route('login.attempt') }}" method="POST" class="flex flex-col gap-4">
                @csrf
                <input type="password" name="pin" inputmode="numeric" autocomplete="current-password" autofocus required
                       placeholder="PIN"
                       class="glass-input rounded-lg w-full py-3 px-4 text-center text-lg tracking-[0.5em] font-bold">
                @error('pin')
                    <span class="text-red-400 text-xs">{{ $message }}</span>
                @enderror
                <button type="submit" class="glass-button text-white font-bold py-3 w-full rounded-lg shadow-lg tracking-widest text-sm">
                    MASUK
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
