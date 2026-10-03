<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marsha Beef - Welcome</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/icons/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icons/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Marsha Beef">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @vite('resources/css/app.css')
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #05080d;
            overflow: hidden;
        }

        /* Ukuran asli video 1280x720: jangan diperbesar (jadi blur), hanya diperkecil jika layar lebih kecil. */
        .bg-video {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: min(100vw, 177.78vh, 1280px);
            height: auto;
            aspect-ratio: 16 / 9;
            z-index: 0;
            pointer-events: none;
            /* Tepi memudar ke latar gelap agar tidak terlihat seperti kotak. */
            -webkit-mask-image: linear-gradient(to right, transparent, #000 10%, #000 90%, transparent),
                linear-gradient(to bottom, transparent, #000 10%, #000 90%, transparent);
            -webkit-mask-composite: source-in;
            mask-image: linear-gradient(to right, transparent, #000 10%, #000 90%, transparent),
                linear-gradient(to bottom, transparent, #000 10%, #000 90%, transparent);
            mask-composite: intersect;
        }

        .enter-text {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="h-screen w-screen m-0 p-0 {{ $pinEnabled ? '' : 'cursor-pointer' }}" @unless($pinEnabled) onclick="window.location.href='{{ route('labels.create') }}'" @endunless>

    <video class="bg-video" src="{{ asset('img/dashboard.mp4') }}" autoplay muted loop playsinline preload="auto" aria-hidden="true"></video>

    <div class="fixed inset-x-0 bottom-10 z-10 flex flex-col items-center gap-3 px-4">
        @if(session('status'))
            <div class="text-yellow-100 text-xs sm:text-sm bg-black/50 backdrop-blur-md border border-yellow-400/40 rounded-full px-5 py-2 text-center">{{ session('status') }}</div>
        @endif

        @if($misconfigured)
            <div class="text-yellow-100 text-xs sm:text-sm bg-black/60 backdrop-blur-md border border-yellow-400/40 rounded-2xl px-5 py-3 text-center max-w-md">
                PIN belum diatur. Isi <b>APP_PIN</b> di file <b>.env</b>, lalu muat ulang halaman ini.
            </div>
        @elseif($pinEnabled)
            <form action="{{ route('login.attempt') }}" method="POST" class="flex items-center gap-2 bg-black/40 backdrop-blur-md pl-5 pr-2 py-2 rounded-full border border-white/20 shadow-xl">
                @csrf
                <input type="password" name="pin" id="pin" inputmode="numeric" autocomplete="off" autofocus required
                       placeholder="MASUKKAN PIN" aria-label="PIN"
                       class="w-40 sm:w-48 bg-transparent text-white text-center font-bold tracking-[0.3em] placeholder:tracking-widest placeholder:text-xs placeholder:font-semibold placeholder:text-pink-300/70 focus:outline-none">
                <button type="submit" aria-label="Masuk" class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-rose-500 to-fuchsia-500 text-white shadow-lg hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>
            @error('pin')
                <div class="text-red-100 text-xs sm:text-sm bg-red-900/60 backdrop-blur-md border border-red-400/40 rounded-full px-5 py-2 text-center">{{ $message }}</div>
            @enderror
        @else
            <div class="flex items-center gap-3 bg-black/40 backdrop-blur-md px-6 py-3 rounded-full border border-white/20 shadow-xl">
                <span class="text-pink-400 font-bold tracking-widest text-sm uppercase enter-text">Klik layar untuk masuk</span>
                <svg class="w-5 h-5 text-pink-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </div>
        @endif
    </div>

    <script>
        const pinField = document.getElementById('pin');
        if (pinField) {
            pinField.focus();
            window.addEventListener('focus', () => pinField.focus());
        }

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
</body>
</html>
