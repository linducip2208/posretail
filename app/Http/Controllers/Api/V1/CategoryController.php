<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use ShopApi;

    public function index(): JsonResponse
    {
        $categories = Category::with('children')
            ->where('active', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load(['parent', 'children']);

        return response()->json(['data' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'create')) {
            return $deny;
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'active' => 'nullable|boolean',
        ]);

        if (filled($data['outlet_id'] ?? null)
            && ! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? null, $data['name']);

        $category = Category::create($data + ['active' => $data['active'] ?? true]);

        return response()->json(['data' => $category], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'inventori', 'edit')) {
            return $deny;
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'outlet_id' => 'nullable|integer|exists:outlets,id',
            'active' => 'nullable|boolean',
        ]);

        if (array_key_exists('parent_id', $data) && (int) ($data['parent_id'] ?? 0) === $category->id) {
            return response()->json(['message' => 'Kategori tidak bisa menjadi induk dirinya sendiri.'], 422);
        }

        if (array_key_exists('outlet_id', $data) && filled($data['outlet_id'] ?? null)
            && ! in_array((int) $data['outlet_id'], $this->outletIds($request), true)) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke outlet ini.'], 403);
        }

        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $data['slug'] = $this->uniqueSlug(
                $data['slug'] ?? null,
                $data['name'] ?? $category->name,
                $category->id
            );
        }

        $category->update($data);
        $category->load(['parent', 'children']);

        return response()->json(['data' => $category]);
    }

    protected function uniqueSlug(?string $wanted, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($wanted ?: $name) ?: 'kategori';
        $slug = $base;
        $i = 2;

        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
