<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_an_article(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/articles', [
            'titre' => 'Mon premier article',
            'contenu' => 'Contenu de mon premier article.',
            'statut' => 'publie',
        ]);

        $response->assertCreated()
            ->assertJsonPath('titre', 'Mon premier article')
            ->assertJsonPath('slug', 'mon-premier-article');

        $this->assertDatabaseHas('articles', [
            'user_id' => $user->id,
            'slug' => 'mon-premier-article',
            'statut' => 'publie',
        ]);
    }

    public function test_public_listing_only_returns_published_articles(): void
    {
        $user = User::factory()->create();
        Article::create([
            'user_id' => $user->id,
            'titre' => 'Article publié',
            'contenu' => 'Visible publiquement.',
            'statut' => 'publie',
        ]);
        Article::create([
            'user_id' => $user->id,
            'titre' => 'Article brouillon',
            'contenu' => 'Non visible publiquement.',
            'statut' => 'brouillon',
        ]);

        $response = $this->getJson('/api/articles');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.titre', 'Article publié');
    }

    public function test_user_cannot_update_another_users_article(): void
    {
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $article = Article::create([
            'user_id' => $author->id,
            'titre' => 'Article privé',
            'contenu' => 'Contenu original.',
        ]);
        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/articles/{$article->id}", [
            'titre' => 'Modification interdite',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'titre' => 'Article privé',
        ]);
    }
}
