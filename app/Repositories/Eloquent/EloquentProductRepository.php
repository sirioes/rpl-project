<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly Product $model) {}

    public function getUnpublished(): Collection
    {
        return $this->model->where('is_published', false)->latest()->get();
    }

    public function getPublished(): Collection
    {
        return $this->model->where('is_published', true)->latest()->get();
    }

    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): void
    {
        $product->update($data);
    }

    public function togglePublish(Product $product): void
    {
        $product->is_published = ! $product->is_published;
        $product->save();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
