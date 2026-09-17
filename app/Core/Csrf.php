<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        Session::start();
        $token = $_SESSION['_csrf_token'] ?? null;

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $_SESSION['_csrf_token'] = $token;
        }

        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    /** Timing-safe comparison against the session token; false on absence. */
    public static function verify(Request $request): bool
    {
        Session::start();
        $stored = $_SESSION['_csrf_token'] ?? null;
        $given = $request->post('_csrf');

        return is_string($stored) && is_string($given)
            && $stored !== '' && hash_equals($stored, $given);
    }
}
