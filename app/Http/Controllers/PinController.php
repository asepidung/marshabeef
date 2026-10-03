<?php

namespace App\Http\Controllers;

use App\Support\AccessPin;
use App\Support\PinSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

class PinController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function show(Request $request): View|RedirectResponse
    {
        $configured = AccessPin::isConfigured();
        $production = app()->environment('production');

        if ($configured && PinSession::active($request->session())) {
            return redirect()->route('labels.create');
        }

        return view('welcome', [
            'pinEnabled' => $configured || $production,
            'misconfigured' => ! $configured && $production,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $key = 'pin-login|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return redirect()->route('login')->withErrors(['pin' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."]);
        }

        $request->validate(['pin' => 'required|string|max:64']);

        if (! AccessPin::isConfigured()) {
            return redirect()->route('login')->withErrors(['pin' => 'PIN belum diatur. Isi APP_PIN di file .env.']);
        }

        if (! AccessPin::verify((string) $request->input('pin'))) {
            RateLimiter::hit($key, 60);

            return redirect()->route('login')->withErrors(['pin' => 'PIN salah.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        PinSession::start($request->session());

        return redirect()->route('labels.create');
    }

    public function lock(Request $request): RedirectResponse
    {
        PinSession::end($request->session());

        return redirect()->route('login')->with('status', 'Terkunci karena tidak ada aktivitas. Masukkan PIN kembali.');
    }

    public function keepalive(): Response
    {
        return response()->noContent();
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
