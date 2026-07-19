<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class MultiOutletScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        if ($user->hasPermission('*')) {
            return;
        }

        $outletIds = $user->getAccessibleOutletIds();
        if (empty($outletIds)) {
            return;
        }

        $columns = method_exists($model, 'getOutletColumns')
            ? $model->getOutletColumns()
            : ['from_outlet_id', 'to_outlet_id'];

        $builder->where(function (Builder $query) use ($columns, $outletIds) {
            foreach ($columns as $column) {
                $query->orWhereIn($column, $outletIds);
            }
        });
    }
}
