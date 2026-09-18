<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Slug;
use RuntimeException;

/**
 * products model. Column names exactly per database/schema.sql.
 * Images (product_images) are out of scope for M6-D1.
 */
final class Product extends Model
{
    public const MAX_JSON_ROWS = 30;
    public const MAX_JSON_LABEL = 200;

    public static function table(): string
    {
        return 'products';
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::select(
            'SELECT * FROM `products` ORDER BY `sort_order` ASC, `name` ASC',
        );
    }

    /** @return array<string, mixed>|array{} */
    public static function find(int $id): array
    {
        return Database::selectOne(
            'SELECT * FROM `products` WHERE `id` = ? LIMIT 1',
            [$id],
        );
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `products` WHERE `slug` = ?',
                [$slug],
            );
        } else {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `products` WHERE `slug` = ? AND `id` != ?',
                [$slug, $excludeId],
            );
        }

        return ((int) ($row['c'] ?? 0)) > 0;
    }

    /**
     * Sanitises a repeater array from the form into a JSON string or null.
     * Accepts only ['label' => string, 'value' => string] pairs; drops other
     * keys, empty rows, over-length labels and over-count rows. Plain strings
     * (raw JSON input) are rejected by type, never parsed.
     */
    public static function encodePairs(mixed $input): ?string
    {
        if ($input === null || $input === '' || $input === []) {
            return null;
        }
        if (!is_array($input)) {
            throw new RuntimeException('Geçersiz veri biçimi.');
        }
        if (count($input) > self::MAX_JSON_ROWS) {
            throw new RuntimeException('En fazla ' . self::MAX_JSON_ROWS . ' satır eklenebilir.');
        }

        $pairs = [];
        foreach ($input as $row) {
            if (!is_array($row)) {
                throw new RuntimeException('Geçersiz satır biçimi.');
            }
            $label = isset($row['label']) && is_string($row['label']) ? trim($row['label']) : '';
            $value = isset($row['value']) && is_string($row['value']) ? trim($row['value']) : '';
            if ($label === '' && $value === '') {
                continue;
            }
            if (mb_strlen($label) > self::MAX_JSON_LABEL) {
                throw new RuntimeException('Etiket en fazla ' . self::MAX_JSON_LABEL . ' karakter olabilir.');
            }
            if (mb_strlen($value) > self::MAX_JSON_LABEL) {
                throw new RuntimeException('Değer en fazla ' . self::MAX_JSON_LABEL . ' karakter olabilir.');
            }
            $pairs[] = ['label' => $label, 'value' => $value];
        }

        return $pairs === [] ? null : (string) json_encode($pairs, JSON_UNESCAPED_UNICODE);
    }

    /** @return array<int, array{label:string, value:string}> */
    public static function decodePairs(mixed $json): array
    {
        if (!is_string($json) || $json === '') {
            return [];
        }
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $rows = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $rows[] = [
                'label' => is_string($row['label'] ?? null) ? $row['label'] : '',
                'value' => is_string($row['value'] ?? null) ? $row['value'] : '',
            ];
        }

        return $rows;
    }

    /** Returns the new id. specs/variants arrive as pre-encoded JSON or null. */
    public static function create(array $data): int
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new RuntimeException('Ürün adı zorunludur.');
        }

        $slug = Slug::generate($name);
        if ($slug === '') {
            throw new RuntimeException('Ürün slug üretilemedi.');
        }
        if (self::slugExists($slug)) {
            throw new RuntimeException('Aynı slug zaten var: ' . $slug);
        }

        $code = trim((string) ($data['code'] ?? ''));
        if ($code !== '' && self::codeExists($code)) {
            throw new RuntimeException('Aynı ürün kodu zaten var: ' . $code);
        }

        return parent::create([
            'category_id' => (int) ($data['category_id'] ?? 0),
            'slug' => $slug,
            'code' => $code === '' ? null : $code,
            'name' => $name,
            'short_description' => $data['short_description'] ?? null,
            'long_description' => $data['long_description'] ?? null,
            'specs_json' => $data['specs_json'] ?? null,
            'variants_json' => $data['variants_json'] ?? null,
            'is_active' => (int) ($data['is_active'] ?? 1),
            'is_featured' => (int) ($data['is_featured'] ?? 0),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    public static function codeExists(string $code, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `products` WHERE `code` = ?',
                [$code],
            );
        } else {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `products` WHERE `code` = ? AND `id` != ?',
                [$code, $excludeId],
            );
        }

        return ((int) ($row['c'] ?? 0)) > 0;
    }

    /** Updates mutable columns; re-slugs on name change; returns affected rows. */
    public static function update(int $id, array $data): int
    {
        $current = self::find($id);
        if ($current === []) {
            throw new RuntimeException('Ürün bulunamadı.');
        }

        $patch = [];
        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw new RuntimeException('Ürün adı zorunludur.');
            }
            $patch['name'] = $name;
            $slug = Slug::generate($name);
            if ($slug === '') {
                throw new RuntimeException('Ürün slug üretilemedi.');
            }
            if ($slug !== ($current['slug'] ?? null) && self::slugExists($slug, $id)) {
                throw new RuntimeException('Aynı slug zaten var: ' . $slug);
            }
            $patch['slug'] = $slug;
        }

        if (array_key_exists('code', $data)) {
            $code = trim((string) $data['code']);
            if ($code !== '' && self::codeExists($code, $id)) {
                throw new RuntimeException('Aynı ürün kodu zaten var: ' . $code);
            }
            $patch['code'] = $code === '' ? null : $code;
        }

        if (array_key_exists('category_id', $data)) {
            $patch['category_id'] = (int) $data['category_id'];
        }

        foreach (['short_description', 'long_description', 'specs_json', 'variants_json'] as $field) {
            if (array_key_exists($field, $data)) {
                $patch[$field] = $data[$field];
            }
        }

        foreach (['is_active', 'is_featured', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $patch[$field] = (int) $data[$field];
            }
        }

        if ($patch === []) {
            return 0;
        }

        return parent::updateById($id, $patch);
    }

    /**
     * FK RESTRICT guard on categories is not relevant here; products has no
     * dependent rows in this milestone (product_images arrives in M6-D2 and is
     * removed by ON DELETE CASCADE at that point).
     */
    public static function delete(int $id): int
    {
        return parent::deleteById($id);
    }
}
