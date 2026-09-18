<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Slug;
use RuntimeException;

/** pages model. Column names exactly per database/schema.sql. */
final class Page extends Model
{
    public static function table(): string
    {
        return 'pages';
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::select(
            'SELECT * FROM `pages` ORDER BY `title` ASC',
        );
    }

    /** @return array<string, mixed>|array{} */
    public static function find(int $id): array
    {
        return Database::selectOne(
            'SELECT * FROM `pages` WHERE `id` = ? LIMIT 1',
            [$id],
        );
    }

    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `pages` WHERE `slug` = ?',
                [$slug],
            );
        } else {
            $row = Database::selectOne(
                'SELECT COUNT(*) AS `c` FROM `pages` WHERE `slug` = ? AND `id` != ?',
                [$slug, $excludeId],
            );
        }

        return ((int) ($row['c'] ?? 0)) > 0;
    }

    /**
     * Auto-generates the slug from title; throws on duplicate slug.
     * Content is plain text (no WYSIWYG in M6).
     */
    public static function create(array $data): int
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new RuntimeException('Page title is required.');
        }

        $slug = Slug::generate($title);
        if ($slug === '') {
            throw new RuntimeException('Page slug could not be generated.');
        }
        if (self::slugExists($slug)) {
            throw new RuntimeException('Duplicate page slug: ' . $slug);
        }

        return parent::create([
            'slug' => $slug,
            'title' => $title,
            'content' => $data['content'] ?? null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
        ]);
    }

    public static function update(int $id, array $data): int
    {
        $current = self::find($id);
        if ($current === []) {
            throw new RuntimeException('Page not found.');
        }

        $patch = [];
        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);
            if ($title === '') {
                throw new RuntimeException('Page title is required.');
            }
            $patch['title'] = $title;
            $slug = Slug::generate($title);
            if ($slug === '') {
                throw new RuntimeException('Page slug could not be generated.');
            }
            if ($slug !== ($current['slug'] ?? null) && self::slugExists($slug, $id)) {
                throw new RuntimeException('Duplicate page slug: ' . $slug);
            }
            $patch['slug'] = $slug;
        }

        foreach (['content', 'seo_title', 'seo_description'] as $field) {
            if (array_key_exists($field, $data)) {
                $patch[$field] = $data[$field];
            }
        }

        if ($patch === []) {
            return 0;
        }

        return parent::updateById($id, $patch);
    }
}
