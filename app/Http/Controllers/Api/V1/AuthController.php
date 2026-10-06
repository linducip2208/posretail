<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak valid.'],
            ]);
        }

        $deviceName = $request->device_name ?? 'Flutter App';
        // Ability standar 'pos-access' + role (kompatibel token lama yang hanya berisi role)
        $token = $user->createToken($deviceName, ['pos-access', $user->role ?? 'kasir'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => self::userPayload($user),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(self::userPayload($request->user()));
    }

    /** Daftar outlet aktif yang boleh diakses user (sumber untuk seleksi outlet kasir). */
    public function outlets(Request $request): JsonResponse
    {
        $outlets = $request->user()->accessibleOutlets()->get(['id', 'name', 'code']);

        return response()->json(['data' => $outlets]);
    }

    protected static function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'outlets' => $user->accessibleOutlets()->get(['id', 'name', 'code'])->toArray(),
        ];
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
