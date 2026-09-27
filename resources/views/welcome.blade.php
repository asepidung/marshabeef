<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marsha Beef - Welcome</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at center, #1e1b4b 0%, #0f172a 100%);
            overflow: hidden;
        }

        /* Floating Animation */
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        /* Pulse Animation */
        @keyframes pulse-glow {
            0% { filter: drop-shadow(0 0 15px rgba(236,72,153,0.3)); }
            50% { filter: drop-shadow(0 0 40px rgba(236,72,153,0.8)); }
            100% { filter: drop-shadow(0 0 15px rgba(236,72,153,0.3)); }
        }

        .animate-float {
            animation: floating 3s ease-in-out infinite, pulse-glow 3s ease-in-out infinite;
        }
        
        .enter-text {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="h-screen w-screen flex flex-col items-center justify-center m-0 p-0 cursor-pointer" onclick="window.location.href='{{ route('labels.create') }}'">
    
    <!-- Latar Belakang Abstrak -->
    <div class="absolute inset-0 z-0 opacity-30 pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-pink-600 rounded-full mix-blend-multiply filter blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-indigo-600 rounded-full mix-blend-multiply filter blur-[100px] animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <!-- Konten Utama -->
    <div class="z-10 flex flex-col items-center justify-center transition-transform transform hover:scale-105 duration-300">
        <img src="{{ asset('img/logo.png') }}" alt="Marsha Beef" class="w-64 md:w-80 h-auto animate-float mb-10">
        
        <h1 class="text-white font-extrabold text-3xl md:text-5xl tracking-[0.2em] mb-4 drop-shadow-lg">MARSHA BEEF</h1>
        
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-md px-6 py-3 rounded-full border border-white/20 shadow-xl">
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
