<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Row-level: kasir hanya boleh akses order di outletnya. */
    public function view(User $user, Order $order): bool
    {
        if ($user->hasPermission('*')) {
            return true;
        }

        return in_array((int) $order->outlet_id, $user->getAccessibleOutletIds(), true);
    }

    public function update(User $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermission('*') || $user->hasPermission('hapus-transaksi');
    }
}
