<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ShopApi;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use ShopApi;

    public function users(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'sistem')) {
            return $deny;
        }
        $request->validate([
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1',
        ]);

        $ids = $this->outletIds($request);
        $query = User::with('outlets:id,name')
            ->whereHas('outlets', fn ($q) => $q->whereIn('outlets.id', $ids))
            ->latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
        }

        $rows = $query->paginate($this->perPage($request));
        $rows->getCollection()->transform(fn (User $u) => [
            'id' => $u->id, 'name' => $u->name, 'email' => $u->email,
            'role' => $u->role, 'active' => (bool) $u->active,
            'outlets' => $u->outlets->map(fn ($o) => ['id' => $o->id, 'name' => $o->name]),
        ]);

        return $this->envelope($rows);
    }

    public function roles(Request $request): JsonResponse
    {
        if ($deny = $this->denyGroup($request, 'sistem')) {
            return $deny;
        }

        return response()->json(['data' => Role::select(['id', 'name', 'slug', 'description'])->orderBy('id')->get()]);
    }
}
