<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$webRoutes = require __DIR__ . '/../routes/web.php';
$apiRoutes = require __DIR__ . '/../routes/api.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

function sendJson(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Match a route list like:
 * ['GET','/api/products/{id}', [Controller::class,'show']]
 *
 * Returns: [handler, params] or null
 */
function matchRoute(array $routes, string $method, string $path): ?array
{
    foreach ($routes as $r) {
        if (!is_array($r) || count($r) !== 3) continue;

        [$m, $pattern, $handler] = $r;
        if ($m !== $method) continue;

        // Convert /api/products/{id} to regex and capture numeric params
        $regex = '#^' . preg_replace('#\{[a-zA-Z_][a-zA-Z0-9_]*\}#', '([0-9]+)', $pattern) . '$#';

        if (preg_match($regex, $path, $matches)) {
            array_shift($matches); // remove full match
            return [$handler, $matches];
        }
    }

    return null;
}

// -----------------------------
// API dispatch (JSON)
// -----------------------------
$apiMatch = matchRoute($apiRoutes, $method, $path);
if ($apiMatch !== null) {
    [$handler, $params] = $apiMatch;
    [$class, $action]   = $handler;

    try {
        $controller = new $class();

        // Cast numeric route params like "5" -> 5 (fixes strict types)
        $params = array_map(
            static fn($v) => (is_string($v) && ctype_digit($v)) ? (int)$v : $v,
            $params
        );

        /** @var array{0:int,1:array} $result */
        $result = $controller->$action(...$params);

        [$status, $payload] = $result;
        sendJson($status, $payload);
    } catch (Throwable $e) {
        sendJson(500, [
            'error'  => 'Server error',
            'detail' => $e->getMessage(),
        ]);
    }
}

// -----------------------------
// Web dispatch (HTML)
// -----------------------------
$pathKey = $path === '' ? '/' : $path;

if (isset($webRoutes[$pathKey])) {
    [$class, $action] = $webRoutes[$pathKey];

    try {
        echo (new $class())->$action();
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        echo 'Server error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit;
    }
}

http_response_code(404);
echo '404 – Route not found';
