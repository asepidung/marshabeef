<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Cetak Label - PT Berkah Marsha Sejahtera</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/icons/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icons/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Marsha Beef">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #020617 100%);
            background-attachment: fixed;
            color: #ffffff;
            position: relative;
        }
        
        /* Tambahkan efek blob warna-warni di belakang seperti referensi */
        body::before {
            content: '';
            position: fixed;
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(236,72,153,0.3) 0%, rgba(0,0,0,0) 70%);
            z-index: -1;
        }
        body::after {
            content: '';
            position: fixed;
            bottom: -10%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99,102,241,0.2) 0%, rgba(0,0,0,0) 70%);
            z-index: -1;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }

        .glass-nav {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-input {
            background: rgba(0, 0, 0, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .glass-input:focus {
            background: rgba(0, 0, 0, 0.5) !important;
            border-color: rgba(236, 72, 153, 0.5) !important;
            outline: none;
            box-shadow: 0 0 0 2px rgba(236, 72, 153, 0.2);
        }
        
        .glass-input option {
            background: #1e293b;
            color: white;
        }

        .glass-button {
            background: linear-gradient(135deg, #f43f5e 0%, #d946ef 100%);
            border: none;
            transition: all 0.3s ease;
        }

        .glass-button:hover {
            background: linear-gradient(135deg, #e11d48 0%, #c026d3 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(217, 70, 239, 0.3);
        }

        .glass-table th {
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .glass-table tr {
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: background 0.2s;
        }
        
        .glass-table tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        
        .text-gray-800, .text-gray-900, .text-gray-700, .text-gray-500 {
            color: #ffffff !important;
        }
    </style>
</head>
@php $pinEnabled = \App\Support\AccessPin::isConfigured(); @endphp
<body class="antialiased min-h-screen flex flex-col relative"
      @if($pinEnabled)
      data-idle-minutes="{{ config('access.idle_minutes') }}"
      data-lock-url="{{ route('lock') }}"
      data-keepalive-url="{{ route('keepalive') }}"
      @endif>
    
    <!-- Floating Toast Notification -->
    @if(session('success') || session('success_del'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 2000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform -translate-y-4"
             class="fixed top-20 left-1/2 transform -translate-x-1/2 z-[100] px-6 py-3 rounded-xl border {{ session('success_del') ? 'border-red-500 bg-red-900/80 text-red-100' : 'border-green-500 bg-green-900/80 text-green-100' }} shadow-2xl backdrop-blur-md flex items-center gap-3">
            <svg class="w-5 h-5 {{ session('success_del') ? 'text-red-400' : 'text-green-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if(session('success_del'))
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                @endif
            </svg>
            <p class="font-bold text-sm tracking-wide">{{ session('success') ?? session('success_del') }}</p>
        </div>
    @endif

    <nav class="glass-nav sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-white font-bold tracking-widest drop-shadow-md uppercase flex items-center">
                        <img src="{{ asset('img/logo.png') }}" alt="Marsha Beef Logo" class="h-11 w-auto mr-3 object-contain">
                        <span class="block sm:hidden text-xl">MARSHA BEEF</span>
                        <span class="hidden sm:block text-lg md:text-xl">MARSHA BEEF</span>
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-baseline space-x-2">
                    <a href="{{ route('labels.create') }}" class="text-white hover:bg-white/20 px-4 py-2 rounded-xl font-medium text-sm transition-all duration-300">Cetak Label</a>
                    <a href="{{ route('products.index') }}" class="text-white hover:bg-white/20 px-4 py-2 rounded-xl font-medium text-sm transition-all duration-300">Master Barang</a>
                    <a href="{{ route('types.index') }}" class="text-white hover:bg-white/20 px-4 py-2 rounded-xl font-medium text-sm transition-all duration-300">Master Suhu</a>
                    @if($pinEnabled)
                    <a href="{{ route('pin.edit') }}" class="text-white hover:bg-white/20 px-4 py-2 rounded-xl font-medium text-sm transition-all duration-300">Ganti PIN</a>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-white/70 hover:text-white hover:bg-white/20 px-4 py-2 rounded-xl font-medium text-sm transition-all duration-300">Keluar</button>
                    </form>
                    @endif
                </div>

                <!-- Hamburger Button -->
                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="text-white hover:text-gray-300 focus:outline-none p-2 rounded-md bg-white/5 border border-white/10">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" class="md:hidden bg-[#0f172a]/95 backdrop-blur-xl border-t border-white/10" style="display: none;" x-transition>
            <div class="px-4 pt-2 pb-4 space-y-2 shadow-xl">
                <a href="{{ route('labels.create') }}" class="text-white block px-4 py-3 rounded-lg text-base font-medium bg-white/5 hover:bg-white/10 border border-white/5">Cetak Label</a>
                <a href="{{ route('products.index') }}" class="text-white block px-4 py-3 rounded-lg text-base font-medium bg-white/5 hover:bg-white/10 border border-white/5">Master Barang</a>
                <a href="{{ route('types.index') }}" class="text-white block px-4 py-3 rounded-lg text-base font-medium bg-white/5 hover:bg-white/10 border border-white/5">Master Suhu</a>
                @if($pinEnabled)
                <a href="{{ route('pin.edit') }}" class="text-white block px-4 py-3 rounded-lg text-base font-medium bg-white/5 hover:bg-white/10 border border-white/5">Ganti PIN</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-white/70 block w-full text-left px-4 py-3 rounded-lg text-base font-medium bg-white/5 hover:bg-white/10 border border-white/5">Keluar</button>
                </form>
                @endif
            </div>
        </div>
    </nav>
    
    <main class="grow w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>
    
    <footer class="py-6 mt-auto border-t border-white/10 bg-[#0f172a]/50 backdrop-blur-xs">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center text-white/70 text-sm font-light">
            <div class="mb-3 md:mb-0 font-medium">
                &copy; {{ date('Y') }} PT Berkah Marsha Sejahtera
            </div>
            <div class="flex items-center gap-2">
                Crafted With <span class="text-lg">☕</span> <a href="https://saepullrock.tech" target="_blank" class="font-bold text-pink-400 hover:text-pink-300 transition-colors drop-shadow-md">IDNX</a>
            </div>
        </div>
    </footer>
    
    @yield('scripts')
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
