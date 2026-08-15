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

$request = ['method' => '?', 'path' => '/'];

try {
    $request = parseRequest();

    if (isBlockedPath($request['path'])) {
        respond($request, 404, ['success' => false, 'message' => 'Ruta bloqueada'], 'Ruta bloqueada');
    }

    $service = match ($request['resource']) {
        'users' => new UserService(),
        'products' => new ProductService(),
        default => null,
    };

    if ($service === null) {
        respond($request, 404, ['success' => false, 'message' => 'Ruta no encontrada'], 'Ruta no encontrada');
    }

    [$status, $result] = handleResource($request, $service);
    respond($request, $status, $result);
} catch (\Throwable $e) {
    Logger::error("{$request['method']} {$request['path']} 500 - " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function parseRequest(): array
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/');
    $segments = array_values(array_filter(explode('/', $path)));

    return [
        'method' => $method,
        'path' => $path === '' ? '/' : $path,
        'resource' => $segments[0] ?? '',
        'id' => isset($segments[1]) && $segments[1] !== '' ? $segments[1] : null,
        'input' => json_decode(file_get_contents('php://input'), true) ?? [],
        'page' => max(1, (int) ($_GET['page'] ?? 1)),
        'perPage' => min(100, max(1, (int) ($_GET['per_page'] ?? 10))),
    ];
}

function isBlockedPath(string $path): bool
{
    return str_contains($path, '..')
        || (bool) preg_match('/\.(php|log|env|json|md|lock)$/i', $path);
}

function handleResource(array $request, object $service): array
{
    $method = $request['method'];
    $id = $request['id'];
    $input = $request['input'];

    switch ($method) {
        case 'GET':
            $result = $id !== null
                ? $service->getById($id)
                : $service->getAll($request['page'], $request['perPage']);
            break;

        case 'POST':
            $result = $service->create($input);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                throw new \InvalidArgumentException('ID requerido en la URL');
            }
            $result = $service->update($id, $input);
            break;

        case 'DELETE':
            if ($id === null) {
                throw new \InvalidArgumentException('ID requerido en la URL');
            }
            $result = $service->delete($id);
            break;

        default:
            return [405, ['success' => false, 'message' => 'Método no permitido']];
    }

    $status = ($result['success'] ?? false) ? ($method === 'POST' ? 201 : 200) : 400;

    return [$status, $result];
}

function respond(array $request, int $status, array $body, ?string $logMessage = null): never
{
    $message = $logMessage ?? ($body['message'] ?? json_encode($body));
    logRequest($status, $request['method'], $request['path'], $message);

    http_response_code($status);
    echo json_encode($body);
    exit;
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
