<?php

namespace App\Traits;

use App\Scopes\MultiOutletScope;

trait HasMultiOutletScope
{
    public static function bootHasMultiOutletScope(): void
    {
        static::addGlobalScope(new MultiOutletScope);
    }

    public function getOutletColumns(): array
    {
        return ['from_outlet_id', 'to_outlet_id'];
    }
}
