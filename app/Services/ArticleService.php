<?php

namespace App\Services;

use App\Models\Article;
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

    public function creerArticle(array $data, int $userId): Article
    {
        $article = Article::create([
            'user_id' => $userId,
            'titre'   => $data['titre'],
            'contenu' => $data['contenu'],
            'statut'  => $data['statut'] ?? 'brouillon',
            'image'   => $data['image'] ?? null,
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