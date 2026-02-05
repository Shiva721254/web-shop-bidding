<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ProductRepository;

final class HomeController
{
    public function index(): string
    {
        $repo = new ProductRepository();
        $products = $repo->allActive();

        $html = "<h1>Products</h1>";

        if (empty($products)) {
            return $html . "<p>No products found.</p>";
        }

        $html .= "<ul>";
        foreach ($products as $p) {
            $title = htmlspecialchars((string)$p['title'], ENT_QUOTES, 'UTF-8');
            $type  = htmlspecialchars((string)$p['type'], ENT_QUOTES, 'UTF-8');

            if ($type === 'buy_now') {
                $price = number_format((float)$p['price'], 2);
                $html .= "<li><strong>{$title}</strong> (buy now) – €{$price}</li>";
            } else {
                $start = number_format((float)$p['starting_bid'], 2);
                $end   = htmlspecialchars((string)($p['auction_end_at'] ?? ''), ENT_QUOTES, 'UTF-8');
                $html .= "<li><strong>{$title}</strong> (auction) – start €{$start} – ends {$end}</li>";
            }
        }
        $html .= "</ul>";

        return $html;
    }
}
