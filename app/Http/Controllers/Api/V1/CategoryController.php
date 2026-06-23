<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::with('children')
            ->where('active', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json(['categories' => $categories]);
    }
}
