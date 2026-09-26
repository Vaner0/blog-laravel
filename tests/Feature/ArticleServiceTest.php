<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ArticleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_cloudinary_public_id_for_new_articles(): void
    {
        $user = User::factory()->create();

        $article = app(ArticleService::class)->creerArticle([
            'titre' => 'Article avec image Cloudinary',
            'contenu' => 'Contenu de l’article.',
            'statut' => 'publie',
            'image' => 'https://res.cloudinary.com/demo/image/upload/article.webp',
            'image_public_id' => 'blog/articles/article',
        ], $user->id);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'image' => 'https://res.cloudinary.com/demo/image/upload/article.webp',
            'image_public_id' => 'blog/articles/article',
        ]);
    }
}
