<?php

namespace Src\Services;

use Src\Repositories\ProductRepository;
use Src\Models\Product;

class ProductService
{
    private ProductRepository $repository;

    public function __construct()
    {
        $this->repository = new ProductRepository();
    }

    public function create(array $data): array
    {
        $name = $data['name'] ?? '';
        $price = (float) ($data['price'] ?? 0);
        $stock = (int) ($data['stock'] ?? 0);

        if (strlen($name) < 3) {
            return ['success' => false, 'message' => 'Nombre muy corto'];
        }

        if ($price <= 0) {
            return ['success' => false, 'message' => 'Precio inválido'];
        }

        if ($stock < 0) {
            return ['success' => false, 'message' => 'Stock inválido'];
        }

        $product = new Product($name, $price, $stock);
        $id = $this->repository->save($product);

        if ($id !== null) {
            return [
                'success' => true,
                'message' => 'Producto registrado',
                'product' => (new Product($name, $price, $stock, $id, $product->getCreatedAt()))->toArray(),
            ];
        }

        return ['success' => false, 'message' => 'Error al registrar'];
    }

    public function getById(string $id): array
    {
        $product = $this->repository->findById($id);

        return $product
            ? ['success' => true, 'product' => $product->toArray()]
            : ['success' => false, 'message' => 'Producto no encontrado'];
    }

    public function getAll(int $page = 1, int $perPage = 10): array
    {
        $total = $this->repository->count();
        $offset = ($page - 1) * $perPage;

        return [
            'success' => true,
            'products' => array_map(
                fn(Product $product) => $product->toArray(),
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

    public function update(string $id, array $data): array
    {
        if (!$this->repository->findById($id)) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }

        $fields = [];
        $values = [];

        if (isset($data['name'])) {
            if (strlen($data['name']) < 3) {
                return ['success' => false, 'message' => 'Nombre muy corto'];
            }
            $fields[] = 'name = ?';
            $values[] = $data['name'];
        }

        if (isset($data['price'])) {
            $price = (float) $data['price'];
            if ($price <= 0) {
                return ['success' => false, 'message' => 'Precio inválido'];
            }
            $fields[] = 'price = ?';
            $values[] = $price;
        }

        if (isset($data['stock'])) {
            $stock = (int) $data['stock'];
            if ($stock < 0) {
                return ['success' => false, 'message' => 'Stock inválido'];
            }
            $fields[] = 'stock = ?';
            $values[] = $stock;
        }

        if (!$fields) {
            return ['success' => false, 'message' => 'No hay campos para actualizar'];
        }

        if (!$this->repository->updateFields($id, $fields, $values)) {
            return ['success' => false, 'message' => 'Error al actualizar'];
        }

        return ['success' => true, 'message' => 'Producto actualizado', 'product' => $this->repository->findById($id)->toArray()];
    }

    public function delete(string $id): array
    {
        $deleted = $this->repository->delete($id);

        return $deleted
            ? ['success' => true, 'message' => 'Producto eliminado']
            : ['success' => false, 'message' => 'Producto no encontrado'];
    }
}
