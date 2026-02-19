<?php

namespace App\Services\Auth;

use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Validation\ValidationException;

class GoogleAuthService
{
    public function verifyIdToken(string $idToken): array
    {
        $client = new GoogleClient([
            'client_id' => config('services.google.client_id'),
        ]);

        $payload = $client->verifyIdToken($idToken);

        if (!$payload) {
            throw ValidationException::withMessages([
                'id_token' => ['Invalid Google token.'],
            ]);
        }

        return $payload;
    }

    public function findOrCreateUser(array $payload): User
    {
        $email = $payload['email'] ?? null;
        $name  = $payload['name'] ?? null;

        if (!$email) {
            throw ValidationException::withMessages([
                'id_token' => ['Google token did not contain an email.'],
            ]);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name'     => $name,
                'email'    => $email,
                'password' => bin2hex(random_bytes(16)), // random
            ]);
        } elseif (!$user->name && $name) {
            $user->update(['name' => $name]);
        }

        if (($payload['email_verified'] ?? false) && !$user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'login' => ['Account is disabled.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        return $user;
    }
}
