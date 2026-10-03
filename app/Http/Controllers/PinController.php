<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PinController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $pin = (string) config('access.pin');

        if ($pin === '' && ! app()->environment('production')) {
            return redirect()->route('labels.create');
        }

        return view('auth.login', ['misconfigured' => $pin === '']);
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate(['pin' => 'required|string|max:64']);

        $pin = (string) config('access.pin');

        if ($pin === '') {
            return back()->withErrors(['pin' => 'PIN belum diatur. Isi APP_PIN di file .env.']);
        }

        if (! hash_equals($pin, (string) $request->input('pin'))) {
            return back()->withErrors(['pin' => 'PIN salah.']);
        }

        $request->session()->regenerate();
        $request->session()->put('pin_ok', true);

        return redirect()->intended(route('labels.create'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
