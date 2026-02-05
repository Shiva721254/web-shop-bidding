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

public function find(int $id): ?array
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("
        SELECT id, title, description, image_url, type, price, starting_bid, auction_end_at, is_active
        FROM products
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

public function create(array $data): array
{
    // Minimal validation (we’ll harden next)
    $title = (string)($data['title'] ?? '');
    $type  = (string)($data['type'] ?? 'buy_now');

    $pdo = Database::pdo();
    $stmt = $pdo->prepare("
        INSERT INTO products (title, description, image_url, type, price, starting_bid, auction_end_at, is_active)
        VALUES (:title, :description, :image_url, :type, :price, :starting_bid, :auction_end_at, :is_active)
    ");

    $stmt->execute([
        'title'         => $title,
        'description'   => $data['description'] ?? null,
        'image_url'     => $data['image_url'] ?? null,
        'type'          => $type,
        'price'         => $data['price'] ?? null,
        'starting_bid'  => $data['starting_bid'] ?? null,
        'auction_end_at'=> $data['auction_end_at'] ?? null,
        'is_active'     => (int)($data['is_active'] ?? 1),
    ]);

    return $this->find((int)$pdo->lastInsertId()) ?? [];
}

public function update(int $id, array $data): ?array
{
    $existing = $this->find($id);
    if ($existing === null) return null;

    $payload = [
        'id'            => $id,
        'title'         => $data['title'] ?? $existing['title'],
        'description'   => $data['description'] ?? $existing['description'],
        'image_url'     => $data['image_url'] ?? $existing['image_url'],
        'type'          => $data['type'] ?? $existing['type'],
        'price'         => array_key_exists('price', $data) ? $data['price'] : $existing['price'],
        'starting_bid'  => array_key_exists('starting_bid', $data) ? $data['starting_bid'] : $existing['starting_bid'],
        'auction_end_at'=> array_key_exists('auction_end_at', $data) ? $data['auction_end_at'] : $existing['auction_end_at'],
        'is_active'     => array_key_exists('is_active', $data) ? (int)$data['is_active'] : (int)$existing['is_active'],
    ];

    $pdo = Database::pdo();
    $stmt = $pdo->prepare("
        UPDATE products
        SET title=:title,
            description=:description,
            image_url=:image_url,
            type=:type,
            price=:price,
            starting_bid=:starting_bid,
            auction_end_at=:auction_end_at,
            is_active=:is_active
        WHERE id=:id
    ");

    $stmt->execute($payload);

    return $this->find($id);
}


public function delete(int $id): bool
{
    $pdo = Database::pdo();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->rowCount() > 0;
}





}
