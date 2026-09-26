<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function inscription(array $donneesInscription): array
    {
        $user = User::create([
            'name'     => $donneesInscription['nom'],
            'email'    => $donneesInscription['email'],
            'password' => Hash::make($donneesInscription['mot_de_passe']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function connexion(array $donneesConnexion): array
    {
        $user = User::where('email', $donneesConnexion['email'])->first();

        if (!$user || !Hash::check($donneesConnexion['mot_de_passe'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Email ou mot de passe incorrect.',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function deconnexion($user): void
    {
        $user->currentAccessToken()->delete();
    }

    
}