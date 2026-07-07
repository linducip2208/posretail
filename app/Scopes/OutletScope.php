<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OutletScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            return;
        }

        if (!method_exists($user, 'getAccessibleOutletIds')) {
            return;
        }

        $outletIds = $user->getAccessibleOutletIds();
        if (empty($outletIds)) {
            return;
        }

        $column = $this->getOutletColumn($model);
        if (!$column) {
            return;
        }

        $table = $model->getTable();
        $qualified = "{$table}.{$column}";

        if ($this->isNullable($model)) {
            $builder->where(function (Builder $q) use ($qualified, $outletIds) {
                $q->whereIn($qualified, $outletIds)
                  ->orWhereNull($qualified);
            });
        } else {
            $builder->whereIn($qualified, $outletIds);
        }
    }

    protected function isNullable(Model $model): bool
    {
        if (method_exists($model, 'isOutletNullable')) {
            return $model->isOutletNullable();
        }

        return false;
    }

    protected function getOutletColumn(Model $model): ?string
    {
        if (method_exists($model, 'getOutletColumn')) {
            return $model->getOutletColumn();
        }

        return 'outlet_id';
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutOutletScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
