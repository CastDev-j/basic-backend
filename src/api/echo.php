<?php

declare(strict_types=1);

header('Content-Type: application/json');

$data = json_decode((string) file_get_contents('php://input'), true) ?? [];

echo json_encode(['received' => $data]);
