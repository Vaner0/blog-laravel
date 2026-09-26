<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(ArticleService $articleService): JsonResponse
    {
        return response()->json($articleService->listerArticles());
    }

    public function show(string $slug, ArticleService $articleService): JsonResponse
    {
        return response()->json($articleService->voirArticle($slug));
    }

    public function store(Request $request, ArticleService $articleService): JsonResponse
    {
        $donneesArticle = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'contenu' => ['required', 'string'],
            'statut' => ['sometimes', 'in:brouillon,publie'],
        ]);

        $article = $articleService->creerArticle($donneesArticle, $request->user()->id);

        return response()->json($article, 201);
    }

    public function update(Request $request, Article $article, ArticleService $articleService): JsonResponse
    {
        abort_unless($article->user_id === $request->user()->id, 403);

        $donneesArticle = $request->validate([
            'titre' => ['sometimes', 'required', 'string', 'max:255'],
            'contenu' => ['sometimes', 'required', 'string'],
            'statut' => ['sometimes', 'in:brouillon,publie'],
        ]);

        return response()->json($articleService->modifierArticle($article, $donneesArticle));
    }

    public function destroy(Request $request, Article $article, ArticleService $articleService): JsonResponse
    {
        abort_unless($article->user_id === $request->user()->id, 403);

        $articleService->supprimerArticle($article);

        return response()->json([
            'message' => 'Article supprimé avec succès.',
        ]);
    }
}
