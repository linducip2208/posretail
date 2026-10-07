<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

/**
 * Login admin: akun nonaktif ditolak dengan pesan yang sama seperti
 * kredensial salah (tanpa user enumeration).
 */
class Login extends BaseLogin
{
    protected function getCredentialsFromFormData(#[\SensitiveParameter] array $data): array
    {
        return [
            ...parent::getCredentialsFromFormData($data),
            'active' => true,
        ];
    }
}
