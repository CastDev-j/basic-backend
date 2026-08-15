<?php

namespace Src\Repositories;

use Src\Models\User;
use Src\Db\Database;
use PDO;

class UserRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function save(User $user): ?string
    {
        $id = uniqid('user-', true);
        $sql = "INSERT INTO users (id, name, email, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        if (!$stmt->execute([$id, $user->getName(), $user->getEmail(), $user->getCreatedAt()])) {
            return null;
        }

        return $id;
    }

    public function findById(string $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? new User($data['name'], $data['email'], $data['id'], $data['created_at']) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $data = $stmt->fetch();

        return $data ? new User($data['name'], $data['email'], $data['id'], $data['created_at']) : null;
    }

    public function findAll(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $users = [];

        while ($data = $stmt->fetch()) {
            $users[] = new User($data['name'], $data['email'], $data['id'], $data['created_at']);
        }

        return $users;
    }

    public function count(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }

    public function updateFields(string $id, array $fields, array $values): bool
    {
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $values[] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }
}
