<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

/**
 * settings key/value model with a FIXED whitelist.
 * No delete method by design: keys are permanent configuration rows.
 */
final class Setting extends Model
{
    public const WHITELIST_KEYS = [
        'whatsapp_number',
        'contact_email',
        'contact_phone',
        'address',
        'seo_title_default',
        'seo_description_default',
    ];

    public static function table(): string
    {
        return 'settings';
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::select(
            'SELECT `id`, `key`, `value` FROM `settings` ORDER BY `key` ASC',
        );
    }

    /** @return array<string, mixed>|array{} */
    public static function find(int $id): array
    {
        return Database::selectOne(
            'SELECT `id`, `key`, `value` FROM `settings` WHERE `id` = ? LIMIT 1',
            [$id],
        );
    }

    /** @return array<string, mixed>|array{} */
    public static function findByKey(string $key): array
    {
        return Database::selectOne(
            'SELECT `id`, `key`, `value` FROM `settings` WHERE `key` = ? LIMIT 1',
            [$key],
        );
    }

    /**
     * Updates the value of an existing whitelisted key; throws when the key is
     * unknown to the whitelist or missing from the table (no upsert, no delete).
     */
    public static function set(string $key, ?string $value): int
    {
        if (!in_array($key, self::WHITELIST_KEYS, true)) {
            throw new RuntimeException('Setting key is not in the whitelist: ' . $key);
        }

        $row = self::findByKey($key);
        if ($row === []) {
            throw new RuntimeException('Setting row does not exist for key: ' . $key);
        }

        return Database::update(
            static::table(),
            ['value' => $value],
            '`key` = ?',
            [$key],
        );
    }
}
