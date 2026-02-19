<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register user with email/phone + password
     */
    public function register(array $data): User
    {
        // DB defaults handle timezone if null; still set if provided
        $user = User::create([
            'name'     => $data['name'] ?? null,
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'] ?? null,
            'password' => $data['password'],
            'timezone' => $data['timezone'] ?? 'Asia/Dhaka',
        ]);

        return $user;
    }

    /**
     * Login with phone/email in single field + password
     */
    public function login(string $login, string $password): User
    {
        $login = trim($login);

        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        $user = User::query()
            ->when($isEmail, fn ($q) => $q->where('email', $login))
            ->when(!$isEmail, fn ($q) => $q->where('phone', $login))
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Invalid credentials.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Account is disabled.'],
            ]);
        }

        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }

    /**
     * Issue a Sanctum token for mobile
     */
    public function issueToken(User $user, string $deviceName = 'mobile'): string
    {
        return $user->createToken($deviceName)->plainTextToken;
    }

    /**
     * Logout current device
     */
    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();
        if ($token) {
            $token->delete();
        }
    }
}
