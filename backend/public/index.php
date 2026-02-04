<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$routes = require __DIR__ . '/../routes/web.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

if (!isset($routes[$path])) {
    http_response_code(404);
    echo '404 – Route not found';
    exit;
}

[$controllerClass, $method] = $routes[$path];

$controller = new $controllerClass();
echo $controller->$method();
