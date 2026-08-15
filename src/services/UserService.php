<?php

namespace Src\Services;

use Src\Repositories\UserRepository;
use Src\Models\User;

class UserService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function register(string $name, string $email): array
    {
        $existing = $this->repository->findByEmail($email);

        if ($existing) {
            return ['success' => false, 'message' => 'Email ya registrado'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido'];
        }

        if (strlen($name) < 3) {
            return ['success' => false, 'message' => 'Nombre muy corto'];
        }

        $user = new User($name, $email);
        $id = $this->repository->save($user);

        if ($id !== null) {
            return [
                'success' => true,
                'message' => 'Usuario registrado',
                'user' => (new User($name, $email, $id, $user->getCreatedAt()))->toArray(),
            ];
        }

        return ['success' => false, 'message' => 'Error al registrar'];
    }

    public function getProfile(string $id): array
    {
        $user = $this->repository->findById($id);

        return $user
            ? ['success' => true, 'user' => $user->toArray()]
            : ['success' => false, 'message' => 'Usuario no encontrado'];
    }

    public function getAllUsers(int $page = 1, int $perPage = 10): array
    {
        $total = $this->repository->count();
        $offset = ($page - 1) * $perPage;

        return [
            'success' => true,
            'users' => array_map(
                fn(User $user) => $user->toArray(),
                $this->repository->findAll($perPage, $offset)
            ),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    public function updateUser(string $id, string $name, string $email): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email inválido'];
        }

        $existing = $this->repository->findById($id);

        if (!$existing) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }

        $user = new User($name, $email, $id, $existing->getCreatedAt());
        $updated = $this->repository->update($user);

        return $updated
            ? ['success' => true, 'message' => 'Usuario actualizado', 'user' => $user->toArray()]
            : ['success' => false, 'message' => 'Error al actualizar'];
    }

    public function deleteUser(string $id): array
    {
        $deleted = $this->repository->delete($id);

        return $deleted
            ? ['success' => true, 'message' => 'Usuario eliminado']
            : ['success' => false, 'message' => 'Usuario no encontrado'];
    }
}
