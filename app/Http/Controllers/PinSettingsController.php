<?php

namespace App\Http\Controllers;

use App\Support\AccessPin;
use App\Support\PinSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class PinSettingsController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function edit(): View
    {
        return view('pin.edit', ['requiresCurrent' => AccessPin::isConfigured()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $key = 'pin-change|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors(['current_pin' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik."]);
        }

        $requiresCurrent = AccessPin::isConfigured();

        $rules = ['new_pin' => ['required', 'regex:/^\d{4,12}$/', 'confirmed']];
        if ($requiresCurrent) {
            $rules['current_pin'] = ['required', 'string', 'max:64'];
        }

        $request->validate($rules, [
            'current_pin.required' => 'Masukkan PIN saat ini.',
            'new_pin.required' => 'Masukkan PIN baru.',
            'new_pin.regex' => 'PIN baru harus berupa 4 sampai 12 angka.',
            'new_pin.confirmed' => 'Ulangi PIN baru dengan benar, kedua isian harus sama.',
        ]);

        $new = (string) $request->input('new_pin');

        if ($requiresCurrent) {
            if (! AccessPin::verify((string) $request->input('current_pin'))) {
                RateLimiter::hit($key, 60);

                return back()->withErrors(['current_pin' => 'PIN saat ini salah.']);
            }

            if (AccessPin::verify($new)) {
                return back()->withErrors(['new_pin' => 'PIN baru harus berbeda dari PIN saat ini.']);
            }
        }

        RateLimiter::clear($key);
        AccessPin::change($new);

        $request->session()->regenerate();
        PinSession::start($request->session());

        return redirect()->route('labels.create')->with('success', 'PIN berhasil diganti.');
    }
}
