<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Repositories\ProductRepository;

final class ProductController
{
    public function index(): array
    {
        $repo = new ProductRepository();
        return [200, ['data' => $repo->allActive()]];
    }

    public function show(int $id): array
    {
        $repo = new ProductRepository();
        $p = $repo->find($id);
        if ($p === null) return [404, ['error' => 'Product not found']];
        return [200, ['data' => $p]];
    }

   public function store(): array
{
    $input = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($input)) return [400, ['error' => 'Invalid JSON']];

    [$ok, $errors, $payload] = $this->validateAndNormalize($input, null);
    if (!$ok) return [422, ['error' => 'Validation failed', 'fields' => $errors]];

    $repo = new ProductRepository();
    $created = $repo->create($payload);

    return [201, ['data' => $created]];
}


public function update(int $id): array
{
    $input = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($input)) return [400, ['error' => 'Invalid JSON']];

    $repo = new ProductRepository();
    $existing = $repo->find($id);
    if ($existing === null) return [404, ['error' => 'Product not found']];

    [$ok, $errors, $payload] = $this->validateAndNormalize($input, $existing);
    if (!$ok) return [422, ['error' => 'Validation failed', 'fields' => $errors]];

    $updated = $repo->update($id, $payload);
    return [200, ['data' => $updated]];
}



    public function destroy(int $id): array
    {
        $repo = new ProductRepository();
        $ok = $repo->delete($id);
        if (!$ok) return [404, ['error' => 'Product not found']];
        return [200, ['message' => 'Deleted']];
    }


    // helper methods for validation, etc. will go here in the future

private function validateAndNormalize(array $input, ?array $existing = null): array
{
    // Merge with existing for PUT so we can validate the final state
    $data = $existing ? array_merge($existing, $input) : $input;

    $errors = [];

    $title = trim((string)($data['title'] ?? ''));
    if ($title === '') {
        $errors['title'][] = 'Title is required.';
    }

    $type = (string)($data['type'] ?? '');
    if (!in_array($type, ['buy_now', 'auction'], true)) {
        $errors['type'][] = 'Type must be buy_now or auction.';
    }

    // Normalize optional text fields
    $description = array_key_exists('description', $data) ? (string)($data['description'] ?? '') : null;
    $imageUrl    = array_key_exists('image_url', $data) ? (string)($data['image_url'] ?? '') : null;

    // Normalize is_active
    $isActive = (int)($data['is_active'] ?? 1);
    if (!in_array($isActive, [0, 1], true)) {
        $errors['is_active'][] = 'is_active must be 0 or 1.';
    }

    // Type-specific validation
    $price        = $data['price'] ?? null;
    $startingBid  = $data['starting_bid'] ?? null;
    $auctionEndAt = $data['auction_end_at'] ?? null;

    if ($type === 'buy_now') {
        if ($price === null || $price === '') {
            $errors['price'][] = 'Price is required for buy_now products.';
        } elseif (!is_numeric($price) || (float)$price <= 0) {
            $errors['price'][] = 'Price must be a number greater than 0.';
        }

        // Force auction fields to null
        $startingBid = null;
        $auctionEndAt = null;
    }

    if ($type === 'auction') {
        if ($startingBid === null || $startingBid === '') {
            $errors['starting_bid'][] = 'Starting bid is required for auction products.';
        } elseif (!is_numeric($startingBid) || (float)$startingBid <= 0) {
            $errors['starting_bid'][] = 'Starting bid must be a number greater than 0.';
        }

        if ($auctionEndAt === null || trim((string)$auctionEndAt) === '') {
            $errors['auction_end_at'][] = 'auction_end_at is required for auction products.';
        } else {
            // Accept "YYYY-MM-DD HH:MM:SS"
            $dt = \DateTime::createFromFormat('Y-m-d H:i:s', (string)$auctionEndAt);
            if (!$dt) {
                $errors['auction_end_at'][] = 'auction_end_at must be in format YYYY-MM-DD HH:MM:SS.';
            }
        }

        // Force buy_now field to null
        $price = null;
    }

    if (!empty($errors)) {
        return [false, $errors, []];
    }

    // Final normalized payload for repository
    $normalized = [
        'title'         => $title,
        'description'   => $description !== null ? ($description === '' ? null : $description) : null,
        'image_url'     => $imageUrl !== null ? ($imageUrl === '' ? null : $imageUrl) : null,
        'type'          => $type,
        'price'         => $price === null ? null : (string)number_format((float)$price, 2, '.', ''),
        'starting_bid'  => $startingBid === null ? null : (string)number_format((float)$startingBid, 2, '.', ''),
        'auction_end_at'=> $auctionEndAt === null ? null : (string)$auctionEndAt,
        'is_active'     => $isActive,
    ];

    return [true, [], $normalized];
}




}
