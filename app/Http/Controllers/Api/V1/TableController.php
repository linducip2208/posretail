<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TableResto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /** Gabung beberapa meja ke satu meja target (pindahkan order aktif). */
    public function merge(Request $request): JsonResponse
    {
        $request->validate([
            'source_table_ids' => 'required|array|min:1',
            'source_table_ids.*' => 'exists:tables,id',
            'target_table_id' => 'required|exists:tables,id',
        ]);

        $moved = DB::transaction(function () use ($request) {
            $count = Order::whereIn('table_id', $request->source_table_ids)
                ->whereIn('order_status', ['pending', 'processing', 'completed'])
                ->where('payment_status', '!=', 'paid')
                ->lockForUpdate()
                ->update(['table_id' => $request->target_table_id]);

            TableResto::whereIn('id', $request->source_table_ids)->update(['status' => 'available']);
            TableResto::where('id', $request->target_table_id)->update(['status' => 'occupied']);

            return $count;
        });

        return response()->json(['data' => ['moved_orders' => $moved]]);
    }
}
