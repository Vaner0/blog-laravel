<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function inscription(Request $request, AuthService $authService): JsonResponse
    {
        $donneesInscription = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return response()->json($authService->inscription($donneesInscription), 201);
    }

    public function connexion(Request $request, AuthService $authService): JsonResponse
    {
        $donneesConnexion = $request->validate([
            'email' => ['required', 'email'],
            'mot_de_passe' => ['required', 'string'],
        ]);

        return response()->json($authService->connexion($donneesConnexion));
    }

    public function deconnexion(Request $request, AuthService $authService): JsonResponse
    {
        $authService->deconnexion($request->user());

        return response()->json([
            'message' => 'Déconnexion réussie.',
        ]);
    }
}
