<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/** client_references model. Column names exactly per database/schema.sql. */
final class ClientReference extends Model
{
    public static function table(): string
    {
        return 'client_references';
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::select(
            'SELECT * FROM `client_references` ORDER BY `sort_order` ASC, `company_name` ASC',
        );
    }

    /** @return array<string, mixed>|array{} */
    public static function find(int $id): array
    {
        return Database::selectOne(
            'SELECT * FROM `client_references` WHERE `id` = ? LIMIT 1',
            [$id],
        );
    }

    /** Returns the new id. Pass logo_path=null when no upload. */
    public static function create(array $data): int
    {
        $allowed = [
            'company_name' => trim((string) ($data['company_name'] ?? '')),
            'logo_path' => $data['logo_path'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (int) ($data['is_active'] ?? 1),
        ];

        return parent::create($allowed);
    }

    public static function updateById(int $id, array $data): int
    {
        $patch = [];
        if (array_key_exists('company_name', $data)) {
            $patch['company_name'] = trim((string) $data['company_name']);
        }
        foreach (['logo_path', 'website_url'] as $field) {
            if (array_key_exists($field, $data)) {
                $patch[$field] = $data[$field];
            }
        }
        foreach (['sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $patch[$field] = (int) $data[$field];
            }
        }
        if ($patch === []) {
            return 0;
        }

        return parent::updateById($id, $patch);
    }
}
