<?php

declare(strict_types=1);

namespace App\Core;

use PDOException;
use RuntimeException;
use Throwable;

final class Auth
{
    /** password_hash with the engine default (bcrypt). */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * DB-backed admin login. FAIL-CLOSED: any DB error yields false with a log
     * entry; successful logins are only possible with a reachable database.
     */
    public static function attempt(string $email, string $password): bool
    {
        $key = 'login:' . strtolower(trim($email));

        if (Throttle::blocked($key)) {
            error_log('[auth] throttle blocked for ' . $key);

            return false;
        }

        try {
            $admin = Database::selectOne(
                'SELECT `id`, `name`, `password` FROM `admins` WHERE `email` = ? LIMIT 1',
                [strtolower(trim($email))],
            );
        } catch (Throwable $exception) {
            // Fail-closed: unreachable DB, missing config, or any PDO error
            // must never authenticate. Log and deny.
            error_log('[auth] fail-closed ' . $exception::class . ': ' . $exception->getMessage());
            Throttle::hit($key);

            return false;
        }

        if ($admin === [] || !password_verify($password, (string) ($admin['password'] ?? ''))) {
            Throttle::hit($key);

            return false;
        }

        Throttle::clear($key);
        Session::start();
        Session::regenerate();
        Session::set('admin_id', (int) $admin['id']);
        Session::set('admin_name', (string) $admin['name']);

        return true;
    }

    public static function check(): bool
    {
        Session::start();

        return Session::has('admin_id');
    }

    /** @return array{admin_id:int, admin_name:string}|null */
    public static function user(): ?array
    {
        Session::start();
        if (!Session::has('admin_id')) {
            return null;
        }

        return [
            'admin_id' => (int) Session::get('admin_id'),
            'admin_name' => (string) Session::get('admin_name'),
        ];
    }

    public static function logout(): void
    {
        Session::start();
        Session::flush();
        Session::regenerate();
    }

    /** Admin guard: 302 to /admin/login with flash; null allows the handler. */
    public static function requireAdmin(): ?Response
    {
        if (self::check()) {
            return null;
        }

        Session::flash('guard_redirect', 'login_required');

        return Response::redirect('/admin/login', 302);
    }
}
