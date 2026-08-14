<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->safeLoad();

header('Content-Type: application/json');

echo json_encode([
    'app' => $_ENV['APP_NAME'] ?? 'basic-backend',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'status' => 'ok',
]);
