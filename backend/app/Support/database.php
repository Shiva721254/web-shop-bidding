<?php
declare(strict_types=1);

namespace App\Support;

use PDO;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = 'db';          // docker service name
        $db   = 'webshop';
        $user = 'developer';
        $pass = 'secret123';
        $port = 3306;

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        self::$pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }
}
