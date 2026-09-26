<?php

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ArticleService
{
    private int $cacheDuree = 1800;

    public function listerArticles(): mixed
    {
        $articleIds = Cache::remember('articles_ids', $this->cacheDuree, function () {
            return Article::where('statut', 'publie')
                ->latest()
                ->pluck('id')
                ->all();
        });

        return Article::with('auteur')
            ->whereIn('id', $articleIds)
            ->where('statut', 'publie')
            ->latest()
            ->get();
    }

    public function voirArticle(string $slug): mixed
    {
        return Article::with(['auteur', 'commentaires.auteur'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function listerArticlesUtilisateur(User $user, string $statut): LengthAwarePaginator
    {
        return $user->articles()
            ->where('statut', $statut)
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * @return Collection<string, int>
     */
    public function compterArticlesUtilisateurParStatut(User $user): Collection
    {
        return $user->articles()
            ->select('statut')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');
    }

    public function creerArticle(array $data, int $userId): Article
    {
        $article = Article::create([
            'user_id' => $userId,
            'titre' => $data['titre'],
            'contenu' => $data['contenu'],
            'statut' => $data['statut'] ?? 'brouillon',
            'image' => $data['image'] ?? null,
            'image_public_id' => $data['image_public_id'] ?? null,
        ]);

        Cache::forget('articles_ids');

        return $article;
    }

    public function modifierArticle(Article $article, array $data): Article
    {
        $article->update($data);

        Cache::forget('articles_ids');

        return $article;
    }

    public function supprimerArticle(Article $article): void
    {
        Cache::forget('articles_ids');

        $article->delete();
    }
}
