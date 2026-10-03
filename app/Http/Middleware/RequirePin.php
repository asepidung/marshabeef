<?php

namespace App\Http\Middleware;

use App\Support\PinSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ((string) config('access.pin') === '' && ! app()->environment('production')) {
            return $next($request);
        }

        $session = $request->session();

        if (PinSession::active($session)) {
            PinSession::touch($session);

            return $next($request);
        }

        $wasLoggedIn = $session->get('pin_ok') === true;
        PinSession::end($session);

        $redirect = redirect()->route('login');

        return $wasLoggedIn
            ? $redirect->with('status', 'Sesi berakhir karena tidak ada aktivitas. Masukkan PIN kembali.')
            : $redirect;
    }
}
