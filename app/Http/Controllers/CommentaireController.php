<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Commentaire;
use App\Services\CommentaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentaireController extends Controller
{
    public function store(
        Request $request,
        Article $article,
        CommentaireService $commentaireService,
    ): JsonResponse {
        $donneesCommentaire = $request->validate([
            'contenu' => ['required', 'string'],
        ]);

        $commentaire = $commentaireService->creerCommentaire(
            $donneesCommentaire,
            $request->user()->id,
            $article->id,
        );

        return response()->json($commentaire->load('auteur'), 201);
    }

    public function destroy(
        Request $request,
        Commentaire $commentaire,
        CommentaireService $commentaireService,
    ): JsonResponse {
        abort_unless($commentaire->user_id === $request->user()->id, 403);

        $commentaireService->supprimerCommentaire($commentaire);

        return response()->json([
            'message' => 'Commentaire supprimé avec succès.',
        ]);
    }
}
