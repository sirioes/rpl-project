<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function getUnpublished(): Collection;

    public function getPublished(): Collection;

    public function findById(int $id): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): void;

    public function togglePublish(Product $product): void;

    public function delete(Product $product): void;
}
