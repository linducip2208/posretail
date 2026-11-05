<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    use ShopApi;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'category_id' => 'nullable|integer|exists:categories,id',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $query = Product::with(['category', 'unit', 'variants'])
            ->where('active', true);

        if ($request->filled('search')) {
            $search = substr($request->search, 0, 100);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', $search);
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->paginate(min(100, max(1, (int) ($request->per_page ?? 50))));

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'unit', 'variants']);

        return response()->json(['data' => $product]);
    }

    public function byBarcode(Request $request): JsonResponse
    {
        $request->validate(['barcode' => 'required|string|max:100']);

        $product = Product::with(['category', 'unit', 'variants'])
            ->where('barcode', $request->barcode)
            ->orWhereHas('variants', fn ($q) => $q->where('barcode', $request->barcode))
            ->first();

        if (! $product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json(['data' => $product]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'create')) {
            return $deny;
        }

        $data = $request->validate($this->rules());

        if (array_key_exists('outlet_id', $data) && filled($data['outlet_id'] ?? null)
            && ! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $data['image'] = $this->storeImage($request, $data, null);
        unset($data['image_base64'], $data['remove_image']);

        $product = Product::create($data + ['active' => $data['active'] ?? true]);
        $product->load(['category', 'unit', 'variants']);

        return response()->json(['data' => $product], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }

        $data = $request->validate($this->rules($product->id));

        foreach (['outlet_id', 'outletId'] as $key) {
            unset($data[$key]);
        }
        if ($request->filled('outlet_id')
            && ! in_array((int) $request->input('outlet_id'), $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }
        if ($request->filled('outlet_id')) {
            $data['outlet_id'] = (int) $request->input('outlet_id');
        }

        $newImage = $this->storeImage($request, $data, $product->image);
        if ($newImage !== null || $request->boolean('remove_image')) {
            if ($this->isLocalPath($product->image)) {
                Storage::disk('public')->delete($this->normalizePath($product->image));
            }
            $data['image'] = $newImage;
        } else {
            unset($data['image']);
        }
        unset($data['image_base64'], $data['remove_image']);

        $product->update($data);
        $product->load(['category', 'unit', 'variants']);

        return response()->json(['data' => $product]);
    }

    /**
     * Aturan validasi create/update. Mendukung upload multipart `image`
     * maupun base64 `image_base64` (data URL) untuk aplikasi mobile.
     *
     * @return array<string, mixed>
     */
    protected function rules(?int $ignoreId = null): array
    {
        $isUpdate = $ignoreId !== null;

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'outlet_id' => ['nullable', 'integer', 'exists:outlets,id'],
            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($ignoreId)],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('products', 'barcode')->ignore($ignoreId)],
            'cost_price' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:1000000000'],
            'selling_price' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0', 'max:1000000000'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'member_price' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'min_stock' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'max_stock' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'current_stock' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'has_variants' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
            'expired_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'image_base64' => ['nullable', 'string', 'max:8000000'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Simpan gambar ke disk public. Prioritas: file multipart `image`,
     * lalu base64 `image_base64`. Return null bila tidak ada gambar baru.
     */
    protected function storeImage(Request $request, array $data, ?string $oldImage): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('products', 'public');
        }

        if (! empty($data['image_base64'])) {
            $parsed = $this->parseBase64Image($data['image_base64']);

            if (! $parsed) {
                abort(response()->json(['message' => 'Format image_base64 tidak valid.'], 422));
            }

            [$extension, $binary] = $parsed;
            $filename = 'products/'.uniqid('product_', true).'.'.$extension;
            Storage::disk('public')->put($filename, $binary);

            return $filename;
        }

        return null;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    protected function parseBase64Image(string $value): ?array
    {
        if (! preg_match('#^data:image/(jpeg|png|webp);base64,(.+)$#s', trim($value), $m)) {
            return null;
        }

        $binary = base64_decode($m[2], true);

        if ($binary === false || strlen($binary) > 5 * 1024 * 1024) {
            return null;
        }

        $extension = $m[1] === 'jpeg' ? 'jpg' : $m[1];

        return [$extension, $binary];
    }

    protected function isLocalPath(?string $image): bool
    {
        if (blank($image)) {
            return false;
        }

        return ! str_starts_with($image, 'http://') && ! str_starts_with($image, 'https://');
    }

    protected function normalizePath(string $image): string
    {
        return ltrim(preg_replace('#^(/?storage/|/)+#', '', $image) ?? '', '/');
    }
}
