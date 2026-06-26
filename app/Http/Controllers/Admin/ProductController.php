<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\DeepLService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ImageService $imageService,
        private readonly DeepLService $deepLService,
    ) {}

    public function create()
    {
        return view('admin.manage-product.add-product');
    }

    public function store(Request $request)
    {
        Log::info('Request Data:', $request->all());
        Log::info('Has File:', ['has_file' => $request->hasFile('product_image')]);

        $validated = $request->validate([
            'product_name'        => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'product_price'       => 'required|numeric|min:0',
            'ticket_quota'        => 'required|integer|min:1',
            'departure_date'      => 'required|date|after:today',
            'departure_locations' => 'nullable|string',
            'product_image'       => 'required|array',
            'product_image.*'     => 'image|mimes:jpeg,png,jpg,svg|max:2048',
            'whatsapp_link'       => 'nullable|url',
        ]);

        try {
            $imagePaths = [];
            if ($request->hasFile('product_image')) {
                foreach ($request->file('product_image') as $file) {
                    $imagePaths[] = $this->imageService->store($file, 'products');
                }
            }

            $translations = $this->deepLService->translateAll([
                'product_name'        => $validated['product_name'],
                'product_description' => $validated['product_description'] ?? '',
                'departure_locations' => strip_tags($validated['departure_locations'] ?? ''),
            ]);

            $this->productRepository->create([
                'product_name'        => $validated['product_name'],
                'product_description' => $validated['product_description'] ?? null,
                'product_price'       => $validated['product_price'],
                'ticket_quota'        => $validated['ticket_quota'],
                'departure_date'      => $validated['departure_date'],
                'departure_locations' => $validated['departure_locations'] ?? null,
                'product_image'       => $imagePaths,
                'is_published'        => false,
                'translations'        => $translations ?: null,
                'whatsapp_link'       => $validated['whatsapp_link'] ?? null,
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product added successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating product:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    public function index()
    {
        $recentlyAdded = $this->productRepository->getUnpublished();
        $archived      = $this->productRepository->getPublished();
        Log::info('Products count:', [
            'recently_added' => $recentlyAdded->count(),
            'archived'       => $archived->count(),
        ]);
        return view('admin.manage-product.product-list', compact('recentlyAdded', 'archived'));
    }

    public function edit(Product $product)
    {
        return view('admin.manage-product.edit-product', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name'        => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'product_price'       => 'required|numeric|min:0',
            'ticket_quota'        => 'required|integer|min:1',
            'departure_date'      => 'required|date',
            'departure_locations' => 'nullable|string',
            'product_image.*'     => 'image|mimes:jpeg,png,jpg,svg|max:2048',
            'delete_images'       => 'nullable|array',
            'whatsapp_link'       => 'nullable|url',
        ]);

        try {
            $existingImages = $product->product_image ?? [];

            if ($request->has('delete_images')) {
                foreach ($request->delete_images as $imagePath) {
                    $this->imageService->delete($imagePath);
                    $existingImages = array_filter($existingImages, fn($img) => $img !== $imagePath);
                }
                $existingImages = array_values($existingImages);
            }

            if ($request->hasFile('product_image')) {
                foreach ($request->file('product_image') as $file) {
                    $existingImages[] = $this->imageService->store($file, 'products');
                }
            }

            $translations = $this->deepLService->translateAll([
                'product_name'        => $validated['product_name'],
                'product_description' => $validated['product_description'] ?? '',
                'departure_locations' => strip_tags($validated['departure_locations'] ?? ''),
            ]);

            $this->productRepository->update($product, [
                'product_name'        => $validated['product_name'],
                'product_description' => $validated['product_description'] ?? null,
                'product_price'       => $validated['product_price'],
                'ticket_quota'        => $validated['ticket_quota'],
                'departure_date'      => $validated['departure_date'],
                'departure_locations' => $validated['departure_locations'] ?? null,
                'product_image'       => $existingImages,
                'translations'        => $translations ?: $product->translations,
                'whatsapp_link'       => $validated['whatsapp_link'] ?? $product->whatsapp_link,
            ]);

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating product:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()])->withInput();
        }
    }

    public function publish($id)
    {
        $product = $this->productRepository->findById($id);
        abort_if(!$product, 404);

        $this->productRepository->update($product, ['is_published' => true]);
        return back()->with('success', 'Produk berhasil dipublish!');
    }

    public function togglePublish(Product $product)
    {
        $this->productRepository->togglePublish($product);
        return back()->with('success', 'Status produk berhasil diperbarui!');
    }

    public function destroy($id)
    {
        try {
            $product = $this->productRepository->findById($id);
            if (!$product) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }

            $this->productRepository->delete($product);

            return response()->json([
                'success' => true,
                'message' => 'The product has been successfully deleted',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage(),
            ], 500);
        }
    }
}
