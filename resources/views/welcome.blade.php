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

        .bg-video {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            pointer-events: none;
        }

        .enter-text {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="h-screen w-screen m-0 p-0 cursor-pointer" onclick="window.location.href='{{ route('labels.create') }}'">

    <video class="bg-video" src="{{ asset('img/dashboard.mp4') }}" autoplay muted loop playsinline preload="auto" aria-hidden="true"></video>

    <!-- Petunjuk masuk -->
    <div class="fixed inset-x-0 bottom-10 z-10 flex justify-center px-4">
        <div class="flex items-center gap-3 bg-black/40 backdrop-blur-md px-6 py-3 rounded-full border border-white/20 shadow-xl">
            <span class="text-pink-400 font-bold tracking-widest text-sm uppercase enter-text">Klik layar untuk masuk</span>
            <svg class="w-5 h-5 text-pink-400 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
</body>
</html>
