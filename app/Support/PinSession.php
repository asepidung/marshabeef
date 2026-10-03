<?php

namespace App\Support;

use Illuminate\Contracts\Session\Session;

class PinSession
{
    public static function idleSeconds(): int
    {
        return max(1, (int) config('access.idle_minutes')) * 60;
    }

    public static function active(Session $session): bool
    {
        if ($session->get('pin_ok') !== true) {
            return false;
        }

        return now()->timestamp - (int) $session->get('last_activity', 0) <= self::idleSeconds();
    }

    public static function start(Session $session): void
    {
        $session->put('pin_ok', true);
        self::touch($session);
    }

    public static function touch(Session $session): void
    {
        $session->put('last_activity', now()->timestamp);
    }

    public static function end(Session $session): void
    {
        $session->forget(['pin_ok', 'last_activity']);
    }
}
