<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Quote extends Model
{
    public static function table(): string
    {
        return 'quotes';
    }

    /** Caller owns the transaction so the request and its item remain atomic. */
    public static function createWithItem(array $data, array $product, int $quantity): int
    {
        $id = self::create($data);
        $now = gmdate('Y-m-d H:i:s');
        Database::insert('quote_items', [
            'quote_id' => $id,
            'product_id' => (int) $product['id'],
            'product_snapshot_name' => (string) $product['name'],
            'product_snapshot_code' => $product['code'] ?? null,
            'quantity' => $quantity,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $id;
    }
}
