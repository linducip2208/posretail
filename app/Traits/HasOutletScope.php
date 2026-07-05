<?php

namespace App\Traits;

trait HasOutletScope
{
    public static function bootHasOutletScope(): void
    {
        static::addGlobalScope(new \App\Scopes\OutletScope);
    }

    public function getOutletColumn(): string
    {
        return 'outlet_id';
    }

    public function isOutletNullable(): bool
    {
        return property_exists($this, 'outletNullable') ? $this->outletNullable : false;
    }
}
