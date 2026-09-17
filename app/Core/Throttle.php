<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Throttle
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900; // 15 minutes

    public static function dir(): string
    {
        $dir = STORAGE_PATH . '/throttle';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Throttle directory is not writable.');
        }

        return $dir;
    }

    private static function file(string $key): string
    {
        $hash = hash('sha256', $key);

        return self::dir() . '/' . preg_replace('/[^A-Za-z0-9_-]/', '', substr($key, 0, 24)) . '-' . $hash . '.json';
    }

    /** @return array{count:int, first:int}|null */
    private static function read(string $key): ?array
    {
        $path = self::file($key);
        if (!is_file($path)) {
            return null;
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);

        return is_array($data) && isset($data['count'], $data['first']) ? $data : null;
    }

    private static function write(string $key, int $count, int $first): void
    {
        @file_put_contents(
            self::file($key),
            json_encode(['count' => $count, 'first' => $first], JSON_THROW_ON_ERROR),
        );
    }

    /** Records an attempt; returns current count within the window. */
    public static function hit(string $key): int
    {
        $now = time();
        $state = self::read($key);

        if ($state === null || $now - (int) $state['first'] >= self::WINDOW_SECONDS) {
            $state = ['count' => 0, 'first' => $now];
        }

        $state['count'] = (int) $state['count'] + 1;
        @file_put_contents(
            self::file($key),
            json_encode($state, JSON_THROW_ON_ERROR),
        );

        return (int) $state['count'];
    }

    /** True while attempts stay inside an unexpired window. */
    public static function blocked(string $key): bool
    {
        $state = self::read($key);

        if ($state === null) {
            return false;
        }
        if (time() - (int) $state['first'] >= self::WINDOW_SECONDS) {
            self::clear($key);

            return false;
        }

        return (int) $state['count'] >= self::MAX_ATTEMPTS;
    }

    public static function clear(string $key): void
    {
        $path = self::file($key);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
