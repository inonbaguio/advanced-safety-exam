<?php

namespace OrderManagement\Repositories;

use OrderManagement\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * Find product by ID with relationships
     */
    public function findWithRelations(int $id): ?Product
    {
        return Product::with(['owner', 'template'])
            ->find($id);
    }

    /**
     * Get all products
     */
    public function getAll(): Collection
    {
        return Product::with(['owner', 'template'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get products by owner
     */
    public function getByOwner(int $ownerId): Collection
    {
        return Product::where('owner_id', $ownerId)
            ->with('template')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get products without owner
     */
    public function getWithoutOwner(): Collection
    {
        return Product::whereNull('owner_id')
            ->with('template')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product
     */
    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    /**
     * Delete a product
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}
