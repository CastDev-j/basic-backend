<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Src\Services\UserService;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

header('Content-Type: application/json');

$service = new UserService();
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/');
$segments = array_values(array_filter(explode('/', $path)));
$id = isset($segments[1]) && $segments[1] !== '' ? $segments[1] : null;
$input = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    switch ($method) {
        case 'GET':
            if ($id !== null) {
                $result = $service->getProfile($id);
                break;
            }

            $page = max(1, (int) ($_GET['page'] ?? 1));
            $perPage = min(100, max(1, (int) ($_GET['per_page'] ?? 10)));
            $result = $service->getAllUsers($page, $perPage);
            break;

        case 'POST':
            $result = $service->register($input['name'] ?? '', $input['email'] ?? '');
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                throw new \InvalidArgumentException('ID requerido en la URL');
            }
            $result = $service->updateUser($id, $input);
            break;

        case 'DELETE':
            if ($id === null) {
                throw new \InvalidArgumentException('ID requerido en la URL');
            }
            $result = $service->deleteUser($id);
            break;

        default:
            http_response_code(405);
            $result = ['success' => false, 'message' => 'Método no permitido'];
    }

    http_response_code(($result['success'] ?? false) ? 200 : 400);
    echo json_encode($result);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
