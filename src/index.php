<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Src\Logger;
use Src\Services\UserService;
use Src\Services\ProductService;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

header('Content-Type: application/json');

Logger::init(__DIR__ . '/logs/app.log');

$userService = new UserService();
$productService = new ProductService();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
$segments = array_values(array_filter(explode('/', $path)));
$resource = $segments[0] ?? '';
$id = isset($segments[1]) && $segments[1] !== '' ? $segments[1] : null;
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $dispatched = match ($resource) {
        'users' => handleResource($method, $id, $input, $userService),
        'products' => handleResource($method, $id, $input, $productService),
        default => null,
    };

    if ($dispatched === null) {
        logRequest(404, $method, $path, 'Ruta no encontrada');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Ruta no encontrada']);
        exit;
    }

    [$status, $result] = $dispatched;
    $message = $result['message'] ?? json_encode($result);
    logRequest($status, $method, $path, $message);

    http_response_code($status);
    echo json_encode($result);
} catch (\Throwable $e) {
    Logger::error("{$method} {$path} 500 - " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleResource(string $method, ?string $id, array $input, object $service): ?array
{
    switch ($method) {
        case 'GET':
            $result = $id !== null
                ? $service->getById($id)
                : $service->getAll(
                    max(1, (int) ($_GET['page'] ?? 1)),
                    min(100, max(1, (int) ($_GET['per_page'] ?? 10)))
                );

            return [($result['success'] ?? false) ? 200 : 400, $result];

        case 'POST':
            $result = $service->create($input);

            return [($result['success'] ?? false) ? 201 : 400, $result];

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                throw new \InvalidArgumentException('ID requerido en la URL');
            }
            $result = $service->update($id, $input);

            return [($result['success'] ?? false) ? 200 : 400, $result];

        case 'DELETE':
            if ($id === null) {
                throw new \InvalidArgumentException('ID requerido en la URL');
            }
            $result = $service->delete($id);

            return [($result['success'] ?? false) ? 200 : 400, $result];

        default:
            return [405, ['success' => false, 'message' => 'Método no permitido']];
    }
}

function logRequest(int $status, string $method, string $path, string $message): void
{
    $line = "{$method} {$path} {$status} - {$message}";

    if ($status >= 500) {
        Logger::error($line);
    } elseif ($status >= 400) {
        Logger::warning($line);
    } else {
        Logger::success($line);
    }
}
