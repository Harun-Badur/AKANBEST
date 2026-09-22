<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use RuntimeException;

/**
 * product_images model. Column names exactly per database/schema.sql.
 * Every image lookup is scoped to its product id (IDOR guard).
 */
final class ProductImage extends Model
{
    public static function table(): string
    {
        return 'product_images';
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return Database::select(
            'SELECT * FROM `product_images` ORDER BY `product_id` ASC, `sort_order` ASC, `id` ASC',
        );
    }

    /** @return array<int, array<string, mixed>> */
    public static function allForProduct(int $productId): array
    {
        return Database::select(
            'SELECT * FROM `product_images` WHERE `product_id` = ? ORDER BY `sort_order` ASC, `id` ASC',
            [$productId],
        );
    }

    /** Ownership-scoped lookup: returns [] when the image belongs elsewhere. */
    public static function findOwned(int $imageId, int $productId): array
    {
        return Database::selectOne(
            'SELECT * FROM `product_images` WHERE `id` = ? AND `product_id` = ? LIMIT 1',
            [$imageId, $productId],
        );
    }

    public static function countForProduct(int $productId): int
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS `c` FROM `product_images` WHERE `product_id` = ?',
            [$productId],
        );

        return (int) ($row['c'] ?? 0);
    }

    /** Expects the stored relative path from ImageUploader (never user input). */
    public static function create(array $data): int
    {
        $productId = (int) ($data['product_id'] ?? 0);
        $path = (string) ($data['image_path'] ?? '');
        if ($productId < 1 || $path === '') {
            throw new RuntimeException('Görsel kaydı eksik bilgi içeriyor.');
        }

        return parent::create([
            'product_id' => $productId,
            'image_path' => $path,
            'alt_text' => isset($data['alt_text']) && $data['alt_text'] !== '' ? (string) $data['alt_text'] : null,
            'is_primary' => (int) ($data['is_primary'] ?? 0),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
    }

    /**
     * Deletes the row and its file. File removal stays inside the uploads dir;
     * paths outside it are refused.
     */
    public static function deleteWithFile(int $imageId, int $productId): int
    {
        $row = self::findOwned($imageId, $productId);
        if ($row === []) {
            return 0;
        }

        $removed = parent::deleteById($imageId);
        $path = (string) ($row['image_path'] ?? '');
        if (str_starts_with($path, 'uploads/products/') && !str_contains($path, '..')) {
            $absolute = BASE_PATH . '/public/' . $path;
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }

        return $removed;
    }

    /** Sets exactly one primary per product, inside a transaction. */
    public static function setPrimary(int $imageId, int $productId): int
    {
        if (self::findOwned($imageId, $productId) === []) {
            throw new RuntimeException('Görsel bulunamadı.');
        }

        return (int) Database::transaction(static function ($pdo) use ($imageId, $productId): int {
            $clear = $pdo->prepare('UPDATE `product_images` SET `is_primary` = 0 WHERE `product_id` = ?');
            $clear->execute([$productId]);
            $set = $pdo->prepare('UPDATE `product_images` SET `is_primary` = 1 WHERE `id` = ? AND `product_id` = ?');
            $set->execute([$imageId, $productId]);

            return $set->rowCount();
        });
    }

    /**
     * Bulk alt_text/sort_order update. Every row is ownership-checked first,
     * inside one transaction. The outer product id is authoritative; row
     * payloads never carry their own product id (IDOR guard).
     *
     * @param array<int, array{imageId: int, alt: string, sort: int}> $rows
     */
    public static function updateMany(int $productId, array $rows): int
    {
        foreach ($rows as $row) {
            if (self::findOwned((int) $row['imageId'], $productId) === []) {
                throw new RuntimeException('Yetkisiz görsel kaydı: ' . $row['imageId']);
            }
        }

        return (int) Database::transaction(static function ($pdo) use ($rows, $productId): int {
            $statement = $pdo->prepare(
                'UPDATE `product_images` SET `alt_text` = ?, `sort_order` = ? WHERE `id` = ? AND `product_id` = ?',
            );
            $total = 0;
            foreach ($rows as $row) {
                $statement->execute([
                    mb_substr((string) $row['alt'], 0, 255),
                    (int) $row['sort'],
                    (int) $row['imageId'],
                    $productId,
                ]);
                $total += $statement->rowCount();
            }

            return $total;
        });
    }
}
