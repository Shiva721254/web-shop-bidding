<?php
declare(strict_types=1);

use App\Controllers\Api\ProductController;

return [
    // Products (CMS)
    ['GET',    '/api/products',           [ProductController::class, 'index']],
    ['GET',    '/api/products/{id}',      [ProductController::class, 'show']],
    ['POST',   '/api/products',           [ProductController::class, 'store']],
    ['PUT',    '/api/products/{id}',      [ProductController::class, 'update']],
    ['DELETE', '/api/products/{id}',      [ProductController::class, 'destroy']],
];
