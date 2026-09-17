<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Minimal abstract row-table base. Concrete models arrive in M4+.
 * All helpers return arrays/ints; no magic, no eager loading.
 * The table name is a fixed developer identifier, never user input.
 */
abstract class Model
{
    abstract public static function table(): string;

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::select('SELECT * FROM `' . static::table() . '`');
    }

    /** @return array<string, mixed>|array{} */
    public static function find(int $id): array
    {
        return Database::selectOne(
            'SELECT * FROM `' . static::table() . '` WHERE `id` = ? LIMIT 1',
            [$id],
        );
    }

    /**
     * Only meaningful for tables that expose a unique `slug` column.
     *
     * @return array<string, mixed>|array{}
     */
    public static function findBySlug(string $slug): array
    {
        return Database::selectOne(
            'SELECT * FROM `' . static::table() . '` WHERE `slug` = ? LIMIT 1',
            [$slug],
        );
    }

    /** Inserts with created_at/updated_at applied; returns the new id. */
    public static function create(array $data): int
    {
        $now = gmdate('Y-m-d H:i:s');
        $data += ['created_at' => $now, 'updated_at' => $now];

        return Database::insert(static::table(), $data);
    }

    public static function updateById(int $id, array $data): int
    {
        $data['updated_at'] = gmdate('Y-m-d H:i:s');

        return Database::update(static::table(), $data, '`id` = ?', [$id]);
    }

    public static function deleteById(int $id): int
    {
        return Database::delete(static::table(), '`id` = ?', [$id]);
    }
}
