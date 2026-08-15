<?php

namespace Src\Repositories;

use Src\Models\Product;
use Src\Db\Database;
use PDO;

class ProductRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function save(Product $product): ?string
    {
        $id = uniqid('prod-', true);
        $sql = "INSERT INTO products (id, name, price, stock, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);

        if (!$stmt->execute([$id, $product->getName(), $product->getPrice(), $product->getStock(), $product->getCreatedAt()])) {
            return null;
        }

        return $id;
    }

    public function findById(string $id): ?Product
    {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch();

        return $data ? $this->hydrate($data) : null;
    }

    public function findAll(int $limit, int $offset): array
    {
        $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit, $offset]);
        $products = [];

        while ($data = $stmt->fetch()) {
            $products[] = $this->hydrate($data);
        }

        return $products;
    }

    public function count(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    }

    public function updateFields(string $id, array $fields, array $values): bool
    {
        $sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $values[] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    private function hydrate(array $data): Product
    {
        return new Product(
            $data['name'],
            (float) $data['price'],
            (int) $data['stock'],
            $data['id'],
            $data['created_at']
        );
    }
}
