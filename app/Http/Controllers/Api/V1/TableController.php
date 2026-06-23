<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TableResto;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function index(): JsonResponse
    {
        $tables = TableResto::with('tableArea')
            ->where('active', true)
            ->whereIn('status', ['available', 'occupied'])
            ->get();

        return response()->json(['data' => $tables]);
    }
}
