<?php

declare(strict_types=1);

namespace Src\Db;

use Dotenv\Dotenv;
use PDO;

Dotenv::createImmutable(__DIR__ . '/../../')->safeLoad();

final class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $url = parse_url($_ENV['DATABASE_URL'] ?? '');

            if (!$url || !isset($url['path'])) {
                throw new \RuntimeException('DATABASE_URL inválida o no definida');
            }

            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $url['host'] ?? 'db',
                $url['port'] ?? 5432,
                ltrim($url['path'], '/')
            );

            self::$pdo = new PDO(
                $dsn,
                $url['user'] ?? 'basic_backend',
                $url['pass'] ?? 'secret',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }

        return self::$pdo;
    }
}
