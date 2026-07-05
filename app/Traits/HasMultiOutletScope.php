<?php

namespace App\Traits;

trait HasMultiOutletScope
{
    public static function bootHasMultiOutletScope(): void
    {
        // Don't add global scope — instead, provide helper methods
    }

    public function getOutletColumns(): array
    {
        return ['from_outlet_id', 'to_outlet_id'];
    }
}
