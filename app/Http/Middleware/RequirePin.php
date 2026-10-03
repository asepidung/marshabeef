<?php

namespace App\Http\Middleware;

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

        if ($request->session()->get('pin_ok') === true) {
            return $next($request);
        }

        return redirect()->guest(route('login'));
    }
}
