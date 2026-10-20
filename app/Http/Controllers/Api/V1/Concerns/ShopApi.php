<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ShopApi
{
    protected function outletIds(Request $request): array
    {
        return $request->user()->getAccessibleOutletIds();
    }

    protected function denyGroup(Request $request, string $group, string $action = 'view'): ?JsonResponse
    {
        if ($request->user()->canAccess($group, $action)) {
            return null;
        }

        return response()->json(['message' => 'Akses ditolak untuk modul ini.'], 403);
    }

    protected function envelope($paginator): JsonResponse
    {
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    protected function perPage(Request $request): int
    {
        return min(100, max(1, (int) ($request->input('per_page') ?? 50)));
    }
}
