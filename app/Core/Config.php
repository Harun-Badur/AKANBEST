<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class Config
{
    private static array $values = [];

    public static function load(string $path): void
    {
        $values = require $path;
        if (!is_array($values)) {
            throw new RuntimeException('Configuration must return an array.');
        }
        self::$values = $values;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$values;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}
