<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Slug;
use RuntimeException;

/**
 * categories table model. Table name is a fixed developer identifier.
 * Max 2 display levels; deeper nodes are promoted to root level.
 */
final class Category extends Model
{
    public static function table(): string
    {
        return 'categories';
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::select(
            'SELECT * FROM `categories` ORDER BY `sort_order` ASC, `name` ASC',
        );
    }

    /** @return array<string, mixed>|array{} */
    public static function find(int $id): array
    {
        return Database::selectOne(
            'SELECT * FROM `categories` WHERE `id` = ? LIMIT 1',
            [$id],
        );
    }

    /** @return array<string, mixed>|array{} */
    public static function findBySlug(string $slug, ?int $excludeId = null): array
    {
        if ($excludeId === null) {
            return Database::selectOne(
                'SELECT * FROM `categories` WHERE `slug` = ? LIMIT 1',
                [$slug],
            );
        }

        return Database::selectOne(
            'SELECT * FROM `categories` WHERE `slug` = ? AND `id` != ? LIMIT 1',
            [$slug, $excludeId],
        );
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `categories` WHERE `slug` = ?',
                [$slug],
            );
        } else {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `categories` WHERE `slug` = ? AND `id` != ?',
                [$slug, $excludeId],
            );
        }

        return ((int) ($row['c'] ?? 0)) > 0;
    }

    /**
     * Groups rows into roots + one child level. Grandchildren are promoted
     * to root level so the dropdown never exceeds 2 display levels.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public static function tree(array $rows): array
    {
        $byId = [];
        foreach ($rows as $row) {
            $row['children'] = [];
            $byId[(int) $row['id']] = $row;
        }

        $roots = [];
        foreach ($byId as $id => $row) {
            $parentId = $row['parent_id'] === null ? null : (int) $row['parent_id'];
            if ($parentId === null || $parentId === $id || !isset($byId[$parentId])) {
                $roots[] = &$byId[$id];
                continue;
            }
            if ($byId[$parentId]['parent_id'] !== null) {
                $roots[] = &$byId[$id];
                continue;
            }
            $byId[$parentId]['children'][] = &$byId[$id];
        }
        unset($row);

        return $roots;
    }

    public static function hasProducts(int $id): bool
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS `c` FROM `products` WHERE `category_id` = ?',
            [$id],
        );

        return ((int) ($row['c'] ?? 0)) > 0;
    }

    /**
     * Auto-generates the slug from name; throws on duplicate slug.
     * Accepts only known columns; database timestamps are applied by the base.
     */
    public static function create(array $data): int
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('Category name is required.');
        }

        $slug = Slug::generate($name);
        if ($slug === '') {
            throw new RuntimeException('Category slug could not be generated.');
        }
        if (self::slugExists($slug)) {
            throw new RuntimeException('Duplicate category slug: ' . $slug);
        }

        $allowed = [
            'name' => $name,
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (int) ($data['is_active'] ?? 1),
        ];

        return parent::create(['slug' => $slug] + $allowed);
    }

    /**
     * Updates name (re-slugs + duplicate check), parent (no self-parent),
     * and scalar fields. Returns affected row count.
     */
    public static function update(int $id, array $data): int
    {
        $current = self::find($id);
        if ($current === []) {
            throw new RuntimeException('Category not found.');
        }

        $patch = [];
        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw new RuntimeException('Category name is required.');
            }
            $patch['name'] = $name;
            $slug = Slug::generate($name);
            if ($slug === '') {
                throw new RuntimeException('Category slug could not be generated.');
            }
            if ($slug !== ($current['slug'] ?? null) && self::slugExists($slug, $id)) {
                throw new RuntimeException('Duplicate category slug: ' . $slug);
            }
            $patch['slug'] = $slug;
        }

        if (array_key_exists('parent_id', $data)) {
            $parentId = $data['parent_id'] === null ? null : (int) $data['parent_id'];
            if ($parentId === $id) {
                throw new RuntimeException('A category cannot be its own parent.');
            }
            $patch['parent_id'] = $parentId;
        }

        foreach (['description', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $patch[$field] = $field === 'description' ? $data[$field] : (int) $data[$field];
            }
        }

        if ($patch === []) {
            return 0;
        }

        return parent::updateById($id, $patch);
    }

    /**
     * FK RESTRICT guard: returns false when products reference the category.
     * Returns affected row count (1) on successful delete.
     */
    public static function delete(int $id): int|false
    {
        if (self::hasProducts($id)) {
            return false;
        }

        return parent::deleteById($id);
    }

}
