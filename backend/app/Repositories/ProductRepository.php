<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;

final class ProductRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function allActive(): array
    {
        $pdo = Database::pdo();

        $sql = "
            SELECT
                id,
                title,
                type,
                price,
                starting_bid,
                auction_end_at
            FROM products
            WHERE is_active = 1
            ORDER BY created_at DESC
            LIMIT 50
        ";

        return $pdo->query($sql)->fetchAll();
    }
}
