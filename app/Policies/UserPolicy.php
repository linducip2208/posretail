<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function update(User $user, User $record): bool
    {
        // Tidak boleh menonaktifkan diri sendiri (anti-lockout).
        if ($user->is($record) && array_key_exists('active', $record->getDirty()) && ! $record->active) {
            return false;
        }

        return true;
    }

    public function delete(User $user, User $record): bool
    {
        return $this->deletable($user, $record);
    }

    public function forceDelete(User $user, User $record): bool
    {
        return $this->deletable($user, $record);
    }

    public function restore(User $user, User $record): bool
    {
        return true;
    }

    protected function deletable(User $user, User $record): bool
    {
        // 1. Tidak boleh hapus akun sendiri.
        if ($user->is($record)) {
            return false;
        }

        // 2. Tidak boleh hapus owner terakhir yang aktif.
        if ($record->isSuperAdmin() && User::otherActiveSuperAdmins($record)->doesntExist()) {
            return false;
        }

        return true;
    }
}
