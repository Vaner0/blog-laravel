<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Commentaire;
use Illuminate\Support\Facades\Cache;

class CommentaireService
{
    public function creerCommentaire(array $data, int $userId, int $articleId): Commentaire
    {
        $commentaire = Commentaire::create([
            'user_id'    => $userId,
            'article_id' => $articleId,
            'contenu'    => $data['contenu'],
        ]);

        Cache::forget("article_{$commentaire->article->slug}");

        return $commentaire;
    }

    public function supprimerCommentaire(Commentaire $commentaire): void
    {
        Cache::forget("article_{$commentaire->article->slug}");
        $commentaire->delete();
    }
}